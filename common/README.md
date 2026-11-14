# mall/common —— 7 个服务共用的横切代码

**这里不是服务，不用启动、不占端口。** 只放"所有服务都要用、且逻辑完全一样"的代码。

## 目录

```
common/
├── composer.json                      # name = mall/common, psr-4: Mall\Common\ => src/
└── src/
    ├── AppResponse.php                # 统一响应壳 {code,msg,data,trace_id}
    ├── ErrorCode.php                  # 错误码（#[Constants]，所有服务同一套码）
    ├── BusinessException.php          # 业务异常
    ├── Handler/
    │   ├── BusinessExceptionHandler.php    # 业务异常 → 200 + 业务码
    │   ├── ValidationExceptionHandler.php  # 校验失败 → 10001
    │   └── AppExceptionHandler.php         # 兜底 → 500 + error.log 堆栈
    ├── Middleware/
    │   ├── TraceIdMiddleware.php      # trace_id 生成/透传
    │   ├── AuthMiddleware.php         # JWT（HMAC-SHA256）
    │   ├── AccessLogMiddleware.php    # 访问日志
    │   └── IdempotentMiddleware.php   # 幂等（Redis SET NX EX）
    ├── Controller/
    │   └── HealthController.php       # GET /health 探针（第 5 章）
    └── Governance/
        └── HttpServiceRegisterListener.php   # 把 HTTP 服务注册进 Nacos（第 5 章）
```

> 第 5 章加的两个类：`HealthController` 是"每个服务都必须有、逻辑完全一样"的探针；
> `HttpServiceRegisterListener` 是因为 Hyperf 只会自动注册 `#[RpcService]` 的 RPC 服务，
> 纯 HTTP 服务得自己注册（详见第 5 章 5.6）。

## 怎么挂到服务上

```powershell
wsl -d Ubuntu-24.04 -- docker exec hyperf-mall sh /opt/www/mall/attach-common.sh all
```

脚本做三件事（幂等，可重复跑）：

1. 服务 `composer.json` 的 `autoload.psr-4` 加 `"Mall\\Common\\": "../common/src/"`；
2. 服务 `config/autoload/annotations.php` 的 `scan.paths` 加 `BASE_PATH . '/../common/src'`
   （**不加这行，`#[Constants]` 的 `@Message` 收集不到，`ErrorCode::getMessage()` 返回 "Undefined"**，实测）；
3. `composer dump-autoload`（离线，2 秒）。

**顺序很重要**：先跑这个脚本，再去改各服务的 `exceptions.php` / `middlewares.php`，最后 `restart.sh <服务名>`。
反过来（先配 `exceptions.php`）会报
`InvalidArgumentException: Invalid exception handler Mall\Common\Handler\Xxx`——
因为 `Mall\Common\` 还没进 autoload，容器 `has()` 解析不到这个类。

**不要用 `php -r "class_exists(...)"` 判断有没有挂上**：CLI 里没 `require vendor/autoload.php`，必然返回 false。
判断标准是这条：

```powershell
wsl -d Ubuntu-24.04 -- docker exec hyperf-mall sh -c "grep Mall /opt/www/mall/<svc>/vendor/composer/autoload_psr4.php"
# 期望：'Mall\\Common\\' => array($baseDir . '/../common/src'),
```

## 什么该放这里，什么不该

| ✅ 放 common | ❌ 不放 common |
|---|---|
| 所有服务返回格式完全一样的响应壳 | 某个服务的业务逻辑（下单、扣库存、支付状态机） |
| 全站统一的错误码表 | 只在 1 个服务用的 Model / Controller |
| 横切中间件（trace/日志/鉴权/幂等） | 有业务含义的中间件（比如"下单前检查用户等级"） |
| 通用工具（限流器、分布式锁、指标探针） | 各服务不同的配置（`middlewares.php` / `exceptions.php` / `logger.php`） |

一句话：**共享实现，不共享策略。**

## 已知边界

- 共享包一改，7 个服务同时变（改完要重启对应服务，或 `restart.sh`）。
- 共享包里出 fatal error，7 个服务一起挂——所以这里的代码要有测试。
- 想让每个服务锁不同版本，就别用这套，改用私有 composer 仓库发 `mall/common:^1.0`（第 4 章 4.6 方案 C）。

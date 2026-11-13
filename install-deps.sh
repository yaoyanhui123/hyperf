#!/bin/sh
# 1.5 依赖安装助手（可重复执行，已装过会自动跳过不重复装）
# 用法:
#   sh install-deps.sh all              # 6 个业务服务一次装完（不含 gateway）
#   sh install-deps.sh gateway          # 网关只装注册发现两件
#   sh install-deps.sh order-service    # 单独补某一个
set -e
MALL=/opt/www/mall
BUSINESS="user-service product-service inventory-service order-service pay-service marketing-service"

install_full() {
  echo "===== $1 ====="
  cd "$MALL/$1" || { echo "no such service: $1"; return 1; }
  composer require \
    'hyperf/json-rpc:~3.2.0' \
    'hyperf/rpc-client:~3.2.0' \
    'hyperf/rpc-server:~3.2.0' \
    'hyperf/service-governance:~3.2.0' \
    'hyperf/service-governance-nacos:~3.2.0' \
    'hyperf/validation:~3.2.0' \
    -W --no-interaction
}

install_gateway() {
  echo "===== gateway（只装注册发现，不装 rpc 那套） ====="
  cd "$MALL/gateway" || { echo "no such service: gateway"; return 1; }
  composer require \
    'hyperf/service-governance:~3.2.0' \
    'hyperf/service-governance-nacos:~3.2.0' \
    -W --no-interaction
}

case "${1:-}" in
  all)
    # 镜像只需配置一次
    composer config -g repos.packagist composer https://mirrors.aliyun.com/composer/ >/dev/null 2>&1 || true
    for s in $BUSINESS; do
      install_full "$s"
    done
    echo ""
    echo "业务服务装完。gateway 还需另跑一次: sh $0 gateway"
    ;;
  gateway)
    install_gateway
    ;;
  "")
    echo "用法: sh $0 all | gateway | <service-name>"
    ;;
  *)
    install_full "$1"
    ;;
esac

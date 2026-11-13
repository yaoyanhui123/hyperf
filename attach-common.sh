#!/bin/sh
# 把共享包 mall/common 挂到各服务上（可重复执行，幂等）
# 用法:
#   sh attach-common.sh all                         # 7 个服务全挂
#   sh attach-common.sh user-service                # 只挂一个
#   MALL_BASE=/tmp/act sh attach-common.sh svc      # 用临时副本测试
#
# 做三件事:
#   1. composer.json 的 autoload.psr-4 里加 "Mall\\Common\\": "../common/src/"
#   2. config/autoload/annotations.php 的 scan.paths 加上 common/src
#      （不加的话 #[Constants] 的 @Message 拿不到，ErrorCode::getMessage() 会返回 "Undefined")
#   3. composer dump-autoload（离线，不需要联网）
BASE="${MALL_BASE:-/opt/www/mall}"

if [ -z "$1" ] || [ "$1" = "all" ]; then
  set -- user-service product-service inventory-service order-service pay-service marketing-service gateway
fi

if [ ! -d "$BASE/common/src" ]; then
  echo "缺少 $BASE/common/src（共享包没放好）"; exit 1
fi

for s in "$@"; do
  d="$BASE/$s"
  if [ ! -f "$d/composer.json" ]; then
    echo "skip $s (no composer.json)"; continue
  fi

  # 1) autoload psr-4（用 chr(92) 拼反斜杠，绕开 shell/PHP 的转义地狱）
  php -r '
$dir = $argv[1];
$f = $dir . "/composer.json";
$j = json_decode(file_get_contents($f), true);
$ns = "Mall" . chr(92) . "Common" . chr(92);
$j["autoload"]["psr-4"][$ns] = "../common/src/";
file_put_contents($f, json_encode($j, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . chr(10));
' "$d" || { echo "FAIL composer.json $s"; exit 1; }

  # 2) 注解扫描路径
  cat > "$d/config/autoload/annotations.php" <<'PHP'
<?php

declare(strict_types=1);

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
            // 共享包（服务目录的上一级 common/src）也要扫，否则常量注解拿不到
            BASE_PATH . '/../common/src',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
    ],
];
PHP

  # 3) 重新生成 autoload
  ( cd "$d" && composer dump-autoload --no-scripts -q ) || { echo "FAIL dump-autoload $s"; exit 1; }

  # 4) 自检
  ( cd "$d" && php -r '
require "vendor/autoload.php";
$ok = class_exists("Mall\Common\AppResponse") && class_exists("Mall\Common\Middleware\TraceIdMiddleware");
echo ($ok ? "OK   " : "FAIL ") . "common attached";
echo PHP_EOL;
' ) || exit 1
  echo "  -> $s"
done

echo "done. 下一步：各服务自己改 config/autoload/middlewares.php / exceptions.php（顺序和开关按服务定，所以不脚本化）"

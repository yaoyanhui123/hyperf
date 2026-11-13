#!/bin/sh
# 初始化 7 个服务骨架（可重复执行）
# 用法（容器内）: sh init-mall.sh
set -e
BASE=/opt/www/mall
SKEL=/opt/www/skeleton
mkdir -p "$BASE"

for s in gateway user-service product-service inventory-service order-service pay-service marketing-service; do
  if [ -d "$BASE/$s" ]; then
    echo "skip $s (exists)"
    continue
  fi
  cp -a "$SKEL" "$BASE/$s"
  # runtime 整个删掉：里面有 skeleton 的 hyperf.pid（如果它正在跑，这个 pid 会让新服务误判“已在运行”）
  rm -rf "$BASE/$s/.git" "$BASE/$s/.idea" "$BASE/$s/runtime"
  mkdir -p "$BASE/$s/runtime/logs"
  rm -f  "$BASE/$s/test/Feature/HttpDemoTest.php"
  sed -i "s#\"name\": \"hyperf/hyperf-skeleton\"#\"name\": \"mall/$s\"#" "$BASE/$s/composer.json"
  echo "created $s"
done

echo "done. next: 为每个服务写 .env（见教程 1.7）"

#!/bin/sh
# 重启: sh restart.sh order-service
BASE="${MALL_BASE:-/opt/www/mall}"
DIR=$(cd "$(dirname "$0")" && pwd)

svc="$1"
if [ -z "$svc" ]; then
  echo "usage: sh restart.sh <service>"; exit 1
fi

MALL_BASE="$BASE" sh "$DIR/stop.sh" "$svc"
sleep 1
MALL_BASE="$BASE" sh "$DIR/start.sh" "$svc"

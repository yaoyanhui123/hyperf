#!/bin/sh
# 优雅停止（SIGTERM，等在途请求处理完）
# 用法: sh stop.sh order-service
BASE="${MALL_BASE:-/opt/www/mall}"

svc="$1"
if [ -z "$svc" ]; then
  echo "usage: sh stop.sh <service>"; exit 1
fi
cd "$BASE/$svc" || { echo "no such service: $BASE/$svc"; exit 1; }
DIR=$(pwd)

# 进程真的活着吗？
# 注意：/proc 目录存在不代表进程活着——僵尸进程（Z 状态）也会留在 /proc，
# 而本容器 PID 1 是 `tail -f /dev/null`，不会回收子进程，所以必须排除 Z。
is_alive() {
  _p="$1"
  [ -n "$_p" ] || return 1
  [ -d "/proc/$_p" ] || return 1
  _st=$(awk '{print $3}' "/proc/$_p/stat" 2>/dev/null)
  [ "$_st" != "Z" ]
}

pid=$(cat runtime/hyperf.pid 2>/dev/null)
# pid 必须真活着，且 cwd 指向本服务目录（防止误杀复制过来的旧 pid）
if ! is_alive "$pid" || [ "$(readlink "/proc/$pid/cwd" 2>/dev/null)" != "$DIR" ]; then
  echo "$svc not running"
  rm -f runtime/hyperf.pid
  exit 0
fi

T0=$(date +%s)
kill -TERM "$pid" 2>/dev/null || true

# 最多等 30 秒
i=0
while is_alive "$pid" && [ "$i" -lt 60 ]; do
  sleep 0.5
  i=$((i + 1))
done
rm -f runtime/hyperf.pid

T1=$(date +%s)
if is_alive "$pid"; then
  echo "$svc still alive after 30s (pid=$pid)，可能有阻塞协程；确认后可 kill -9 $pid"
  exit 1
fi
echo "stopped $svc (pid=$pid, $((T1 - T0))s)"

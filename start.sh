#!/bin/sh
# 启动服务（后台）
# 用法: sh start.sh order-service
# 说明: Hyperf 3.2 的 start 命令已移除 -d/--daemonize，后台只能 nohup + &
BASE="${MALL_BASE:-/opt/www/mall}"

svc="$1"
if [ -z "$svc" ]; then
  echo "usage: sh start.sh <service>"; exit 1
fi
cd "$BASE/$svc" || { echo "no such service: $BASE/$svc"; exit 1; }
DIR=$(pwd)

# 判断 pid 文件里的进程是不是“本目录启动的那个”：
# cp -a 复制工程时会把别人的 runtime/hyperf.pid 一起带过来，直接信任会误判“已在运行”；
# 僵尸进程（Z）也要排除——容器里 PID 1 不回收子进程，Z 会一直留在 /proc
is_ours() {
  _p="$1"
  [ -n "$_p" ] || return 1
  [ -d "/proc/$_p" ] || return 1
  _st=$(awk '{print $3}' "/proc/$_p/stat" 2>/dev/null)
  [ "$_st" != "Z" ] || return 1
  _cwd=$(readlink "/proc/$_p/cwd" 2>/dev/null)
  [ "$_cwd" = "$DIR" ]
}

pid=$(cat runtime/hyperf.pid 2>/dev/null)
if is_ours "$pid"; then
  echo "$svc already running, pid=$pid"
  exit 0
fi
rm -f runtime/hyperf.pid

# 注解/配置改动必须清缓存，否则“改了不生效”
rm -rf runtime/container
mkdir -p runtime/logs

nohup php bin/hyperf.php start > runtime/logs/stdout.log 2>&1 &

# 等 pid 文件出现（最多 15 秒；第一次启动要清注解缓存、装进程，会慢一点）
i=0
while [ ! -f runtime/hyperf.pid ] && [ "$i" -lt 30 ]; do
  sleep 0.5
  i=$((i + 1))
done

if [ -f runtime/hyperf.pid ]; then
  echo "$svc started, pid=$(cat runtime/hyperf.pid)"
  tail -n 3 runtime/logs/hyperf.log 2>/dev/null
else
  echo "$svc FAILED to start, see runtime/logs/stdout.log"
  tail -n 20 runtime/logs/stdout.log 2>/dev/null
  exit 1
fi

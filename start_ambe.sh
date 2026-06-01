#!/bin/bash
sleep 10
BASEDIR="/home/pi/AMBE_SERVER"
INI="$BASEDIR/AMBEserver.ini"
LOG="$BASEDIR/ambe.log"
PID="$BASEDIR/ambe.pid"

SPEED=$(grep '^velocidad=' "$INI" | cut -d= -f2)
TTY=$(grep '^puerto=' "$INI" | cut -d= -f2)
PORT=$(grep '^puertonet=' "$INI" | cut -d= -f2)

fuser -k "$TTY" 2>/dev/null
sleep 1

nohup "$BASEDIR/AMBEserver" -s "$SPEED" -i "$TTY" -p "$PORT" >> "$LOG" 2>&1 &
echo $! > "$PID"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] >>> Autoarranque cron PID $!" >> "$LOG"
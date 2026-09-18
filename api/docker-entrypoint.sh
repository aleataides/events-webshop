#!/bin/sh
set -e

export XDEBUG_MODE="off"
[ "$XDEBUG" = "true" ] && export XDEBUG_MODE="debug"

exec "$@"

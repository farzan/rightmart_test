#!/usr/bin/env bash

SCRIPT_DIR="$(dirname "$(realpath "$0")")"
cd "$SCRIPT_DIR/.."

mkdir -p data/esdata
mkdir -p data/nginx/logs
chmod 777 -R data

chmod +x docker/wait_for_elastic.sh
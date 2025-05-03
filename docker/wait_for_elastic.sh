#!/bin/bash

# This script waits for the ElasticSearch to become up and ready.
# If ES does not get ready in 120 seconds, it fails.

timeout_seconds=120  # Maximum wait time in seconds
start_time=$(date +%s)

echo "Waiting for Elasticsearch to become ready (timeout: ${timeout_seconds} seconds)..."
while true; do
  if curl -s --max-time 5 -o /dev/null -w "%{http_code}" http://${ELASTICSEARCH_HOST}:${ELASTICSEARCH_PORT}/_cluster/health | grep -q "200"; then
    echo "Elasticsearch is ready!"
    break
  fi
  elapsed_time=$(( $(date +%s) - start_time ))
  if (( elapsed_time >= timeout_seconds )); then
    echo "Timeout waiting for Elasticsearch!"
    exit 1
  fi
  echo -n "."
  sleep 1
done
#!/usr/bin/env bash
set -euo pipefail

echo "Removing cclsp MCP server registration..."
CLAUDECODE= claude mcp remove --scope user cclsp

echo "Done. You may also want to remove the Docker image:"
echo "  docker rmi cclsp-phpactor:latest"

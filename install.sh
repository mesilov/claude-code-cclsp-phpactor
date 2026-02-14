#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "Building Docker image cclsp-phpactor:latest..."
docker build -t cclsp-phpactor:latest "${SCRIPT_DIR}"

if [ ! -f "${SCRIPT_DIR}/cclsp.json" ]; then
    cp "${SCRIPT_DIR}/cclsp.json.example" "${SCRIPT_DIR}/cclsp.json"
    echo "Created cclsp.json from template. Edit it to add your projects."
fi

echo "Registering cclsp as user-scope MCP server..."
CLAUDECODE= claude mcp add -s user cclsp -- env "CCLSP_CONFIG_PATH=${SCRIPT_DIR}/cclsp.json" npx -y cclsp@latest

echo "Done. Edit cclsp.json to add your projects, then restart Claude Code."

#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="${1:?Usage: phpactor-lsp.sh /path/to/project}"

if [ ! -d "${PROJECT_DIR}" ]; then
    echo "Error: Project directory '${PROJECT_DIR}' does not exist" >&2
    exit 1
fi

CACHE_DIR="${PROJECT_DIR}/var/cache/phpactor"
mkdir -p "${CACHE_DIR}"

exec docker run --rm -i \
    --user "$(id -u):$(id -g)" \
    -e "HOME=${CACHE_DIR}" \
    -e "PHPACTOR_UNCONDITIONAL_TRUST=1" \
    -v "${PROJECT_DIR}:${PROJECT_DIR}:ro" \
    -v "${CACHE_DIR}:${CACHE_DIR}" \
    -w "${PROJECT_DIR}" \
    cclsp-phpactor:latest \
    phpactor language-server

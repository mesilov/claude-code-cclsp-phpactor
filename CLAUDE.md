# cclsp-phpactor

Centralized Phpactor LSP configuration for Claude Code across multiple PHP projects.

## Purpose

Eliminates per-project duplication of MCP LSP setup (Dockerfile, wrapper script, cclsp.json, docker-compose service, Makefile target). One repository serves all PHP projects — update with `git pull && make build`.

## Architecture

```
~/.claude.json (user-scope MCP)
  → npx cclsp@latest
    → ~/tools/cclsp-phpactor/cclsp.json
      → ~/tools/cclsp-phpactor/phpactor-lsp.sh <project-path>
        → docker run cclsp-phpactor:latest
          → phpactor language-server
```

cclsp routes LSP requests to the correct Phpactor instance by matching `rootDir` to the file being queried. Each project gets its own Docker container (stdio, no port conflicts). Cache is isolated per project in `<project>/var/cache/phpactor/`.

## Key Files

| File | Role |
|---|---|
| `Dockerfile` | PHP 8.4 + Phpactor image (`cclsp-phpactor:latest`) |
| `phpactor-lsp.sh` | Wrapper script — takes project path as `$1`, runs Phpactor in Docker |
| `cclsp.json` | Multi-project config for cclsp (gitignored, user-specific) |
| `cclsp.json.example` | Template for `cclsp.json` |
| `install.sh` | First-time setup: build image + register cclsp as user-scope MCP server |
| `uninstall.sh` | Remove cclsp registration from Claude Code |
| `Makefile` | Build/install/uninstall targets |
| `plan.md` | Detailed implementation plan (in Russian) |

## Commands

```bash
make build      # Build Docker image cclsp-phpactor:latest
make install    # Build + run install.sh (register MCP server)
make uninstall  # Run uninstall.sh (remove MCP server registration)
```

## Multi-Project Configuration

Edit `cclsp.json` to add/remove projects. Each entry needs `extensions`, `command` (path to wrapper + project path), and `rootDir`:

```json
{
  "servers": [
    {
      "extensions": ["php"],
      "command": ["/absolute/path/to/cclsp-phpactor/phpactor-lsp.sh", "/path/to/project-1"],
      "rootDir": "/path/to/project-1"
    }
  ]
}
```

After editing `cclsp.json`, restart Claude Code to pick up changes.

## Per-Project Requirements

Each PHP project should have:
- `.phpactor.yml` — project-specific indexing settings
- `var/cache/phpactor/` in `.gitignore` — Phpactor cache directory

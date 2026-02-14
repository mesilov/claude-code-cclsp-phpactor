# cclsp-phpactor

Centralized Phpactor LSP configuration for Claude Code. A single repository serves all your PHP projects — no need to duplicate settings in each one.

## Requirements

- [Docker](https://docs.docker.com/get-docker/)
- [Node.js](https://nodejs.org/) (for `npx`)
- [Claude Code CLI](https://docs.anthropic.com/en/docs/claude-code) (`claude` in PATH)

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/<your-username>/cclsp-phpactor.git ~/tools/cclsp-phpactor
cd ~/tools/cclsp-phpactor

# 2. Build the Docker image and register the MCP server
make install
```

`make install` performs:
- Building the `cclsp-phpactor:latest` Docker image (PHP 8.4 + Phpactor)
- Creating `cclsp.json` from the template (if the file doesn't exist yet)
- Registering cclsp as a user-scope MCP server in Claude Code

After installation, edit `cclsp.json` and restart Claude Code.

## Adding a Project

### 1. Configure cclsp.json

Add an entry for each PHP project:

```json
{
  "servers": [
    {
      "extensions": ["php"],
      "command": ["/Users/me/tools/cclsp-phpactor/phpactor-lsp.sh", "/Users/me/work/my-project"],
      "rootDir": "/Users/me/work/my-project"
    }
  ]
}
```

All paths must be absolute. The `rootDir` field tells cclsp which server to use for files in that directory.

### 2. Prepare the project

Create `.phpactor.yml` in the root of your PHP project with indexing settings:

```yaml
# .phpactor.yml
indexer.enabled_watchers: []
```

Add the Phpactor cache to the project's `.gitignore`:

```
var/cache/phpactor/
```

### 3. Restart Claude Code

Changes to `cclsp.json` are picked up only after a restart.

## Updating

```bash
cd ~/tools/cclsp-phpactor
git pull && make build
```

## Uninstalling

```bash
cd ~/tools/cclsp-phpactor
make uninstall
```

To also remove the Docker image:

```bash
docker rmi cclsp-phpactor:latest
```

## How It Works

```
~/.claude.json (user-scope MCP)
  → npx cclsp@latest
    → ~/tools/cclsp-phpactor/cclsp.json
      → ~/tools/cclsp-phpactor/phpactor-lsp.sh <project-path>
        → docker run cclsp-phpactor:latest
          → phpactor language-server
```

- Claude Code connects to cclsp via MCP (stdio)
- cclsp reads `cclsp.json` and routes LSP requests to the appropriate Phpactor instance based on `rootDir`
- Each project runs in a separate Docker container with no port conflicts
- Project code is mounted read-only; cache is stored in `<project>/var/cache/phpactor/`
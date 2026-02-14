# cclsp-phpactor

Централизованная конфигурация Phpactor LSP для Claude Code. Один репозиторий обслуживает все ваши PHP-проекты — без дублирования настроек в каждом из них.

## Требования

- [Docker](https://docs.docker.com/get-docker/)
- [Node.js](https://nodejs.org/) (для `npx`)
- [Claude Code CLI](https://docs.anthropic.com/en/docs/claude-code) (`claude` в PATH)

## Установка

```bash
# 1. Клонируйте репозиторий
git clone https://github.com/<your-username>/cclsp-phpactor.git ~/tools/cclsp-phpactor
cd ~/tools/cclsp-phpactor

# 2. Соберите Docker-образ и зарегистрируйте MCP-сервер
make install
```

`make install` выполняет:
- сборку Docker-образа `cclsp-phpactor:latest` (PHP 8.4 + Phpactor)
- создание `cclsp.json` из шаблона (если файл ещё не существует)
- регистрацию cclsp как user-scope MCP-сервера в Claude Code

После установки отредактируйте `cclsp.json` и перезапустите Claude Code.

## Добавление проекта

### 1. Настройте cclsp.json

Добавьте запись для каждого PHP-проекта:

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

Все пути должны быть абсолютными. Поле `rootDir` указывает cclsp, какой сервер использовать для файлов из данной директории.

### 2. Подготовьте проект

В корне PHP-проекта создайте `.phpactor.yml` с настройками индексации:

```yaml
# .phpactor.yml
indexer.enabled_watchers: []
```

Добавьте кеш Phpactor в `.gitignore` проекта:

```
var/cache/phpactor/
```

### 3. Перезапустите Claude Code

Изменения в `cclsp.json` подхватываются только после перезапуска.

## Обновление

```bash
cd ~/tools/cclsp-phpactor
git pull && make build
```

## Удаление

```bash
cd ~/tools/cclsp-phpactor
make uninstall
```

При необходимости удалите Docker-образ вручную:

```bash
docker rmi cclsp-phpactor:latest
```

## Как это работает

```
~/.claude.json (user-scope MCP)
  → npx cclsp@latest
    → ~/tools/cclsp-phpactor/cclsp.json
      → ~/tools/cclsp-phpactor/phpactor-lsp.sh <project-path>
        → docker run cclsp-phpactor:latest
          → phpactor language-server
```

- Claude Code подключается к cclsp через MCP (stdio)
- cclsp читает `cclsp.json` и маршрутизирует LSP-запросы к нужному экземпляру Phpactor по `rootDir`
- Каждый проект запускается в отдельном Docker-контейнере, без конфликтов портов
- Код проекта монтируется в read-only режиме, кеш хранится в `<project>/var/cache/phpactor/`

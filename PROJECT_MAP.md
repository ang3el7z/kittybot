# Project Map

## Purpose

Telegram bot for managing a single-host VPN stack through Docker Compose.

## Entrypoints

- `app/index.php` - HTTP entrypoint for Telegram webhook, PAC/subscription, webapp callbacks.
- `app/init.php` - startup initialization: webhook, commands, queues, port sync.
- `app/service.php` - background/service maintenance tasks.
- `app/cron.php` - scheduled bot maintenance.
- `makefile` - local/server compose lifecycle.
- `scripts/init.sh` - install script for fresh server.

## Main Runtime

- `docker-compose.yml` defines core and optional services.
- `app/bot.php` is the legacy monolith. New code should move behavior into `app/src/*` and keep `bot.php` as an integration shell until it is small enough to retire.
- `/config` contains generated service configs consumed by containers.
- `/data/kittybot.sqlite` is the new source of truth for bot state.
- `docker-compose.override.yml` is generated runtime state and is intentionally ignored.

## Service Groups

Core, not disableable:

- `php`
- `service`
- `ng`
- `up`

Optional:

- `wg`
- `wg1`
- `xr`
- `ad`
- `tg`
- `oc`
- `np`
- `wp`
- `proxy`
- `ss`
- `dnstt`
- `hy`

## Verification

- `php -l <file>` for changed PHP.
- `docker compose config` for compose changes.
- SQLite migration smoke through `app/bin/migrate.php`.

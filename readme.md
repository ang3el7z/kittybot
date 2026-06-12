Telegram bot to manage a single-host VPN stack from Telegram.

- VLESS (Reality OR Websocket)
- NaiveProxy
- OpenConnect
- Wireguard
- Amnezia
- AdguardHome
- MTProto
- PAC
- automatic ssl

---
environment: ubuntu 22.04/24.04, debian 11/12

## Install:

```shell
wget -O- https://raw.githubusercontent.com/ang3el7z/kittybot/main/scripts/init.sh | sh -s YOUR_TELEGRAM_BOT_KEY main
```
#### Restart:
```shell
make r
```
#### autoload:
```shell
make cron
```

## Runtime state

- Bot database: `/data/kittybot.sqlite`
- Local config template: `.env.example`
- Local runtime config: `.env`
- Generated compose override: `docker-compose.override.yml`
- Generated service-state override: `docker-compose.services.yml`

Optional containers can be disabled from the bot in `Settings -> Services`.
Disabled containers stay disabled across restarts until enabled again.

## Backups and migrations

- Bot export backups are available in Telegram: `Settings -> Backup -> backup history`.
- The SQLite database is stored on the host in `./data/kittybot.sqlite`.
- Migrations run automatically when the bot first opens the database.
- Manual migration smoke test from a running stack: `docker compose exec php php bin/migrate.php`.

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

Optional containers can be disabled from the bot in `Settings -> Services`.
Disabled containers stay disabled across restarts until enabled again.

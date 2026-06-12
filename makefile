b:
	docker compose build
u: # запуск контейнеров
	$(eval IP := $(shell hostname -I | awk '{print $$1}'))
	bash ./update/update.sh &
	touch ./override.env ./docker-compose.override.yml ./docker-compose.services.yml ./config/location.conf ./config/override.conf
	IP=$(IP) VER=$(shell git describe --tags --always --dirty 2>/dev/null || echo dev) docker compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.services.yml --env-file ./.env --env-file ./override.env up -d --force-recreate
d: # остановка контейнеров
	-kill -9 $(shell cat ./update/update_pid) > /dev/null
	docker compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.services.yml down --remove-orphans
dv: # остановка контейнеров
	docker compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.services.yml down -v
r: d u
ps: # список контейнеров
	docker compose ps
l: # логи из контейнеров
	docker compose logs
php: # консоль сервиса
	docker compose exec php /bin/sh
wg: # консоль сервиса
	docker compose exec wg /bin/sh
wg1: # консоль сервиса
	docker compose exec wg1 /bin/sh
ss: # консоль сервиса
	docker compose exec ss /bin/sh
ng: # консоль сервиса
	docker compose exec ng /bin/sh
np: # консоль сервиса
	docker compose exec np /bin/sh
up: # консоль сервиса
	docker compose exec up /bin/sh
ad: # консоль сервиса
	docker compose exec ad /bin/sh
wp: # консоль сервиса
	docker compose exec wp bash
proxy: # консоль сервиса
	docker compose exec proxy /bin/sh
tg: # консоль сервиса
	docker compose exec tg /bin/sh
dnstt: # консоль сервиса
	docker compose exec dnstt /bin/sh
hy: # консоль сервиса
	docker compose exec hy /bin/sh
xr: # консоль сервиса
	docker compose exec xr /bin/sh
oc: # консоль сервиса
	docker compose exec oc /bin/sh
service: # консоль сервиса
	docker compose exec service /bin/sh
delete:
	make d
	docker system prune -f -a
	docker volume prune -f -a
	rm -rf /root/kittybot
push:
	docker compose push
s:
	git status -su
c:
	git add config/
	git checkout .
	git reset
webhook:
	docker compose exec php php checkwebhook.php
reset:
	make d
	git reset --hard
	git clean -fd
	docker volume rm kittybot_adguard kittybot_warp
	make u
backup:
	docker compose exec php php backup.php > backup.json
cron: # установка задачи в cron для автозапуска при перезагрузке
	@(crontab -l 2>/dev/null | grep -v "cd /root/kittybot && make r"; echo "@reboot cd /root/kittybot && make r") | crontab -
uncron: # удаление задачи из cron
	@crontab -l 2>/dev/null | grep -v "cd /root/kittybot && make r" | crontab -

path=`pwd`
sed "s|path|$path|g" "$path/scripts/vpnbot.service" > /etc/systemd/system/kittybot.service
systemctl daemon-reload
systemctl enable kittybot

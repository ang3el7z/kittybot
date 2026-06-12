<?php

require __DIR__ . '/timezone.php';
require __DIR__ . '/src/Autoload.php';

require __DIR__ . '/bot.php';
require __DIR__ . '/config.php';
require __DIR__ . '/i18n.php';
if ($c['debug']) {
    require __DIR__ . '/debug.php';
}

$bot = new Bot($c['key'], $i);
(new \KittyBot\Storage\MigrationRunner((new \KittyBot\Storage\Database())->pdo()))->migrate();
$bot->cleanQueue();
$bot->setwebhook();
$bot->syncPortClients();
$bot->setcommands();

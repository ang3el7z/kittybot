<?php

require __DIR__ . '/../src/Autoload.php';

use KittyBot\Storage\Database;
use KittyBot\Storage\MigrationRunner;

$db = new Database($argv[1] ?? null);
(new MigrationRunner($db->pdo()))->migrate();

echo "migrated\n";

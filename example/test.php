<?php

require "../vendor/autoload.php";

$connection = new \ByJG\AnyDataset\ConnectionManagement('mysql://migrateuser:migratepwd@localhost/migratedatabase');

$migration = new \ByJG\DbMigration\Migration($connection, '.');

$migration->reset();


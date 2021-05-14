<?php

use VfkImporter\DbConfig;
use VfkImporter\ExecuteException;
use VfkImporter\Importer;
use VfkImporter\PostgresExecutor;

require '../src/DbConfig.php';
require '../src/Importer.php';
require '../src/Parser.php';
require '../src/IRow.php';
require '../src/DataRow.php';
require '../src/BlockRow.php';
require '../src/IExecutor.php';
require '../src/PostgresExecutor.php';
require '../src/ColumnDefinition.php';
require '../src/ExecuteException.php';
require '../src/Vfk.php';

$dbConfig = new DbConfig();
$dbConfig->host = 'localhost';
$dbConfig->port = '5432';
$dbConfig->database = 'liquibase_test';
$dbConfig->username = 'liquibase_test_user';
$dbConfig->password = 'liquibase_test_user_password';

try {
    $executor = new PostgresExecutor($dbConfig);
    $executor->setSchema("vfk");

    // Katastralni mapa
    $importer = new Importer($executor);
    $file = __DIR__ . '/km_chodov.vfk';
    $importer->run($file);

    // Geometricke plany
    $file = __DIR__ . '/gp_chodov.vfk';
    $importer->run($file);
} catch (ExecuteException $e) {
    echo $e->getMessage();
}


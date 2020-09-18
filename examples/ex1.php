<?php

use VfkImporter\DbConfig;
use VfkImporter\Importer;
use VfkImporter\PostgreExecutor;

require '../vendor/autoload.php';
require '../src/DbConfig.php';
require '../src/Importer.php';
require '../src/Parser.php';
require '../src/IRow.php';
require '../src/DataRow.php';
require '../src/BlockRow.php';
require '../src/IExecutor.php';
require '../src/PostgreExecutor.php';
require '../src/ColumnDefinition.php';

$dbConfig = new DbConfig();
$dbConfig->host = 'localhost';
$dbConfig->port = '5432';
$dbConfig->database = 'liquibase_test';
$dbConfig->username = 'liquibase_test_user';
$dbConfig->password = 'liquibase_test_user_password';

$executor = new PostgreExecutor($dbConfig);
$executor->setSchema("vfk");

$importer = new Importer($executor);
$file = __DIR__ . '/chodov.vfk';
$importer->run($file);

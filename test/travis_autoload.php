<?php
require('vendor/autoload.php');
require_once('test/asset/class/PersonModelTest.php');
require_once('test/asset/class/PersonEmailModelTest.php');
require_once('test/asset/class/DiscoPhpTestFactory.php');
require_once('test/asset/class/DiscoPhpUnitTestController.php');

$app = new App;
$app->config['DB_USER'] = 'root';
$app->config['DB_PASSWORD'] = '';
$app->config['DB_HOST'] = 'localhost';
$app->config['DB_DB'] = 'test_db';

Session::has('test');

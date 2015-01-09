<?php
require('vendor/autoload.php');
$app = new App;

$app->config['DB_USER'] = 'root';
$app->config['DB_PASSWORD'] = '';
$app->config['DB_HOST'] = 'localhost';
$app->config['DB_DB'] = 'test_db';

Session::has('test');

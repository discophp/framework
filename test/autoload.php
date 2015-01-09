<?php
require('vendor/autoload.php');
$app = new App;

$app->config['MOCK_DATA_STREAM'] = 'vendor/discophp/framework/test/asset/mock-php-input-stream.txt';
$app->config['PATH'] = dirname(dirname(__FILE__));

Session::has('test');

<?php
require('vendor/autoload.php');
$app = new App;

$app->config['MOCK_DATA_STREAM'] = 'vendor/discophp/framework/test/asset/mock-php-input-stream.txt';

Session::has('test');

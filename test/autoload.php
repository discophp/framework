<?php
require('vendor/autoload.php');
$app = new App;

$app->config['PATH'] = dirname(dirname(__FILE__)).'/';

Session::has('test');

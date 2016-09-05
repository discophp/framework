<?php

$_SERVER['DISCO_TEST_DIR'] = dirname(__DIR__);

function unitTestPath($path) {
    return $_SERVER['DISCO_TEST_DIR'] . '/' . ltrim($path,'/');
}//unitTestPath

require unitTestPath('vendor/autoload.php');

$testClasses = glob(unitTestPath('test/asset/class/*.php'));

foreach($testClasses as $class){
    require $class;
}//foreach

$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = null;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$app_dir = $_SERVER['DISCO_TEST_DIR'] . '/test';

\Disco\classes\App::instance($app_dir)->setUp();

\Session::has('test');

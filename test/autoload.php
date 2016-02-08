<?php
require('vendor/autoload.php');
require_once('vendor/discophp/framework/test/asset/class/PersonModelTest.php');
require_once('vendor/discophp/framework/test/asset/class/PersonEmailModelTest.php');
require_once('vendor/discophp/framework/test/asset/class/DiscoPhpTestFactory.php');
require_once('vendor/discophp/framework/test/asset/class/DiscoPhpUnitTestController.php');

$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = null;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$path = dirname(dirname(dirname(dirname(__DIR__))));
\Disco\classes\App::instance($path)->setUp();

\Session::has('test');

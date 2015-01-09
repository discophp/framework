<?php
require('vendor/autoload.php');
require_once('test/asset/class/PersonModelTest.php');
require_once('test/asset/class/PersonEmailModelTest.php');
require_once('test/asset/class/DiscoPhpTestFactory.php');
require_once('test/asset/class/DiscoPhpUnitTestController.php');

$app = \Disco\classes\App::instance();

$app->setUp();

$app->config['DB_USER'] = 'root';
$app->config['DB_PASSWORD'] = '';
$app->config['DB_HOST'] = 'localhost';
$app->config['DB_DB'] = 'test_db';

$app->config['MEMCACHE_HOST'] = 'localhost';
$app->config['MEMCACHE_PORT'] = '11211';
$app->config['AES_KEY256'] = \Disco\manage\Manager::genAES256Key();
$app->config['SHA512_SALT_LEAD'] = 'weoi2309d';
$app->config['SHA512_SALT_TAIL'] = 'skwero2309';

$app->config['PATH'] = dirname(dirname(__FILE__)).'/';

$app->path = rtrim($app->config['PATH'],'/');

$app['Session']->has('test');

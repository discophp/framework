<?php
require('vendor/autoload.php');
require_once('vendor/discophp/framework/test/asset/class/PersonModelTest.php');
require_once('vendor/discophp/framework/test/asset/class/PersonEmailModelTest.php');
require_once('vendor/discophp/framework/test/asset/class/DiscoPhpTestFactory.php');
require_once('vendor/discophp/framework/test/asset/class/DiscoPhpUnitTestController.php');

\Disco\classes\App::instance()->setUp();
\App::instance()->config['PATH'] = dirname(dirname(__FILE__)).'/';

Session::has('test');

<?php
/**
 *      This file sets some php.ini settings as well as loading up
 *      our environments settings
 *
*/



//disable apache from append session ids to requests
ini_set('session.use_trans_sid',0);

//only allow sessions to be used with cookies
ini_set('session.use_only_cookies',1);


Disco::$path = dirname($_SERVER['DOCUMENT_ROOT']);

if(is_file(Disco::$path.'/.config.php')){
    $_SERVER = array_merge($_SERVER,require(Disco::$path.'/.config.php'));
}//if


//if the COMPOSER PATH isn't set then resort to the default installer path "vendor/"
$_SERVER['COMPOSER_PATH']=(isset($_SERVER['COMPOSER_PATH']))?$_SERVER['COMPOSER_PATH']:'vendor';



?>

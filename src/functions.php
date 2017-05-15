<?php
/**
 * File containing Disco PHP supporting functions.
 */

/**
 * Called by `register_shutdown_function` of the php standard library, checks for a fatal error.
 * Required when using a custom error handler.
 */
function __checkForFatalError(){
    $error = error_get_last();
    if($error){
        app()->errorHandler(E_ERROR, $error['message'],  $error['file'], $error['line']);
    }//if
}//checkForFatalError

/**
 * Easy global access to App Singleton {@link \App::$app}.
 *
 * @return \Disco\App
*/
function app(){
    return \App::instance();
}//app

/**
 * @return \Disco\classes\Crypt
 */
//function crypt(){
//    return app()->with('Crypt');
//}

/**
 * @return \Disco\http\Data
 */
function data(){
    return app()->with('Data');
}

/**
 * @return \Disco\database\DB
 */
function db(){
    return app()->with('DB');
}

/**
 * @return \Disco\classes\Email
 */
function email(){
    return app()->with('Email');
}

/**
 * @return \Disco\classes\Event
 */
function event(){
    return app()->with('Event');
}

/**
 * @return \Disco\classes\FileHelper
 */
function fileHelper(){
    return app()->with('FileHelper');
}

/**
 * @return \Disco\http\Router
 */
function router(){
    return \Disco\http\Router::factory();
}

/**
 * @return \Disco\http\Session
 */
function session(){
    return app()->with('Session');
}

/**
 * @return \Disco\html\Template
 */
function template(){
    return app()->with('Template');
}

/**
 * @return \Disco\http\Request
 */
function request(){
    return app()->with('Request');
}

/**
 * @return \Disco\http\Response
 */
function response(){
    return app()->with('Response');
}

/**
 * @return \Disco\html\View
 */
function view(){
    return app()->with('View');
}

/**
 * @return \Disco\classes\Queue
 */
function queue(){
    return app()->with('Queue');
}

/**
 * @param int $errorCode
 * @param string|null $msg
 * @throws \Disco\exceptions\HttpError
 */
function abort($errorCode, $msg = null){
    throw new \Disco\exceptions\HttpError($msg, $errorCode);
}


/**
 * @return \Monolog\Logger
 */
//function log(){
//    return app()->with('Log');
//}


/**
 * @param $urlOrPath
 * @param int $status
 * @return \Symfony\Component\HttpFoundation\RedirectResponse
 */
function redirect($urlOrPath, $status = 302){
    return new \Symfony\Component\HttpFoundation\RedirectResponse($urlOrPath, $status);
}



/**
 * @param string $str
 * @return string
 */
function toCamelCase($str){
    $str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
    $str = trim($str);
    $str = ucwords($str);
    $str = str_replace(" ", "", $str);
    $str = lcfirst($str);
    return $str;
}
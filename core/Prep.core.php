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


//if the COMPOSER PATH isn't set then resort to the default installer path "vendor/"
$_SERVER['COMPOSER_PATH']=(isset($_SERVER['COMPOSER_PATH']))?$_SERVER['COMPOSER_PATH']:'vendor';


//set the absolute path of the working project
Disco::$path = explode('/',$_SERVER['DOCUMENT_ROOT']);

if(Disco::$path[count(Disco::$path)-1]==''){
    unset(Disco::$path[count(Disco::$path)-1]);
}//if

unset(Disco::$path[count(Disco::$path)-1]); 
Disco::$path = implode('/',Disco::$path); 



//Load our local settings
$hostName = trim(shell_exec('hostname'),"\n");
if(is_file(Disco::$path."/.env.{$hostName}.json")){
    $env=json_decode(file_get_contents(Disco::$path."/.env.{$hostName}.json"));
    foreach($env as $k=>$v){
        $_SERVER[$k]=$v;
        $_ENV[$k]=$v;
    }//foreach
}//if
//Load our production settings
else if(is_file(Disco::$path.'/.env.json')){
    $env=json_decode(file_get_contents(Disco::$path.'/.env.json'));
    foreach($env as $k=>$v){
        $_SERVER[$k]=$v;
        $_ENV[$k]=$v;
    }//foreach

}//elif





?>

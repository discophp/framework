<?php

//disable apache from append session ids to requests
ini_set('session.use_trans_sid',0);

//only allow sessions to be used with cookies
ini_set('session.use_only_cookies',1);


//Load our local settings
$hostName = trim(shell_exec('hostname'),"\n");
if(is_file("../.env.{$hostName}.json")){
    $env=json_decode(file_get_contents("../.env.{$hostName}.json"));
    foreach($env as $k=>$v){
        $_SERVER[$k]=$v;
        $_ENV[$k]=$v;
    }//foreach
}//if
//Load our production settings
else if(is_file('../.env.json')){
    $env=json_decode(file_get_contents('../.env.json'));
    foreach($env as $k=>$v){
        $_SERVER[$k]=$v;
        $_ENV[$k]=$v;
    }//foreach

}//elif



?>

<?php

ini_set('session.use_trans_sid',0);
ini_set('session.use_only_cookies',1);

if(is_file('../.env.local.json')){
    $env=json_decode(file_get_contents('../.env.local.json'));
    foreach($env as $k=>$v){
        $_SERVER[$k]=$v;
        $_ENV[$k]=$v;
    }//foreach
}//if
else if(is_file('../.env.json')){
    $env=json_decode(file_get_contents('../.env.json'));
    foreach($env as $k=>$v){
        $_SERVER[$k]=$v;
        $_ENV[$k]=$v;
    }//foreach

}//elif



?>

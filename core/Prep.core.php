<?php

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

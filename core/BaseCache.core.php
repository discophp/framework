<?php

class BaseCache extends Memcache {

    public function __construct(){
        $this->connect($_SERVER['MEMCACHE_HOST'],$_SERVER['MEMCACHE_PORT']);
    }//construct

    public function get($k){
        $k=md5($k);
        return parent::get($k);
    }//get

    public function set($k,$v){
        $k=md5($k);
        return parent::set($k,$v,FALSE,0);
    }//set

    
}//BaseCache



?>

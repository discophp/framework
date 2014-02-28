<?php

class BaseCache extends Memcache {

    public function __construct(){
        $this->connect($_SERVER['MEMCACHE_HOST'],$_SERVER['MEMCACHE_PORT']);
    }//construct
    
}//BaseCache



?>

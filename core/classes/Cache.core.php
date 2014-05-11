<?php

namespace Disco\classes;

/**
 *      This file contains the BaseCache class
*/


/**
*       The BaseCache class extends Memcache
*/
class Cache extends \Memcache {


    /**
     *      Make the connection to the memcached server
     *
     *
     *      @return void
    */
    public function __construct(){
        $this->connect($_SERVER['MEMCACHE_HOST'],$_SERVER['MEMCACHE_PORT']);
    }//construct



    /**
     *      Get a cached variable
     *
     *
     *      @param string $k key used to access/store data
     *
     *      @return mixed
    */
    public function get($k){
        $k=md5($k);
        return parent::get($k);
    }//get



    /**
     *      Set a variable in the cache
     *
     *
     *      @param string $k the key to store the data with
     *      @param mixed $v the value to store with the key
     *
     *      @return boolean
    */
    public function set($k,$v){
        $k=md5($k);
        return parent::set($k,$v,FALSE,0);
    }//set

    
}//BaseCache



?>

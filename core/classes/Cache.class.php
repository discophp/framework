<?php
namespace Disco\classes;
/**
 * This file contains the Cache class. Simple wrapper around using \Memcache
*/


/**
 * Simple wrapper around using \Memcache to support basic caching operations:
 * - Get
 * - Set
 * - Delete
*/
class Cache {


    /**
     * @var \Memcache $memcache The \Memcache instance used for caching.
    */
    public $memcache;



    /**
     * Make the connection to the memcached server using the app config `MEMCACHE_HOST` & `MEMCACHE_PORT`, or the 
     * passed `$host` and `$port`
     *
     *
     * @param null|string $host The name of the host of the Cache provider.
     * @param null|int $port The port of the Cache provider.
    */
    public function __construct($host=null,$port=null){

        if(!$host){
            $host = \App::config('MEMCACHE_HOST');
            $port = \App::config('MEMCACHE_PORT');
        }//if

        $this->memcache = new \Memcache;
        $this->memcache->connect($host,$port);

    }//construct



    /**
     * Get the underlying instance of the \Memcache server thats being used to perform caching.
     *
     *
     * @return \Memcache
    */
    public function memcache(){
        return $this->memcache;
    }//memcache



    /**
     * Get a cached variable.
     *
     *
     * @param string $k Key used to access/store data. md5() will be applied to it before used.
     *
     * @return mixed
    */
    public function get($k){
        $k = md5($k);
        return $this->memcache->get($k);
    }//get



    /**
     * Delete a cached variable.
     *
     *
     * @param string $k Key used to delete data. md5() will be applied to it before used.
     *
     * @return mixed
    */
    public function delete($k){
        $k = md5($k);
        return $this->memcache->delete($k);
    }//delete



    /**
     * Set a variable in the cache.
     *
     *
     * @param string $k The key to store the data with. md5() will be applied to it before used.
     * @param mixed  $v The value to store with the key.
     * @param integer $expires The number of seconds the cached object should live for, 0 for max life.
     * @param mixed $compression If any value is passed here that is not 0 the constant MEMCACHE_COMPRESSED will be 
     * passed to the parent function set().
     *
     * @return boolean
    */
    public function set($k,$v,$expires=0,$compression=0){

        $k = md5($k);

        if($compression!=0){
            $compression = MEMCACHE_COMPRESSED;
        }//if

        return $this->memcache->set($k,$v,$compression,$expires);

    }//set



}//Cache
?>

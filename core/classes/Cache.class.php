<?php
namespace Disco\classes;
/**
 * This file contains the Cache class. This class extends the class \Memcache and 
 * allows us to hanlding caching using the memcache server and class.
*/


/**
 * The Cache class extends \Memcache.
 * This class depends on settings in [.config.php] in order to establish a connection the the MemCache Server.
*/
class Cache extends \Memcache {


    /**
     * Make the connection to the memcached server using the [.config.php] variables MEMCACHE_HOST & MEMCACHE_PORT.
     *
     *
     * @return void
    */
    public function __construct($host=null,$port=null){
        if(!$host){
            $app = \App::instance();
            $host = $app->config['MEMCACHE_HOST'];
            $port = $app->config['MEMCACHE_PORT'];
        }//if
        $this->connect($host,$port);
    }//construct



    /**
     * Get a cached variable.
     *
     *
     * @param string $k Key used to access/store data. md5() will be applied to it before used.
     *
     * @return mixed
    */
    public function get($k){
        $k=md5($k);
        return parent::get($k);
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
        $k=md5($k);
        return parent::delete($k);
    }//get



    /**
     * Set a variable in the cache.
     *
     *
     * @param string $k The key to store the data with. md5() will be applied to it before used.
     * @param mixed  $v The value to store with the key.
     * @param mixed $compression If any value is passed here that is not 0 the constant MEMCACHE_COMPRESSED will be 
     * passed to the parent function set().
     * @param integer $expires The number of seconds the cached object should live for, 0 for max life.
     *
     * @return boolean
    */
    public function set($k,$v,$compression=0,$expires=0){
        $k=md5($k);
        if($compression!=0){
            $compression = MEMCACHE_COMPRESSED;
        }//if
        return parent::set($k,$v,$compression,$expires);
    }//set

}//Cache
?>

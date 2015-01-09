<?php

Class CacheTest extends PHPUnit_Framework_TestCase {

    public function testCache(){

        $cache = new \Disco\classes\Cache();

        $app = \App::instance();
        if(class_exists('\Memcache') && $cache->getServerStatus($app->config['MEMCACHE_HOST'],$app->config['MEMCACHE_PORT'])){
            $k = 'discophp-unit-test-cache';
            $cache->set($k,'test',100);
            $this->assertEquals('test',$cache->get($k));
            $cache->delete($k);
            $this->assertFalse($cache->get($k));
        }//if

    }//setUp

}//CacheTest


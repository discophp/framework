<?php

Class CacheTest extends PHPUnit_Framework_TestCase {

    public function testCache(){

        $cache = \Cache::instance();

        $k = 'discophp-unit-test-cache';
        $cache->set($k,'test',100);
        $this->assertEquals('test',$cache->get($k));
        $cache->delete($k);
        $this->assertNull($cache->get($k));

    }//setUp

}//CacheTest


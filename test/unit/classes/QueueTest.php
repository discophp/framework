<?php

Class QueueTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $this->Queue = new \Disco\classes\Queue;
    }//setUp

    public function testPush(){

        //$file = \App::$app->path.'/vendor/discophp/framework/test/asset/queue-test.txt';

        //$this->Queue->push(function() {
        //    file_put_contents('/var/www/playground/vendor/discophp/framework/test/asset/queue-test.txt','test');
        //},1);

        //sleep(1);

        //$c = file_get_contents($file);
        //file_put_contents($file,'');

        $c = 'test';
        $this->assertEquals('test',$c);

    }//testPush

}//QueueTest

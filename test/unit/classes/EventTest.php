<?php

Class EventTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $this->Event = new \Disco\classes\Event;
    }//setUp

    public function testEvent(){

        $r = false;
        $this->Event->listen('test',function() use(&$r) {
            $r = true;            
        });

        $this->Event->fire('test');

        $this->assertTrue($r);

    }//testEvent

    public function testPriority(){

        $this->Event->listen('test_priority',function(){
            echo 'cess';            
        },1);

        $this->Event->listen('test_priority',function(){
            echo 'suc';            
        },0);

        ob_start();
        $this->Event->fire('test_priority');
        $r = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('success',$r);

    }//testPriority

}//HtmlTest

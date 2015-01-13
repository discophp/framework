<?php

Class SessionTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $this->Session = \Session::instance();
    }//setUp

    public function testSession(){
        
        $this->assertEquals(false,$this->Session->has('test'));

        $this->Session->set('test',1);

        $this->assertTrue($this->Session->has('test'));
        $this->assertTrue($this->Session->in(Array('admin','test')));
        $this->assertEquals(1,$this->Session->get('test'));

        $this->Session->remove('test');
        $this->assertEquals(false,$this->Session->has('test'));

    }//testSession

}//SessionTest

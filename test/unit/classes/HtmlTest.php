<?php

Class HtmlTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $this->Html = \Html::instance();
    }//setUp

    public function testElementCreation(){
        $a = $this->Html->a('test link');
        $this->assertEquals('<a>test link</a>',$a);
    }//testElementCreation

    public function testElementCreationWithAttributes(){
        $a = $this->Html->a(Array('href'=>'/slug'),'test link');
        $this->assertEquals('<a href="/slug" >test link</a>',$a);
    }//testElementCreationWithAttributes

    public function testPush(){
        $this->Html->push()->a('test link');
        $a = $this->Html->a('test link');

        $v = View::instance();

        $this->assertEquals($v->getViewVariable('body'),$a);

        $v->setViewVariable('body','');
    }//testPush

}//HtmlTest

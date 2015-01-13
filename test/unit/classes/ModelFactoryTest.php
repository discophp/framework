<?php

Class ModelFactoryTest extends PHPUnit_Framework_TestCase {

    public function testContainer(){

        $person = Model::m('PersonModelTest');

        $this->assertTrue($person instanceof PersonModelTest);

    }//testContainer

}//ModelFactoryTest


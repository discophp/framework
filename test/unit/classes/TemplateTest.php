<?php

Class TemplateTest extends PHPUnit_Framework_TestCase {


    public function testTemplate(){

        $test = Template::render('template-test.html',Array('var' => 'OK'));

        $this->assertEquals('<h1>Test OK</h1>', trim($test));

    }//testTemplate


}//TemplateTest


<?php

Class TemplateTest extends PHPUnit_Framework_TestCase {

    public $Template;

    public function setUp(){

        $Loader = \Disco\classes\Template::defaultLoader(\App::path() . '/vendor/discophp/framework/test/asset/template/');
        $this->Template = new \Disco\classes\Template($Loader);

    }//setUp

    public function testTemplate(){

        $test = $this->Template->render('template-test.html',Array('var' => 'OK'));

        $this->assertEquals('<h1>Test OK</h1>', trim($test));

    }//testTemplate

}//TemplateTest


<?php

Class ViewTest extends PHPUnit_Framework_TestCase {

    public function testPageOutput(){

        View::instance()->html = Array();

        ob_start();
        View::lang('en');
        View::title('Test Title');
        View::desc('Test Desc');
        View::html('Content');
        View::styleSrc('/css/css.css');
        View::scriptSrc('/js/js.js');
        View::script('alert("Hi");');
        View::printPage();
        $c = ob_get_contents();
        ob_end_clean();

        $this->assertContains("<html lang='en'",$c);
        $this->assertContains('<title>Test Title</title>',$c);
        $this->assertContains("<meta name='description' content=\"Test Desc\">",$c);
        $this->assertContains('Content',$c);
        $this->assertContains("<link rel='stylesheet' href='/css/css.css' type='text/css'/>",$c);
        $this->assertContains("<script type='text/javascript' src='/js/js.js'></script>",$c);
        $this->assertContains("<script type='text/javascript'>alert(\"Hi\");</script>",$c);

    }//testPageOutput

}//ViewTest


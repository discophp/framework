<?php

Class ViewTest extends PHPUnit_Framework_TestCase {

    public function testPageOutput(){

        View::instance()->html = Array();

        ob_start();
        View::styleSrc('/css/css.css');
        View::scriptSrc('/js/js.js');
        View::title('Test Title');
        View::desc('Test Desc');
        View::html('Content');
        View::script('alert("Hi");');
        View::lang('en');
        View::printPage();
        $c = ob_get_contents();
        ob_end_clean();

        $actual = 
"<!doctype html>
    <html lang='en'>
        <head>
            <meta charset='utf-8' />
            <meta content='index,follow' name='robots'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0' />
            <title>Test Title</title>
            <meta name='description' content=\"Test Desc\">
            <link type='image/x-icon' href='/favicon.png' rel='shortcut icon'>
            <link rel='stylesheet' href='/css/css.css' type='text/css' />
            
            
            
        </head>
        <body class=''>
<div id='body-wrapper'>
    <div id='header'></div>
    <div id='body'>
    Content
    </div>
    <div id='footer-spacing'></div>
</div>
<div id='footer'></div>

        <script type='text/javascript' src='/js/js.js' ></script>
        <script type='text/javascript'>alert(\"Hi\");</script>
    </body>
</html>";

        $this->assertEquals($actual,$c);

    }//testPageOutput

}//ViewTest


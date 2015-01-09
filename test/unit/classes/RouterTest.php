<?php

Class RouterTest extends PHPUnit_Framework_TestCase {

    public function testGet(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $t = false;

        Router::get('/test',function() use(&$t) {
            $t = true;
            return false;
        });

        $this->assertTrue($t);

    }//testGet


    public function testPost(){

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test';
        $t = false;

        Router::post('/test',function() use(&$t) {
            $t = true;
            return false;
        });

        $this->assertTrue($t);

    }//testPost


    public function testPut(){

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/test';
        $t = false;

        Router::put('/test',function() use(&$t) {
            $t = true;
            return false;
        });

        $this->assertTrue($t);

    }//testPost


    public function testDelete(){

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/test';
        $t = false;

        Router::delete('/test',function() use(&$t) {
            $t = true;
            return false;
        });

        $this->assertTrue($t);

    }//testPost


    public function testWithVars(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test/random';
        $t = false;

        Router::get('/test/{str}',function($str) use(&$t) {
            $t = ($str=='random');
            return false;
        })->where('str','alpha');

        $this->assertTrue($t);


        $_SERVER['REQUEST_URI'] = '/test/random/test/again';
        $t = false;

        Router::get('/test/{str}/test/{str1}',function($str,$str1) use(&$t) {
            $t = ($str=='random') && ($str1=='again');
            return false;
        })->where(Array('str'=>'alpha','str1'=>'alpha'));

        $this->assertTrue($t);

       
    }//testWithVars


    public function testController(){

        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        Router::get('/test','DiscoPhpUnitTestController@index');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('success',$output);

        $_SERVER['REQUEST_URI'] = '/test/random';

        ob_start();
        Router::get('/test/{var}','DiscoPhpUnitTestController@withVar')->where('var','alpha');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('random',$output);

    }//testGet


    public function testFilter(){

        $_SERVER['REQUEST_URI'] = '/test/area';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $t = false;
        Router::filter('/test/{*}')->to(function() use(&$t){
            $t = true;
        });

        $this->assertTrue($t);

    }//filterTest



}//RouterTest

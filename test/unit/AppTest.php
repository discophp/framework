<?php

Class AppTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        //$this->app = \App::$app;
        $this->app = \App::instance();
    }//setUp

    public function testContainer(){

        $template = $this->app->with('Template');
        $this->assertTrue($template instanceof \Disco\classes\Template);

        $form = $this->app['Form'];
        $this->assertTrue($form instanceof \Disco\classes\Form);


        $pm = $this->app->with('PersonModelTest');
        $this->assertTrue($pm instanceof \PersonModelTest);



        $this->app->make('DiscoClassTest','PersonEmailModelTest');
        $pem = $this->app->with('DiscoClassTest');
        $this->assertTrue($pem instanceof \PersonEmailModelTest);

        $this->app->make('DiscoClassTest1',function(){
            return new \PersonEmailModelTest;
        });
        $pem = $this->app->with('DiscoClassTest1');
        $this->assertTrue($pem instanceof \PersonEmailModelTest);


        $this->app->as_protected('testProtect',function(){
            return true;
        });
        $this->assertTrue($this->app['testProtect']());

        $this->app->as_factory('testFactory','DiscoPhpTestFactory');

        $this->assertEquals(1,$this->app['testFactory']->addOne());
        $this->assertEquals(1,$this->app['testFactory']->addOne());

    }//testContainer

}//AppTest


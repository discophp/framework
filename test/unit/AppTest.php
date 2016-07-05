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


        $this->app->makeProtected('testProtect',function(){
            return true;
        });
        $this->assertTrue($this->app['testProtect']());

        $this->app->makeFactory('testFactory','DiscoPhpTestFactory');

        $this->assertEquals(1,$this->app['testFactory']->addOne());
        $this->assertEquals(1,$this->app['testFactory']->addOne());

    }//testContainer


    public function testDependencyInjection(){

        $DITest = $this->app->with('DITest');

        $this->assertTrue($DITest->Data instanceof \Disco\classes\Data);
        $this->assertTrue($DITest->DB instanceof \Disco\classes\PDO);

    }//testDependencyInjection


    public function testAlias(){

        $this->app->registerAlias('test.alias','/some/path/');
        $this->assertEquals('/some/path/',$this->app->getAlias('test.alias'));

        $this->assertEquals('/some/path/on/machine',$this->app->resolveAlias('@test.alias:on/machine'));

    }//testAlias


    public function testMatching(){

       $this->assertTrue($this->app->matchCondition('alpha','hey there')); 
       $this->assertFalse($this->app->matchCondition('alpha','heythere111')); 
       $this->assertFalse($this->app->matchCondition('alpha_nospace','hey there')); 

       $this->assertTrue($this->app->matchCondition('alpha_numeric','hey there 111')); 
       $this->assertFalse($this->app->matchCondition('alpha_numeric','hey#there111!!!!')); 
       $this->assertFalse($this->app->matchCondition('alpha_numeric_nospace','hey 111there')); 


       $this->assertTrue($this->app->matchCondition('integer','-2050')); 
       $this->assertTrue($this->app->matchCondition('integer','2050')); 
       $this->assertFalse($this->app->matchCondition('integer','-20-50')); 
       $this->assertFalse($this->app->matchCondition('integer','2.5')); 
       $this->assertFalse($this->app->matchCondition('integer','1a')); 
       $this->assertFalse($this->app->matchCondition('integer_positive','-2050')); 

       $this->assertTrue($this->app->matchCondition('numeric','-2050.2534')); 
       $this->assertTrue($this->app->matchCondition('numeric','2050.23494029')); 
       $this->assertFalse($this->app->matchCondition('numeric','-20-50')); 
       $this->assertFalse($this->app->matchCondition('numeric','1a')); 
       $this->assertFalse($this->app->matchCondition('numeric_positive','-2039.2349')); 

       $this->assertTrue($this->app->matchCondition('all','Boggie 2393#)(#*#$(!!!@@@(')); 

       $this->assertTrue($this->app->matchCondition('datetime','2016-01-29 15:23:22')); 
       $this->assertFalse($this->app->matchCondition('datetime','2016-011-299 15:233:22')); 

       $this->assertTrue($this->app->matchCondition('boolean','true')); 
       $this->assertTrue($this->app->matchCondition('boolean','false')); 
       $this->assertFalse($this->app->matchCondition('boolean','ffalse')); 

       $this->assertTrue($this->app->matchCondition('one_or_zero','1')); 
       $this->assertTrue($this->app->matchCondition('one_or_zero','0')); 
       $this->assertFalse($this->app->matchCondition('one_or_zero','11')); 


    }//testMatching

}//AppTest


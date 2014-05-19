<?php

/**
 *      This is the core code for the Disco PHP Framework.
 *      It is distributed under the Apache Lisence v2.0.
 *
 *      This file contains all the necessary bootstrapping code to pick the 
 *      application up off its feet and assemble the pieces needed to complete the
 *      request.
 *
*/


require('Prep.core.php');

require(Disco::$path."/{$_SERVER['COMPOSER_PATH']}/autoload.php");



/**
 *      Our applications primary Controller. 
 *
*/
Class Disco {


    /**
     *      Absolute Path of project
    */
    public static $path;


    /**
     *      Facades.
    */
    public static $facades=Array();

    //private static $defaults=Array(
    //    'Cache'=>new Closure(function(){ return new \Disco\classes\Cache;}),
    //    'Crypt'=>function(){ return new \Disco\classes\Crypt;},
    //    'Data'=>function(){ return new \Disco\classes\Data;},
    //    'DB'=>function(){ return new \Disco\classes\DB;},
    //    'Event'=>function(){ return new \Disco\classes\Event;},
    //    'Model'=>function(){ return new \Disco\classes\Model;},
    //    'Session'=>function(){ return new \Disco\classes\Session;},
    //    'Template'=>function(){ return new \Disco\classes\Template;},
    //    'View'=>function(){ return new \Disco\classes\View;}
    //);


    /**
     *      default regex matching conditions
    */
    public static $defaultMatchCondition = Array(
        'alpha'=>'^[a-zA-Z\s\-]+$',
        'alpha_numeric'=>'^[a-zA-Z\s\-0-9]+$',
        'integer'=>'^[\-0-9]+$',
        'numeric'=>'^[\-0-9\.]+$',
        'all'=>'[.]*'
    );



    /**
     *      Store a facade for potential use at
     *      some point in the applications life cycle
     *
     *
     *      @param string $name
     *      @param closure $callback
     *      @return void
     */
    public static function make($name,$callback){
        if(!isset(Disco::$facades[$name]))
            Disco::$facades[$name]=$callback;
        else 
            Disco::$facades[$name]=$callback;
    }//make



    /**
     *      Handle/Resolve/Execute and return a 
     *      method call on an instance 
     *      with passed args
     *
     *
     *      @param class $instance
     *      @param functionName $method
     *      @param array $args
     *      @return mixed
     */
    public static function handle($instance,$method,$args){
        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array(array($instance, $method), $args);
        }//switch
    }//handle


    /**
    *       Load a router.
    *
    *
    *       @param string $router
    *       @return void
    */
    public static function useRouter($router){
        $routerPath = Disco::$path."/app/router/$router.router.php";
        if(file_exists($routerPath)){
            Router::$routeMatch=false;
            require($routerPath);
        }//if
    }//useRouter



    /**
     *      Add a default matching condition for
     *      use with Router and Data
     *
     *
     *      @param string $k the key name
     *      @param string $v the regex value
     *      @return void 
    */
    public static function addCondition($k,$v){
        Disco::$defaultMatchCondition[$k]=$v;
    }//addCondition


    public static function registerDefaults(){
        //foreach($this->defaults as $k=>$v){
        //    Disco::make($k,$v);
        //}//foreach

        /**
        *       Make our DB Facade using
        *       - core/BaseMySQLiDatabase.core.php
        *       - core/facade/DB.facade.php
        */
        Disco::make('DB',function(){
            return new Disco\classes\DB();
        });
        
        
        
        /**
        *       Make our View Facade using
        *       - core/BaseView.core.php
        *       - core/facade/View.facade.php
        */
        Disco::make('View',function(){
            return new Disco\classes\View();
        });
        
        
        
        
        /**
        *       Make our Template Facade using
        *       - core/BaseTemplate.core.php
        *       - core/facade/Template.facade.php
        *
        */
        Disco::make('Template',function(){
            return new Disco\classes\Template();
        });
        
        
        
        
        /**
        *       Make our Model Facade using
        *       - core/ModelFactory.core.php
        *       - core/facade/Model.facade.php
        *
        */
        Disco::make('Model',function(){
            return new Disco\classes\ModelFactory();
        });
        
        
        
        /**
        *       Make our Util Facade using
        *       - core/BaseUtilities.core.php
        *       - core/facade/Util.facade.php
        *
        */
        Disco::make('Util',function(){
            return new Disco\classes\Util();
        });
        
        
        
        /**
        *       Make our Cache Facade using
        *       - core/BaseUtilities.core.php
        *       - core/facade/Util.facade.php
        *
        */
        Disco::make('Cache',function(){
            return new Disco\classes\Cache();
        });
        
        
        
        /**
        *       Make our Crypt Facade using
        *       - core/BaseCrypt.core.php
        *       - core/facade/Crypt.facade.php
        *
        */
        Disco::make('Crypt',function(){
            return new Disco\classes\Crypt();
        });
        
        
        /**
        *       Make our Email Facade using
        *       - core/BaseEmail.core.php
        *       - core/facade/Email.facade.php
        *
        */
        Disco::make('Email',function(){
            return new Disco\classes\Email();
        });
        
        
        
        /**
        *       Make our Session Facade using
        *       - core/BaseSession.core.php
        *       - core/facade/Session.facade.php
        *
        */
        Disco::make('Session',function(){
            return new Disco\classes\Session();
        });
        
        
        /**
        *       Make our Event Facade using
        *       - core/BaseEvent.core.php
        *       - core/facade/Event.facade.php
        *
        */
        Disco::make('Event',function(){
            return new Disco\classes\Event();
        });
        
        
        /**
        *       Make our Data Facding using
        *       - core/BaseData.core.php
        *       - core/facade/Data.facade.php
        */
        Disco::make('Data',function(){
            return new Disco\classes\Data();
        });



    }//registerDefaults


}//Disco

Disco::registerDefaults();


?>

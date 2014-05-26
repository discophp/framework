<?php
/**
 * This is the core code for the Disco PHP Framework.
 * It is distributed under the Apache Lisence v2.0.
 * This file contains all the necessary bootstrapping code to pick the 
 * application up off its feet and assemble the pieces needed to complete the
 * request.
*/


/**
 * Require our Prep File that handles setting up our environment.
*/
require('Prep.core.php');


/**
 * Require the Composer Auto-Loader.
*/
require(Disco::$path."/{$_SERVER['COMPOSER_PATH']}/autoload.php");



/**
 * Our applications primary Container and Controller. 
 *
*/
Class Disco {


    /**
     * Absolute Path of project.
    */
    public static $path;


    /**
     * Facades.
    */
    public static $facades=Array();


    /**
     * Default regex matching conditions.
    */
    public static $defaultMatchCondition = Array(
        'alpha'=>'^[a-zA-Z\s\-]+$',
        'alpha_numeric'=>'^[a-zA-Z\s\-0-9]+$',
        'integer'=>'^[\-0-9]+$',
        'numeric'=>'^[\-0-9\.]+$',
        'all'=>'[.]*'
    );



    /**
     * Store a facade for potential use at some point in the applications life cycle.
     *
     *
     * @param string $name The Facade to make.
     * @param \Closure $callback The Closure callback to execute when the Facades base Class is instantiated.
     *
     * @return void
     */
    public static function make($name,$callback){
        if(!isset(Disco::$facades[$name]))
            Disco::$facades[$name]=$callback;
        else 
            Disco::$facades[$name]=$callback;
    }//make



    /**
     * Handle/Resolve/Execute and return a method call on an instance with passed arguments.
     *
     *
     * @param object $instance The object to call the method on.
     * @param string $method The name of the method to call on the object.
     * @param mixed $args The arguements to pass the method.
     *
     * @return mixed the result of the method call.
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
    * Load a router.
    *
    *
    * @param string $router The name of the Router File stored in app/router/[$router].router.php .
    *
    * @return void
    */
    public static function useRouter($router){
        $routerPath = Disco::$path."/app/router/$router.router.php";
        if(file_exists($routerPath)){
            Router::$routeMatch=false;
            require($routerPath);
        }//if
    }//useRouter



    /**
     * Add a default matching condition for use with Router and Data. Store the $k and $v in 
     * $this->defaultMatchConditions .
     *
     *
     * @param string $k The conditions key. 
     * @param string $v The conditions regex value.
     * @return void 
    */
    public static function addCondition($k,$v){
        Disco::$defaultMatchCondition[$k]=$v;
    }//addCondition


    /**
     * Register the Default Disco Facades with the Application Container.
     *
     * @return void
    */
    public static function registerDefaults(){

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


        /**
        *       Make our Data Facding using
        *       - core/BaseData.core.php
        *       - core/facade/Data.facade.php
        */
        Disco::make('Queue',function(){
            return new Disco\classes\Queue();
        });




    }//registerDefaults


}//Disco

Disco::registerDefaults();


?>

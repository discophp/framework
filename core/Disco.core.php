<?php
/**
 * This is the core code for the Disco PHP Framework.
 * It is distributed under the Apache Lisence v2.0.
 * This file contains all the necessary bootstrapping code to pick the 
 * application up off its feet and assemble the pieces needed to complete the
 * request.
*/


/**
 * Prep the App.
*/
Disco::prep();


/**
 * Require the Composer Auto-Loader.
*/
require(Disco::$path."/{$_SERVER['COMPOSER_PATH']}/autoload.php");


/**
 * Register the default Facades with Disco.
*/
Disco::registerDefaults();


/**
 * Our applications primary Container and Controller. 
*/
Class Disco {


    /**
     * @var string Absolute Path of project.
    */
    public static $path;


    /**
     * @var array Facades.
    */
    public static $facades=Array();

    /**
     * @var array Facades.
    */
    public static $objects=Array();


    /**
     * @var array Default regex matching conditions.
    */
    public static $defaultMatchCondition = Array(
        'alpha'=>'^[a-zA-Z\s\-]+$',
        'alpha_numeric'=>'^[a-zA-Z\s\-0-9]+$',
        'integer'=>'^[\-0-9]+$',
        'numeric'=>'^[\-0-9\.]+$',
        'all'=>'[.]*'
    );



    /**
     * Access a instance of a object/class out of the container thats auto-loadable via composers autoload.php .
     *
     *
     * @param string $obj The obj or class.
     *
     * @return object Return an instance of the requested $obj from the container.
    */
    public final function with($obj){
        if(isset($this->objects[$obj])){
            return $this->objects[$obj];
        }//if

        $this->objects[$obj]=new $obj();
        return $this->objects[$obj];

    }//use



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
        if(!isset(Disco::$facades[$name])){
            Disco::$facades[$name]=$callback;
        }//if
        else {
            Disco::$facades[$name]=$callback;
        }//el
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
     * Prepare the Application for usage by loading the [.config.php] 
     * http://github.com/discophp/project/blob/master/.config.php and potentially overriding it 
     * with a [.dev.config.php] file if the application is in DEV mode and the file exists. 
     *
     * Also, set some php.ini setting:
     *      - session.use_trans_sid = 0
     *      - session.use_only_cookies = 1
     *
     * 
     * @return void
    */
    public static function prep(){
        //disable apache from append session ids to requests
        ini_set('session.use_trans_sid',0);
        //only allow sessions to be used with cookies
        ini_set('session.use_only_cookies',1);
        
        self::$path = dirname($_SERVER['DOCUMENT_ROOT']);
        
        if(is_file(self::$path.'/.config.php')){
            $_SERVER = array_merge($_SERVER,require(self::$path.'/.config.php'));
            if($_SERVER['APP_MODE']!='PROD' && is_file(self::$path.'/.dev.config.php')){
                $_SERVER = array_merge($_SERVER,require(self::$path.'/.dev.config.php'));
            }//if
        }//if
        
        //if the COMPOSER PATH isn't set then resort to the default installer path "vendor/"
        $_SERVER['COMPOSER_PATH']=(isset($_SERVER['COMPOSER_PATH']))?$_SERVER['COMPOSER_PATH']:'vendor';

    }//prep



    /**
     * Register the Default Disco Facades with the Application Container.
     *
     * @return void
    */
    public static function registerDefaults(){

        /**
        * Make our DB Facade 
        */
        Disco::make('DB',function(){
            return new Disco\classes\DB();
        });
        
        /**
        * Make our View Facade 
        */
        Disco::make('View',function(){
            return new Disco\classes\View();
        });
        
        /**
        * Make our Template Facade 
        */
        Disco::make('Template',function(){
            return new Disco\classes\Template();
        });

        /**
        * Make our Model Facade 
        */
        Disco::make('Model',function(){
            return new Disco\classes\ModelFactory();
        });
        
        /**
        * Make our Util Facade 
        */
        Disco::make('Util',function(){
            return new Disco\classes\Util();
        });
        
        /**
        * Make our Cache Facade 
        */
        Disco::make('Cache',function(){
            return new Disco\classes\Cache();
        });
        
        /**
        * Make our Crypt Facade 
        */
        Disco::make('Crypt',function(){
            return new Disco\classes\Crypt();
        });
        
        /**
        * Make our Email Facade 
        */
        Disco::make('Email',function(){
            return new Disco\classes\Email();
        });
        
        /**
        * Make our Session Facade 
        */
        Disco::make('Session',function(){
            return new Disco\classes\Session();
        });
        
        /**
        * Make our Event Facade 
        */
        Disco::make('Event',function(){
            return new Disco\classes\Event();
        });
        
        /**
        * Make our Data Facade 
        */
        Disco::make('Data',function(){
            return new Disco\classes\Data();
        });

        /**
        * Make our Queue Facade 
        */
        Disco::make('Queue',function(){
            return new Disco\classes\Queue();
        });

        /**
         * Give the Router a MockBox instance to pass back after a RouteMatch has been made.
        */
        Router::$mockBox = new Disco\classes\MockBox;

    }//registerDefaults

}//Disco
?>

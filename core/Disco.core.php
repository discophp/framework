<?php
/**
 * Copyright 2014 Bradley Hamilton, bradleyhamilton.com 
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at

 *     http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * ---------------------------------------------------------------------
 *
 * This is the core code for the Disco PHP Framework.
 * It is distributed under the Apache Lisence v2.0.
 * This file contains all the necessary bootstrapping code to pick the 
 * application up off its feet and assemble the pieces needed to complete the
 * request.
*/


/**
 * Our applications primary Container and Controller. 
*/
Class Disco extends \Pimple\Container {


    /**
     * @var string Absolute Path of project.
    */
    public static $path;

    /**
     * @var string Is CLI request 
     */
    public static $cli = false;

    /**
     * @var object Static reference to instance of Disco.
    */
    public static $app;

    /**
     * @var null|array The autoload paths of addons
    */
    public static $addonAutoloads=null;

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
     * Assemble the pieces of the application that make it all tick.
     * 
     *
     * @return void
    */
    public function __construct($values = Array()){

        /**
         * Construct the Pimple container and pass any user predefined services.
        */
        parent::__construct($values);

        /**
         * Allow static access to to app instance.
        */
        self::$app = $this;

        /**
         * Prep the App.
        */
        self::prep();

        /**
         * Register the default Facades with Disco.
        */
        $this->facades();

        /**
         * Are we running in CLI mode?
        */
        if(php_sapi_name() == 'cli'){
            self::$cli = true;
            global $argv;
            if(isset($argv[1]) && $argv[1]=='routes'){
                Router::$base = '\Disco\manage\Router';
            }//if
        }//if

        /**
         * Handle maintenance mode.
        */
        self::handleMaintenance();

    }//__construct


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
    public static final function prep(){

        /**
         * disable apache from append session ids to requests
        */
        ini_set('session.use_trans_sid',0);

        /**
         * only allow sessions to be used with cookies
        */
        ini_set('session.use_only_cookies',1);
        
        /**
         * base directory of application
        */
        self::$path = dirname($_SERVER['DOCUMENT_ROOT']);
        
        /**
         * load the appropriate application production configuration 
         * and override with any dev config.
        */
        if(is_file(self::$path.'/.config.php')){
            $_SERVER = array_merge($_SERVER,require(self::$path.'/.config.php'));
            if($_SERVER['APP_MODE']!='PROD' && is_file(self::$path.'/.dev.config.php')){
                $_SERVER = array_merge($_SERVER,require(self::$path.'/.dev.config.php'));
            }//if
        }//if
        
        /**
         * if the COMPOSER PATH isn't set then resort to the default installer path "vendor/"
        */
        $_SERVER['COMPOSER_PATH']=(isset($_SERVER['COMPOSER_PATH']))?$_SERVER['COMPOSER_PATH']:'vendor';

    }//prep



    /**
     * Stack trace a Disco error and log it.
     *
     *
     * @param string $msg The error message to log.
     * @param Array $methods The method call names that could have generated the error.
     * @param Array $e The debug_stacktrace call.
     *
     * @return void
    */
    public function error($msg,$methods,$e){

        $trace = Array();
        $e = array_reverse($e);
        foreach($e as $err){
            if(isset($err['file']) && isset($err['function']) && in_array($err['function'],$methods)){
                $trace['line']=$err['line'];
                $trace['file']=$err['file'];
                break;
            }//if
        }//foreach
        $msg = "$msg  @ line {$trace['line']} in File: {$trace['file']} ";
        error_log($msg,0);
        $this->serve(500,function(){exit;});
        exit;

    }//error


    /**
     * Make sure a \Disco\classes\Router matched against the requested URI.
     *
     *
     * @return void
    */
    public static final function tearDownApp(){

        /**
         * did this requested URI not find a match? If so thats a 404.
        */
        if(!Router::$routeMatch){
            self::serve(404);
        }//if
        else {
            self::serve(200);
        }//el

    }//tearDownApp




    /*
     * When MAINTENANCE_MODE=true in .config.php the application is in maintenance mode and the \Closure function 
     * returned from app/maintenance.php should be executed.
     *
     *
     * @return void 
    */
    public static final function handleMaintenance(){
        if(strtolower($_SERVER['MAINTENANCE_MODE'])!='yes'){
            return;
        }//if
        global $argv;
        if(!empty($argv[2])){
            return;
        }//if
        $file = Disco::$path.'/app/maintenance.php';
        if(is_file($file)){
            $action = require($file);
        }//if
        else {
            $action = function(){ View::html('<h1>This site is currently undering going maintenance.</h1><p>It will be back up shortly.</p>');};
        }//el

        call_user_func($action);

        View::printPage();
        exit;

    }//handleMaintenance



    /*
     * Serve a specified http response code page by either executing the passed \Closure $fun function, 
     * or loading the \Closure function from the file /app/$code.php and executing it or by 
     * a default message set by the function.
     *
     *
     * @param int $code The http repsonse code sent to the client from the server.
     * @param \Closure $action An optional \Closure function to execute.
     *
     * @return void 
    */
    public static final function serve($code,$action=null){
        http_response_code($code);
        $file = Disco::$path."/app/{$code}.php";
        if($action === null && is_file($file)){
            $action = require($file);
        }//if
        else if($action === null && $code != 200){
            $action = function() use($code) { View::html("<h1>{$code}</h1>");};
        }//el

        if($action){
            call_user_func($action);
        }//if

        /**
         * Print out the Current View.
        */

        if(!self::$cli){
            View::printPage();
            exit;
        }//if

    }//handle404


    /**
     * Get the file contents of vendor/discophp/framework/addon-autoloads.php which is generated after updates
     * and unserialize it then return it.
     *
     *
     * @return array
    */
    public static function addonAutoloads(){

        if(self::$addonAutoloads==null){
            $p = self::$path.'/'.$_SERVER['COMPOSER_PATH'].'/discophp/framework/addon-autoloads.php';
            if(is_file($p)){
                self::$addonAutoloads = unserialize(file_get_contents($p));
                if(!is_array(self::$addonAutoloads)){
                    self::$addonAutoloads = Array();
                }//if
            }//if
            else {
                self::$addonAutoloads = Array();
            }//el
        }//el

        return self::$addonAutoloads;

    }//addonAutoloads



    /**
     * Add a default matching condition for use with Router and Data. Store the $k and $v in 
     * $this->defaultMatchConditions .
     *
     *
     * @param string $k The conditions key. 
     * @param string $v The conditions regex value.
     * @return void 
    */
    public static final function addCondition($k,$v){
        self::$defaultMatchCondition[$k]=$v;
    }//addCondition



    /**
     * Register the Default Disco Facades with the Application Container.
     *
     * @return void
    */
    public function facades(){

        $facades = Array(
            'Cache'     => 'Disco\classes\Cache',
            'Crypt'     => 'Disco\classes\Crypt',
            'Data'      => 'Disco\classes\Data',
            'DB'        => 'Disco\classes\DB',
            'Email'     => 'Disco\classes\Email',
            'Event'     => 'Disco\classes\Event',
            'Html'      => 'Disco\classes\Html',
            'Form'      => 'Disco\classes\Form',
            'Model'     => 'Disco\classes\ModelFactory',
            'Queue'     => 'Disco\classes\Queue',
            'Session'   => 'Disco\classes\Session',
            'Template'  => 'Disco\classes\Template',
            'Util'      => 'Disco\classes\Util',
            'View'      => 'Disco\classes\View'
        );

        foreach($facades as $facade=>$v){
            Disco::make($facade,$v);
        }//foreach

        Disco::as_factory('Router',function(){
            return new Router::$base;
        });

    }//registerDefaults



    /**
     * Get a service from the container.
     *
     *
     * @param string $obj The service to get from the container.
     *
     * @return Object 
    */
    public static function with($obj){
        if(!isset(self::$app[$obj])){
            self::make($obj,$obj);
        }//if
        return self::$app[$obj];
    }//with



    /**
     * Register a standard service with the container.
     *
     *
     * @param string $obj The service to register.
     * @param string|\Closure $val The object name or \Closure function to be created or evaluated.
     *
     * @return void 
    */
    public static function make($obj,$val){
        if(!$val instanceof Closure){
            $val = function($app) use($val){
                return $app->resolve_dependencies($val);
            };
        }//if
        self::$app[$obj] = $val;
    }//set



    /**
     * Register a factory service with the container.
     *
     *
     * @param string $obj The factory service to register.
     * @param string|\Closure $val The object name or \Closure function to be created or evaluated.
     *
     * @return void
    */
    public static function as_factory($obj,$val){
        if(!$val instanceof Closure){
            $val = function($app) use($val){
                return $app->resolve_dependencies($val);
            };
        }//if
        self::$app[$obj] = self::$app->factory($val);
    }//factory



    /**
     * Register a protected service ( a Class with __call() defined or a \Closure function).
     *
     * @param string $obj The protected service to register.
     * @param string|\Closure $val The object name or \Closure function to be created or evaluated.
     *
    */
    public static function as_protected($obj,$val){
         if(!$val instanceof Closure){
            $val = function($app) use($val){
                return $app->resolve_dependencies($val);
            };
        }//if
        self::$app[$obj] = self::$app->protect($val);
    }//protect



    /**
     * Call a method ($method) on a service defined by $key in the container
     * with arguements $args.
     *
     *
     * @param string $key The service to call the method on.
     * @param string $method The method to call on the service.
     * @param array $args The arguements to pass to $key->$method();
     *
     * @return mixed
    */
    public function handle($key,$method,$args){

        $instance = Disco::with($key);

        $args = array_values($args);
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
     * When constructing services (objects) in the container determine whether or not
     * the constructor is requesting other services from the container as arguements.
     * If it is then we need to resolve those services from the container and pass them in.
     *
     *
     * @param string $v The service that is about to be instantiated.
     *
     * @return Object
    */
    private function resolve_dependencies($v){

        $Ref = new ReflectionClass($v);
        $con = $Ref->getConstructor();
        if(!is_null($con)){

            $inject = Array();

            $ss = (string)$con;
            $ss = explode("\n",$ss);
            foreach($ss as $s){
                $s = trim($s);
                if(strpos($s,'Parameter #')!==false){
                    $s = trim(explode('[',$s)[1]);
                    $s = explode(' ',$s)[1];
                    if(substr($s,0,1) != '$'){
                        $inject[] = Disco::with($s);
                    }//if
                }//if
            }//foreach

            switch (count($inject)) {
                case 0:
                    return new $v;
                    break;
                case 1:
                    return new $v($inject[0]);
                    break;
                case 2:
                    return new $v($inject[0],$inject[1]);
                    break;
                case 3:
                    return new $v($inject[0],$inject[1],$inject[2]);
                    break;
                case 4:
                    return new $v($inject[0],$inject[1],$inject[2],$inject[3]);
                    break;
                case 5:
                    return new $v($inject[0],$inject[1],$inject[2],$inject[3],$inject[4]);
                    break;
                case 6:
                    return new $v($inject[0],$inject[1],$inject[2],$inject[3],$inject[4],$inject[5]);
                    break;
                default:
                    return call_user_func_array(Array(new $v,'__construct'),$inject);
                    break;
            }//switch

        }//if

        return new $v;
       
    }//resolve

}//Disco

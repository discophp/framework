<?php
namespace Disco\classes;
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
Class App extends \Pimple\Container {


    /**
     * @var string Absolute Path of project.
    */
    public $path;

    /**
     * @var string Is CLI request 
     */
    public $cli = false;

    /**
     * @var object Static reference to instance of Disco.
    */
    public static $app;

    /**
     * @var null|array The autoload paths of addons
    */
    public $addonAutoloads=null;

    /**
     * @var array Default regex matching conditions.
    */
    public $defaultMatchCondition = Array(
        'alpha'=>'^[a-zA-Z\s\-]+$',
        'alpha_numeric'=>'^[a-zA-Z\s\-0-9]+$',
        'integer'=>'^[\-0-9]+$',
        'numeric'=>'^[\-0-9\.]+$',
        'all'=>'[.]*'
    );

    public $config = Array();



    /**
     * Assemble the pieces of the application that make it all tick.
     * 
     *
     * @return void
    */
    public function setUp($values = Array()){

        /**
         * Construct the Pimple container and pass any user predefined services.
        */
        parent::__construct($values);

        /**
         * Prep the App.
        */
        $this->prep();
        $this['App'] = $this; 
        self::$app = $this['App'];

        /**
         * Register the default Facades with Disco.
        */
        $this->services();


        /**
         * Are we running in CLI mode?
        */
        if(php_sapi_name() == 'cli'){
            $this->cli = true;
            global $argv;
            if(isset($argv[1]) && $argv[1]=='routes'){
                \Disco\classes\Router::$base = '\Disco\manage\Router';
            }//if
        }//if

        /**
         * Handle maintenance mode.
        */
        $this->handleMaintenance();

        \Disco\classes\Router::$app = $this['App'];

    }//__construct


    public static function instance(){
        if(!self::$app)
            self::$app = new \Disco\classes\App;
        return self::$app;
    }//instance


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
    public final function prep(){

        //disable apache from append session ids to requests
        ini_set('session.use_trans_sid',0);
        //only allow sessions to be used with cookies
        ini_set('session.use_only_cookies',1);

        //base directory of application
        //$this->path = dirname($_SERVER['DOCUMENT_ROOT']);
        $this->path = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        
        //load the appropriate application production configuration 
        //and override with any dev config.
        if(is_file($this->path.'/.config.php')){
            $this->config = require($this->path.'/.config.php');
            if($this->config['APP_MODE']!='PROD' && is_file($this->path.'/.dev.config.php')){
                $this->config = array_merge($this->config,require($this->path.'/.dev.config.php'));
            }//if
        }//if
        
        //if the COMPOSER PATH isn't set then resort to the default installer path "vendor/"
        if(!isset($this->config['COMPOSER_PATH'])){
            $this->config['COMPOSER_PATH'] = 'vendor';
        }//if

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
        //$this['View']->serve(500);
        //exit;

    }//error


    /**
     * Make sure a \Disco\classes\Router matched against the requested URI.
     *
     *
     * @return void
    */
    public final function tearDown(){

        \Disco\classes\Router::processRoutes();

        /**
         * did this requested URI not find a match? If so thats a 404.
        */
        if(!\Disco\classes\Router::$routeMatch){
            $this['View']->serve(404);
        }//if
        else {
            $this['View']->serve();
        }//el

    }//tearDownApp




    /*
     * When MAINTENANCE_MODE=true in .config.php the application is in maintenance mode and the \Closure function 
     * returned from app/maintenance.php should be executed.
     *
     *
     * @return void 
    */
    public final function handleMaintenance(){
        if(strtolower($this->config['MAINTENANCE_MODE'])!='yes'){
            return;
        }//if
        global $argv;
        if(!empty($argv[2])){
            return;
        }//if
        $file = $this->path.'/app/maintenance.php';
        if(is_file($file)){
            $action = require($file);
        }//if
        else {
            $action = function(){ View::html('<h1>This site is currently undering going maintenance.</h1><p>It will be back up shortly.</p>');};
        }//el

        call_user_func($action);

        $this['View']->printPage();
        exit;

    }//handleMaintenance




    /**
     * Get the file contents of vendor/discophp/framework/addon-autoloads.php which is generated after updates
     * and unserialize it then return it.
     *
     *
     * @return array
    */
    public function addonAutoloads(){

        if($this->addonAutoloads==null){
            $p = $this->path.'/'.$this->config['COMPOSER_PATH'].'/discophp/framework/addon-autoloads.php';
            if(is_file($p)){
                $this->addonAutoloads = unserialize(file_get_contents($p));
                if(!is_array($this->addonAutoloads)){
                    $this->addonAutoloads = Array();
                }//if
            }//if
            else {
                $this->addonAutoloads = Array();
            }//el
        }//el

        return $this->addonAutoloads;

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
    public final function addCondition($k,$v){
        $this->defaultMatchCondition[$k]=$v;
    }//addCondition



    /**
     * Register the Default Disco Facades with the Application Container.
     *
     * @return void
    */
    public function services(){

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
            'View'      => 'Disco\classes\View'
        );

        foreach($facades as $facade=>$v){
            $this->make($facade,$v);
        }//foreach

        $this->as_factory('Router',function(){
            return new \Disco\classes\Router::$base;
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
    public function with($obj){
        if(!isset($this[$obj])){
            $this->make($obj,$obj);
        }//if
        return $this[$obj];
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
    public function make($obj,$val){
        if(!$val instanceof \Closure){
            $val = function($app) use($val){
                return $app->resolve_dependencies($val);
            };
        }//if
        $this[$obj] = $val;
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
    public function as_factory($obj,$val){
        if(!$val instanceof \Closure){
            $val = function($app) use($val){
                return $app->resolve_dependencies($val);
            };
        }//if
        $this[$obj] = $this->factory($val);
    }//factory



    /**
     * Register a protected service ( a Class with __call() defined or a \Closure function).
     *
     * @param string $obj The protected service to register.
     * @param string|\Closure $val The object name or \Closure function to be created or evaluated.
     *
    */
    public function as_protected($obj,$val){
         if(!$val instanceof \Closure){
            $val = function($app) use($val){
                return $app->resolve_dependencies($val);
            };
        }//if
        $this[$obj] = $this->protect($val);
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

        $instance = $this->with($key);

        $args = (!is_array($args)) ? Array() : array_values($args);
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

        $Ref = new \ReflectionClass($v);
        $con = $Ref->getConstructor();

        if(is_null($con)){
            return new $v;
        }//if

        $inject = Array();

        $ss = (string)$con;
        $ss = explode("\n",$ss);
        foreach($ss as $s){
            $s = trim($s);
            if(strpos($s,'Parameter #')!==false){
                $s = trim(explode('[',$s)[1]);
                $s = explode(' ',$s)[1];
                if(substr($s,0,1) != '$'){
                    $inject[] = $this->with($s);
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

    }//resolve

}//Disco

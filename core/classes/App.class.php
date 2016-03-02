<?php
namespace Disco\classes;
/**
 * Copyright 2014 WebYoke, webyoke.com 
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
 * The application container. This class is a Singleton.
*/
Class App extends \Pimple\Container {


    /**
     * @var string Absolute path of project.
    */
    public $path;


    /**
     * @var string The configuration directory.
    */
    public $configDir = '/app/config/';


    /**
     * @var string Is CLI request 
     */
    public $cli = false;


    /**
     * @var object Static reference to instance of App {@link \Disco\classes\App}.
    */
    public static $app;


    /**
     * @var array Default regex matching conditions.
    */
    public $defaultMatchCondition = Array(
        'alpha'         => '^[a-zA-Z\s\-]+$',
        'alpha_numeric' => '^[a-zA-Z\s\-0-9]+$',
        'integer'       => '^[\-0-9]+$',
        'numeric'       => '^[\-0-9\.]+$',
        'all'           => '[.]*'
    );


    /**
     * @var array $config Application configuration variables.
    */
    public $config = Array();


    /**
     * @var array $alias Application aliases.
    */
    public $alias = Array();


    /**
     * @var string $domain The domain name of the current application.
    */
    public $domain;


    /**
     * @var string $maintenance The PHP file path relative to the root of the app that should be executed when in 
     * maintenance mode.
    */
    public $maintenance = '/app/maintenance.php';



    /**
     * Get the application instance singleton {@link \Disco\classes\App}.
     *
     *
     * @param null|string $path Absolute path to root of the project.
     * @param null|string $domain The domain name the app is serving.
     *
     * @return \Disco\classes\App
    */
    public static function instance($path = null,$domain = null){

        if(!self::$app){
            self::$app = new \Disco\classes\App($path,$domain);
        }//if

        return self::$app;

    }//instance



    /**
     * Set up the application path, domain, configs, services, factories, 
     *
     *
     * @param null|string $path Absolute path to root of the project.
     * @param null|string $domain The domain name the app is serving.
    */
    public function __construct($path = null,$domain = null){

        //construct the PimpContainer
        parent::__construct();

        //Are we running in CLI mode?
        if(php_sapi_name() == 'cli'){

            $_SERVER['SERVER_NAME'] = null;

            if($path === null){
                $this->path = dirname(dirname(array_pop(debug_backtrace())['file']));
            } else {
                $this->path = $path;
            }//el

            $this->cli = true;

            global $argv;
            if(isset($argv[1]) && $argv[1]=='routes'){
                \Disco\classes\Router::$base = '\Disco\manage\Router';
            }//if

        }//if
        else if($path === null){
            $this->path = dirname($_SERVER['DOCUMENT_ROOT']);
        } else {
            $this->path = $path;
        }//el

        //a little magic
        $this['App']    = $this; 
        self::$app      = $this['App'];

        //register the default configuration options
        $this->registerConfig($this->path . $this->configDir . 'config.php');

        //register the dev configuration options if necessary
        if($this->config('DEV_MODE')){
            $this->registerConfig($this->path . $this->configDir . 'dev.config.php');
        }//if

        //regiser the default services into the container
        $this->registerServices($this->path . $this->configDir . 'services.php');

        //regiser the default factories into the container
        $this->registerFactories($this->path . $this->configDir . 'factories.php');

        //force the registery of the Router factory.
        $this->makeFactory('Router',function(){
            return \Disco\classes\Router::factory();
        });

        if($domain === null){
            if(!$this->config('DOMAIN')){
                $this->domain = 'http' . (!empty($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['SERVER_NAME'];
            } else {
                $this->domain = $this->config('DOMAIN');
            }//el
        } else {
            if(substr($domain,0,4) != 'http'){
                $domain = 'http://' . $domain;
            }//if
            $this->domain = $domain;
        }//el


    }//__construct



    /**
     * Set up some basic ini settings, the domain, running console commands if CLI, maintenance mode screen, and 
     * forcing requests to be HTTPS if necessary.
     *
     *
     * @return void
    */
    public function setUp(){


        //disable apache from append session ids to requests
        ini_set('session.use_trans_sid',0);

        //only allow sessions to be used with cookies
        ini_set('session.use_only_cookies',1);

        $this->registerAlias('disco.mime',dirname(__DIR__) . '/util/mimeTypes.php');

        //handle console commands
        if($this->cli && basename($_SERVER['PHP_SELF']) == 'index.php'){
            $console = new \Disco\classes\Console;
        }//if

        if(!$this->cli && $this->config('FORCE_HTTPS') && empty($_SERVER['HTTPS'])){
            \View::redirect(str_replace('http','https',$this->domain));
        }//if

        /**
         * Handle maintenance mode.
        */
        $this->handleMaintenance();


    }//setup



    /**
     * Make sure a \Disco\classes\Router matched against the requested URI, and serve the necessary page.
     *
     *
     * @return void
    */
    public final function tearDown(){

        \Disco\classes\Router::processLastCreatedRoute();


        /**
         * did this requested URI not find a match? If so thats a 404.
        */
        if(!\Disco\classes\Router::$routeMatch){
            \View::serve(404);
        }//if
        else {
            \View::serve();
        }//el

    }//tearDown



    /**
     * Merge a configuration file with the current app configuration.
     *
     *
     * @param array|string $config Either an array of configs, or a path to a config definition file that 
     * returns an array.
     *
     * @return boolean Was it registered.
    */
    public final function registerConfig($config){

        if(is_string($config)){
            if(!is_file($config)){
                return false;
            }//if
            $config = require $config;
        }//if

        if(!is_array($config)){
            return false;
        }//if

        $this->config = array_merge($this->config,$config);

        return true;

    }//registerConfig



    /**
     * Get/Set a config value.
     *
     * @param string $name Configuration setting to touch.
     * @param mixed $value The value to set.
     *
     * @return false|mixed False if not set, the value otherwise.
    */
    public function config($name,$value=null){

        if($value === null){

            if(!isset($this->config[$name])){
                return false;
            }//if

            return $this->config[$name];

        }//if

        $this->config[$name] = $value;

    }//config



    /**
     * Whether the application is running in dev mode.
     *
     * @return boolean
    */
    public function devMode(){
        return $this->config('DEV_MODE');
    }//devMode



    /**
     * Return the root working system path of the discophp project.
     *
     * @return string 
    */
    public function path(){
        return $this->path;
    }//path



    /**
     * Get the current fully qualified domain name. eg: `https://yoursite.com`.
     *
     *
     * @return string
    */
    public function domain(){
        return $this->domain;     
    }//domain



    /**
     * Register an alias. Aliases should adhere to the convention `your.alias`.
     *
     *
     * @param string $name The alias name (key).
     * @param string $path The alias path (value).
     *
     * @return void
    */
    public final function registerAlias($name,$path){
        $this->alias[$name] = $path;
    }//registerAlias



    /**
     * Get a previously registerd alias.
     *
     *
     * @param string $name The alias name (key).
     * 
     * @return string
    */
    public final function getAlias($name){
        return $this->alias[$name];
    }//getAlias



    /**
     * Resolve a path that uses an alias that has been registered. Aliases are resolved by using the syntax 
     * `@your.alias:the/rest/of/your/path/to/file.ext` where `@your.alias:` was defined earlier by registering 
     * `your.alias`.
     *
     *
     * @param string $path The aliased path.
     *
     * @return boolean|string False if no alias, the resolved alias path otherwise.
    */
    public final function resolveAlias($path){

        if(substr($path,0,1) != '@'){
            return false; 
        }//if

        $parts = explode(':',$path);
        $alias = substr($parts[0],1,strlen($parts[0]));
        $name = $parts[1];

        return $this->alias[$alias].$name;

    }//resolveAlias



    /**
     * Add a default matching condition for use with Router and Data. Store the $k and $v in 
     * $this->defaultMatchConditions .
     *
     *
     * @param string $k The conditions key. 
     * @param string $v The conditions regex value.
     *
     * @return void 
    */
    public final function registerCondition($k,$v){
        $this->defaultMatchCondition[$k]=$v;
    }//registerCondition



    /**
     * Get a matching condition.
     *
     *
     * @param string $k The conditions key.
     *
     * @return string
    */
    public final function getCondition($k){

        if(!isset($this->defaultMatchCondition[$k])){
            return false;
        }//if

        return $this->defaultMatchCondition[$k];

    }//getCondition



    /**
     * Match a registered condition against a value.
     *
     * @param string $k The condition key.
     * @param mixed $v The value to test the condition against.
     *
     * @return boolean The condition passed.
    */
    public final function matchCondition($k,$v){

        $condition = $this->getCondition($k);

        if(!$condition || !preg_match("/{$condition}/",$v)){
            return false;
        }//if

        return true;

    }//matchCondition


    /**
     * Register services into the application container. If the service already exists, it will be extended.
     *
     *
     * @param array|string $services Either an array of services, or a path to a services definition file that 
     * returns an array.
     * 
     * @return boolean Were the services registered.
    */
    public function registerServices($services){

        if(is_string($services)){
            if(!is_file($services)){
                return false;
            }//if
            $services = require $services;
        }//if

        if(!is_array($services)){
            return false;
        }//if

        foreach($services as $k => $v){

            if(!isset($this[$k])){
                $this->make($k,$v);
            }//if
            else {
                $this->extend($k,$v);
            }//el

        }//foreach

        return true;

    }//registerServices



    /**
     * Register services as factories into the application container.
     *
     *
     * @param array|string $factories Either an array of factories, or a path to a factories definition file that 
     * returns an array.
     *
     * @return boolean Were the factories registered.
    */
    public function registerFactories($factories){

        if(is_string($factories)){
            if(!is_file($factories)){
                return false;
            }//if
            $factories = require $factories;
        }//if

        if(!is_array($factories)){
            return false;
        }//if

        foreach($factories as $k => $v){

            $this->makeFactory($k,$v);

        }//foreach

        return true;

    }//registerFactories



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
                return $app->resolveDependencies($val);
            };

        }//if

        $this[$obj] = $val;

    }//make



    /**
     * Overwrite an existing service in the application container with a new service.
     *
     *
     * @param string $obj The service to register.
     * @param string|\Closure $val The object name or \Closure function to be created or evaluated.
     *
     * @return void
     *
    */
    public function extend($obj,$val){

        $app = self::$app;

        if(!$val instanceof \Closure){

            $val = function() use($val,$app){
                return $app->resolveDependencies($val);
            };

        }//if
       
        parent::extend($obj,$val);

    }//extend



    /**
     * Register a factory service with the container.
     *
     *
     * @param string $obj The factory service to register.
     * @param string|\Closure $val The object name or \Closure function to be created or evaluated.
     *
     * @return void
    */
    public function makeFactory($obj,$val){

        if(!$val instanceof \Closure){

            $val = function($app) use($val){
                return $app->resolveDependencies($val);
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
    public function makeProtected($obj,$val){
         if(!$val instanceof \Closure){
            $val = function($app) use($val){
                return $app->resolveDependencies($val);
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
    private function resolveDependencies($v){

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

    }//resolveDependencies



    /**
     * When MAINTENANCE_MODE=true in config.php the application is in maintenance mode and the \Closure function 
     * returned from app/maintenance.php should be executed.
     *
     *
     * @return void 
    */
    public final function handleMaintenance(){

        if(!$this->config('MAINTENANCE_MODE') || $this->cli){
            return;
        }//if

        http_response_code(503);

        $file = $this->path . $this->maintenance;
        if(is_file($file)){
            require $file;
        }//if
        else {
            echo '<h1>This site is currently undering going maintenance.</h1><p>It will be back up shortly.</p>';
        }//el

        exit;

    }//handleMaintenance



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

    }//error



}//Disco



/**
 * Easy global access to App Singleton {@link \App::$app}.
 *
 *
 * @param null|string $service A service to return from the application container.
 *
 * @return mixed|\Disco\classes\App
*/
function app($service = null){

    if($service !== null){
        return \App::instance()->with($service);
    }//if

    return \App::instance();

}//app

<?php

$composerPath = (isset($_SERVER['COMPOSER_PATH']))?$_SERVER['COMPOSER_PATH']:'vendor';
require_once("../{$composerPath}/autoload.php");

require_once('Prep.core.php');
//require_once('Controller.core.php');


//      The $
Class Disco {


    //      Has a router matched a request
    public static $routeMatch=false;

    //      Our Facades
    public static $facades=Array();

    /**
     *      Store a facade for potential use at
     *      some point in the applications life cycle
     *
     *      @param string $name
     *      @param closure $callback
     */
    //public static function make($name,$callback){
    //    global $disco;
    //    if(!isset($disco->facades[$name]))
    //        $disco->facades[$name]=$callback;
    //    else 
    //        $disco->facades[$name]=$callback;
    //}//make

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
     *      a router instance 
     *
     *      @return core/BaseRouter
     */
    public static function router(){
        return new BaseRouter();
    }//router


    /**
     *      Once a router has found a match we notify disco so we dont perform more match attempts.
     *      Unless we have a nested router, in which case we will flip the flag back to false
     *      to allow further processing
     *
     *      @param boolean $m
     *      @return boolean
     */
    public static function routeMatch($m=null){
        if($m!=null)
            Disco::$routeMatch=$m;

        return Disco::$routeMatch;

    }//routerMatch


    /**
    *       set a router
    *
    *       @param string $router
    */
    public static function useRouter($router){
        $routerPath = "../app/router/$router.router.php";
        if(file_exists($routerPath)){
            require_once($routerPath);
            Disco::routeMatch(false);
        }//if
    }//useRouter


}//Disco



/*
*       Make our DB Facade using
*       - core/BaseMySQLiDatabase.core.php
*       - core/facade/DB.facade.php
*/
Disco::make('DB',function(){
    return new BaseMySQLiDatabase();
});




/*
*       Make our View Facade using
*       - core/BaseView.core.php
*       - core/facade/View.facade.php
*/
Disco::make('View',function(){
    return new BaseView();
});




/**
*       Make our Template Facade using
*       - core/BaseTemplate.core.php
*       - core/facade/Template.facade.php
*
*/
Disco::make('Template',function(){
    return new BaseTemplate();
});




/**
*       Make our Model Facade using
*       - core/BaseModel.core.php
*       - core/facade/Model.facade.php
*
*/
Disco::make('Model',function(){
    return new BaseModel();
});



/**
*       Make our Util Facade using
*       - core/BaseUtilities.core.php
*       - core/facade/Util.facade.php
*
*/
Disco::make('Util',function(){
    return new BaseUtilities();
});



/*
*       Make our Cache Facade using
*       - core/BaseUtilities.core.php
*       - core/facade/Util.facade.php
*
*/
Disco::make('Cache',function(){
    return new BaseCache();
});




?>

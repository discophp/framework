<?php


/**
 *      The Router Class acts as a singlton one time consumer
 *
 */
class Router {

    /**
     *      Has a router matched a request.
    */
    public static $routeMatch=false;


    public static function get($param,$function){
        if($_SERVER['REQUEST_METHOD']!='GET'){
            return;
        }//if
        return self::instance()->get($param,$function);
    }//get

    public static function post($param,$function){
        if($_SERVER['REQUEST_METHOD']!='POST'){
            return;
        }//if
        return self::instance()->post($param,$function);
    }//post

    public static function any($param,$function){
        return self::instance()->any($param,$function);
    }//any

    public static function put($param,$function){
        if($_SERVER['REQUEST_METHOD']!='PUT'){
            return;
        }//if
        return self::instance()->put($param,$function);
    }//any


    public static function delete($param,$function){
        if($_SERVER['REQUEST_METHOD']!='DELETE'){
            return;
        }//if
        return self::instance()->delete($param,$function);
    }//any

    public static function secure(){
        if(empty($_SERVER['HTTPS'])){
            return;
        }//if
        return self::instance()->secure();
    }//any

    public static function auth($session,$action=null){
        return self::instance()->auth($session,$action);
    }//any




    /**
     *      a router instance 
     *
     *
     *      @return core/BaseRouter
     */
    private static function instance(){
        return new Disco\classes\Router();
    }//instance


    /**
     *      Once a router has found a match we notify disco so we dont perform more match attempts.
     *      Unless we have a nested router, in which case we will flip the flag back to false
     *      to allow further processing.
     *
     *
     *      @param boolean $m
     *      @return boolean
     */
    public static function routeMatch($m=null){
        if($m!=null)
            self::$routeMatch=$m;

        return self::$routeMatch;

    }//routerMatch


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
            self::$routeMatch=false;
            require($routerPath);
        }//if
    }//useRouter



}//Router








?>

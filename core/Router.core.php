<?php
/**
 *  This file holds the Router class.
*/


/**
 *      The Router Class acts as a Controller and Factory for
 *      all instances of \Disco\classes\Router.
 */
class Router {

    /**
     * @var boolean Has a Disco\classes\Router matched a request?
    */
    public static $routeMatch=false;


    /**
     * @var \Disco\classes\MockBox A MockBox instance.
    */
    public static $mockBox;


    /**
     * Give access to \Disco\classes\Router methods via the overloading of call static.
     * If there is already a Router that resolved an endpoint return the self::$mockBox which
     * can emulate an object that has the methods of \Disco\classes\Router in order to allow the method
     * chain specified by the originating call to continue without a Fatal error.
     *
     *
     * @param callable $method The method to be called.
     * @param array $args The arguements that were passed to $method.
     *
     * @return \Disco\classes\Router|\disco\classes\MockBox 
    */
    public static function __callStatic($method,$args){

        if(!self::$routeMatch){
            return call_user_func_array(Array(self::instance(),$method),$args);
        }//if

        return self::$mockBox;

    }//__callStatic



    /**
     * Get a fresh router instance. 
     *
     *
     * @return \Disco\classes\Router 
     */
    private static function instance(){
        return new Disco\classes\Router();
    }//instance



    /**
     * Once a router has found a match we dont perform more match attempts. 
     * This function is both a setter and a getter.
     *
     *
     * @param  boolean $m
     *
     * @return boolean
     */
    public static function routeMatch($m=null){
        if($m!=null)
            self::$routeMatch=$m;

        return self::$routeMatch;

    }//routerMatch



    /**
    * Load a Router File for processing.
    *
    *
    * @param string $router
    * @return void
    */
    public static function useRouter($router){
        $routerPath = Disco::$path."/app/router/$router.router.php";
        if(file_exists($routerPath)){
            self::$routeMatch=false;
            require($routerPath);
            return;
        }//if
        else {
            $routers = Disco::addonAutoloads();
            $routers = $routers['.router.php'];
            foreach($routers as $r){
                $test = substr($r,0,strlen($r)-strlen('.router.php'));
                $tail = substr($test,strlen($test)-strlen($router),strlen($router));
                if($router==$tail && is_file($r)){
                    self::$routeMatch=false;
                    require($r);
                    return;
                }//if
            }//foreach
        }//el

        $trace = Array();
        $e = debug_backtrace(FALSE,40);
        $e = array_reverse($e);
        $methods = Array('unknown','useRouter');
        foreach($e as $err){
            if(isset($err['file']) && isset($err['function']) && in_array($err['function'],$methods)){
                $trace['line']=$err['line'];
                $trace['file']=$err['file'];
                break;
            }//if
        }//foreach
        $msg = "Router::Router not found - {$router}  @ line {$trace['line']} in File: {$trace['file']} ";
        TRIGGER_ERROR($msg,E_USER_ERROR);

    }//useRouter

}//Router
?>

<?php

class Router extends Disco\classes\Facade {

    /**
     * @var boolean Has a Disco\classes\Router matched a request?
    */
    public static $routeMatch=false;

    /**
     * @var string The class to resolve from the container when Router is called. 
    */
    public static $base = '\Disco\classes\Router';



    protected static function returnFacadeId(){
        return 'Router';
    }//returnFacadeId



    /**
     * Once a router has found a match we dont perform more match attempts. 
     * This function is both a setter and a getter.
     *
     *
     * @param  boolean $m
     *
     * @return boolean
     */
    //public static function routeMatch($m=null){

    //    if($m !== null){

    //        if($m == true){
    //            \Disco::make('Router','\Disco\classes\MockBox');
    //        }//if
    //        else if(self::$routeMatch==true && $m==false){
    //            \Disco::as_factory('Router',function(){
    //                return new self::$base;
    //            });
    //        }//el

    //        self::$routeMatch=$m;

    //    }//if

    //    return self::$routeMatch;

    //}//routerMatch



    /**
    * Load a Router File for processing.
    *
    *
    * @param string $router
    * @return void
    */
    public static function useRouter($router){

        if(self::$routeMatch){
            return;
        }//if

        $routerPath = \Disco::$path."/app/router/$router.router.php";
        if(file_exists($routerPath)){
            require($routerPath);
            return;
        }//if
        else {
            $routers = \Disco::addonAutoloads();
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

        $app = \Disco::$app;
        $app->error("Router $router.router.php not found",Array('unknown','useRouter'),debug_backtrace(TRUE,4));

    }//useRouter


}//Cache



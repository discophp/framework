<?php

class Router extends Disco\Facade {


    public static function routeMatch($m = null){
        return \Disco\http\Router::routeMatch($m);
    }//routeMatch


    protected static function returnFacadeId(){
        return 'Router';
    }//returnFacadeId


}//Router

?>

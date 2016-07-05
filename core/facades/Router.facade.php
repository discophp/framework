<?php

class Router extends Disco\classes\Facade {


    public static function routeMatch($m = null){
        return \Disco\classes\Router::routeMatch($m);
    }//routeMatch


    protected static function returnFacadeId(){
        return 'Router';
    }//returnFacadeId


}//Router

?>

<?php

abstract class Facade {

    /**
     *      magic method __callStatic
     *
     *      @param function $method
     *      @param mixed $args
     *      @return mixed
     */
    public static function __callStatic($method,$args){

        $instance = Disco::$facades[static::returnFacadeId()];

        if($instance instanceof Closure){
            $instance=call_user_func($instance);
            Disco::$facades[static::returnFacadeId()]=$instance;
        }//if

        return Disco::handle($instance,$method,$args);

    }//callStatic



    abstract protected static function returnFacadeId();



}//BaseInstanceController

?>

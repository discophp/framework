<?php

abstract class Facade {


    /**
     *      classes that extend the Facade MUST IMPLEMENT 
     *      this method!
     */
    abstract protected static function returnFacadeId();



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


    /**
     *      Return the instance of the object 
     *
     *      @return mixed
     */
    public static function instance(){

        $instance = Disco::$facades[static::returnFacadeId()];

        if($instance instanceof Closure){
            $instance=call_user_func($instance);
            Disco::$facades[static::returnFacadeId()]=$instance;
        }//if

        return $instance;

    }//callStatic



}//Facade

?>

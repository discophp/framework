<?php

namespace Disco;

/**
 *      This file holds the Facade class
 *
 *      This abstract class is extremelly important to the operation of the 
 *      Disco Framework. It empowers our Inversion of Control and Facading principles.
 *
 *      You should only tamper with it if you are willing to break it :)
*/



/**
 *
 *      Facade class.
 *      This class is abstract meaning it cannot be instantiated directly.
 *      If you are to extend it you must implement the method returnFacadeId().
 *
*/
abstract class Facade {


    /**
     *      classes that extend the Facade MUST IMPLEMENT 
     *      this method!
     */
    abstract protected static function returnFacadeId();



    /**
     *      magic method __callStatic
     *
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

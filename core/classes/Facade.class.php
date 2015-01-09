<?php
namespace Disco\classes;
/**
 * This file holds the Facade class.
 *
 * This abstract class is extremelly important to the operation of the 
 * Disco Framework! It empowers our Inversion of Control and Facading principles.
 *
 * You should really get comfortable with how this Class works if you want to master the Disco PHP Framework.
 * You how ever, should only tamper with it if you are willing to break it :)
*/



/**
 * Facade class.
 * This class is abstract meaning it cannot be instantiated directly.
 * If you are to extend it you must implement the method returnFacadeId().
 *
 * For information on what this class accomplishes and how it works see the Disco documentation for Inversion of Control and 
 * Facading at http://discophp.com/docs/IoC-facades .
*/
abstract class Facade {


    /**
     * Classes that extend the Facade MUST IMPLEMENT this method!
     * This method is in charge of returning the Key used in the IoC container to access the underlying instance .
     * It must be unique within your application and within the Framework.
     *
     * @return string The Key of the instance in the IoC container.
     */
    protected static function returnFacadeId(){}



    /**
     * magic method __callStatic(), see php doc at 
     * http://php.net/manual/en/language.oop5.overloading.php#object.callstatic .
     *
     * When a static method call on the Facade is made (ex DB::query()) if the static method called
     * on the Facade does not exist, then resolve the underlying Base Class from the IoC container and attempt to call
     * a non-static method of the same name, and with the same parameters on the instance.
     *
     *
     * @param callable  $method The method that is attempting to be accessed on the Static Facade but failed to exist.
     * @param mixed     $args The arguements that were passed to the original $method.
     *
     * @return mixed    Return the result of the method call on the resolved instance from the IoC container.
     */
    public static function __callStatic($method,$args){

        $app = \Disco\classes\App::instance();
        return $app->handle(static::returnFacadeId(),$method,$args);

    }//callStatic



    /**
     * Return the instance of the Facades underlying Base Class from the IoC container.
     *
     *
     * @return object  
     */
    public static function instance(){

        //$app = \App::$app;
        $app = \Disco\classes\App::instance();
        return $app[static::returnFacadeId()];

    }//callStatic

}//Facade
?>

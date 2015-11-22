<?php
namespace Disco\classes;
/**
 * This file holds the ModelFactory Class.
*/


/**
 * ModelFactory class.
*/
class ModelFactory {



    /**
     * Get a new model of `$name`.
     *
     *
     * @param string $name The name of the model.
     *
     * @return object The model.
    */
    public final function m($name){
        return new $name;
    }//m



}//ModelFactory
?>

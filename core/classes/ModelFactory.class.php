<?php
namespace Disco\classes;
/**
 * This file holds the ModelFactory Class.
*/


/**
 * ModelFactory class.
 * Centeralized point of access to instances of models.
*/
class ModelFactory {

    /**
     * @var array Model storage.
    */
    private $models=Array();



    /**
     * Access a model.
     *
     *
     * @param string $name The name of the model.
     *
     * @return object Return $this->models[$name] the instance of the Model
    */
    public final function m($name){
        if(isset($this->models[$name]))
            return $this->models[$name];

        $this->models[$name]=new $name();
        return $this->models[$name];

    }//use

}//ModelFactory
?>

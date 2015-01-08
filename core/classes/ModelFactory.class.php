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

    private $app;

    public function __construct(\App $app){
        $this->app = $app;
    }//__construct

    /**
     * Access a model.
     *
     *
     * @param string $name The name of the model.
     *
     * @return object Return $this->models[$name] the instance of the Model
    */
    public final function m($name){
        return $app->with($name);
    }//use

}//ModelFactory
?>

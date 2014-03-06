<?php


class BaseModel {

    private $models=Array();

    public final function m($name){
        if(isset($this->models[$name]))
            return $this->models[$name];

        $this->models[$name]=new $name();
        return $this->models[$name];

    }//use

    public function __invoke($name){
        return $this->m($name);
    }//__invoke

}//Model


?>

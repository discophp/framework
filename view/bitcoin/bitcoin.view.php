<?php

class bitcoin extends BaseView {

    public $title = 'Default Title'; 

    //name of your default stylesheet
    public $styleSheet = 'css';

    //name of your default javascript file
    public $script = 'js';


    public function header(){
        return Template::build('bitcoin/header');
    }//header


    //this function will be called whenever the object is constructed
    public function prepare(){

        //$this->title = "Default Page Title";
        //$this->description = "'Default Page Description'";

        //foundation dependencies
        $this->styleSrc('foundation.min');
        $this->scriptSrc('foundation.min');
        $this->scriptSrc('modernizr');
        //initilization call for foundation
        $this->pushScript('$(document).foundation();');


    }//construct


}//View

?>

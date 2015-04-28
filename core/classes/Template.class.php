<?php
namespace Disco\classes;
/**
 * This file holds the Template class.
*/


/**
 * Template class.
 * Provide support for using tempaltes that are stored in ../app/template/ .
 * See documentation online at http://discophp.com/docs/Template .
*/
Class Template extends \Twig_Environment {

    public $extension;

    /**
     * Build a template and push its html onto the Views html stack.
     *
     *
     * @param string $name The template name.
     * @param array $data The data to pass the template.
     *
     * @return void
    */
    public function with($name,$data=Array()){
        \App::with('View')->html($this->build($name,$data));
    }//with



    /**
     * Build a template.
     *
     *
     * @param string $name The template name.
     * @param array $data The data to pass the template.
     *
     * @return string
    */
    public function build($name,$data=Array()){
        if(!$this->extension) $this->extension = \App::config('TEMPLATE_EXTENSION');
        return $this->render($name.$this->extension,$data);
    }//build



    /**
    /**
     * Build a template directly from a Model.
     *
     *
     * @param string $name  The name of the template.
     * @param string $model The name of the Model.
     * @param string $key   The key used to select data from the model with.
     *
     * @return string The built template.
    */
    public function buildFrom($name,$model,$key){
        $d = $this->app->with($model)->select('*')->where($key)->data();
        $o = '';
        while($r = $d->fetch_assoc()){
            $o .= $this->build($name,$r);
        }//while

        return $o;
    }//from



    /**
     * Build a template directly from a Model and push it onto the Views html stack.
     *
     *
     * @return void
    */
    public function from($name,$model,$key){
        \App::with('View')->html($this->build_from($name,$model,$key));
    }//from


}//Template

?>

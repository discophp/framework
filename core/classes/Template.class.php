<?php
namespace Disco\classes;
/**
 * This file holds the Template class.
*/


/**
 * Template class.
 * Extend \Twig_Environment to provide extra functionality..
*/
Class Template extends \Twig_Environment {



    /**
     * Get the default disco twig loader which enables extension-less template use.
     *
     *
     * @param null|string|array $path The absolute path to the template directory, or an array of directories.
     *
     * @return \Disco\classes\TemplateLoader
    */
    public static function defaultLoader($path = null){

        if($path === null){
            $path = \App::path() . '/' . trim(\App::config('TEMPLATE_PATH'),'/');
        }//if

        return new \Disco\classes\TemplateLoader($path);

    }//defaultLoader



    /**
     * Construct the \Twig_Environment.
     *
     *
     * @param null|\Twig_Loader_Filesystem|\Twig_Loader_Array $loader The loader in charge of finding twig 
     * templates.
     * @param null|string|array $options Either a relative string path to the twig configuration options, an array 
     * of configuration options, or null for no options.
    */
    public function __construct($loader = null, $options = 'app/config/twig.php'){

        if($loader === null){
            $loader = self::defaultLoader();
        }//if

        if($options !==null && !is_array($options)){
            $options = require \App::path() . '/' . trim($options,'/');
        }//if

        parent::__construct($loader, $options);

        $this->addExtension(new \Disco\twig\TwigExtension);

    }//construct



    /**
     * Is the file a template that exists?
     *
     *
     * @param string $name The template.
     *
     * @return boolean Whether the template exists.
    */
    public function isTemplate($name){

        try {
            $this->loadTemplate($name);
            return true;
        } catch(\Twig_Error_Loader $e){
            return false;
        }//catch 

    }//isTemplate



    /**
     * Render a template resolving any aliases used in the template name path.
     *
     *
     * @param string $name The template name.
     * @param array $data The variables to pass into the template.
     *
     * @return string The rendered template.
    */
    public function render($name,array $data = Array()){
        return parent::render($name,$data);
    }//render



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
        return $this->render($name,$data);
    }//build



}//Template

?>

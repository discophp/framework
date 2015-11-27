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
     * @var string $path The root directory to mount the \Twig_Loader_Filesystem at.
    */
    private $path;



    /**
     * Using the application coniguration `TEMPLATE_PATH` create a \Twig_Loader_Filesystem and construct the 
     * \Twig_Environment specifying from the application configuration:
     * - `cache => TEMPLATE_CACHE`
     * - `auto_reload => TEMPLATE_RELOAD`
     * - `autoescape => TEMPLATE_AUTOESCAPE`
     *
     * or you can specify these in the constructor arguements.
     *
     * Adds as globals to the twig environment 3 services:
     * - `View`
     * - `Request`
     * - `Cache`
     *
     * @param null|string $path The path relative to the projects root directory that contains templates.
     * @param null|string $cachePath The path relative to the projects root directory to store compiled templates in.
     * @param null|boolean $reload Auto reload templates.
     * @param null|boolean $escape Auto escape html entities.
    */
    public function __construct($path=null, $cachePath=null, $reload=null, $escape=null){

        if($path === null){

            $path       = \App::path() . '/' . trim(\App::config('TEMPLATE_PATH'),'/');
            $cachePath  = \App::path() . '/' . trim(\App::config('TEMPLATE_CACHE'),'/');
            $reload     = \App::config('TEMPLATE_RELOAD');
            $escape     = \App::config('TEMPLATE_AUTOESCAPE');

        }//if

        $this->path = $path;

        $loader = new \Twig_Loader_Filesystem($path);

        parent::__construct($loader, array(
            'cache'         => $cachePath,
            'auto_reload'   => $reload,
            'autoescape'    => $escape
        ));

        $this->addFunction(new \Twig_SimpleFunction('View',function(){
            return \App::with('View');
        }));

        $this->addFunction(new \Twig_SimpleFunction('Request',function(){
            return \App::with('Request');
        }));

        $this->addFunction(new \Twig_SimpleFunction('Cache',function(){
            return \App::with('Cache');
        }));

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

        $name = $this->buildTemplatePath($name);

        if(!is_file($this->path . '/' . $name)){
            return false;
        }//if

        return true;

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
        $name = $this->buildTemplatePath($name);
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
     * Resolve any aliases contained in the template path name and apply a default template extension if the 
     * extension is not already present in the path name. Returned the corrected path name.
     *
     *
     * @param string $name The template name.
     *
     * @return string The corrected path.
    */
    public function buildTemplatePath($name){

        if(($alias = \App::resolveAlias($name)) !== false){
            $name = $alias; 
        }//if

        $extension = \App::config('TEMPLATE_EXTENSION');
        $extLen = strlen($extension);
        $nameLen = strlen($name);
        if($extLen && substr($name,$nameLen-$extLen,$nameLen) !== $extension){
            $name .= $extension;
        }//if

        return $name;

    }//buildTemplatePath



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



    /**
     * Build a template directly from a Model.
     *
     *
     * @param string $name The name of the template.
     * @param string $model The name of the Model.
     * @param mixed $key The key(s) used to select data from the model.
     *
     * @return string The built template.
    */
    public function buildFrom($name,$model,$key){
        $d = \App::with($model)->select('*')->where($key)->data();
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
     * @param string $name The name of the tempalte.
     * @param string $model The name of the Model.
     * @param mixed $key The key(s) used to select data from the model.
     *
     * @return void
    */
    public function from($name,$model,$key){
        \App::with('View')->html($this->build_from($name,$model,$key));
    }//from



}//Template

?>

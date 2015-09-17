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

    private $path;

    public function __construct(){

        $path = trim(\App::config('TEMPLATE_PATH'),'/');
        $cachePath = trim(\App::config('TEMPLATE_CACHE'),'/');
        $path = \App::path() . '/' .$path;

        $this->path = $path;

        $loader = new \Twig_Loader_Filesystem($path);

        parent::__construct($loader, array(
            'cache'         => \App::path(). '/' . $cachePath,
            'auto_reload'   => \App::config('TEMPLATE_RELOAD'),
            'autoescape'    => \App::config('TEMPLATE_AUTOESCAPE')
        ));

        //register the url function with twig
        $this->addFunction(new \Twig_SimpleFunction('url',array('\Disco\classes\View','url')));
        $this->addGlobal('View',\App::with('View'));
        $this->addGlobal('Request',\App::with('Request'));

    }//construct



    public function isTemplate($name){

        $name = $this->buildTemplatePath($name);

        if(!is_file($this->path . '/' . $name)){
            return false;
        }//if

        return true;

    }//isTemplate


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



    private function buildTemplatePath($name){

        if(($alias = \App::resolveAlias($name)) !== false){
            $name = $alias; 
        }//if

        if(!$this->extension) {
            $this->extension = \App::config('TEMPLATE_EXTENSION');
            $extLen = strlen($this->extension);
            $nameLen = strlen($name);
            if($extLen && substr($name,$nameLen-$extLen,$nameLen) !== $this->extension){
                $name .= $this->extension;
            }//if
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

        $name = $this->buildTemplatePath($name);

        return $this->render($name,$data);

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

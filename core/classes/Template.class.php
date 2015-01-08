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
Class Template {

    /**
     * @var array Store our accessed templates.
    */
    public $templates = Array();


    /**
     * @var string Name of the working template.
    */
    private $workingTemplate='';


    /**
     * @var string HTML of template being manipulated.
    */
    private $beingModified='';


    /**
     * @var string How we access variables.
     */
    private $delin = "{{\$%1\$s}}";


    /**
     * @var string How we access copies of a variable.
    */
    private $copy = "{{&\$%1\$s}}";


    /**
     * @var Array Assoc keys of variables names and there data for nested templates.
    */
    private $dataStack = Array();


    /**
     * @var string Live template to build.
    */
    private $live;


    private $app;

    public function __construct(){
        $this->app = \App::instance();
    }//__construct


    /**
     * Load a template from disk/Cache.
     *
     *
     * @param string $name The name of the template.
     *
     * @return void
    */
    private function loadTemplate($name){

        $path = $this->templatePath($name);

        if(class_exists('Memcache',false) && isset($this->app->config['MEMCACHE_HOST']) && isset($this->app->config['MEMCACHE_PORT'])){
            if($this->app['Cache']->getServerStatus($this->app->config['MEMCACHE_HOST'],$this->app->config['MEMCACHE_PORT'])!=0){
                $lastModifiedCache = $this->app['Cache']->get($path.'-last-modified');
                if($lastModifiedCache){
                    $lastModified = filemtime($path);
                    if($lastModifiedCache!=$lastModified){
                        $this->cacheTemplate($path,$name);
                    }//if
                    else {
                        $this->templates[$name]=$this->app['Cache']->get($path);
                    }//el
                }//if
                else {
                    $this->cacheTemplate($path,$name);
                }//el

            }//if
            else if(!isset($this->templates[$name])){
                $this->templates[$name] = $this->getTemplateFromDisk($path);
            }//if
        }//if
        else if(!isset($this->templates[$name])){
            $this->templates[$name] = $this->getTemplateFromDisk($path);
        }//if

    }//getTemplate


    private function templatePath($name){

        $userPath = $this->app->path."/app/template/{$name}.template.html";

        if(is_file($userPath)){
            return $userPath;
        }//if
        else {

            $templates = $this->app->addonAutoloads();
            $templates = $templates['.template.html'];
            foreach($templates as $t){
                $test = substr($t,0,strlen($t)-strlen('.template.html'));
                $tail = substr($test,strlen($test)-strlen($name),strlen($name));
                if($name==$tail && is_file($t)){
                    return $t;
                }//if
            }//foreach

        }//el

        return $userPath;
        
    }//templateInfo



    /**
     * Retrieve a specified tempalte from disk.
     *
     *
     * @param string $path The path to the tempalte.
     *
     * @return string 
    */
    private function getTemplateFromDisk($path){

        if(file_exists($path)){
            return file_get_contents($path);
        }//if
        else {
            $this->app->error("Template::Error loading template $path",Array('with','build','live','from'),debug_backtrace(TRUE,12));
        }//el

    }//getTempalteFromDisk



    /**
     * Set the tempalte in the running memcached server if there is one available and connected.
     *
     *
     * @param string $path The path to the template.
     * @param string $name The name of the template.
     *
     * @return void
    */
    private function cacheTemplate($path,$name){

        $this->templates[$name]=$this->getTemplateFromDisk($path);
        $this->app['Cache']->set($path.'-last-modified',filemtime($path));
        $this->app['Cache']->set($path,$this->templates[$name]);

    }//cacheTemplate



    /**
     * Get a template, if its not in the cache, get it from the disk and cache it..
     *
     *
     * @return string
    */
    private function getTemplate($name){
         if(!isset($this->templates[$name])){
            $this->loadTemplate($name);        
        }//if
        return $this->templates[$name];
    }//getTemplate



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
        $this->app['View']->html($this->build($name,$data));
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

        if(!$this->live){
            $t = $this->getTemplate($name);
        }//if
        else {
            $t = $this->live;
            $this->live = null;
        }//el

        $t = $this->set($t,$data);

        $stack = $this->dataStack;
        $this->dataStack = Array();

        foreach($stack as $k=>$v){

            do {

                $info = $this->parseInfo($k,$t);
                if(!$info)
                    continue;

                $nest = '';
                if(isset($v[0]) && is_array($v[0])){
                    foreach($v as $nk=>$nv){
                        $nest .= $this->build($info['templateName'],$nv);
                    }//foreach
                }
                else if($v) {
                    $nest .= $this->build($info['templateName'],$v);
                }//el
                else if($info['justTemplate']){
                    $nest .= $this->build($info['templateName']);
                }//el

                $t = str_replace($info['textBlock'],$nest,$t);

            } while($info);

        }//foreach

        $info = null;

        do {

            $info = $this->parseInfo('',$t);
            if(!$info)
                continue;


            $nest = $this->build($info['templateName']);
            $t = str_replace($info['textBlock'],$nest,$t);

        } while($info);

        $t = $this->setElses($t,$data);

        return $t;

    }//build



    /**
     * Treat dynamic markup as a template to be processed.
     *
     *
     * @param string    $markup The string to treat as a template.
     * @param array     $data   The data to pass to the $markup.
     *
     * @return string
    */
    public function buildLive($markup,$data=Array()){
        $this->live = $markup;
        return $this->build('',$data);
    }//live



    /**
     * Treat dynamic markup as a template to be processed and push it onto the Views html stack.
     *
     *
     * @param string    $markup The string to treat as a template.
     * @param array     $data   The data to pass to the $markup.
     *
     * @return string
    */
    public function live($markup,$data=Array()){
        $this->live = $markup;
        $this->app['View']->html($this->build('',$data));
    }//live



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
        $this->app['View']->html($this->build_from($name,$model,$key));
    }//from



    /**
     * Set data into the template.
     *
     *
     * @param array $data The data to set into the template.
     *
     * @return self 
    */
    public function set($t,$data){

        foreach($data as $k=>$v){

            if(is_array($v)){ 
                $this->dataStack[$k] = $v;
                continue;
            }//if

            $s = sprintf($this->delin,$k);
            $td = $k;
            $copy = sprintf($this->copy,$k);

            $t = implode($v,explode($s,$t,2));

            $t = implode($v,explode($copy,$t));

        }//foreach

        return $t;

    }//set



    /**
     * Get any nested templates information so that they can injected.
     *
     * 
     * @param string $k The name of data a template would need passed.
     * @param string $t The template to parse for nested templates.
     *
     * @return mixed
    */
    private function parseInfo($k,$t){
        $pos = stripos($t,"{{\${$k} with @");
        $data = Array('templateName'=>'','textBlock'=>'','justTemplate'=>false);

        if($pos===false){
            $data['justTemplate']=true;
            $pos = stripos($t,"{{@");
            if($pos===false){
                return;
            }//if
        }//if


        //this looks like
        //{{people with @person}}
        $nt = substr($t,$pos,stripos($t,"}}",$pos)+2-$pos);
        $nt = trim($nt);

        //this looks like "person"
        $ntemp = substr($nt,stripos($nt,'@')+1);
        $ntemp = substr($ntemp,0,-2);
        $ntemp=trim($ntemp);

        $data['templateName']=$ntemp;
        $data['textBlock']=$nt;

        return $data;

    }//parseInfo



    /**
     *  Set the else statements in the template. This is only run once 
     *  per template build.
     *
     *
     *  @return void
    */
    private function setElses($t,$data){

        $constLen = 6;

        do {

            $else = stripos($t,'{{else ');
            if($else===false){
                return $t;
            }//if

            $beg = stripos($t,'{{');
            $end = stripos($t,'}}',$else);

            $endV = substr($t,$else+$constLen+1,$end - ($else+$constLen+1) );

            if(substr($endV,0,1) == '$'){
                $endV = ltrim($endV,'$');
                $endV = (isset($data[$endV])) ? $data[$endV] : '';
            }//if
            else {
                $endV = substr($endV,1,strlen($endV)-2);
            }//el

            $t = substr_replace($t,$endV,$beg,$end+2 - $beg);

        } while($else !== false);

        return $t;

    }//setElses



    /**
     * Determine whether or not an array is associative or numeric
     *
     * 
     * @param array $array
     * @return boolean
    */
    private function is_assoc(&$array) {
        foreach($array as $k=>$v){
            if(is_int($k))
                return false;
            return true;
        }//foreach
    }//is_assoc

}//Template

?>

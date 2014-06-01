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
     * Load a template from disk/Cache.
     *
     *
     * @param string $name The name of the template.
     *
     * @return void
    */
    private function loadTemplate($name){

        $path = $this->templatePath($name);

        if(isset($_SERVER['MEMCACHE_HOST']) && isset($_SERVER['MEMCACHE_PORT'])){
            if(\Cache::getServerStatus($_SERVER['MEMCACHE_HOST'],$_SERVER['MEMCACHE_PORT'])!=0){

                $lastModifiedCache = \Cache::get($path.'-last-modified');
                if($lastModifiedCache){
                    $lastModified = filemtime($path);
                    if($lastModifiedCache!=$lastModified){
                        $this->cacheTemplate($path,$name);
                    }//if
                    else {
                        $this->templates[$name]=\Cache::get($path);
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

        $userPath = \Disco::$path."/app/template/{$name}.template.html";

        if(is_file($userPath)){
            return $userPath;
        }//if
        else {

            $templates = \Disco::addonAutoloads();
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
            $trace = Array();
            $e = debug_backtrace(TRUE, 12);
            $methods = Array('with','build','live');
            foreach($e as $err){
                if(isset($err['file']) && isset($err['function']) && in_array($err['function'],$methods)){
                    $trace['args']=$err['args'];
                    $trace['line']=$err['line'];
                    $trace['file']=$err['file'];
                }//if
            }//foreach
            $msg = "Template::Error loading template - {$trace['args'][0]} @ line {$trace['line']} in File: {$trace['file']} ";
            TRIGGER_ERROR($msg,E_USER_ERROR);
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
        \Cache::set($path.'-last-modified',filemtime($path));
        \Cache::set($path,$this->templates[$name]);

    }//cacheTemplate



    /**
     * Get the current template.
     *
     *
     * @return string
    */
    private function getWorkingTemplate(){
        if(!isset($this->templates[$this->workingTemplate])){
            $this->loadTemplate($this->workingTemplate);        
        }//if
        return $this->templates[$this->workingTemplate];
    }//getWorkingTempalte



    /**
     * Work on a specific template.
     *
     *
     * @param string $name The name of the template to work on.
     *
     * @return self 
     */
    public function name($name){
        $this->workingTemplate=$name;
        $this->beingModified=$this->getWorkingTemplate();
        return $this;
    }//use



    /**
     * Get the markup of the active template.
     *
     *
     * @return string
    */
    public function html(){
       return $this->beingModified; 
    }//return



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
        \View::html($this->build($name,$data));
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
        $this->workingTemplate=$name;
        $this->beingModified = $this->getWorkingTemplate();
        $this->beingModified = $this->appendTemplate($data);

        if(count($data)!=0){

            //call set to embed the variables
            $this->set($data);

        }//if

        //cal setElses to set the else clauses
        $this->setElses();



        return $this->beingModified;

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
    public function live($markup,$data=Array()){
        $this->beingModified = $markup;
        $this->beingModified = $this->appendTemplate($data);

        if(count($data)!=0){

            //call set to embed the variables
            $this->set($data);

        }//if

        //cal setElses to set the else clauses
        $this->setElses();


        return $this->beingModified;

    }//live



    /**
     * Set data into the template. This function is recursive in nature!
     *
     *
     * @param array $data The data to set into the template.
     *
     * @return self 
    */
    public function set($data){
        $t = $this->beingModified;
        $arrays = Array();

        $this->beingModified=$this->appendTemplate('');

        foreach($data as $k=>$v){

            if(is_array($v)){ 
                $arrays[]=$v;
                continue;
            }//if


            if(is_numeric($k)){
                $s = sprintf($this->delin,$this->lastArrayName);
                $td=$this->lastArrayName;
                $copy = sprintf($this->copy,$this->lastArrayName);
            }//if
            else {
                $s = sprintf($this->delin,$k);
                $td = $k;
                $copy = sprintf($this->copy,$k);
            }//el

            $t = implode($v,explode($s,$t,2));

            $t = implode($v,explode($copy,$t));

            $this->beingModified=$t;

            unset($data[$k]);

        }//foreach

        foreach($arrays as $k=>$v){

            if(is_array($v)){ 
                $this->beingModified=$this->appendTemplate($v);
                $this->lastArrayName=$k;
                $this->set($v);
                unset($arrays[$k]);
                continue;
            }//if

        }//foreach

        return $this;

    }//setData



    /**
     * Append nested templates.
     *
     *
     * @param array $data The data to carry to the nested template.
     *
     * @return string
    */
    private function appendTemplate($data){
        $t = $this->beingModified;
        if(count($data)==0){

            do {
                $info = $this->parseInfo('',$t);
                if($info==null){
                    continue;
                }//if
                $t = $this->insertTemplate($info,'',$t);
            } while($info);

            return $t;
        }//if

        foreach($data as $k=>$v){

            do {
                $info = $this->parseInfo($k,$t);
                if($info==null){
                    continue;
                }//if
                $t = $this->insertTemplate($info,$v,$t);
            } while($info);
        }//foreach
        return $t;
    }//appendTemplate



    /**
     * Inject the template into the calling template.
     *
     *
     * @param array $data The data returned from calling $this->parseInfo on a template.
     * @param mixed $v The data that should be passed to the injected template. 
     * @param string $t The template currently needing injecting.
     *
     * @return string
    */
    private function insertTemplate($data,$v,$t){
        $this->loadTemplate($data['templateName']);

        $copies = $this->templates[$data['templateName']];
        if($data['justTemplate']){
            $t = str_replace($data['textBlock'],$copies,$t);
        }//if
        else if(is_array($v)){
            if(!$this->is_assoc($v))
                $copies = str_repeat($copies,count($v));
            $t = str_replace($data['textBlock'],$copies,$t);
        }//if
        else {
            $t = str_replace($data['textBlock'],$copies,$t);
        }//el

        return $t;

    }//insert



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
    private function setElses(){

        $testDelin = '({{\$[a-zA-Z0-9]+}}{{else [a-zA-Z0-9\s\"\'\>\<\/]*}})';
        preg_match("/{$testDelin}/",$this->beingModified,$matches);
        do {
            if($matches){
                foreach($matches as $m){
                    $orgM=$m;

                    $elsePos = stripos($orgM,'else '); 
                    if($elsePos!==false)
                        $elseContent = rtrim(substr($orgM,$elsePos+6),'\'"}');
                    else
                        $elseContent='';

                    $this->beingModified = implode($elseContent,explode($orgM,$this->beingModified));

                }//foreach
            }//if

            if($matches)
                preg_match("/{$testDelin}/",$this->beingModified,$matches);

        } while($matches);


        do {

            $testDelin = '({{else [a-zA-Z0-9\s\"\'\>\<\/]*}})';
            preg_match("/{$testDelin}/",$this->beingModified,$matches);
            if($matches){
                foreach($matches as $m){
                    $this->beingModified = implode('',explode($m,$this->beingModified));
                }//foreach
            }//if

            if($matches)
                preg_match("/{$testDelin}/",$this->beingModified,$matches);


        } while($matches);


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
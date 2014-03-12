<?php
/**
*       This file holds the BaseTemplate class
*/


/**
*       BaseTemplate class.
*       Provide support for using tempaltes that are stored in ../app/template/
*       See documentation online at discophp.com/docs/Tempalte
*
*/
Class BaseTemplate {

    /**
     *      Store our accessed templates
    */
    public $templates = Array();


    /**
     *      name of the working template
    */
    private $workingTemplate='';


    /**
     *      html of template being manipulated 
    */
    private $beingModified='';


    /**
     *      how we access variables
     */
    private $delin = "{{\$%1\$s}}";


    /**
     *      how we access copies of a variable
    */
    private $copy = "{{&\$%1\$s}}";



    /**
     *      Load a template from disk/Cache
     *
     *
     *      @param string $name
     *      @return void
    */
    private function loadTemplate($name){
        $path = "../app/template/{$name}.template.html";

        if(isset($_SERVER['MEMCACHE_HOST']) && isset($_SERVER['MEMCACHE_PORT'])){
            if(Cache::getServerStatus($_SERVER['MEMCACHE_HOST'],$_SERVER['MEMCACHE_PORT'])!=0){

                $lastModifiedCache = Cache::get($path.'-last-modified');
                if($lastModifiedCache){
                    $lastModified = filemtime($path);
                    if($lastModifiedCache!=$lastModified){
                        $this->cacheTemplate($path,$name);
                    }//if
                    else {
                        $this->templates[$name]=Cache::get($path);
                    }//el
                }//if
                else {
                    $this->cacheTemplate($path,$name);
                }//el

            }//if
            else if(!isset($this->templates[$name]) && file_exists($path)){
                $this->templates[$name] = file_get_contents($path);
            }//if
        }//if
        else if(!isset($this->templates[$name]) && file_exists($path)){
            $this->templates[$name] = file_get_contents($path);
        }//if

    }//getTemplate



    /**
     *      Retrieve a specified tempalte from disk
     *
     *
     *      @param string $path the path to the tempalte
     *      @return string 
    */
    private function getTemplateFromDisk($path){

        if(file_exists($path)){
            return file_get_contents($path);
        }//if

    }//getTempalteFromDisk



    /**
     *      Set the tempalte in the running memcached server if there is 
     *      one available and connected.
     *
     *
     *      @param string $path the path to the template
     *      @param string $name the name of the template
     *      @return void
    */
    private function cacheTemplate($path,$name){

        $this->templates[$name]=$this->getTemplateFromDisk($path);
        Cache::set($path.'-last-modified',$lastModified);
        Cache::set($path,$this->templates[$name]);

    }//cacheTemplate



    /**
     *      Get the current template
     *
     *      @return string
    */
    private function getWorkingTemplate(){
        if(!isset($this->templates[$this->workingTemplate])){
            $this->loadTemplate($this->workingTemplate);        
        }//if
        return $this->templates[$this->workingTemplate];
    }//getWorkingTempalte



    /**
     *      work on a specific template
     *
     *      @return object
     */
    public function name($name){
        $this->workingTemplate=$name;
        $this->beingModified=$this->getWorkingTemplate();
        return $this;
    }//use



    /**
     *      get the markup of the active template
     *
     *      @return string
    */
    public function html(){
       return $this->beingModified; 
    }//return



    /**
     *      build a template and push its html
     *      onto the Views html stack
     *
     *      @param string $name
     *      @param array $data
    */
    public function with($name,$data=Array()){
        View::html($this->build($name,$data));
    }//with



    /**
     *      build a template 
     *
     *      @param string $name
     *      @param array $data
     *      @return string
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

    }//returnData



    /**
     *      Set data into the template. This function is recursive in nature!
     *
     *      @param array $data
     *      @return object
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
     *      append nested templates
     *
     *      @param array $data
     *      @return string
    */
    private function appendTemplate($data){
        $t = $this->beingModified;
        if(!$data){
            $info = $this->parseInfo('',$t);
            if($info){
                $t = $this->insertTemplate($info,'','',$t);
            }//if
            return $t;
        }//if

        foreach($data as $k=>$v){
            $info = $this->parseInfo($k,$t);
            if($info){
                $t = $this->insertTemplate($info,$k,$v,$t);
            }//if
        }//foreach
        return $t;
    }//appendTemplate



    /**
     *      inject the template into the calling template
     *
     *      @param array $data
     *      @param string $k
     *      @param mixed $v
     *      @param string $t
     *      @return string
    */
    private function insertTemplate($data,$k,$v,$t){
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
     *      get any nested templates names that need to be injected
     *      
     *      @param string $k
     *      @param string $t
     *      @return mixed
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
     *      Set the else statements in the template. This is only run once 
     *      per template build.
     *
     *
     *      @return void
     *
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
     *      Determine whether or not an array is associative or numeric
     *
     *      
     *      @param array $array
     *      @return boolean
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

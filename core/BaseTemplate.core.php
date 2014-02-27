<?php

Class BaseTemplate {

    public $templates = Array();
    private $workingTemplate='';
    private $delin = "{{\$%1\$s}}";
    private $testdelin = Array('({{\$','','[a-zA-Z0-9\s\"\']+}})');
    private $copy = "{{&\$%1\$s}}";
    private $beingModified='';

    protected static function returnBICID(){
        return 'Template';
    }

    private function loadTemplate($name){
        $path = "../app/template/{$name}.template.html";
        if(!isset($this->templates[$name]) && file_exists($path)){
            $this->templates[$name] = file_get_contents($path);
        }//if
    }//getTemplate

    private function getWorkingTemplate(){
        if(!isset($this->templates[$this->workingTemplate])){
            $this->loadTemplate($this->workingTemplate);        
        }//if
        return $this->templates[$this->workingTemplate];
    }//getWorkingTempalte

    public function name($name){
        $this->workingTemplate=$name;
        $this->beingModified=$this->getWorkingTemplate();
        return $this;
    }//use

    public function html(){
       return $this->beingModified; 
    }//return


    public function with($name,$data=Array()){
        //Disco::view()->pushHTML($this->build($name,$data));
        View::html($this->build($name,$data));
    }//with

    public function build($name,$data=Array()){
        $this->workingTemplate=$name;
        $this->beingModified = $this->getWorkingTemplate();
        $this->beingModified = $this->appendTemplate($data);

        if(count($data)!=0){
            $this->set($data);

            $testDelin = '({{\$[a-zA-Z0-9\s\"\']*}})';
            preg_match("/{$testDelin}/",$this->beingModified,$matches);
            if($matches){
                foreach($matches as $m){
                    $orgM=$m;
                    $m = trim(trim($m,'{'),'}');
                    $v = substr($m,0,stripos($m,' '));
                    $d = "{$v}=null;if({$v}!=null)return {$v};";
                    $else = trim(substr($m,stripos($m,' ')),' ');
                    $else = str_replace(' ',' return ',$else);
                    $eva = eval($d.$else.';');

                    $this->beingModified = implode($eva,explode($orgM,$this->beingModified,2));

                }//foreach
            }//if

        }//if

        return $this->beingModified;

    }//returnData


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

            $testDelin = '({{\$'.$td.'[a-zA-Z0-9\s\"\']*}})';
            preg_match("/{$testDelin}/",$t,$matches);
            if($matches)
                $t = implode($v,explode($matches[0],$t,1));



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

    private function insertTemplate($data,$k,$v,$t){
        $this->loadTemplate($data['templateName']);

        $copies = $this->templates[$data['templateName']];
        if($data['justTemplate']){
            $t = str_replace($data['textBlock'],$copies,$t);
        }//if
        else if(is_array($v)){
            $copies = str_repeat($copies,count($v));
            $t = str_replace($data['textBlock'],$copies,$t);
        }//if
        else {
            $t = str_replace($data['textBlock'],$copies,$t);
        }//el

        return $t;

    }//insert

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



}//Template

?>

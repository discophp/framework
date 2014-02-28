<?php

class BaseRouter {
    private $param;
    private $haveMatch = false;
    private $ctrlerRequest=false;
    private $function;
    private $variableRestrictions = Array();

    public function __destruct(){

        if(!Disco::routeMatch() && $this->match($this->param)){
            Disco::routeMatch(true);
            if(!$this->function instanceof Closure){
                if(stripos($this->function,'@')!==false){
                    $ctrl = explode('@',$this->function);
                    $obj = new $ctrl[0];
                    $vars = Array();

                    if(count($this->variables)>0)
                        foreach($this->variables as $k=>$v)
                            $vars[]=$v;

                    Disco::handle($obj,$ctrl[1],$vars);
                }//if
            }//if
            else if($this->variables)
                call_user_func_array($this->function,$this->variables);
            else 
                call_user_func($this->function);
        }//if

    }//destruct

    public function get($param,$function){
        if(count($_POST)>0){
            $this->function=null;
            $this->param=null;
            return $this;
        }//if
        $this->function=$function;
        $this->param=$param;
        return $this;
    }//get

    public function any($param,$function){
        $this->param=$param;
        $this->function=$function;
        return $this;
    }//any

    public function post($param,$function){
        if(count($_POST)==0){
            $this->param=null;
            $this->function=null;
            return $this;
        }//if
        $this->param=$param;
        $this->function=$function;
        return $this;
    }//post

    public function where($k,$v){
        if(is_array($k)){
            $this->variableRestrictions = $v;
            return;
        }//if
        $this->variableRestrictions[$k]=$v;
    }//where

    private function match($param){
        $url = $_SERVER['REQUEST_URI'];

        if($param==$url){
            $this->variables=null;
            return true;
        }//if

        preg_match_all('/({[a-zA-Z0-9]+})+/',$param,$matches);
        if($matches){
            $variables=Array();

            $orgLen=strlen($param);
            $ns = trim($param,$url);
            $url = substr($url,$orgLen-strlen($ns));


            foreach($matches[0] as $m){
                $nextChar = stripos($ns,$m)+strlen($m);
                $nextChar = substr($ns,$nextChar,1);
                $nextPos = stripos($url,$nextChar);
                if($nextPos===false)
                    $value=$url;
                else 
                    $value = substr($url,0,$nextPos);
                
                $url = substr($url,strlen($value)+1);

                $slashCheck = stripos($value,'/');
                if($slashCheck!==false){
                    $value=substr($value,0,$slashCheck);
                }//if

                $name = trim(trim($m,'{'),'}');
                $variables[$name]=$value;
            }//foreach


            foreach($variables as $k=>$v){
                if(isset($this->variableRestrictions[$k])){
                    if(!preg_match("/{$this->variableRestrictions[$k]}/",$v)){
                        return false;
                    }//if
                }//if
                $param = str_replace("{{$k}}",$v,$param);
            }//foreach


            if($param==$_SERVER['REQUEST_URI']){
                $this->variables=$variables;
                return true;
            }//if
            else 
                return false;

        }//if


        return false;
    }//match

}//Router

class Router {

    public static function get($param,$function){
        return Disco::router()->get($param,$function);
    }//get

    public static function post($param,$function){
        return Disco::router()->post($param,$function);
    }//post

    public static function any($param,$function){
        return Disco::router()->any($param,$function);
    }//any

}//Router

?>

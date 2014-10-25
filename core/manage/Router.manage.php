<?php
namespace Disco\manage;

Class Router extends \Disco\classes\Router {

    public function __destruct(){

        $d = Array();
        $d['type'] = $this->type;
        $d['url'] = $this->param;
        $d['action'] = ($this->function instanceof \Closure) ? 'Closure' : $this->function;
        $d['https'] = $this->secureRoute;
        $d['variables'] = $this->variableRestrictions;
        $d['filter'] = $this->isFilter;
        $d['filtered_to'] = ($this->useRouter instanceof \Closure) ? 'Closure' : $this->useRouter;
        $d['auth_session'] = $this->auth['session'];
        $d['auth_fail_action'] = $this->auth['action'];
        $d['file'] = \Disco\manage\Manager::$routerInFile;

        \Disco\manage\Manager::$routes[] = $d;

        parent::__destruct();

    }//destruct

    private function whiteOutRoute(){
        return $this;
    }//whiteOutRoute

    public function auth($session,$action=null){
        $this->auth = Array('session'=>$session,'action'=>$action);
        return $this;
    }//auth

    public function secure(){
        $this->secureRoute=true;    
        return $this;
    }//secure

    public function filter($param){
        $this->type = 'ANY';
        $this->isFilter = true;
        $this->param = $param;
        return $this;
    }//filter

    public function to($function){
        $this->useRouter = $function;
        return $this;
    }//to

    public function where($k,$v=null){
        if(is_array($k)){
            $this->variableRestrictions = $k;
            return $this;
        }//if
        $this->variableRestrictions[$k]=$v;
        return $this;
    }//where


    public function any($param,$function){
        $this->type = 'ANY';
        $this->param = $param;
        $this->function = $function;
        return parent::get($param,$function);
    }

    public function get($param,$function){
        $this->type = 'GET';
        $this->param = $param;
        $this->function = $function;
        return parent::get($param,$function);
    }

    public function post($param,$function){
        $this->type = 'POST';
        $this->param = $param;
        $this->function = $function;
        return parent::post($param,$function);
    }

    public function put($param,$function){
        $this->type = 'PUT';
        $this->param = $param;
        $this->function = $function;
        return parent::put($param,$function);
    }

    public function delete($param,$function){
        $this->type = 'DELETE';
        $this->param = $param;
        $this->function = $function;
        return parent::delete($param,$function);
    }




}//Router


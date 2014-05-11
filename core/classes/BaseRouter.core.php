<?php

namespace Disco;

/**
 *      This file holds the class BaseRouter
*/



/**
 *      BaseRouter class.
 *      Used to resove a RESTful endpoint to an action, either a Closure or a Controller.
 *
*/
class BaseRouter {

    /**
     *      url path to match
    */
    private $param;

    /**
     *      action to take if matched
    */
    private $function;

    /**
     *      did we find a match?
    */
    private $haveMatch = false;

    /**
     *      where restrictions
    */
    private $variableRestrictions = Array();

    /**
     *      Is route HTTPS
    */
    private $secureRoute=false;


    /**
     *      Store authentication requirements on route
    */
    private $auth=null;



    /*
     *      When we tear down the object is when we do the work.
     *      Since the BaseRouter instance is instantiated and never referenced is it destroyed as soon as it is 
     *      called and its method chain has been executed.
     *
     *      Find if there is a match and take the appropriate action:
     *          - Execute an instance of a Closure
     *          - Resolve the requested Controller and method
     *          - Bind the passed data into the Closure function or class method
     *
     *
     *      @return void
    */
    public function __destruct(){

        //if the route should be authenticated and no action should be taken
        if($this->auth!=null && $this->auth['action']==null){
            if(!$this->authenticated()){
                return;
            }//if
        }//if

        //have no match already and this matches?
        if(!\Router::routeMatch() && $this->match($this->param)){
            \Router::routeMatch(true);

            //if the route should be authenticated and an action should be taken
            if($this->auth!=null){
                if(!$this->authenticated()){
                    header('Location:'.$this->auth['action']);
                }//if
            }//if

            if(!$this->function instanceof \Closure){

                //is a controller being requested?
                if(stripos($this->function,'@')!==false){
                    $ctrl = explode('@',$this->function);
                    $obj = new $ctrl[0];
                    $vars = Array();

                    if(count($this->variables)>0)
                        foreach($this->variables as $k=>$v)
                            $vars[]=$v;

                    \Disco::handle($obj,$ctrl[1],$vars);
                }//if
            }//if
            else if($this->variables)
                call_user_func_array($this->function,$this->variables);
            else 
                call_user_func($this->function);
        }//if

    }//destruct


    /**
     *      Return whether or not the request is authenticated by a session
     *
     *
     *      @return boolean
    */
    private function authenticated(){

        if(is_array($this->auth['session'])){
            $has=false;
            foreach($this->auth['session'] as $s){
                if(Session::has($s)){
                    $has=true;
                }//if
            }//foreach
            if(!$has){
                return false;
            }//if
        }//if
        else {
            if(!Session::has($this->auth['session'])){
                return false;
            }//if
        }//el

        return true;

    }//authenticated


    /**
     *      Only allow match on route if request method 
     *      was HTTPS
     *
     *      
     *      @return object $this
    */
    public function secure(){
        $this->secureRoute=true;    
        return $this;
    }//secure


    /**
     *      Protect a route via the exsistence of a  session
     *
     *
     *      @param mixed $session Either the session name, or an array of session names
     *      @param mixed $action a URL string to redirect to if the route matches and the user isn't authenticated
     *      @return object $this
     *
    */
    public function auth($session,$action=null){

        $this->auth = Array('session'=>null,'action'=>null);

        if(is_array($session)){
            $this->auth['session'] = $session;
        }//if
        else {
            $this->auth['session']=$session;
        }//el

        $this->auth['action']=$action;

        return $this;
    }//auth



    /**
     *      When a route is not a match this function essentially destroys it
     *
     *
     *      @return object $this
    */
    private function whiteOutRoute(){
        $this->function=null;
        $this->param=null;
        return $this;
    }//whiteOutRoute



    /**
     *      Match a get url route
     *
     *
     *      @param string   $param the url to match
     *      @param mixed    $function the action to take if there is a match
     *      @return object $this
     */
    public function get($param,$function){
        if(count($_POST)>0 || count(\Data::put()->all())>0 || count(\Data::delete()->all())>0)
            return $this->whiteOutRoute();
        else if($this->secureRoute && empty($_SERVER['HTTPS']))
            return $this->whiteOutRoute();

        $this->function=$function;
        $this->param=$param;
        return $this;
    }//get



    /**
     *      Match a any url route
     *
     *
     *      @param string   $param the url to match
     *      @param mixed    $function the action to take if there is a match
     *      @return object $this
     */
    public function any($param,$function){
        if($this->secureRoute && empty($_SERVER['HTTPS']))
            return $this->whiteOutRoute();

        $this->param=$param;
        $this->function=$function;
        return $this;
    }//any



    /**
     *      Match a post url route
     *
     *
     *      @param string   $param the url to match
     *      @param mixed    $function the action to take if there is a match
     *      @return object $this
     */
    public function post($param,$function){
        if(count($_POST)==0){
            return $this->whiteOutRoute();
        }//if
        else if($this->secureRoute && empty($_SERVER['HTTPS']))
            return $this->whiteOutRoute();

        $this->param=$param;
        $this->function=$function;
        return $this;
    }//post



    /**
     *      Match a put url route
     *
     *
     *      @param string   $param the url to match
     *      @param mixed    $function the action to take if there is a match
     *      @return object $this
     */
    public function put($param,$function){
        if($_SERVER['REQUEST_METHOD']!='PUT'){
            return $this->whiteOutRoute();
        }//if
        else if($this->secureRoute && empty($_SERVER['HTTPS']))
            return $this->whiteOutRoute();

        $this->param=$param;
        $this->function=$function;
        return $this;
    }//put



    /**
     *      Match a delete url route
     *
     *
     *      @param string   $param the url to match
     *      @param mixed    $function the action to take if there is a match
     *      @return object $this
     */
    public function delete($param,$function){
        if($_SERVER['REQUEST_METHOD']!='DELETE'){
            return $this->whiteOutRoute();
        }//if
        else if($this->secureRoute && empty($_SERVER['HTTPS']))
            return $this->whiteOutRoute();

        $this->param=$param;
        $this->function=$function;
        return $this;
    }//put



    /**
     *      Add where variables restrictions
     *
     *
     *      @param mixed    $k Either a string key or an array
     *      @param mixed    $v either null or a string
     *      @return void
     */
    public function where($k,$v=null){
        if(is_array($k)){
            $this->variableRestrictions = $k;
            return;
        }//if
        $this->variableRestrictions[$k]=$v;
        return $this;
    }//where



    /**
     *      Match a url route against the $param
     *
     *
     *      @param string $param the url
     *      @return boolean
     */
    private function match($param){
        $url = $_SERVER['REQUEST_URI'];

        if($param==$url){
            $this->variables=null;
            return true;
        }//if
        else if(count($this->variableRestrictions)<=0)
            return false;

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

                $name = trim(trim($m,'{'),'}');
                $variables[$name]=$value;
            }//foreach


            foreach($variables as $k=>$v){
                if(isset($this->variableRestrictions[$k])){
                    $matchCondition = $this->variableRestrictions[$k];

                    if(isset(\Disco::$defaultMatchCondition[$matchCondition]))
                        $matchCondition=\Disco::$defaultMatchCondition[$matchCondition];

                    if(!preg_match("/{$matchCondition}/",$v))
                        return false;

                }//if
                $param = str_replace("{{$k}}",$v,$param);
            }//foreach


            if($param==$_SERVER['REQUEST_URI']){
                $this->variables=$variables;

                foreach($variables as $k=>$v){
                    $_GET[$k]=$v;
                }//foreach

                return true;
            }//if
            else 
                return false;

        }//if

        return false;
    }//match


}//BaseRouter



?>

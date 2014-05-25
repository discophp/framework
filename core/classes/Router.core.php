<?php

namespace Disco\classes;

/**
 *      This file holds the class Router 
*/



/**
 *      Router class.
 *      Used to resove a RESTful endpoint to an action, either a Closure or a Controller.
 *
*/
class Router {

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
     *      Is this Router a Filter Router?
    */
    private $isFilter=false;

    /**
     *      Send a filtered route to Router file, or Closure
    */
    private $useRouter=null;

    /**
     *      Store authentication requirements on route
    */
    private $auth=null;



    /*
     *      When we tear down the object is when we do the work.
     *      Since the (this)Router instance is instantiated and never referenced is it destroyed as soon as it is 
     *      called and its method chain has been executed.
     *
     *      Find if there is a match and take the appropriate action:
     *          - Execute an instance of a Closure
     *          - Resolve the requested Controller and method
     *          - Bind the passed data into the Closure function or class method
     *          - Filter routes to another Router or Closure
     *
     *
     *      @return void
    */
    public function __destruct(){

        //if the route should be authenticated and no action should be taken
        if($this->auth!=null && $this->auth['action']==null){
            //not authenticated?
            if(!$this->authenticated()){
                return;
            }//if
        }//if

        //no route match and this Router is a Filter and its Filter matches?
        if(!\Router::routeMatch() && $this->isFilter && $this->filterMatch($this->param)){

            //if the route should be authenticated and an action should be taken
            if($this->auth!=null){
                //not authenticated?
                if(!$this->authenticated()){
                    header('Location:'.$this->auth['action']);
                    exit;
                }//if
            }//if

            //is this Router for HTTPS and the request isn't?
            if($this->secureRoute && empty($_SERVER['HTTPS'])){
                return;
            }//if

            if($this->useRouter instanceof \Closure){
                call_user_func($this->useRouter);
            }//if
            else {
                \Router::useRouter($this->useRouter);
            }//el

        }//if
        //have no match already and this matches?
        else if(!\Router::routeMatch() && $this->match($this->param)){
            \Router::routeMatch(true);

            //if the route should be authenticated and an action should be taken
            if($this->auth!=null){
                //not authenticated?
                if(!$this->authenticated()){
                    header('Location:'.$this->auth['action']);
                    exit;
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
                if(\Session::has($s)){
                    $has=true;
                }//if
            }//foreach
            if(!$has){
                return false;
            }//if
        }//if
        else {
            if(!\Session::has($this->auth['session'])){
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
     *      Filter a url route using {*} notation
     *
     *
     *      @param  string  $param the url filter
     *      @return object  $this
    */
    public function filter($param){
        $this->isFilter=true;
        $this->param = $param;
        return $this;
    }//filter



    /**
     *      When a Router is used as a Filter and the filter matches 
     *      there needs to be either a Router File or a Closure passed to handle the filtering
     *
     *
     *      @param  mixed  $r     A string representing a Router File, or a Closure
     *      @return object $this
    */
    public function to($r){
        $this->useRouter=$r;
        return $this;
    }//filter



    /**
     *      Match a get url route
     *
     *
     *      @param  string   $param    the url to match
     *      @param  mixed    $function the action to take if there is a match
     *      @return object   $this
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
     *      @param  string   $param    the url to match
     *      @param  mixed    $function the action to take if there is a match
     *      @return object   $this
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
     *      @param  string  $param    the url to match
     *      @param  mixed   $function the action to take if there is a match
     *      @return object  $this
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
     *      @param  string   $param    the url to match
     *      @param  mixed    $function the action to take if there is a match
     *      @return object   $this
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
     *      @param  string   $param    the url to match
     *      @param  mixed    $function the action to take if there is a match
     *      @return object   $this
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
     *      @param  mixed  $k Either a string key or an array
     *      @param  mixed  $v either null or a string
     *      @return void
     */
    public function where($k,$v=null){
        if(is_array($k)){
            $this->variableRestrictions = $k;
            return $this;
        }//if
        $this->variableRestrictions[$k]=$v;
        return $this;
    }//where



    /**
     *      Match a url route against the $param
     *
     *
     *      @param  string  $param the url
     *      @return boolean
     */
    private function match($param){
        $url = $_SERVER['REQUEST_URI'];

        //direct match?
        if($param==$url){
            $this->variables=null;
            return true;
        }//if
        //if theres no variables an no direct match, then no match
        else if(count($this->variableRestrictions)<=0){
            return false;
        }//elif

        $paramPieces = explode('/',$param);
        $urlPieces = explode('/',$url);

        //if the url and the param are not the same depth, no match
        if(count($paramPieces) != count($urlPieces)){
            return false;
        }//if

        foreach($urlPieces as $k=>$urlPiece){
            $paramPiece = $paramPieces[$k];

            //not a variable place holder?
            if(substr($paramPiece,0,1)!='{'){

                //pieces do not match?
                if($paramPiece!=$urlPiece){
                    return false;
                }//if

            }//if
            else {

                //get the variable
                $paramKey = trim($paramPiece,'{}'); 

                //the variable isn't part of the restrictions on this route?
                if(!isset($this->variableRestrictions[$paramKey])){
                    return false;
                }//if

                //condition to match variable with url piece
                $condition = $this->variableRestrictions[$paramKey];

                //is the condition using one of the default reserved words?
                if(isset(\Disco::$defaultMatchCondition[$condition])){
                    $condition=\Disco::$defaultMatchCondition[$condition];
                }//if

                //does the variable not match its corresponding url piece?
                if(!preg_match("/{$condition}/",$urlPiece)){
                    return false;
                }//if
                else {
                    //store the variable to pass into the Closure or Controller
                    $this->variables[$paramKey]=$urlPiece;
                }//el

            }//el

        }//foreach

        return true;

    }//match


    /**
     *      Filter a url route against the $param
     *
     *
     *      @param  string  $param the url
     *      @return boolean
     */
    private function filterMatch($param){
        $url = $_SERVER['REQUEST_URI'];

        //where to being filtering
        $i = stripos($param,'{*}');

        //if no filter or the url couldn't match the filter due to size
        if($i===false || $i>strlen($url)){
            return false;
        }//if

        //filter route does not match url?
        if(substr($param,0,$i) != substr($url,0,$i)){
            return false;
        }//if

        return true;

    }//filterMatch


}//BaseRouter



?>

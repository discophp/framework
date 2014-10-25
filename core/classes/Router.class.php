<?php
namespace Disco\classes;
/**
 *      This file holds the class Router 
*/



/**
 * Router class.
 * Used to resove a RESTful endpoint to an action, either a \Closure or a Controller.
*/
class Router {

    /**
     * @var string URI path to match.
    */
    public $param;

    /**
     * @var \Closure|string Action to take if matched.
    */
    private $function;

    /**
     * @var array The routers where restrictions.
    */
    private $variableRestrictions = Array();

    /**
     * @var boolean Is route HTTPS?
    */
    private $secureRoute=false;

    /**
     * @var boolean Is this Router a Filter Router?
    */
    private $isFilter=false;

    /**
     * @var null|string|\Closure Send a filtered route to Router file, or Closure.
    */
    private $useRouter=null;

    /**
     * @var null|string|array Store authentication requirements on route.
    */
    private $auth=null;



    /*
     * When we tear down the object is when we do the work.
     *
     * Since the (this)Router instance is instantiated and never referenced is it destroyed as soon as it is 
     * called and its method chain has been executed.
     *
     * Find if there is a match and take the appropriate action:
     *     - Execute an instance of a Closure
     *     - Resolve the requested Controller and method
     *     - Bind the passed data into the Closure function or class method
     *     - Filter routes to another Router or Closure
     *
     *
     * @return void
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

            //handle authenication
            $this->authenticationHandle();

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

            //handle authenication
            $this->authenticationHandle();

            if(!$this->function instanceof \Closure){

                //is a controller being requested?
                if(stripos($this->function,'@')!==false){
                    $ctrl = explode('@',$this->function);
                    $app = \Disco::$app;
                    $res = $app->handle($ctrl[0],$ctrl[1],$this->variables);
                }//if
            }//if
            else if($this->variables){
                $res = call_user_func_array($this->function,$this->variables);
            }//elif
            else {
                $res = call_user_func($this->function);
            }//el

            if($res === false){
                \Router::routeMatch(false);
            }//if
            else {
                \Router::routeMatch(true);
            }//el
            return $res;
        }//if

    }//destruct



    /**
     * If authenication was requested on the route and the user is not authenicated,
     * redirect the user to the specified redirect.
     *
     *
     * @return void
    */
    private function authenticationHandle(){
        //if the route should be authenticated and an action should be taken
        if($this->auth!=null){
            //not authenticated?
            if(!$this->authenticated()){
                header('Location:'.$this->auth['action']);
                exit;
            }//if
        }//if
    }//authenticationHandle 



    /**
     * Return whether or not the request is authenticated by a session.
     *
     *
     * @return boolean
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
     * Only allow match on route if request method was HTTPS.
     *
     * 
     * @return self 
    */
    public function secure(){
        $this->secureRoute=true;    
        return $this;
    }//secure



    /**
     * Protect a route via the exsistence of a SESSION.
     *
     *
     * @param string|array $session Either the session name, or an array of session names.
     * @param null|string $action a URI string to redirect to if the route matches and the user isn't authenticated.
     *
     * @return self 
    */
    public function auth($session,$action=null){
        $this->auth = Array('session'=>$session,'action'=>$action);
        return $this;
    }//auth



    /**
     * When a route is not a match this function essentially destroys it.
     *
     *
     * @return self 
    */
    private function whiteOutRoute(){
        $this->function=null;
        $this->param=null;
        return $this;
    }//whiteOutRoute



    /**
     * Filter a url route using {*} notation.
     *
     *
     * @param  string  $param the URI filter
     * @return self 
    */
    public function filter($param){
        $this->isFilter=true;
        $this->param = $param;
        return $this;
    }//filter



    /**
     * When a Router is used as a Filter and the filter matches 
     * there needs to be either a Router File or a Closure passed to handle the filtering.
     *
     *
     * @param  string|\Closure $r     A string representing a Router File, or a Closure
     *
     * @return self 
    */
    public function to($r){
        $this->useRouter=$r;
        return $this;
    }//filter



    /**
     * Match a GET URI route.
     *
     *
     * @param  string           $param    The URI to match.
     * @param  string|\Closure  $function The action to take if there is a match.
     *
     * @return self 
     */
    public function get($param,$function){
        if($_SERVER['REQUEST_METHOD']!='GET'){
            return $this->whiteOutRoute();
        }//if
        else if($this->secureRoute && empty($_SERVER['HTTPS'])){
            return $this->whiteOutRoute();
        }//elif

        $this->function=$function;
        $this->param=$param;
        return $this;
    }//get



    /**
     * Match any URI route.
     *
     *
     * @param  string           $param    The URI to match.
     * @param  string|\Closure  $function The action to take if there is a match.
     *
     * @return self 
     */
    public function any($param,$function){
        if($this->secureRoute && empty($_SERVER['HTTPS']))
            return $this->whiteOutRoute();

        $this->param=$param;
        $this->function=$function;
        return $this;
    }//any



    /**
     * Match a POST URI route.
     *
     *
     * @param  string           $param    The URI to match.
     * @param  string|\Closure  $function The action to take if there is a match.
     *
     * @return self 
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
     * Match a PUT URI route.
     *
     *
     * @param  string           $param    The URI to match.
     * @param  string|\Closure  $function The action to take if there is a match.
     *
     * @return self 
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
     * Match a DELETE URI route
     *
     *
     * @param  string           $param    The url to match.
     * @param  string|\Closure  $function The action to take if there is a match.
     *
     * @return self 
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
     * Add where variables restrictions to the URI route.
     *
     *
     * @param  string|array $k Either a string key or an array.
     * @param  null|string  $v Either null or a string.
     *
     * @return void
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
     * Match a URI route against the $param.
     *
     *
     * @param  string  $param The URI to match the route against.  
     *
     * @return boolean Was this $param a match to the REQUEST_URI?
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

                //store the variable to pass into the Closure or Controller
                $this->variables[$paramKey]=$urlPiece;

            }//el

        }//foreach

        return true;

    }//match



    /**
     * Filter a URI route against the $param.
     *
     *
     * @param  string  $param The URI to filter. 
     *
     * @return boolean
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

}//Router
?>

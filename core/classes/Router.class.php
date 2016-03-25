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
     * @var array $routes Collection of all instances of Routers we make.
    */
    public static $routers = Array();


    /**
     *@var int $numberOfProcessedRoutes The number of routes that have been processed.
    */
    private static $numberOfProcessedRoutes = 0;


    /**
     * @var boolean Has a Disco\classesself matched a request?
    */
    public static $routeMatch=false;


    /**
     * @var string The class to resolve from the container when Router is called. 
    */
    public static $base = '\Disco\classes\Router';


    /**
     * @var string URI path to match.
    */
    public $param;


    /**
     * @var \Closure|string Action to take if matched.
    */
    public $function;


    /**
     * @var array The routers where restrictions.
    */
    public $variableRestrictions = Array();


    /**
     * @var boolean Is route HTTPS?
    */
    private $secureRoute=false;


    /**
     * @var boolean Is this Router a Filter Router?
    */
    private $isFilter=false;


    /**
     * @var boolean When we a do a filter we store the base here
    */
    private $filterBase;


    /**
     * @var boolean When we a do a filter we store the filtered portion here
    */
    private $filteredOn;


    /**
     * @var null|string|\Closure Send a filtered route to Router file, or Closure.
    */
    private $useRouter=null;


    /**
     * @var null|string|array Store authentication requirements on route.
    */
    public $auth=null;


    /**
     * @var boolean $allowURLParameters Allow GET variables to be contained in the route.
    */
    public $allowURLParameters=false;



    /**
     * Get a new Router instance. This is the only method that should be used to access instances of this class. 
     * Before the new Router instance is returned, any previously registered routers will be processed off the 
     * router stack.
     *
     *
     * @return \Disco\classes\Router
    */
    public static function factory(){

        self::processAvailableRoutes();
        $r = new self::$base;
        self::$routers[] = $r;
        return $r;

    }//factory



    /**
     * Process the last created router in the stack.
    */
    public static function processLastCreatedRoute(){
        if(isset(self::$routers[self::$numberOfProcessedRoutes])){
            self::$numberOfProcessedRoutes++;
            self::$routers[self::$numberOfProcessedRoutes-1]->process();
        }//if
    }//processLastCreatedRoute



    /**
     * Process all routers in the stack that haven't been processed yet.
    */
    public static function processAvailableRoutes(){
        while(!self::routeMatch() && self::$numberOfProcessedRoutes < count(self::$routers)){
            self::processLastCreatedRoute();
        }//while
    }//processAvailableRoutes



    /**
     * Process the the router.
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
    public function process(){

        if(!$this->param){ 
            return;
        }//if

        if($this->secureRoute && empty($_SERVER['HTTPS'])){
            return;
        }//if

        //no route match and this Router is a Filter and its Filter matches?
        if(!self::routeMatch() && $this->isFilter && $this->filterMatch($this->param,$this->auth)){

            if($this->useRouter instanceof \Closure){
                call_user_func_array($this->useRouter,Array($this->filterBase,$this->filteredOn));
            }//if
            else {
                self::useRouter($this->useRouter);
            }//el

            //process the Routers that became available from calling the filter
            self::processAvailableRoutes();

        }//if
        //have no match already and this matches?
        else if(!self::routeMatch() && ($this->variables = $this->match($this->param,$this->variableRestrictions,$this->auth,$this->allowURLParameters))){
            self::executeRoute($this->function,$this->variables);
        }//if

    }//process



    /**
     * Allow URL parameters/variables to be present in the URL of the route.
     *
     *
     * @param array $params The paramaters that are allowed to be present.
     *
     * @return self
    */
    public function allowURLParameters($params = Array()){
        if(is_string($params)){
            $params = Array($params);
        }//if
        $this->allowURLParameters = $params;
        return $this;
    }//allowUrlParamaters



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
        if($_SERVER['REQUEST_METHOD']!='POST'){
            return $this->whiteOutRoute();
        }//if

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
     * @param array $restrict The variables that must exist in the URI.
     * @param null|string|array The authentication for the route.
     * @param null|array The GET URI paramaters allowed.
     *
     * @return boolean Was this $param a match to the REQUEST_URI?
     */
    public static function match($param,$restrict,$auth,$allowParams){

        $url = $_SERVER['REQUEST_URI'];

        if($allowParams === false && $_SERVER['QUERY_STRING']){
            return false;
        }//if
        else if($allowParams !== false && $_SERVER['QUERY_STRING']){
            $url = explode('?' . $_SERVER['QUERY_STRING'],$url)[0]; 
            if(is_array($allowParams) && count($allowParams)){
                parse_str($_SERVER['QUERY_STRING'],$params);
                if(count(array_diff_key($params,array_flip($allowParams)))){
                    return false;
                }//if
            }//if
        }//if

        //direct match?
        if($param==$url){
            if(!self::authenticated($auth)) return false;
            return true;
        }//if


        //if theres no variables an no direct match, then no match
        if(count($restrict)<=0){
            return false;
        }//elif

        $paramPieces = explode('/',$param);
        $urlPieces = explode('/',$url);

        //if the url and the param are not the same depth, no match
        if(count($paramPieces) != count($urlPieces)){
            return false;
        }//if

        if(!self::authenticated($auth)) return false;

        $return = Array();
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
                if(!isset($restrict[$paramKey])){
                    return false;
                }//if

                //condition to match variable with url piece
                $condition = $restrict[$paramKey];

                //is the condition using one of the default reserved words?
                if(\App::getCondition($condition)){
                    $condition = \App::getCondition($condition);
                }//if


                //does the variable not match its corresponding url piece?
                if(!preg_match("/{$condition}/",$urlPiece)){
                    return false;
                }//if

                //store the variable to pass into the Closure or Controller
                $return[$paramKey]=$urlPiece;

            }//el

        }//foreach

        return $return;

    }//match



    /**
     * Filter a URI route against the $param.
     *
     *
     * @param  string  $param The URI to filter. 
     * @param null|string|array The authentication on the route.
     *
     * @return boolean
     */
    private function filterMatch($param,$auth){

        $url = $_SERVER['REQUEST_URI'];

        //where to being filtering
        $i = stripos($param,'{*}');

        //if no filter or the url couldn't match the filter due to size
        if($i===false || $i>strlen($url)){
            return false;
        }//if

        $filter = substr($param,0,$i);

        //filter route does not match url?
        if($filter  != substr($url,0,$i)){
            return false;
        }//if

        if(!self::authenticated($auth)) return false;

        $this->filterBase = $filter;
        $this->filteredOn = substr($url,$i,strlen($url));

        return true;

    }//filterMatch



    /**
     * Execute the action specified by a route, either Closure or Controller Method passing in arguements
     * from the URI appropriatly.
     *
     *
     * @param \Closure|string $function The action to be taken.
     * @param Array $variables The variables to be passed to the action.
     *
     *
     * @return bool 
    */
    public static function executeRoute($function,$variables=Array()){

        if(!$function instanceof \Closure){

            //is a controller being requested?
            if(stripos($function,'@')!==false){
                $ctrl = explode('@',$function);
                $res = \App::handle($ctrl[0],$ctrl[1],$variables);
            }//if
        }//if
        else if(is_array($variables)){
            $res = call_user_func_array($function,$variables);
        }//el
        else {
            $res = call_user_func($function);
        }//el

        if($res === false){
            self::routeMatch(false);
        }//if
        else {
            self::routeMatch(true);
        }//el

        return $res;

    }//executeRoute



    /**
     * Return whether or not the request is authenticated by a session.
     *
     *
     * @param array The authentication.
     *
     * @return boolean
    */
    public static function authenticated($auth){

        if($auth && !\App::with('Session')->in($auth['session'])){
            if($auth['action']) {
                header('Location: '.$auth['action']);
                exit;
            }//if
            return false;
        }//if

        return true;

    }//authenticated



    /**
     * Once a router has found a match we dont perform more match attempts. 
     * This function is both a setter and a getter.
     *
     *
     * @param  boolean $m
     *
     * @return boolean
     */
    public static function routeMatch($m=null){

        if($m !== null){

            if($m == true){
                \App::make('Router','\Disco\classes\MockBox');
            }//if
            else if(self::$routeMatch==true && $m==false){
                \App::as_factory('Router',function(){
                    return \Disco\classes\Router::factory();
                });
            }//el

            self::$routeMatch=$m;

        }//if

        return self::$routeMatch;

    }//routerMatch



    /**
    * Load a Router File for processing.
    *
    *
    * @param string $router
    * @return void
    */
    public static function useRouter($routerPath){

        if(self::$routeMatch){
            return;
        }//if

        if(($path = \App::resolveAlias($routerPath)) !== false && file_exists($path)){
            require $path;
            return;
        } else {

            $routerPath = \App::path() . "/app/router/{$routerPath}.router.php";
            if(file_exists($routerPath)){
                require $routerPath;
                return;
            }//if

        }//el

        $message = "Router {$routerPath}.router.php not found";

        \App::error($message,Array('unknown','useRouter'),debug_backtrace(TRUE,4));
        throw new \Disco\exceptions\Exception($message);

    }//useRouter



}//Router
?>

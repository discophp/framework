<?php
namespace Disco\classes;
/**
 * This file holds the class Router
*/



/**
 * Router class.
 * Used to resolve a RESTful endpoint to an action, either a \Closure or a Controller.
*/
class Router {


    /**
     * @var array $routes Collection of all instances of Routers we make.
    */
    private static $routers = Array();


    /**
     *@var int $numberOfProcessedRoutes The number of routes that have been processed.
    */
    private static $numberOfProcessedRoutes = 0;


    /**
     * @var boolean $routeMatch Has a router matched a request?
    */
    private static $routeMatch=false;


    /**
     * @var string $base The class to resolve from the container when Router is called. 
    */
    public static $base = '\Disco\classes\Router';


    /**
     * @var int $paginateCurrentPage The current page being requested for a paginated route.
    */
    public static $paginateCurrentPage = 1;


    /**
     * @var string $requestMethod The request method the router servers ie : `get`,`post`,`put`,`delete`.
    */
    private $requestMethod = null;


    /**
     * @var string $uri URI path to match.
    */
    private $uri;


    /**
     * @var \Closure|string $action Action to take if matched.
    */
    private $action;


    /**
     * @var array $variableRestrictions The routers where restrictions.
    */
    private $variableRestrictions = Array();


    /**
     * @var array $variables The extracted variables from the route uri based on the variableRestrictions.
    */
    private $variables = Array();


    /**
     * @var boolean $secureRoute Is route HTTPS?
    */
    private $secureRoute = false;


    /**
     * @var boolean $isFilter Is this Router a Filter Router?
    */
    private $isFilter = false;


    /**
     * @var boolean $filterBase When we a do a filter we store the base here
    */
    private $filterBase = false;


    /**
     * @var boolean $filteredOn When we a do a filter we store the filtered portion here
    */
    private $filteredOn = false;


    /**
     * @var null|string|array|\Closure $useRouter Send a filtered route to Router file,an array of routes, or Closure.
    */
    private $useRouter = null;


    /**
     * @var null|array $children Children of a route.
    */
    private $children = null;


    /**
     * @var null|string|array $auth Store authentication requirements on route.
    */
    private $auth = null;


    /**
     * @var boolean $allowURLParameters Allow GET variables to be contained in the route.
    */
    private $allowURLParameters = false;


    /**
     * @var boolean $paginate Is the route a paginated route?
    */
    private $paginate = false;



    /**
     * Get a new Router instance. This is the only method that should be used to access instances of this class. 
     * Before the new Router instance is returned, any previously registered routers will be processed off the 
     * router stack.
     *
     *
     * @return \Disco\classes\Router
    */
    public static function factory(){

        static::processAvailableRoutes();
        $r = new static::$base;
        static::$routers[] = &$r;
        return $r;

    }//factory



    /**
     * Process the last created router in the stack.
    */
    public static function processLastCreatedRoute(){
        if(isset(static::$routers[static::$numberOfProcessedRoutes])){
            static::$routers[static::$numberOfProcessedRoutes]->process();
        }//if
    }//processLastCreatedRoute



    /**
     * Process all routers in the stack that haven't been processed yet.
    */
    public static function processAvailableRoutes(){
        while(!static::$routeMatch && static::$numberOfProcessedRoutes < count(static::$routers)){
            static::processLastCreatedRoute();
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

        static::$numberOfProcessedRoutes++;

        if($this->requestMethod && $this->requestMethod != $_SERVER['REQUEST_METHOD']){
            if($this->children){
                $this->buildRelativeChildren();
            }//if
            return;
        }//if

        if(!$this->uri){ 
            return;
        }//if

        if($this->secureRoute && empty($_SERVER['HTTPS'])){
            return;
        }//if

        //no route match yet?
        if(!static::$routeMatch){ 

            //this Router is a Filter?
            if($this->isFilter){

                //Filter matches?
                if($this->filterMatch($this->uri,$this->auth)){

                    if($this->useRouter instanceof \Closure){
                        call_user_func_array($this->useRouter,Array($this->filterBase,$this->filteredOn));
                    }//if
                    else if($this->useRouter){
                        static::useRouter($this->useRouter);
                    }//el

                    //process the Routers that became available from calling the filter
                    static::processAvailableRoutes();

                    if(!static::$routeMatch && $this->children){

                        $children = Array();

                        foreach($this->children as $uri => $route){
                            $children[$this->filterBase . $uri] = $route;
                        }//foreach

                        static::processRouterArray($children);

                    }//if

                }//if

            }//if
            else if($this->paginate){

                //base uri of pagination matched without including pagination format
                if($this->match()){
                    //default value of 1 for page variable (which is required in pagination format)
                    $this->variables['page'] = 1;
                    static::executeRoute();
                }//if
                else {

                    $format = '/page/{page}';

                    if(\App::configKeyExists('paginate')){
                        $format = \App::config('paginate');
                    }//if

                    if(substr($format,0,1) == '/' && substr($this->uri,-1) == '/'){
                        $this->uri = rtrim($this->uri,'/');
                    }//elif

                    $this->uri .= $format;

                    $this->variableRestrictions['page'] = 'integer_positive';

                    if($this->match()){

                        if($this->variables['page'] == '0'){
                            $page0 = str_replace('{page}','0',$format);
                            $page1 = str_replace('{page}','1',$format);
                            $redirect = str_replace($page0,$page1,$this->uri);
                            header("Location: {$redirect}");
                            exit;
                        }//if

                        static::$paginateCurrentPage = $this->variables['page'];
                        static::executeRoute();

                    }//if

                }//el

            }//elif
            else {

                if($this->match()){
                    static::executeRoute();
                }//if
                else if($this->children){
                    $this->buildRelativeChildren();
                }//elif

            }//el

        }//if

    }//process



    /**
     * Convert a children array of routes to use the parent routes information by prepending the parent URI to the 
     * children's URIs and merge the children's where variable restrictions with the parents where variable
     * restrictions.
    */
    private function buildRelativeChildren(){

        if(!is_array($this->children)){
            $this->children = static::resolveRouterPath($this->children);        
        }//if

        $children = Array();

        foreach($this->children as $uri => $route){
            if(count($this->variableRestrictions)){
                if(!array_key_exists('where',$route)){
                    $route['where'] = $this->variableRestrictions;
                }//if
                else {
                    $route['where'] = array_merge($route['where'],$this->variableRestrictions);
                }//el
            }//if
            $children[$this->uri . $uri] = $route;
        }//foreach

        static::processRouterArray($children);

    }//buildRelativeChildren



    /**
     * Allow URL (GET) parameters/variables to be present in the URL of the route. Passing nothing or an empty array 
     * means any URL parameters are allowed.
     *
     *
     * @param array $params The paramaters that are allowed to be present.
     *
     * @return self
    */
    public function allowURLParameters($params = Array()){
        if(is_string($params)){
            $uris = Array($params);
        }//if
        $this->allowURLParameters = $params;
        return $this;
    }//allowURLParameters



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
     * Filter a url route using {*} notation.
     *
     *
     * @param string $uri The URI filter.
     * @param string|\Closure $action The action to take if there is a match.
     * @return self 
    */
    public function filter($uri,$action = null){
        $this->isFilter=true;
        $this->useRouter = $action;
        $this->uri = $uri;
        return $this;
    }//filter



    /**
     * When a Router is used as a Filter and the filter matches 
     * there needs to be either a Router File or a Closure passed to handle the filtering.
     *
     *
     * @param  string|array|\Closure $r     A string representing a Router File, an array of routes, or a Closure.
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
     * @param  string           $uri    The URI to match.
     * @param  string|\Closure  $action The action to take if there is a match.
     *
     * @return self 
     */
    public function get($uri,$action){
        $this->requestMethod = 'GET';
        $this->action=$action;
        $this->uri=$uri;
        return $this;
    }//get



    /**
     * Match any URI route.
     *
     *
     * @param  string           $uri    The URI to match.
     * @param  string|\Closure  $action The action to take if there is a match.
     *
     * @return self 
     */
    public function any($uri,$action){
        $this->uri=$uri;
        $this->action=$action;
        return $this;
    }//any



    /**
     * Match a POST URI route.
     *
     *
     * @param  string           $uri    The URI to match.
     * @param  string|\Closure  $action The action to take if there is a match.
     *
     * @return self 
     */
    public function post($uri,$action){
        $this->requestMethod = 'POST';
        $this->uri=$uri;
        $this->action=$action;
        return $this;
    }//post



    /**
     * Match a PUT URI route.
     *
     *
     * @param  string           $uri    The URI to match.
     * @param  string|\Closure  $action The action to take if there is a match.
     *
     * @return self 
     */
    public function put($uri,$action){
        $this->requestMethod = 'PUT';
        $this->uri=$uri;
        $this->action=$action;
        return $this;
    }//put



    /**
     * Match a DELETE URI route
     *
     *
     * @param  string           $uri    The url to match.
     * @param  string|\Closure  $action The action to take if there is a match.
     *
     * @return self 
     */
    public function delete($uri,$action){
        $this->requestMethod = 'DELETE';
        $this->uri=$uri;
        $this->action=$action;
        return $this;
    }//delete



    /**
     * Match a URI to multiple actions based on the request type.
     *
     *
     * @param  string $uri The url to match.
     * @param  array $actions The possible actions for the match based on the current request type. For example if 
     * the request is a GET the actions array should contain a key `get` that points to a string (controller) or 
     * a Closure function..
     *
     * @return self 
     */
    public function multi($uri,$actions){
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $key = strtolower($_SERVER['REQUEST_METHOD']);
        if(!array_key_exists($key,$actions)){
            return $this;
        }//if
        $this->uri=$uri;
        $this->action=$actions[$key];
        return $this;
    }//multi



    /**
     * Match a GET request that uses pagination. 
     *
     * @param  string           $uri    The url to match.
     * @param  string|\Closure  $action The action to take if there is a match.
     *
     * @return self 
     */
    public function paginate($uri,$action){

        $this->paginate = true;
        $this->requestMethod = 'GET';
        $this->uri = $uri;
        $this->action = $action;

        return $this;

    }//paginate



    /**
     * Add where variables restrictions to the URI route.
     *
     *
     * @param  string|array $k Either a string key or an array.
     * @param  null|string  $v Either null or a string.
     *
     * @return self
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
     * Routes that are children to the parent. The keys of the array (the URIs) are relative to the parent URI and 
     * will automatically have the parent URI prepended to them.
     *
     *
     * @param array $children The child routes (take the same form as children passed to `self::processRouterArray`).
     *
     * @return self;
    */
    public function children($children){
        $this->children = $children;
        return $this;
    }//children



    /**
     * Base on the conditions established in the route, does the request URI match with the routes URI. Any 
     * variables that are extracted from the URI are stored in `$this->variables`.
     *
     * @return boolean
     */
    private function match(){

        $uri = $this->uri;
        $restrict = $this->variableRestrictions;
        $auth = $this->auth;
        $allowParams = $this->allowURLParameters;

        //if there is authentication and it doesn't pass, no match
        if(!static::authenticated($auth)){
            return false;
        }//if

        $url = $_SERVER['REQUEST_URI'];

        if($allowParams === false && $_SERVER['QUERY_STRING']){
            return false;
        }//if
        else if($allowParams !== false && $_SERVER['QUERY_STRING']){
            $url = explode('?' . $_SERVER['QUERY_STRING'],$url)[0]; 
            if(is_array($allowParams) && count($allowParams)){
                parse_str($_SERVER['QUERY_STRING'],$uris);
                if(count(array_diff_key($uris,array_flip($allowParams)))){
                    return false;
                }//if
            }//if
        }//if

        //direct match?
        if($uri==$url){
            return true;
        }//if


        //if theres no variables an no direct match, then no match
        if(count($restrict)<=0){
            return false;
        }//elif

        $uriPieces = explode('/',$uri);
        $urlPieces = explode('/',$url);

        //if the url and the param are not the same depth, no match
        if(count($uriPieces) != count($urlPieces)){
            return false;
        }//if

        foreach($urlPieces as $k=>$urlPiece){
            $uriPiece = $uriPieces[$k];

            $variableCount = substr_count($uriPiece,'{');

            //not a variable place holder?
            if(!$variableCount){

                //pieces do not match?
                if($uriPiece != $urlPiece){
                    return false;
                }//if

            }//if
            //only 1 variable in the URI piece
            else if($variableCount === 1){

                //get the variable
                $uriKey = trim($uriPiece,'{}'); 

                //the variable isn't part of the restrictions on this route?
                if(!array_key_exists($uriKey,$restrict)){
                    return false;
                }//if

                //condition to match variable with url piece
                $condition = $restrict[$uriKey];

                $reserved = \App::getCondition($condition);
                //is the condition using one of the default reserved words?
                if($reserved){
                    $condition = $reserved;
                }//if

                //does the variable not match its corresponding url piece?
                if(!preg_match("/{$condition}/",$urlPiece)){
                    return false;
                }//if

                //store the variable to pass into the Closure or Controller
                $this->variables[$uriKey]=$urlPiece;

            }//elif
            //more than 1 variable in the URI piece
            else {

                $restrictOn = Array();

                $uriRegex = $uriPiece;

                //use the restriction on the route to convert the URI piece into a regexp
                foreach($restrict as $variableKey => $condition){

                    //does this restriction key exist in the URI piece
                    if(strpos($uriPiece,"{{$variableKey}}") !== false){

                        $restrictOn[] = $variableKey;

                        $reserved = \App::getCondition($condition);
                        //is the condition using one of the default reserved words?
                        if($reserved){
                            //remove caret and dollar so string doesn't have to begin and end with individual pattern
                            $condition = trim($reserved,'^$');
                        }//if

                        //replace the restrict variable inside the URI piece with its condition using a matching key
                        $uriRegex = str_replace("{{$variableKey}}","(?P<{$variableKey}>$condition)",$uriRegex);

                    }//if

                }//foreach

                //get the matches
                preg_match_all('/^'.$uriRegex.'$/',$urlPiece,$matches);

                //make sure we got matches for any restriction inside the URI piece
                foreach($restrictOn as $variableKey){

                    if(!isset($matches[$variableKey][0]) || !$matches[$variableKey][0]){
                        return false;
                    }//if

                    $this->variables[$variableKey] = $matches[$variableKey][0];

                }//foreach

            }//el

        }//foreach

        return true;

    }//match



    /**
     * Filter a URI route against the $uri.
     *
     *
     * @param  string  $uri The URI to filter. 
     * @param null|string|array The authentication on the route.
     *
     * @return boolean
     */
    private function filterMatch($uri,$auth){

        $url = $_SERVER['REQUEST_URI'];

        //where to being filtering
        $i = stripos($uri,'{*}');

        //if no filter or the url couldn't match the filter due to size
        if($i===false || $i>strlen($url)){
            return false;
        }//if

        $filter = substr($uri,0,$i);

        //filter route does not match url?
        if($filter  != substr($url,0,$i)){
            return false;
        }//if

        if(!static::authenticated($auth)) return false;

        $this->filterBase = $filter;
        $this->filteredOn = substr($url,$i,strlen($url));

        return true;

    }//filterMatch



    /**
     * Execute the action (`$this->action`) specified by a route, either Closure or Controller Method passing in agreements
     * from the URI appropriately (`$this->variables`).
     *
     * @return bool 
    */
    private function executeRoute(){

        $action = $this->action;
        $variables = $this->variables;

        if(!$action instanceof \Closure){

            //is a controller being requested?
            if(stripos($action,'@')!==false){
                $ctrl = explode('@',$action);
                $res = \App::handle($ctrl[0],$ctrl[1],$variables);
            }//if
        }//if
        else if(is_array($variables)){
            $res = call_user_func_array($action,$variables);
        }//el
        else {
            $res = $action();
        }//el


        if($res === false){
            static::__routeMatch(false);
        }//if
        else {
            static::__routeMatch(true);
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
     * Private method for setting and getting whether we have a route match yet. Big difference is that this method 
     * does not call `static::processAvailableRoutes()` unlike its public counter part `routeMatch()`. The reason
     * the public method calls `static::processAvailableRoutes()` is so that say a Router was created, and 
     * immediately after the a call is made to `Router::routeMatch()` to check if the last route satisifed the
     * request, well if the `process()` method was not called on it directly, it wont be processed until another
     * Router is created or the end of the application method `tearDown()` is called. So to make sure we respond 
     * with the correct anwser we need to make sure any un-processed routes are processed first.
     *
     * @param  boolean $m
     *
     * @return boolean
     */
    private static function __routeMatch($m=null){

        if($m !== null){

            if(static::$routeMatch === false && $m === true){
                \App::make('Router','\Disco\classes\MockBox');
            }//if
            else if(static::$routeMatch === true && $m === false){
                \App::makeFactory('Router',function(){
                    return \Disco\classes\Router::factory();
                });
            }//el

            static::$routeMatch = $m;

        }//if

        return static::$routeMatch;

    }//routerMatch



    /**
     * Once a router has found a match we don't perform more match attempts.
     * This function is both a setter and a getter.
     *
     *
     * @param  boolean $m
     *
     * @return boolean
     */
    public static function routeMatch($m=null){

        static::processAvailableRoutes();

        return static::__routeMatch($m);

    }//routerMatch



    /**
     * Resolve a string name to a router file path. Can be as simple as say `user` which would map to 
     * `app/router/user.router.php` or aliased like `@shopping`.
     *
     * @param string $routerPath The relative name of the router.
     *
     * @return string The path to the router file.
     *
     * @throws \Disco\exceptions\Exception When no router file is found given the passed router path.
    */
    public static function resolveRouterPath($routerPath){

        if(($path = \App::resolveAlias($routerPath)) !== false && file_exists($path)){
            return $path;
        } else {

            $routerPath = \App::path() . "/app/router/{$routerPath}.router.php";
            if(file_exists($routerPath)){
                return $routerPath;
            }//if

        }//el

        $message = "Router {$routerPath}.router.php not found";
        \App::error($message,Array('unknown','useRouter'),debug_backtrace(TRUE,4));
        throw new \Disco\exceptions\Exception($message);

    }//resolveRouterPath



    /**
    * Load a Router File for processing.
    *
    *
    * @param string|array $routerPath Path to a router file that contains Router definitions, or returns an array of
    * routes. Or an array of routes to process.
    */
    public static function useRouter($routerPath){

        if(static::$routeMatch){
            return;
        }//if

        if(is_array($routerPath)){
            static::processRouterArray($routerPath);
            return;
        }//if

        $router = require static::resolveRouterPath($routerPath);

        if(is_array($router)){
            static::processRouterArray($router);
        }//if

    }//useRouter



    /**
     * Process an array of routes.
     *
     * A standard route is defined like so:
     *
     * ```
     * Array(
     *  '/uri/path/{var}' => Array(
     *      'type' (required) => string ('get','post','put','delete','multi','filter'),
     *      'action' (required) => string|\Closure|array,
     *      'where' (optional) => array,
     *      'allowURLParameters' (optional) => string|array,
     *      'auth' (optional) => Array(
     *          'session' (required) => string,
     *          'redirect' (optional) => string
     *      ),
     *      'secure' (optional) => boolean,
     *      'children' (optional) => array,
     *  )
     * )
     * ```
     *
     * @param array $routes The array of routes to process.
     *
     * @return void
     */
    public static function processRouterArray($routes){

        if(static::$routeMatch){
            return;
        }//if

        foreach($routes as $uri => $props){

            $router = static::factory();

            if($props['type'] == 'filter' && !array_key_exists('action',$props)){
                $props['action'] = null;
            }//if

            $router->{$props['type']}($uri,$props['action']);

            if(array_key_exists('children',$props)){
                $router->children($props['children']);
            }//if

            if(array_key_exists('where',$props)){
                $router->where($props['where']);
            }//if

            if(array_key_exists('allowURLParameters',$props)){
                $router->allowURLParameters($props['allowURLParameters']);
            }//if

            if(array_key_exists('auth',$props)){

                if(is_string($props['auth'])){
                    $router->auth($props['auth'],null);
                } else {

                    $redirect = null;
                    if(array_key_exists('redirect',$props['auth'])){
                        $redirect = $props['auth']['redirect'];
                    }//if
                    $router->auth($props['auth']['session'],$redirect);

                }//el

            }//if

            if(array_key_exists('secure',$props)){
                $router->secure();
            }//if

            static::processLastCreatedRoute();

            if(static::$routeMatch){
                break;
            }//if

        }//foreach

    }//processRouterArray


}//Router
?>

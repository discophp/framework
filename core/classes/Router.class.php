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

    const ROUTES = '/app/routes';

    public static $app;

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
     * @var null|string|\Closure Send a filtered route to Router file, or Closure.
    */
    private $useRouter=null;

    /**
     * @var null|string|array Store authentication requirements on route.
    */
    public $auth=null;



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


        if($this->secureRoute && empty($_SERVER['HTTPS'])){
            return;
        }//if

        //no route match and this Router is a Filter and its Filter matches?
        if(!self::routeMatch() && $this->isFilter && $this->filterMatch($this->param,$this->auth)){

            if($this->useRouter instanceof \Closure){
                call_user_func($this->useRouter);
            }//if
            else {
                self::useRouter($this->useRouter);
            }//el

        }//if
        //have no match already and this matches?
        else if(!self::routeMatch() && ($this->variables = $this->match($this->param,$this->variableRestrictions,$this->auth))){
            return $this->executeRoute($this->function,$this->variables);
        }//if

    }//destruct



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
     *
     * @return boolean Was this $param a match to the REQUEST_URI?
     */
    public static function match($param,$restrict,$auth){

        $url = $_SERVER['REQUEST_URI'];

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
                if(isset(self::$app->defaultMatchCondition[$condition])){
                    $condition=self::$app->defaultMatchCondition[$condition];
                }//if


                //does the variable not match its corresponding url piece?
                if(!preg_match("/{$condition}/",$urlPiece)){
                    return false;
                }//if

                //store the variable to pass into the Closure or Controller
                //$this->variables[$paramKey]=$urlPiece;
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

        //filter route does not match url?
        if(substr($param,0,$i) != substr($url,0,$i)){
            return false;
        }//if

        if(!self::authenticated($auth)) return false;

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
                $res = self::$app->handle($ctrl[0],$ctrl[1],$variables);
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
     * @return boolean
    */
    public static function authenticated($auth){

        if($auth && !self::$app['Session']->in($auth['session'])){
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
                self::$app->make('Router','\Disco\classes\MockBox');
            }//if
            else if(self::$routeMatch==true && $m==false){
                self::$app->as_factory('Router',function(){
                    return new self::$base;
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
    public static function useRouter($router){

        if(self::$routeMatch){
            return;
        }//if

        $routerPath = self::$app->path."/app/router/$router.router.php";
        if(file_exists($routerPath)){
            require($routerPath);
            return;
        }//if
        else {
            $routers = self::$app->addonAutoloads();
            $routers = $routers['.router.php'];
            foreach($routers as $r){
                $test = substr($r,0,strlen($r)-strlen('.router.php'));
                $tail = substr($test,strlen($test)-strlen($router),strlen($router));
                if($router==$tail && is_file($r)){
                    self::$routeMatch=false;
                    require($r);
                    return;
                }//if
            }//foreach
        }//el

        self::$app->error("Router $router.router.php not found",Array('unknown','useRouter'),debug_backtrace(TRUE,4));

    }//useRouter




    /**
     * Process the the file app/routes for routes and execute the matching route.
     *
     *
     * @return void
    */
    public static final function processRoutes(){

        $r = file_get_contents(self::$app->path.self::ROUTES);
        $routes = explode("\n",$r);
        $total = count($routes);
        $current = 0;

        //echo '<pre>';

        $final = Array();

        //process each line
        while($current < $total){

            //get current line
            $r = $routes[$current];
            $current++;

            //is the line blank or commented out?
            if(!$r || substr($r,0,1)=='#') continue;

            //clean up spaces
            $r = preg_replace('/\s+/',' ',$r);

            //is this line specifying a FILTER?
            if(substr($r,0,8)=='[FILTER '){
                $section = explode(' ',$r);

                $i=1; $filteringSecure = false;
                $filterMeta = Array('auth'=>Array('action'=>null),'path'=>'');

                //check each possible filter section
                while(isset($section[$i])){
                    //URI filter
                    if(substr($section[$i],0,1) == '/'){
                        $filterMeta['path'] = $section[$i];
                    }//if
                    //AUTH filter
                    else if(stripos($section[$i],'auth(')!==false){
                        $auth = substr($section[$i],5,strlen($section[$i])-7);
                        $auth = explode(',',$auth);
                        $filterMeta['auth']['session'] = explode('|',$auth[0]);
                        if(isset($auth[1])) $filterMeta['auth']['action'] = $auth[1];
                    }//elif
                    //HTTPS filter
                    else if($section[$i]==':s'){
                        $filteringSecure = true;
                    }//elif
                    $i++;
                }//while

                $filterFail = false;
                //Does the filter not apply to the current request?
                if($filteringSecure && empty($_SERVER['HTTPS'])) $filterFail = true;
                else if(!self::filterMatch($filterMeta['path'],$filterMeta['auth'])) $filterFail = true;

                //skip all routes in the filter on fail
                if($filterFail){
                    do {
                        $current++;
                    } while($routes[$current] != '[/FILTER]');
                }//if

                //continue to next line
                continue;

            }//if

            $r = trim($r);

            //is the route HTTPS
            $s = substr($r,0,2)=='s:'; 
            if($s && empty($_SERVER['HTTPS']))          continue;
            else if(!$s && !empty($_SERVER['HTTPS']))   continue;

            //if its HTTPS and passed trim the :s
            if($s) $r = substr($r,2,strlen($r));

            $section = explode(' ',$r);

            //request method doesn't match?
            if($section[0]!='ANY' && $_SERVER['REQUEST_METHOD']!=$section[0]) continue;

            $meta = Array('auth'=>null,'where'=>null);
            $meta['type'] = $section[0];
            $meta['path'] = $section[1];
            $meta['controller'] = $section[2];

            $i = 3;
            //process the router line
            while(isset($section[$i])){
                //WHERE condition
                if(stripos($section[$i],'where(')!==false){
                    $where = substr($section[$i],6,strlen($section[$i])-7);
                    $where = explode(',',$where);
                    array_walk($where,function($value) use(&$meta){
                        list($k,$v) = explode('=>',$value);
                        $meta['where'][$k] = $v;
                    });
                }//if
                //AUTH condition
                else if(stripos($section[$i],'auth(')!==false){
                    $auth = substr($section[$i],5,strlen($section[$i])-6);
                    $auth = explode(',',$auth);
                    $meta['auth'] = Array();
                    $meta['auth']['session'] = explode('|',$auth[0]);
                    if(isset($auth[1])) $meta['auth']['action'] = $auth[1];

                }//if
                $i++;
            }//while

            $final[] = $meta;

        }//foreach

        //print_r($final);

        foreach($final as $r){
            if(!self::routeMatch() && $match = \Disco\classes\Router::match($r['path'],$r['where'],$r['auth'])){
                self::executeRoute($r['controller'],$match);
            }//if 
        }//foreach

        //echo '</pre>';

    }//processRoutes


}//Router
?>

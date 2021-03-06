<?php
namespace Disco\classes;
/**
 * This file holds the Disco\classes\View class
*/


/**
 * Disco\classes\View class.
 * The View class provides the functionality of dynamically creating
 * web pages. Essentially the eventual response to the client.
 *
 * See the docs at discophp.com/docs/View
*/
class View {


    /**
     * @var string The base template to use for the view.
    */
    public $baseTemplate = '_default.html';


    /**
     * @var string The base template to use for the view.
    */
    public $ajaxTemplate = '_ajax_default.html';


    /**
     * @var string The relative path to the directory which holds server error responses.
    */
    public $errorDir = 'app/error/';

    
    /**
     * @var array The data to be injected into the $baseTemplate template when rendered.
    */
    protected $view = Array(
        'title'             => '',
        'description'       => '',
        'charset'           => 'utf-8',
        'lang'              => 'en',
        'favIcon'           => '/favicon.png',
        'robots'            => 'index,follow',
        'ajax'              => false,
        'json'              => false,
        'scriptSrcs'        => Array(),
        'scripts'           => '',
        'headScriptSrcs'    => Array(),
        'styles'            => '',
        'styleSrcs'         => Array(),
        'headExtra'         => '',
        'bodyClass'        => Array(),
        'header'            => '',
        'body'              => '',
        'footer'            => '',
        'schema'            => Array(),
    );


    /**
     * @var string Flips between script and style.
    */
    private $lastCallType;


    /**
     * @var array How should it be scraped.
    */
    private $indexes = Array(
        0=>'index,follow',
        1=>'noindex,nofollow',
        2=>'index,nofollow',
        3=>'noindex,follow'
    );

    
    /**
     * @var integer Standard scrape for `$indexes`.
    */
    public $activeIndex=0;



    /**
     * If the view is called in a twig template we want to just return an empty string.
     *
     * @return string
    */
    public function __toString(){
        return '';
    }//__toString



    /**
     * Get a variable in the view template variable.
     *
     * @param string $k The variable name.
     *
     * @return mixed 
     */
    public function get($k){

        if(isset($this->view[$k])){
            return $this->view[$k];
        }//if

        return null;

    }//get



    /**
     * Set a variable in the view template variable.
     *
     * @param string $k The variable name.
     * @param mixed $v The variables value.
     *
     * @return void
     */
    public function set($k,$v){
        $this->view[$k] = $v;
    }//set



    /**
     * Override the default body template for the View.
     *
     * @param string $template The template to use.
     *
     * @return void
    */
    public function setBaseTemplate($template){
        $this->baseTemplate = $template;
    }//setBodyTemplate



    /**
     * Override the default body template for the Ajax View.
     *
     *
     * @param string $template The template to use.
     *
     * @return void
    */
    public function setAjaxTemplate($template){
        $this->ajaxTemplate = $template;
    }//setBodyTemplate



    /**
     * Set a variable in the view template variable.
     *
     * @param string $k The variable name.
     * @param mixed $v The variables value.
     *
     * @return void
     */
    public function setViewVariable($k,$v){
        $this->view[$k] = $v;
    }//setViewVariable



    /**
     * Get a variable in the view template variable.
     *
     * @param string $k The variable name.
     *
     * @return mixed 
     */
    public function getViewVariable($k){

        if(isset($this->view[$k])){
            return $this->view[$k];
        }//if

        return false;
    }//setViewVariable



    /**
     * Specify that the output of this view should be JSON. If `$data` is provided the response will be sent 
     * immeditatly and the application will exit.
     *
     *
     * @param null|mixed $data The response to JSON encode and return to the client. If `$data` is a string, it is 
     * assumed that it has already been JSON encoded.
     * @param int $code The HTTP response code to be returned to the client.
     *
     * @return void
    */
    public function json($data = null, $code = 200){

        $this->ajax();

        header('Content-type: application/json');

        $this->view['json'] = true;

        if($data !== null){
            http_response_code($code);
            if(!is_string($data)){
                echo json_encode($data);
            }//if
            else {
                echo $data;
            }//el
            exit;
        }//if

    }//json



    /**
     * Return whether the View is JSON.
     *
     *
     * @return boolean
    */
    public function isJson(){
        return $this->view['json'];
    }//isJson



    /**
     * Get the markup set for the View header, from $this->header.
     *
     *
     * @return string Return $this->header 
    */
    public function header(){
        return $this->view['header'];
    }//nav



    /**
     * Get the markup of the header, if its been set in `header` that will returned otherwise `$this->header()` 
     * will be called.
     *
     * @return string
    */
    public function getHeader(){

        if($this->view['header']){
            return $this->view['header'];
        }//if

        return $this->header();

    }//getHeader



    /**
     * Set the markup for the view header, in $this->header.
     * 
     *
     * @param string $html the markup
     * @return void
    */
    public function setHeader($html){
        $this->view['header'] = $html;
    }//setHeader



    /**
     * Get the markup set for the view footer.
     * 
     *
     * @return string Returns $this->footer
    */
    public function footer(){
        return $this->view['footer'];
    }//footer



    /**
     * Get the markup of the footer, if its been set in `footer` that will returned otherwise `$this->footer()` 
     * will be called.
     *
     * @return string
    */
    public function getFooter(){

        if($this->view['footer']){
            return $this->view['footer'];
        }//if

        return $this->footer();

    }//getFooter



    /**
     * Set the markup for the view footer.
     *
     *
     * @param string $html The markup to put in $this->footer
     * @return void
    */
    public function setFooter($html){
        $this->view['footer'] = $html;
    }//setFooter



    /**
     * Set the lang of the page.
     *
     *
     * @param string $lang The language to set the page as.
     * @return string|null
    */
    public function lang($lang = null){
        if(!$lang){ 
            return $this->view['lang'];
        }//if
        $this->view['lang'] = $lang;
    }//lang



    /**
     * Set the charset of the page.
     *
     *
     * @param string $charset the charset the page should use 
     * @return string|null
    */
    public function charset($charset = null){
        if($charset === null){
            return $this->view['charset'];
        }//if
        $this->view['charset'] = $charset;
    }//charset



    /**
     * Set extra elements in the header as a string.
     * 
     *
     * @param string $extra the markup to put in the head of the page
     * @return string|null
    */
    public function headExtra($extra = null){
        if($extra === null){
            return $this->view['headExtra'];
        }//if
        $this->view['headExtra'] .= $extra;
    }//headExtra



    /**
     * Return the robots index type.
     *
     * @return string.
    */
    public function robots(){
        return $this->indexes[$this->activeIndex];
    }//robots



    /**
     * Set information to be used in schema.org json-ld data that will be set in the header of the view.
     *
     *
     * @param string|array $keyOrData Either a key to set in the schema, or an array to merge with the current schema.
     * @param mixed|null $value A value to set in the schema or null if `$keyOrData` was an array.
    */
    public function schema($keyOrData, $value = null){
        if(!is_array($keyOrData)){
            $this->view['schema'][$keyOrData] = $value;
        } else {
            $this->view['schema'] = array_merge($this->view['schema'],$keyOrData);
        }//el
    }//schema



    /**
     * Get the current schema data.
     *
     * @return array The schema data.
    */
    public function getSchema(){
        return $this->view['schema'];
    }//getSchema



    /**
     * Set the schema.org markup to be set in the header, overwriting any existing data set prior.
     *
     * @param array $schema The schema data.
    */
    public function setSchema($schema){
        $this->view['schema'] = $schema;
    }//setSchema



    /**
     * When print page is called if there is schema information to add to the head of the page this will add it 
     * inside a script tag with the type set to `application/ld+json`. This will also check and see if the key 
     * `@context` has been set, if not it will be set to `http://schema.org`.
    */
    protected function addSchemaToHeadExtra(){

        if(count($this->view['schema'])){

            if(!array_key_exists('@context',$this->view['schema'])){
                $this->view['schema']['@context'] = 'http://schema.org';
            }//if

            $this->view['headExtra'] .= '<script type="application/ld+json">' . json_encode($this->view['schema']) . '</script>';

        }//if

    }//addSchemaToHeadExtra



    /**
     * If the view is AJAX render and echo the template `$this->ajaxTemplate` otherwise render the template
     * `$this->baseTemplate`.
    */
    public function printPage(){

        $this->addSchemaToHeadExtra();

        if(!$this->view['ajax']){
            $template = $this->baseTemplate;
        }//if
        else if(!$this->view['json']){
            $template = $this->ajaxTemplate;
        }//elif 
        else {
            return;
        }//el

        if($template){
            echo \Template::build($template);
        }//if

    }//printPage



    /**
     * Set that the view is returning a response to a client via an AJAX request.
     *
     * @param boolean $bool Whether were responding to an AJAX request.
    */
    public function ajax($bool = true){
        $this->view['ajax'] = $bool;
    }//ajax



    /**
     * Is the request AJAX?
     *
     * @return boolean
    */
    public function isAjax(){
        return $this->view['ajax'];
    }//isAjax



    /**
     * Set/Get the title of the view.
     *
     *
     * @param null|string $t The title of the page.
     *
     * @return string|null
    */
    public function title($t = null){
        if($t === null) return $this->view['title'];
        $this->view['title'] = $t;
    }//title



    /**
     * Set/Get the description of the view.
     *
     *
     * @param null|string $d The description of the page.
     *
     * @return string|null
    */
    public function desc($d = null){
        if($d === null) return $this->view['description'];
        $this->view['description'] = $d;
    }//desc



    /**
     * Add a snippet of html to the view.
     *
     *
     * @param string $h a string to put into the view
    */
    public function html($h){
        $this->view['body'] .= $h;
    }//html



    /**
     * Get/Set the body of the request.
     *
     *
     * @param null|string $b The body.
     *
     * @return string|null
    */
    public function body($b = null){
        if($b === null) return $this->view['body'];
        $this->view['body'] = $b;
    }//body



    /**
     * Set/Get the favicon to be used by the page.
     *
     *
     * @param null|string $v the path to the favicon
     *
     * @return string|null
    */
    public function favIcon($v = null){
        if($v === null) return $this->view['favIcon'];
        $this->view['favIcon'] = $v;
    }//favIcon



    /**
     * Create FQDN link.
     *
     * @param string $p The path of the resource.
     *
     * @return string The FQDN.
    */
    public static function url($p){
        return \App::domain() . $p;                                                                             
    }//url



    /**
     * Create FQDN link.
     *
     * @param string $p The path of the resource.
     *
     * @return string The FQDN.
    */
    public static function localUrl($p){
        return \App::domain() . $p;
    }//localUrl



    /**
     * Append the last modified time to a resource path in a GET variable (default is `c`). This can be useful if 
     * your caching assets via `.htaccess` rules and you dont want to have to rename them manually after every 
     * change.
     *
     * @param string $path The path to the resource as used in the front end.
     * @param string $var The GET variable name to store the last modified time in.
     *
     * @return string The path with the last modified time appended.
    */
    public function appendLastMod($path, $var = 'c'){

        if(substr($path,0,1) !== '/' || substr($path,0,2) === '//'){
            return $path;
        }//if

        return $path . '?c=' . stat(\App::path() . '/public' . $path)['mtime'];

    }//appendLastMod



    /**
     * Set a property on a script or style.
     *
     *
     * @param string $k The key name.
     * @param string $v The value.
     * @return void 
    */
    public function prop($k,$v){
        if($this->lastCallType=='script')
            $this->view['scriptSrcs'][count($this->view['scriptSrcs'])-1][$k]=$v;
        else if($this->lastCallType=='style')
            $this->view['styleSrcs'][count($this->view['styleSrcs'])-1][$k]=$v;
        else 
            $this->view['headScriptSrcs'][count($this->view['headScriptSrcs'])-1][$k]=$v;
    }//prop



    /**
     * Add a Javascript snippet to the page.
     *
     *
     * @param string $s A block of javascript code.
    */
    public function script($s){
        $this->view['scripts'] .= $s;
    }//pushScript



    /**
     * Add a Javascript file to the page by URL.
     *
     *
     * @param string $path A URL path to a javascript file.
     * @param boolean $appendLastMod Whether to append the last modified time to the resource path.
     *
     * @return self 
    */
    public function scriptSrc($path,$appendLastMod = false){

        if($appendLastMod){
            $path = $this->appendLastMod($path);
        }//if

        $this->view['scriptSrcs'][] = Array(
            'type'  => 'text/javascript',
            'src'   => $path,
        );

        $this->lastCallType='script';
        return $this;

    }//scriptSrc



    /**
     * Add a Javascript file to the page head by URL.
     *
     *
     * @param string $path A URL path to a javascript file.
     * @param boolean $appendLastMod Whether to append the last modified time to the resource path.
     *
     * @return self 
    */
    public function headScriptSrc($path,$appendLastMod = false){

        if($appendLastMod){
            $path = $this->appendLastMod($path);
        }//if

        $this->view['headScriptSrcs'][] = Array(
            'type'  => 'text/javascript',
            'src'   => $path,
        );

        $this->lastCallType='headScript';
        return $this;

    }//headScriptSrc



    /**
     * Add a css style to the page.
     *
     *
     * @param string $s The css style that should be applied to the page.
     * @return void
    */
    public function style($s){
        $this->view['styles'] .= $s;
    }//style



    /**
     * Add a CSS file to the page by URL.
     *
     *
     * @param string $path A url path to a CSS file.
     * @param boolean $appendLastMod Whether to append the last modified time to the resource path.
     *
     * @return self 
    */
    public function styleSrc($path,$appendLastMod = false){

        if($appendLastMod){
            $path = $this->appendLastMod($path);
        }//if

        $this->view['styleSrcs'][] = Array(
            'rel'   => 'stylesheet',
            'type'  => 'text/css',
            'href'  => $path,
        );

        $this->lastCallType='style';
        return $this;

    }//styleSrc



    /**
     * Add a body class.
    */
    public function bodyClass($class){
        $this->view['bodyClass'][] = $class;
    }//bodyClass



    /**
     * Get the class of the body.
     *
     *
     * @return string
    */
    public function bodyClasses(){
        return implode(' ',$this->view['bodyClass']);
    }//bodyClasses



    /**
     * Set the index type to index,follow ; $this->activeIndex=0.
    */
    public function index(){
        $this->activeIndex=0;
    }//index



    /**
     * Set the index type to noindex,nofollow ; $this->activeIndex=1.
    */
    public function noIndex(){
        $this->activeIndex=1;
    }//noIndex



    /**
     * Set the index type to index,nofollow ; $this->activeIndex=2.
    */
    public function indexNoFollow(){
        $this->activeIndex=2;
    }//indexNoFollow



    /**
     * Set the index type to noindex,follow ; $this->activeIndex=3.
    */
    public function noIndexFollow(){
        $this->activeIndex=3;
    }//noIndexFollow



    /**
     * Complile the style(css) sources to html `<link>` elements.
     *
     *
     * @return string The compiled elements.
    */
    public function printStyleSrcs(){

        $styles = '';

        foreach($this->view['styleSrcs'] as $s){
            $styles .= \Html::link($s);
        }//foreach

        return $styles;

    }//printStyleSrcs



    /**
     * Create a single `<style><style/>` element containing any added styles.
     *
     *
     * @return null|string
    */
    public function printStyles(){
        if($this->view['styles']){
            return '<style>'.$this->view['styles'].'</style>';
        }//if
    }//printStyles



    /**
     * Compile the head script(js) sources to html `<script>` elements.
     *
     *
     * @return string The compiled elements.
    */
    public function printHeadScriptSrcs(){
        return $this->printScriptsFromSource($this->view['headScriptSrcs']);
    }//printHeadScriptSrcs



    /**
     * Compile the script(js) sources to html `<script>` elements.
     *
     *
     * @return string The compiled elements.
    */
    public function printScriptSrcs(){
        return $this->printScriptsFromSource($this->view['scriptSrcs']);
    }//printScriptSrcs



    /**
     * Compile the script(js) sources to html `<script>` elements.
     *
     *
     * @param array $src The js sources.
     *
     * @return string The compiled elements.
    */
    private function printScriptsFromSource($src){

        $scripts = '';
        foreach($src as $s){
            $scripts .= \Html::script($s);
        }//foreach

        return $scripts;

    }//printScriptsFromSource



    /**
     * Create a single `<script><script/>` element containing any added scripts.
     *
     *
     * @return null|string
    */
    public function printScripts(){
        if($this->view['scripts']){
            return '<script type="text/javascript">' . $this->view['scripts'] . '</script>';
        }//if
    }//printScripts



    /**
     * Serve a 404, executing an optional closure or template.
     *
     * @param boolean|string|\Closure $action Optional template or Closure function.
    */
    public function abort($action = false){
        $this->serve(404, $action);
    }//abort



    /**
     * Serve an error page, defined by a template or an error file, stored in $this->errorDir.
     *
     * @param int $code The HTTP Error code.
     * @param boolean|string|\Closure $action Optional template or Closure function.
    */
    public function error($code = 500, $action = false){
        $this->serve($code, $action);
    }//abort



    /**
     * Redirect the request to another URL.
     *
     *
     * @param string $url The URL to redirect to.
     * @return void
    */
    public function redirect($url){
        header('Location: ' . $url);
        exit;
    }//redirect



    /**
     * Add a HTTP header to the response.
     *
     *
     * @param string $h The HTTP header to add.
     * @return void
    */
    public function httpHeader($h){
        header($h);
    }//httpHeader



    /**
     * Serve a specified http response code page by either executing a template or the passed \Closure $fun function, 
     * or loading the \Closure function from the file $this->errorDir . $code.php and executing it or by 
     * a default message set by the function.
     *
     *
     * @param int $code The http repsonse code sent to the client from the server.
     * @param boolean|string|\Closure $action A template or closure to execute when $code != 200.
     * @return void 
    */
    public final function serve($code = 200, $action = false){

        if($code != 200){

            //set the response code
            http_response_code($code);

            //clear the body of the view
            \View::body('');

            if(is_string($action)){
                \Template::with($action);
            }//if
            else if($action !== false && $action instanceof \Closure){
                call_user_func($action);
            }//if
            else {
                $file = \App::path() . '/' . trim($this->errorDir,'/') . '/' . $code . '.php';
                if(is_file($file)){
                    $action = require($file);
                    call_user_func($action,\App::instance());
                }//if
            }//el
        }//if

        //Print out the Current View.
        if(!\App::instance()->cli){
            \View::printPage();
        }//if

        exit;

    }//serve



}//View
?>

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
     * @var array The data to be injected into the $baseTemplate template when rendered.
    */
    private $view = Array(
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
        'footer'            => ''
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
     * @param null|mixed $data The response to JSON encode and return to the client.
     * @param int $code The HTTP response code to be returned to the client.
     *
     * @return void
    */
    public function json($data = null,$code = 200){
        $this->ajax();
        header('Content-type: application/json');
        $this->view['json'] = true;

        if($data !== null){
            http_response_code($code);
            echo json_encode($data);
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
     * @return void
    */
    public function lang($lang = null){
        if(!$lang) return $this->view['lang'];
        $this->view['lang'] = $lang;
    }//lang



    /**
     * Set the charset of the page.
     *
     *
     * @param string $charset the charset the page should use 
     * @return void
    */
    public function charset($charset = null){
        if($charset === null) return $this->view['charset'];
        $this->view['charset'] = $charset;
    }//charset



    /**
     * Set extra elements in the header as a string.
     * 
     *
     * @param string $extra the markup to put in the head of the page
     * @return void
    */
    public function headExtra($extra = null){
        if($extra === null) return $this->view['headExtra'];
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
     * If the view is AJAX render and echo the tempalte `$this->ajaxTemplate` otherwise render the template 
     * `$this->baseTemplate`.
     *
     * @return void
    */
    public function printPage(){


        if(!$this->view['ajax']){
            $template = $this->baseTemplate;
        }//if
        else if(!$this->view['json']){
            $template = $this->ajaxTemplate;
        } else {
            return;
        }//el

        echo \Template::build($template);

    }//printPage



    /**
     * Set that the view is returning a response to a client via an AJAX request.
     *
     * @param boolean $bool Whether were responding to an AJAX request.
     * @return void
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
     * @return string|void
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
     * @return string|void
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
     * @return string|void
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
     * @return string|void
    */
    public function favIcon($v = null){
        if($v === null) return $this->view['favIcon'];
        $this->view['favIcon'] = $v;
    }//favIcon



    /**
     * When we create full path links to resources and the browser is using SSL/HTTPS
     * we need to make sure we request that resource as such in order to avoid mixed content errors.
     *
     *
     * @param string $p The path of the resource.
     * @param string $h The host of the resource ( if not local).
    */
    public static function url($p,$h=null){
        if(!empty($_SERVER['HTTPS']) && $h===null && substr($p,0,1)=='/'){
            $p = \App::domain() . $p;                                                                             
        }//if                                                                                                               
        else if($h!==null && substr($h,0,3)!='http'){                                                                        
            $p = 'http://'.$h.$p;                                                                                           
        }//elif   
        return $p;
    }//url



    /**
     * When we create full path links to resources and the browser is using SSL/HTTPS
     * we need to make sure we request that resource as such in order to avoid mixed content errors.
     *
     *
     * @param string $p The path of the resource.
    */
    public static function localUrl($p){
        return \App::domain() . $p;
    }//localUrl



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
            $this->view['scriptSrcs'][count($this->view['scriptSrcs'])-1]['props'][$k]=$v;
        else if($this->lastCallType=='style')
            $this->view['styleSrcs'][count($this->view['styleSrcs'])-1]['props'][$k]=$v;
        else 
            $this->view['headScriptSrcs'][count($this->view['headScriptSrcs'])-1]['props'][$k]=$v;
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
     * @param string $s A URL path to a javascript file.
     * @return self 
    */
    public function scriptSrc($s){
        $this->view['scriptSrcs'][]=Array('src'=>$s,'props'=>Array());
        $this->lastCallType='script';
        return $this;
    }//scriptSrc



    /**
     * Add a Javascript file to the page head by URL.
     *
     *
     * @param string $s A URL path to a javascript file.
     * @return self 
    */
    public function headScriptSrc($s){
        $this->view['headScriptSrcs'][]=Array('src'=>$s,'props'=>Array());
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
     * @param string $s A url path to a CSS file.
     * @return self 
    */
    public function styleSrc($s){
        $this->view['styleSrcs'][]=Array('src'=>$s,'props'=>Array());
        $this->lastCallType='style';
        return $this;
    }//styleSrc



    /**
     * Add a body class.
     *
     *
     * @param string $s A CSS class.
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
     * Set the index type to index,follow , $this->activeIndex=0.
    */
    public function index(){
        $this->activeIndex=0;
    }//index



    /**
     * Set the index type to noindex,nofollow , $this->activeIndex=1.
    */
    public function noIndex(){
        $this->activeIndex=1;
    }//noIndex



    /**
     * Set the index type to index,nofollow, $this->activeIndex=2.
    */
    public function indexNoFollow(){
        $this->activeIndex=2;
    }//indexNoFollow



    /**
     * Set the index type to noindex,follow, $this->activeIndex=3.
    */
    public function noIndexFollow(){
        $this->activeIndex=3;
    }//noIndexFollow



    /**
     * Serve a specified http response code page by either executing the passed \Closure $fun function, 
     * or loading the \Closure function from the file /app/$code.php and executing it or by 
     * a default message set by the function.
     *
     *
     * @param int $code The http repsonse code sent to the client from the server.
     * @param string|\Closure $callable A file with a callable function returned or a \Closure, to be executed when 
     * `$code != 200`.
     *
     * @return void 
    */
    public final function serve($code=200,$callable=false){

        if($code!=200){
            http_response_code($code);
            $file = \App::path()."/app/{$code}.php";
            if($callable !== false && $callable instanceof \Closure){
                call_user_func($callable);
            }//if
            else if(is_file($file)){
                $action = require($file);
                call_user_func($action,\App::instance());
            }//if
        }//if

        //Print out the Current View.
        if(!\App::instance()->cli){
            View::printPage();
        }//if

        //exit;

    }//serve



    /**
     * Complile the style(css) sources to html `<link>` elements.
     *
     *
     * @return string The compliled elements.
    */
    public function printStyleSrcs(){

        $styles = '';
        foreach($this->view['styleSrcs'] as $s){
            $styles .= '<link rel="stylesheet" href="'.$this->url($s['src']).'" type="text/css"';
            foreach($s['props'] as $p=>$a){
                $styles .= " {$p}=\"{$a}\"";
            }//foreach
            $styles .= '/>';
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
     * Complile the head script(js) sources to html `<script>` elements.
     *
     *
     * @return string The compliled elements.
    */
    public function printHeadScriptSrcs(){
        return $this->printScriptsFromSource($this->view['headScriptSrcs']);
    }//printHeadScriptSrcs



    /**
     * Complile the script(js) sources to html `<script>` elements.
     *
     *
     * @return string The compliled elements.
    */
    public function printScriptSrcs(){
        return $this->printScriptsFromSource($this->view['scriptSrcs']);
    }//printScriptSrcs



    /**
     * Complile the script(js) sources to html `<script>` elements.
     *
     *
     * @param array $src The js sources.
     *
     * @return string The compliled elements.
    */
    private function printScriptsFromSource($src){

        $scripts = '';
        foreach($src as $s){
            $scripts .= '<script src="' . $this->url($s['src']) . '" type="text/javascript"';
            foreach($s['props'] as $p=>$a){
                $scripts .= " {$p}=\"{$a}\"";
            }//foreach
            $scripts .= '></script>';
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
     * Serve a 404, executing an optional closure.
     *
     * @param boolean|\Closure $closure Optional Closure function to execute.
    */
    public function abort($closure = false){
        $this->serve(404,$closure);
    }//abort



}//View
?>

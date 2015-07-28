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
     * @var array The data to be injected into the $baseLayout template in the view variable.
    */
    private $view = Array(
        'title'             => '',
        'description'       => '',
        'charset'           => 'utf-8',
        'lang'              => 'en',
        'favIcon'           => '/favicon.png',
        'robots'            => '',
        'ajax'              => false,
        'scriptSrcs'        => Array(),
        'scripts'           => '',
        'headScriptSrcs'    => Array(),
        'styles'            => '',
        'styleSrcs'         => Array(),
        'headExtra'         => '',
        'bodyStyles'        => '',
        'header'            => '',
        'body'              => '',
        'footer'            => ''
    );


    /**
     * @var string Flips between script and style
    */
    private $lastCallType;


    /**
     * @var array How should it be scraped
    */
    private $indexes = Array(
        0=>'index,follow',
        1=>'noindex,nofollow',
        2=>'index,nofollow',
        3=>'noindex,follow'
    );

    
    /**
     * @var integer Standard scrape for $indexes
    */
    public $activeIndex=0;



    //public function get($k){

    //    if(isset($this->view[$k])){
    //        return $this->view[$k];
    //    }//if

    //    return null;

    //}//get


    /**
     * Override the default body template for the View.
     *
     *
     * @var string $template The template to use.
     * @return void
    */
    public function setBaseTemplate($template){
        $this->baseTemplate = $template;
    }//setBodyTemplate



    /**
     * Override the default body template for the Ajax View.
     *
     *
     * @var string $template The template to use.
     * @return void
    */
    public function setAjaxTemplate($template){
        $this->ajaxTemplate = $template;
    }//setBodyTemplate



    /**
     * Set a variable in the view template variable.
     *
     * @var string $n The variable name.
     * @var mixed $v The variables value.
     *
     * @return void
     */
    public function setViewVariable($n,$v){
        $this->view[$n] = $v;
    }//setViewVariable



    /**
     * Get a variable in the view template variable.
     *
     * @var string $n The variable name.
     *
     * @return mixed 
     */
    public function getViewVariable($n){
        return $this->view[$n];
    }//setViewVariable



    /**
     * Specify that the output of this view should be JSON.
     *
     *
     * @return void
    */
    public function json(){
        $this->ajax();
        header('Content-type: application/json');
    }//json



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
        if(!$charset) return $this->view['charset'];
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
        if(!$extra) return $this->view['headExtra'];
        $this->view['headExtra'] .= $extra;
    }//headExtra



    public function robots(){
        $this->indexes[$this->activeIndex];
    }//robots




    /**
     * This function handles putting togethor
     * and echoing the pieces that make up the View.
     *
     *
     * @return void
    */
    public function printPage(){


        if(!$this->view['ajax']){
            $template = $this->baseTemplate;
        }//if
        else {
            $template = $this->ajaxTemplate;
        }//el

        if(($alias = \App::resolveAlias($template)) !== false){
            $template = $alias;
        }//if

        echo \Template::render($template);


    }//printPage



    /**
     * Set that a request is AJAX. 
     *
     * @var boolean $bool 
     * @return void
    */
    public function ajax($bool = true){
        $this->view['ajax'] = $bool;
    }//isAjax



    /**
     * Is the request AJAX?
     *
     * @return boolean
    */
    public function isAjax(){
        return $this->view['ajax'];
    }//isAjax



    /**
     * Set the title of the view.
     *
     *
     * @param string $t the title of the page
    */
    public function title($t = null){
        if(!$t) return $this->view['title'];
        $this->view['title'] = $t;
    }//setTitle



    /**
     * Set the description of the view.
     *
     *
     * @param string $d the description of the page
    */
    public function desc($d = null){
        if(!$d) return $this->view['description'];
        $this->view['description'] = $d;
    }//setDesc



    /**
     * Add a snippet of html to the view.
     *
     *
     * @param string $h a string to put into the view
    */
    public function html($h){
        $this->view['body'] .= $h;
    }//html



    public function body(){
        return $this->view['body'];
    }//body


    /**
     * Set the favicon to be used by the page.
     *
     *
     * @param string $v the path to the favicon
     * @return void
    */
    public function favIcon($v = null){
        if(!$v) return $this->view['favIcon'];
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
            $p = 'https://'.\App::config('URL').$p;                                                                             
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
     * @param string $h The host of the resource ( if not local).
    */
    public static function localUrl($p){
        if(!empty($_SERVER['HTTPS']) && substr($p,0,1)=='/'){
            $p = 'https://'.\App::config('URL').$p;
        }//if
        else if(substr($p,0,3)!='http'){
            $p = 'http://'.\App::config('URL').$p;
        }//elif
        return $p;
    }//url



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
    }//pushScriptSrc


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
    }//pushScriptSrc




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
    }//pushStyleSrc



    /**
     * Add a body class.
     *
     *
     * @param string $s A CSS class.
    */
    public function bodyStyle($s){
        $this->view['bodyStyles'] .= $s.' ';
    }//pushBodyStyle



    /**
     * Set the index type to index,follow , $this->activeIndex=0.
     *
     *
     * @return void
    */
    public function index(){
        $this->activeIndex=0;
    }//index


    /**
     * Set the index type to noindex,nofollow , $this->activeIndex=1.
     *
     *
     * @return void
    */
    public function noIndex(){
        $this->activeIndex=1;
    }//noIndex



    /**
     * Set the index type to index,nofollow, $this->activeIndex=2.
     *
     *
     * @return void
    */
    public function indexNoFollow(){
        $this->activeIndex=2;
    }//indexNoFollow



    /**
     * Set the index type to noindex,follow, $this->activeIndex=3.
     *
     *
     * @return void
    */
    public function noIndexFollow(){
        $this->activeIndex=3;
    }//noIndexFollow




    /*
     * Serve a specified http response code page by either executing the passed \Closure $fun function, 
     * or loading the \Closure function from the file /app/$code.php and executing it or by 
     * a default message set by the function.
     *
     *
     * @param int $code The http repsonse code sent to the client from the server.
     *
     * @return void 
    */
    public final function serve($code=200,$callable=false){

        if($code!=200){
            http_response_code($code);
            $file = \App::path()."/app/{$code}.php";
            if($callable !== false){
                //call_user_func($callable);
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



    public function printStyles(){
        if($this->view['styles']){
            return '<style>'.$this->view['styles'].'</style>';
        }//if
    }//printStyles



    public function printHeadScriptSrcs(){
        return $this->printScriptsFromSource($this->view['headScriptSrcs']);
    }//printScriptSrcs



    public function printScriptSrcs(){
        return $this->printScriptsFromSource($this->view['scriptSrcs']);
    }//printScriptSrcs



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

    }//printStyleSrcs



    public function printScripts(){
        if($this->view['scripts']){
            return '<script type="text/javascript">' . $this->view['scripts'] . '</script>';
        }//if
    }//printScripts



    public function abort($closure = false){
        $this->serve(404,$closure);
    }//status


}//BaseView


?>

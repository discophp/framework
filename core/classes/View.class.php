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
     * @var array The data to be injected into the $baseLayout template in the view variable.
    */
    private $view = Array(
        'title'             => '',
        'description'       => '',
        'charset'           => 'utf-8',
        'lang'              => 'en',
        'favIcon'           => '/favicon.png',
        'robots'            => '',
        'isAjax'              => false,
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
        $this->isAjax();
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
    public function lang($lang){
        $this->view['lang'] = $lang;
    }//lang



    /**
     * Set the charset of the page.
     *
     *
     * @param string $charset the charset the page should use 
     * @return void
    */
    public function charset($charset){
        $this->view['charset'] = $charset;
    }//charset



    /**
     * Set extra elements in the header as a string.
     * 
     *
     * @param string $extra the markup to put in the head of the page
     * @return void
    */
    public function headExtra($extra){
        $this->view['headExtra'] .= $extra;
    }//headExtra



    /**
     * This function handles putting togethor
     * and echoing the pieces that make up the View.
     *
     *
     * @return void
    */
    public function printPage(){


        $render = $this->view;
        $render['robots'] = $this->indexes[$this->activeIndex];
        $render['header'] = $this->header();
        $render['footer'] = $this->footer();
        echo \Template::render($this->baseTemplate,Array('view'=>$render));

    }//printPage



    /**
     * Set that a request is AJAX. 
     *
     * @var boolean $bool 
     * @return void
    */
    public function isAjax($bool = true){
        $this->view['isAjax'] = $bool;
    }//isAjax



    /**
     * Set the title of the view.
     *
     *
     * @param string $t the title of the page
    */
    public function title($t){
        $this->view['title'] = $t;
    }//setTitle



    /**
     * Set the description of the view.
     *
     *
     * @param string $d the description of the page
    */
    public function desc($d){
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


    /**
     * Set the favicon to be used by the page.
     *
     *
     * @param string $v the path to the favicon
     * @return void
    */
    public function favIcon($v){
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
    public function url($p,$h=null){
        if(!empty($_SERVER['HTTPS']) && $h==null && substr($p,0,1)=='/'){
            $p = 'https://'.App::config('URL').$p;                                                                             
        }//if                                                                                                               
        else if($h!=null && substr($h,0,3)!='http'){                                                                        
            $p = 'http://'.$h.$p;                                                                                           
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
    public final function serve($code=200){

        if($code!=200){
            http_response_code($code);
            $file = \App::path()."/app/{$code}.php";
            if(is_file($file)){
                $action = require($file);
                call_user_func($action,\App::instance());
            }//if
        }//if

        //Print out the Current View.
        if(!\App::instance()->cli){
            View::printPage();
        }//if

    }//handle404




}//BaseView


?>

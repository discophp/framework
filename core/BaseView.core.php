<?php
/**
 *      This file holds the BaseView class
*/


/**
 *
 *      BaseView class.
 *      The BaseView class provides the functionality of dynamically creating
 *      web pages. Essentially the eventual response to the client.
 *
 *      See the docs at discophp.com/docs/View
*/
class BaseView {

    /**
     *      Flips between script and style
    */
    private $lastCallType;

    /**
    *       hold html bits
    */
    private $html = Array();

    /**
     *      hold script(js) bits
    */
    private $scripts = Array();


    /**
     *      hold script(js) URLs 
    */
    private $scriptSrcs = Array();


    /**
     *      hold head script(js) URLs 
    */
    private $headScriptSrcs = Array();


    /**
     *      hold style(css) bits
    */
    private $styles = Array();

    /**
     *      hold style(css) URLs 
    */
    private $styleSrcs = Array();


    /**
     * hold classes to apply to the body element
    */
    private $bodyStyles = Array();

    /**
     *      page title
    */
    public $title;

    /**
     *      page description
    */
    public $description;


    /**
     *      Path to favicon
    */
    public $favIcon='/favicon.png';


    /**
     *      set this to the path of your working project
    */
    public $path;

    /**
     *      name of your default stylesheet
    */
    public $styleSheet = 'css';

    /**
     *      name of your default javascript file
    */
    public $script = 'js';

    /**
     *      html mark up of our header
    */
    public $header='';

    /**
     *      html mark up of our footer
    */
    public $footer='';

    /**
     *      is the request AJAX?
    */
    private $isAjax=false;

    /**
     *      how should it be scraped
    */
    private $indexes = Array(
        0=>'index,follow',
        1=>'noindex,nofollow',
        2=>'index,nofollow',
        3=>'noindex,follow'
    );
    
    /**
     *      standard scrape
    */
    public $activeIndex=0;



    /**
    *      Default Constructor
    *
    *
    *      @return void
    */
    public function __construct(){

        //is a url set from .env.local.json || .env.json
        if(isset($_SERVER['URL'])){
            $this->path=$_SERVER['URL'];
        }//if

        $this->title = "";
        $this->description = "";

    }//construct



    /**
     *      Specify that the output of this view should be json
     *
     *
     *      @return void
    */
    public function json(){
        View::isAjax();
        header('Content-type: application/json');
    }//json



    /**
     *      Get the markup set for the view header
     *  
     *
     *      @return string $this->header 
    */
    public function header(){
        return $this->header;;
    }//nav



    /**
     *      Set the markup for the view header
     *      
     *
     *      @param string $html the markup
    */
    public function setHeader($html){
        $this->header = $html;
    }//setHeader



    /**
     *      Get the markup set for the view footer
     *      
     *
     *      @return string $this->footer
    */
    public function footer(){
        return $this->footer;
    }//footer



    /**
     *      Set the markup for the view footer
     *
     *
     *      @param string $html the markup
    */
    public function setFooter($html){
        $this->footer = $html;
    }//setFooter



    /**
     *      Return the markup for the Views <head></head> element
     *
     *
     *      @return string 
   */
    private function metaHeader(){

        $metaHeader = " 
        <!doctype html>
            <html class='no-js' lang='en'>
            <head>
                <meta charset='utf-8' />
                <meta content='%6\$s' name='robots'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0' />
                
                <title> %1\$s </title>
                <meta name='description' content='%2\$s'>
        
                <link type='image/x-icon' href='%7\$s' rel='shortcut icon'>
                
                <!--[if IE]>
                <script type='text/javascript'>var isIE=true;</script>
                <![endif]-->
                            
                %3\$s
                %4\$s
                %8\$s
        
                </head>
            <body class='%5\$s'>
            ";

        return sprintf($metaHeader,
            $this->title,
            $this->description,
            $this->printStyles(),
            $this->printStyleSrcs(),
            $this->bodyStyles(),
            $this->indexes[$this->activeIndex],
            $this->favIcon,
            $this->printScriptSrcs($this->headScriptSrcs)
        );

    }//buildMetaHeader



    /**
     *      This function handles putting togethor
     *      and echoing the pieces that make up the View
     *
     *
     *      @return void
    */
    public function printPage(){

        if($this->isAjax){
            $this->printAjaxPage();
            return;
        }//isAjax

        //print the metaheader
        echo $this->metaHeader();

        //print the body
        echo '<div id="body">';
            $this->HTMLDump();
        echo '</div>';

        //print the header  & footer
        echo "
            <div id='header'>
            ".(($this->header=='')?$this->header():$this->header)."
            </div>

            <div id='footer'>
            ".(($this->footer=='')?$this->footer():$this->footer)."
            </div>
            ";

        //print the closing page info and markup 
        $this->printFooter();

    }//printPage



    /**
     *      Set that a request is AJAX 
     *
     *      @return void
    */
    public function isAjax(){
        $this->isAjax=true;
    }//isAjaxa



    /**
     *      Print an ajax request.
     *      Will not contain any meta head info
     *      only html bits and added scripts
     *
     *      @return void
    */
    private function printAjaxPage(){
        echo $this->HTMLDump();
    }//printAjaxPage



    /**
     *     Return the closing of the page and the scripts and script srcs 
     *
     *
     *     @return void
    */
    public function printFooter(){
        echo "
            {$this->printScriptSrcs($this->scriptSrcs)}
            {$this->printScripts()}
            </body>
         </html>";
    }//printFooter



    /**
     *      Set the title of the view.
     *
     *
     *      @param string $t the title of the page
    */
    public function title($t){
        $this->title=$t;
    }//setTitle



    /**
     *      Set the description of the view
     *
     *
     *      @param string $d the description of the page
    */
    public function desc($d){
        $this->description=$d;
    }//setDesc



    /**
     *      Add a snippet of html to the view
     *
     *
     *      @param string $h a string to put into the view
    */
    public function html($h){
        $this->html[]=$h;
    }//html


    /**
     *      Set the favicon to be used by the page
     *
     *
     *      @param string $v the path to the favicon
     *      @return void
    */
    public function favIcon($v){
        $this->favIcon=$v;
    }//favIcon




    /**
     *      Set a property on a script or style
     *
     *
     *      @param string $k the key name
     *      @param string $v the value
     *      @return void 
    */
    public function prop($k,$v){
        if($this->lastCallType=='script')
            $this->scriptSrcs[count($this->scriptSrcs)-1]['props'][$k]=$v;
        else if($this->lastCallType=='style')
            $this->styleSrcs[count($this->styleSrcs)-1]['props'][$k]=$v;
        else 
            $this->headScriptSrcs[count($this->headScriptSrcs)-1]['props'][$k]=$v;
    }//prop



    /**
     *      Add a Javascript snippet to the page
     *
     *
     *      @param string $s a block of javascript code
    */
    public function script($s){
        if(is_array($s))
            array_merge($this->scripts,$s);
        else
            $this->scripts[]=$s;
    }//pushScript



    /**
     *      Add a Javascript file to the page by URL
     *
     *
     *      @param string $s a url path to a javascript file
     *      @return object $this
    */
    public function scriptSrc($s){

        $this->scriptSrcs[]=Array('src'=>$s,'props'=>Array());

        $this->lastCallType='script';
        return $this;
    }//pushScriptSrc


    /**
     *      Add a Javascript file to the page head by URL
     *
     *
     *      @param string $s a url path to a javascript file
     *      @return object $this
    */
    public function headScriptSrc($s){

        $this->headScriptSrcs[]=Array('src'=>$s,'props'=>Array());

        $this->lastCallType='headScript';
        return $this;
    }//pushScriptSrc




    /**
     *      Add a css style to the page
     *
     *
     *      @param string $s
    */
    public function style($s){
        $this->styles[]=$s;
    }//style



    /**
     *      Add a CSS file to the page by URL
     *
     *
     *      @param string $s a url path to a CSS file
     *      @return object $this
    */
    public function styleSrc($s){
        $this->styleSrcs[]=Array('src'=>$s,'props'=>Array());

        $this->lastCallType='style';
        return $this;
    }//pushStyleSrc



    /**
     *      Add a body class 
     *
     *
     *      @param string $s a css class
    */
    public function bodyStyle($s){
        $this->bodyStyles[] = $s;
    }//pushBodyStyle



    /**
     *      create html to include added scripts      
     *
     *
     *      @return string 
    */
    private function printScripts(){
        return '<script type="text/javascript">'.implode('',$this->scripts).'</script>';
    }//printScripts



    /**
     *      Create the html that includes the needed JavaScript files
     *
     *
     *      @param array $sData Array of scripts to print
     *      @return string
    */
    private function printScriptSrcs($sData){
        $scripts='';
        foreach($sData as $s){
            $props='';
            foreach($s['props'] as $k=>$v)
                $props.="{$k}='{$v}' ";
           $scripts.="<script type='text/javascript' src='{$s['src']}' {$props}></script>"; 
        }//foreach
        return $scripts;
    }//printScriptSrcs



    /**
     *      return html to contain added css styles fragments
     *
     *
     *      @return string
    */
    private function printStyles(){
        if(count($this->styles)==0){
            return '';
        }//if
        return '<style>'.implode('',$this->styles).'</style>';
    }//printStyles



    /** 
     *      Create the html that includes the needed CSS stylesheets 
     *
     *
     *      @return string
    */
    private function printStyleSrcs(){
        $styles = '';
        foreach($this->styleSrcs as $s){
            $props='';
            foreach($s['props'] as $k=>$v)
                $props.="{$k}='{$v}' ";
            $styles.= "<link rel='stylesheet' href='{$s['src']}' type='text/css' {$props}/>";
        }//foreach
        return $styles;
    }//printStyleSrcs



    /**
     *      Return the classes to be added to the body.
     *
     *
     *      @return string
    */
    private function bodyStyles(){
        return implode(' ',$this->bodyStyles);
    }//addBodyStyles



    /**
     *      echo all our bits
     *
     *
     *      @return void
    */
    private function HTMLDump(){
        foreach($this->html as $p){
            echo $p;
        }//foreach
    }//HTMLDump



    /**
     *      set the index type to
     *      noindex,nofollow
     *
     *
     *      @return void
    */
    public function noIndex(){
        $this->activeIndex=1;
    }//noIndex



    /**
     *      set the index type to
     *      index,nofollow
     *
     *
     *      @return void
    */
    public function indexNoFollow(){
        $this->activeIndex=2;
    }//indexNoFollow



    /**
     *      set the index type to
     *      noindex,follow
     *
     *
     *      @return void
    */
    public function noIndexFollow(){
        $this->activeIndex=3;
    }//noIndexFollow



}//BaseView


?>

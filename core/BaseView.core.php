<?php

class BaseView {

    //hold html bits
    private $html = Array();

    //hold script(js) bits
    private $scripts = Array();

    //hold script(js) URLs 
    private $scriptSrcs = Array();

    //hold style(css) bits
    private $styles = Array();

    //hold style(css) URLs 
    private $styleSrcs = Array();

    //hold classes to apply to the body element
    private $bodyStyles = Array();

    //page title
    public $title;

    //page description
    public $description;

    //set this to the path of your working project
    public $path;

    //name of your default stylesheet
    public $styleSheet = 'css';

    //name of your default javascript file
    public $script = 'js';

    //html mark up of our header
    public $header='';

    //html mark up of our footer
    public $footer='';

    //is the request AJAX?
    private $isAjax=false;

    //how should it be scraped
    private $indexes = Array(
        0=>'index,follow',
        1=>'noindex,nofollow',
        2=>'index,nofollow',
        3=>'noindex,follow'
    );
    
    //standard scrape
    public $activeIndex=0;


    /**
    *      Default Constructor
    */
    public function __construct(){

        //is a url set from .env.local.json || .env.json
        if(isset($_SERVER['URL'])){
            $this->path=$_SERVER['URL'];
        }//if

        $this->title = "Default Page Title";
        $this->description = "";


        //provide the same end point url as a javascript variable for use
        //      provides consistency 
        $this->script('var endPoint="'.$this->path.'";');

    }//construct



    /**
     *      @return html
    */
    public function header(){
        return $this->header;;
    }//nav



    /**
     *      @param html
    */
    public function setHeader($html){
        $this->header = $html;
    }//setHeader


    /**
     *      @return html
    */
    public function footer(){
        return $this->footer;
    }//footer


    /**
     *      @param string $html
    */
    public function setFooter($html){
        $this->footer = $html;
    }//setFooter


    /**
     *      @return html
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
        
                <link type='image/x-icon' href='/favicon.png' rel='shortcut icon'>
                
                <!--[if IE]>
                <script type='text/javascript'>var isIE=true;</script>
                <![endif]-->
                            
                %3\$s
                %4\$s
        
                </head>
            <body class='row %5\$s'>
            ";

        return sprintf($metaHeader,
            $this->title,
            $this->description,
            $this->printStyles(),
            $this->printStyleSrcs(),
            $this->printBodyStyles(),
            $this->indexes[$this->activeIndex]
        );

    }//buildMetaHeader

    /**
     *      This function handles putting togethor
     *      and echoing the pieces that make up the View
    */
    public function printPage(){

        if($this->isAjax){
            $this->printAjaxPage();
            return;
        }//isAjax

        echo $this->metaHeader();

        echo '<div id="body">';
            $this->HTMLDump();
        echo '</div>';//close #bottom-page

        //print the header 
        echo "
            <div id='header'>
            ".(($this->header=='')?$this->header():$this->header)."
            </div>

            <div id='footer'>
            ".(($this->footer=='')?$this->footer():$this->footer)."
            </div>
            ";

        $this->printFooter();

    }//printPage


    /**
     *      Print an ajax request 
     *      Will not contain any meta head info
     *      only html bits and added scripts
    */
    private function printAjaxPage(){
        echo $this->HTMLDump();
        echo $this->printScripts();
    }//printAjaxPage


    /**
     *      the footer
    */
    public function printFooter(){
        echo "
            {$this->printScriptSrcs()}
            {$this->printScripts()}
            </body>
         </html>";
    }//printFooter


    /**
     *      @param string $t
    */
    public function title($t){
        $this->title=$t;
    }//setTitle


    /**
     *      @param string $d
    */
    public function desc($d){
        $this->description=$d;
    }//setDesc


    /**
     *      @param string $h
    */
    public function html($h){
        $this->html[]=$h;
    }//html


    /**
     *      @param string $s 
    */
    public function script($s){
        if(is_array($s))
            array_merge($this->scripts,$s);
        else
            $this->scripts[]=$s;
    }//pushScript


    /**
     *      @param string $s
    */
    public function scriptSrc($s){
        if(is_array($s))
            array_merge($this->scriptSrcs,$s);
        else
            $this->scriptSrcs[]=$s;
    }//pushScriptSrc


    /**
     *      @param string $s
    */
    public function style($s){
        $this->styles[]=$s;
    }//style


    /**
     *      @param string $s
    */
    public function styleSrc($s){
        if(is_array($s))
            array_merge($this->styleSrcs,$s);
        else 
            $this->styleSrcs[]=$s;
    }//pushStyleSrc


    /**
     *      @param string $s
    */
    public function bodyStyle($s){
        $this->bodyStyles[] = $s;
    }//pushBodyStyle


    /**
     *      @return string
    */
    private function printScripts(){
        return '<script type="text/javascript">'.implode('',$this->scripts).'</script>';
    }//printScripts



    /**
     *      @return string
    */
    private function printScriptSrcs(){
        $scripts='';
        foreach($this->scriptSrcs as $s){
           $scripts.="<script type='text/javascript' src='{$s}'></script>"; 
        }//foreach
        return $scripts;
    }//printScriptSrcs



    /**
     *      @return string
    */
    private function printStyles(){
        if(count($this->styles)==0){
            return '';
        }//if
        return '<style>'.implode('',$this->styles).'</style>';
    }//printStyles



    /** 
     *      @return string
    */
    private function printStyleSrcs(){
        $styles = '';
        foreach($this->styleSrcs as $s){
            $styles.= "<link rel='stylesheet' href='{$s}' type='text/css'/>";
        }//foreach
        return $styles;
    }//printStyleSrcs


    /**
     *      @return string
    */
    private function printBodyStyles(){
        return implode(' ',$this->bodyStyles);
    }//addBodyStyles


    /**
     *      echo all our bits
    */
    private function HTMLDump(){
        foreach($this->html as $p){
            echo $p;
        }//foreach
    }//HTMLDump

    /**
     *      set the index type to
     *      noindex,nofollow
    */
    public function noIndex(){
        $this->activeIndex=1;
    }//noIndex

    /**
     *      set the index type to
     *      index,nofollow
    */
    public function indexNoFollow(){
        $this->activeIndex=2;
    }//indexNoFollow

    /**
     *      set the index type to
     *      noindex,follow
    */
    public function noIndexFollow(){
        $this->activeIndex=3;
    }//noIndexFollow

}//BaseView


?>

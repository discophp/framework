<?php

namespace Disco\classes;

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
class View {

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
     *      Extra elements (added by user as a string) to go in the head of the page
    */
    public $headExtra = '';

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
     *      default language
    */
    public $lang='en';

    /**
     *      default charset 
    */
    public $charset='utf-8';

    /**
     *      SEO view?
    */
    public $seo=false;



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
     *      Set the lang of the page
     *
     *
     *      @param string $lang the language to set the page as
     *      @return void
    */
    public function lang($lang){
        $this->lang=$lang;
    }//lang



    /**
     *      Set the charset of the page
     *
     *
     *      @param string $charset the charset the page should use 
     *      @return void
    */
    public function charset($charset){
        $this->charset=$charset;
    }//charset



    /**
     *      Set extra elements in the header as a string
     *      
     *
     *      @param string $extra the markup to put in the head of the page
     *      @return void
    */
    public function headExtra($extra){
        $this->headExtra.=$extra;
    }//headExtra



    /**
     *      Set extra elements in the header as a string
     *      
     *
     *      @param string $extra the markup to put in the head of the page
     *      @return void
    */
    public function seo($bool=true){
        $this->seo=$bool;
    }//seo



    /**
     *      Return the markup for the Views <head></head> element
     *
     *
     *      @return string 
   */
    private function metaHeader(){

        $metaHeader = " 
        <!doctype html>
            <html lang='%1\$s'>
            <head>
                <meta charset='%2\$s' />
                <meta content='%3\$s' name='robots'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0' />
                
                <title>%4\$s</title>
                <meta name='description' content='%5\$s'>
        
                <link type='image/x-icon' href='%6\$s' rel='shortcut icon'>
                            
                %7\$s
                %8\$s
                %9\$s
                %10\$s
        
                </head>
            <body class='%11\$s'>
            ";

        return sprintf($metaHeader,
            $this->lang,
            $this->charset,
            $this->indexes[$this->activeIndex],
            $this->title,
            $this->description,
            $this->favIcon,
            $this->printStyleSrcs(),
            $this->printStyles(),
            $this->printScriptSrcs($this->headScriptSrcs),
            $this->headExtra,
            $this->bodyStyles()
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

        if($this->seo)
            $this->bodyStyle('disco-seo');

        //print the metaheader
        echo $this->metaHeader();

        $header = '<div id="header">'.(($this->header=='')?$this->header():$this->header).'</div>';
        $footer = '<div id="footer">'.(($this->footer=='')?$this->footer():$this->footer).'</div>';

        if($this->seo){
            echo '<div id="body-wrapper">';
                echo '<div id="body">';
                $this->HTMLDump();
                echo '</div>';
                echo $header;
                echo '<div id="footer-spacing"></div>';
            echo '</div>';
            echo $footer;
        }//if
        else {
            echo '<div id="body-wrapper">';
                echo $header;
                echo '<div id="body">';
                $this->HTMLDump();
                echo '</div>';
                echo '<div id="footer-spacing"></div>';
            echo '</div>';
            echo $footer;
        }//el

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
     *      When we create full path links to resources and the browser is using SSL/HTTPS
     *      we need to make sure we request that resource as such in order to avoid mixed content errors
     *
     *
     *      @param string $p the path of the resource
     *      @h string $h the host of the resource ( if not local)
    */
    public function url($p,$h=null){
        if(!empty($_SERVER['HTTPS']) && $h==null && substr($p,0,1)=='/'){
            $p = 'https://'.$_SERVER['URL'].$p;                                                                             
        }//if                                                                                                               
        else if($h!=null && substr($h,0,3)!='http'){                                                                        
            $p = 'http://'.$h.$p;                                                                                           
        }//elif   
        return $p;
    }//url



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
            $s['src'] = $this->url($s['src']);
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
            $s['src'] = $this->url($s['src']);
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

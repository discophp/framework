<?php
ini_set('display_errors',1);
error_reporting(E_ALL);


class TemplateControl {
    //hold a reference to our singleton Control object
    public $ctrl;

    //hold html bits
    public $html = Array();

    //hold script bits
    public $scripts = Array();

    //hold script URLs 
    public $scriptSrcs = Array();

    //hold style bits
    public $styles = Array();

    //hold style URLs 
    public $styleSrcs = Array();

    //hold a class to apply to the body element
    public $bodyStyles = Array();

    //page title
    public $title;

    //page description
    public $description;

    //set this to the path of your working project
    public $path = 'http://phpBoilerPlate.localhost/';

    //name of your default stylesheet
    public $defaultStyleSheet = 'css';

    //name of your default javascript file
    public $defaultScript = 'js';



    public function __construct($ctrl){
        //set the reference to the singleton Control object
        $this->ctrl = $ctrl;

        $this->title = "Default Page Title";
        $this->description = "'Default Page Description'";


        //foundation dependencies
        $this->pushStyleSrc('foundation.min');
        $this->pushScriptSrc('foundation.min');
        $this->pushScriptSrc('modernizr');
        //initilization call for foundation
        $this->pushScript('$(document).foundation();');

        //Default style and js
        $this->pushScriptSrc($this->defaultScript);
        $this->pushStyleSrc($this->defaultStyleSheet);


        //provide the same end point url as a javascript variable for use
        //      provides consistency 
        $this->pushScript('var endPoint="'.$this->path.'";');


    }//construct



    public function setTitle($t){
        $this->title=$t;
    }//setTitle



    public function setDesc($d){
        $this->description=$d;
    }//setDesc



    public function  pushHTML($p){
        $this->html[]=$p;
    }//pushHTML



    public function pushScript($s){
        $this->scripts[]=$s;
    }//pushScript



    public function pushScriptSrc($s){
        $this->scriptSrcs[]=$s;
    }//pushScriptSrc



    public function pushStyle($s){
        $this->styles[]=$s;
    }//style



    public function pushStyleSrc($s){
        $this->styleSrcs[]=$s;
    }//pushStyleSrc



    public function pushBodyStyle($s){
        $this->bodyStyles[] = $s;
    }//pushBodyStyle



    private function printScripts(){
        return '<script type="text/javascript">'.implode('',$this->scripts).'</script>';
    }//printScripts



    private function printScriptSrcs(){
        $scripts='';
        foreach($this->scriptSrcs as $s){
           $scripts.="<script type='text/javascript' src='{$this->path}scripts/{$s}.js'></script>"; 
        }//foreach
        return $scripts;
    }//printScriptSrcs



    private function printStyles(){
        if(count($this->styles)==0){
            return '';
        }//if
        return '<style>'.implode('',$this->styles).'</style>';
    }//printStyles



    private function printStyleSrcs(){
        $styles = '';
        foreach($this->styleSrcs as $s){
            $styles.= "<link rel='stylesheet' href='{$this->path}css/{$s}.css' type='text/css'/>";
        }//foreach
        return $styles;
    }//printStyleSrcs



    private function printBodyStyles(){
        return implode(' ',$this->bodyStyles);
    }//addBodyStyles



    //this function handles printing the entire page
    public function printPage(){

        echo $this->buildMetaHeader();

        echo '<div id="bottom-page" class="row">';

            $this->HTMLDump();

        echo '</div>';//close #bottom-page

        //print the navigation
        echo $this->nav();

        $this->printFooter();

    }//printPage



    public function printFooter(){
        echo "
            {$this->printScriptSrcs()}
            {$this->printScripts()}
            </body>
         </html>";
    }//printFooter



    public function HTMLDump(){
        foreach($this->html as $p){
            echo $p;
        }//foreach
    }//HTMLDump



    public function nav(){
        
        return " 
            <div id='top-page' class='row'>
                <img src='{$this->path}images/design/logo.jpg'/>
                <h1>PHP Foundation Boiler Plate</h1>
                <div class='clear'></div>
            </div>
            ";
    }//nav


    public function buildMetaHeader(){

        $metaHeader = <<<HEAD
<!doctype html>
    <html class="no-js" lang="en">
        
    <head>
        <meta charset="utf-8" />
        
        
        <meta content="index,follow" name="robots">
        
        <title> %1\$s </title>
        <meta name="description" content=%2\$s>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link type="image/x-icon" href="{$this->path}favicon.png" rel="shortcut icon">
        
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js" type="text/javascript"></script>

        
        
        <!-- styles for phones -->
        <link href="{$this->path}css/phone.css" rel="stylesheet" media="screen and (min-width:200px) and (max-width:800px)"/>

        <!-- styles for ipads in landscape mode -->
        <link href="{$this->path}css/styles.css" rel="stylesheet" media="screen and (min-width:1024px) and (max-width:1024px)"/>

        <!--[if IE]>
        <script type="text/javascript">var isIE=true;</script>
        <![endif]-->
                    
        %3\$s
        %4\$s


        </head>
    <body class="row %5\$s">

HEAD;

        return sprintf($metaHeader,
            $this->title,
            $this->description,
            $this->printStyles(),
            $this->printStyleSrcs(),
            $this->printBodyStyles()
        );

    }//buildMetaHeader


}//templateControl


?>

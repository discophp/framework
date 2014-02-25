<?php
ini_set('display_errors',1);
error_reporting(E_ALL);


class View {
    //hold a reference to our singleton Control object
    public static $disco;
    //hold html bits
    public static $html = Array();
    //hold script bits
    public static $scripts = Array();
    //hold script URLs 
    public static $scriptSrcs = Array();
    public static $styles = Array();
    //hold style URLs 
    public static $styleSrcs = Array();
    //hold a class to apply to the body element
    public static $bodyStyles = Array();
    //page title
    public static $title;
    //page description
    public static $description;
    //set this to the path of your working project
    public static $path = 'http://disco.localhost/';
    //name of your default stylesheet
    public static $defaultStyleSheet = 'css';
    //name of your default javascript file
    public static $defaultScript = 'js';
    public static $header='';


    public static function prepare($disco){
        //set the reference to the singleton Control object
        View::disco= $disco;

        View::title = "Default Page Title";
        View::description = "'Default Page Description'";


        //foundation dependencies
        View::pushStyleSrc('foundation.min');
        View::pushScriptSrc('foundation.min');
        View::pushScriptSrc('modernizr');
        //initilization call for foundation
        View::pushScript('$(document).foundation();');

        //Default style and js
        View::pushScriptSrc(View::defaultScript);
        View::pushStyleSrc(View::defaultStyleSheet);


        //provide the same end point url as a javascript variable for use
        //      provides consistency 
        View::pushScript('var endPoint="'.View::path.'";');


    }//construct


    public static function header(){
        return View::header;;
    }//nav

    public static function setHeader($html){
        View::header = $html;
    }//setHeader


    public static function metaHeader(){

        $metaHeader = <<<HEAD
<!doctype html>
    <html class="no-js" lang="en">
        
    <head>
        <meta charset="utf-8" />
        
        
        <meta content="index,follow" name="robots">
        
        <title> %1\$s </title>
        <meta name="description" content=%2\$s>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link type="image/x-icon" href="{View::path}favicon.png" rel="shortcut icon">
        
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js" type="text/javascript"></script>

        
        <!--[if IE]>
        <script type="text/javascript">var isIE=true;</script>
        <![endif]-->
                    
        %3\$s
        %4\$s


        </head>
    <body class="row %5\$s">

HEAD;

        return sprintf($metaHeader,
            View::title,
            View::description,
            View::printStyles(),
            View::printStyleSrcs(),
            View::printBodyStyles()
        );

    }//buildMetaHeader




    public static function setTitle($t){
        View::title=$t;
    }//setTitle



    public static function setDesc($d){
        View::description=$d;
    }//setDesc



    public static function  pushHTML($p){
        View::html[]=$p;
    }//pushHTML



    public static function pushScript($s){
        View::scripts[]=$s;
    }//pushScript



    public static function pushScriptSrc($s){
        View::scriptSrcs[]=$s;
    }//pushScriptSrc



    public static function pushStyle($s){
        View::styles[]=$s;
    }//style



    public static function pushStyleSrc($s){
        View::styleSrcs[]=$s;
    }//pushStyleSrc



    public static function pushBodyStyle($s){
        View::bodyStyles[] = $s;
    }//pushBodyStyle



    private static function printScripts(){
        return '<script type="text/javascript">'.implode('',View::scripts).'</script>';
    }//printScripts



    private static function printScriptSrcs(){
        $scripts='';
        foreach(View::scriptSrcs as $s){
           $scripts.="<script type='text/javascript' src='{View::path}scripts/{$s}.js'></script>"; 
        }//foreach
        return $scripts;
    }//printScriptSrcs



    private static function printStyles(){
        if(count(View::styles)==0){
            return '';
        }//if
        return '<style>'.implode('',View::styles).'</style>';
    }//printStyles



    private static function printStyleSrcs(){
        $styles = '';
        foreach(View::styleSrcs as $s){
            $styles.= "<link rel='stylesheet' href='{View::path}css/{$s}.css' type='text/css'/>";
        }//foreach
        return $styles;
    }//printStyleSrcs



    private static function printBodyStyles(){
        return implode(' ',View::bodyStyles);
    }//addBodyStyles



    //this function handles printing the entire page
    public static function printPage(){

        echo View::metaHeader();

        echo '<div id="bottom-page" class="row">';

            View::HTMLDump();

        echo '</div>';//close #bottom-page

        //print the navigation
        echo "
            <div id='top-page' class='row'>
            ".((View::header=='')?View::header():View::header)."
            </div>";

        View::printFooter();

    }//printPage



    public static function printAjaxPage(){
        echo View::HTMLDump();
        echo View::printScripts();
    }//printAjaxPage



    public static function printFooter(){
        echo "
            {View::printScriptSrcs()}
            {View::printScripts()}
            </body>
         </html>";
    }//printFooter



    public static function HTMLDump(){
        foreach(View::html as $p){
            echo $p;
        }//foreach
    }//HTMLDump





}//view


$view = new View($disco);

?>

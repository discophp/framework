<?php
ini_set('display_errors',1);
error_reporting(E_ALL);


class BaseView {

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
    public $path;

    //name of your default stylesheet
    public $styleSheet = 'css';

    //name of your default javascript file
    public $script = 'js';

    public $header='';

    public $isAjax=false;

    public function __destruct(){
        //View::prepare();
        //View::printPage();
    }//destruct

    private $views = Array();


    public function __construct(){

        if(isset($_SERVER['URL'])){
            $this->path=$_SERVER['URL'];
        }//if

        $this->title = "Default Page Title";
        $this->description = "'Default Page Description'";


        //provide the same end point url as a javascript variable for use
        //      provides consistency 
        $this->pushScript('var endPoint="'.$this->path.'";');


    }//construct


    public function header(){
        return $this->header;;
    }//nav

    public function setHeader($html){
        $this->header = $html;
    }//setHeader


    public function metaHeader(){

        $metaHeader = <<<HEAD
<!doctype html>
    <html class="no-js" lang="en">
        
    <head>
        <meta charset="utf-8" />
        
        
        <meta content="index,follow" name="robots">
        
        <title> %1\$s </title>
        <meta name="description" content=%2\$s>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link type="image/x-icon" href="/favicon.png" rel="shortcut icon">
        
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
            $this->title,
            $this->description,
            $this->printStyles(),
            $this->printStyleSrcs(),
            $this->printBodyStyles()
        );

    }//buildMetaHeader


    public function setTitle($t){
        $this->title=$t;
    }//setTitle

    public function title($t){
        $this->title=$t;
    }//setTitle

    public function setDesc($d){
        $this->description=$d;
    }//setDesc

    public function html($h){
        $this->html[]=$h;
    }//html

    public function pushHTML($p){
        $this->html[]=$p;
    }//pushHTML


    public function pushScript($s){
        if(is_array($s))
            array_merge($this->scripts,$s);
        else
            $this->scripts[]=$s;
    }//pushScript

    public function script($s){
        if(is_array($s))
            array_merge($this->scripts,$s);
        else
            $this->scripts[]=$s;
    }//pushScript


    public function pushScriptSrc($s){
        if(is_array($s))
            array_merge($this->scriptSrcs,$s);
        else
            $this->scriptSrcs[]=$s;
    }//pushScriptSrc

    public function scriptSrc($s){
        if(is_array($s))
            array_merge($this->scriptSrcs,$s);
        else
            $this->scriptSrcs[]=$s;
    }//pushScriptSrc


    public function pushStyle($s){
        $this->styles[]=$s;
    }//style

    public function style($s){
        $this->styles[]=$s;
    }//style


    public function pushStyleSrc($s){
        if(is_array($s))
            array_merge($this->styleSrcs,$s);
        else 
            $this->styleSrcs[]=$s;
    }//pushStyleSrc

    public function styleSrc($s){
        if(is_array($s))
            array_merge($this->styleSrcs,$s);
        else 
            $this->styleSrcs[]=$s;
    }//pushStyleSrc


    public function bodyStyle($s){
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

        //Default style and js
        $this->scriptSrc($this->script);
        $this->styleSrc($this->styleSheet);


        if($this->isAjax){
            $this->printAjaxPage();
            return;
        }//isAjax

        echo $this->metaHeader();

        echo '<div id="bottom-page" class="row">';

            $this->HTMLDump();

        echo '</div>';//close #bottom-page

        //print the navigation
        echo "
            <div id='top-page' class='row'>
            ".(($this->header=='')?$this->header():$this->header)."
            </div>";

        $this->printFooter();

    }//printPage


    public function printAjaxPage(){
        echo $this->HTMLDump();
        echo $this->printScripts();
    }//printAjaxPage


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


}//view

//Class View {
//
//    public static function title($t){
//        Disco::view()->setTitle($t);
//    }//setTitle
//
//    public static function html($h){
//        Disco::view()->html($h);
//    }//html
//
//    public static function script($s){
//        Disco::view()->script($s);
//    }//script
//
//    public static function scriptSrc($s){
//        Disco::view()->scriptSrc($s);
//    }//scriptSrc
//
//    public static function bodyStyle($s){
//        Disco::view()->bodyStyle($s);
//    }//bodyStyle
//
//    public static function printPage(){
//        Disco::view()->printPage();
//    }//printPage
//
//    public static function printAjaxPage(){
//        Disco::view()->printAjaxPage();
//    }//printPage
//
//}//View


?>

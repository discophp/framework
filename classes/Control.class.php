<?php
define('DOCUMENT_ROOT',dirname(realpath(__FILE__)).'/');

require_once('Database.class.php');
require_once('Utilities.class.php');
require_once('TemplateControl.class.php');

class Control {
    public $db;
    public $tc;
    public $user;
    public $util;
    public $settings = Array();
    public $loggedIn=false;

    // protect a directories access past index.php with a session name
    // ('directoryName','sessionName')
    public $pageSecurity = Array('admin'=>'admin');


    public function __construct($settings=null){
        $this->settings = $settings;

        //$this->db = new Database;
        $this->util = new Utilities($this);

        $this->setUp();

        //protect specific areas of the site
        $this->securityGate();


        //depending on the type of SESSION use a different template
        //the templates that aren't default simply extend the default template
        if(isset($_SESSION['admin'])){
            require_once('AdminTemplateControl.class.php');
            $this->tc = new AdminTemplateControl($this);
        }//if
        else{
            $this->tc = new TemplateControl($this);
        }//el


    }//construct



    //take care of things that must happen / be processed on any page
    private function setUp(){

        session_start();
        if(isset($_GET['logout'])){
            session_destroy();
            header("Location: {$_GET['ref']}");
        }//if

    }//setUp



    //protect a directory with a session requirement
    private function securityGate(){

        foreach($this->pageSecurity as $dir => $session){
            //if trying to access somewhere within the $dir directory and there is no session
            //send them to the login page
            if(preg_match("/\/{$dir}\//",$_SERVER['REQUEST_URI'])){
                $urlPath = parse_url($_SERVER['REQUEST_URI'])['path'];
                if(($urlPath!="/{$dir}/" && $urlPath!="/{$dir}/index.php") && !isset($_SESSION[$session])){
                    header("Location:/{$dir}/");
                }//if
            }//if
        }//foreach

    }//securityGate


}//Control

?>

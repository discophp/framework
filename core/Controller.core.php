<?php

class Controller {
    public $models=Array();
    //public $facades=Array();
    public $user;
    public $loggedIn=false;

    // protect a directories access past index.php with a session name
    // ('directoryName','sessionName')
    public $pageSecurity = Array('admin'=>'admin');

    //take care of things that must happen / be processed on any page
    public function setUp(){

        session_start();
        if(isset($_GET['logout'])){
            session_destroy();
            header("Location: {$_GET['ref']}");
        }//if

        $this->securityGate();

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


//$disco = new Controller();


?>

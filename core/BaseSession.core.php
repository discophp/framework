<?php

class BaseSession {

    /**
     *      Start up our session
     *      and do some security checks
    */
    public function __construct(){
        session_start();

        if(!isset($_SESSION['IP_REFER_CHECK']))
            $_SESSION['IP_REFER_CHECK']=$_SERVER['REMOTE_ADDR'];
        else if($_SESSION['IP_REFER_CHECK']!=$_SERVER['REMOTE_ADDR']){
            TRIGGER_ERROR(
                "POTENTIAL SESSION HIJACKING:\nOrg: {$_SESSION['IP_REFER_CHECK']}\nAttack:{$_SERVER['REMOTE_ADDR']}",
                E_USER_WARNING
            ); 
            Util::death();
        }//elif
    }//construct


    /**
     *      does a session variable exist
     *
     *      @param mixed $k
     *      @return boolean
    */
    public function has($k){
        if(isset($_SESSION[$k]))
            return true;
        else 
            return false;
    }//has


    /**
     *      set a session variable 
     *
     *      @param mixed $k
     *      @param mixed $v
    */
    public function set($k,$v){
        $_SESSION[$k]=$v;
    }//set


    /**
     *      get a session variable 
     *
     *      @return mixed
    */
    public function get($k){
        return $_SESSION[$k];
    }//get


    /**
     *      delete a session variable
     *
     *      @param mixed $k
    */
    public function remove($k){
        unset($_SESSION[$k]);
    }//remove


    /**
     *      regenerate a session id
     *      and keep the data
    */
    public function regen(){
        session_regenerate_id();
    }//regen


    /**
     *      reset a session
    */
    public function reset(){
        session_regenerate_id(true);
    }//reset


    /**
     *      Clear all session variables
    */
    public function flush(){
        session_unset();
    }//flush


    /**
     *      Destroy A session
     */
    public function destroy(){
        session_destroy();
    }//destroy


}//BaseSession

?>

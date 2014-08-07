<?php
namespace Disco\classes;
/**
 * This file holds the Session class.
*/


/**
 * Convient wrapper around using $_SESSION variables.
*/
class Session {


    /**
     * Start up our session by calling session_start() .
     *
     *
     * @return void
    */
    public function __construct(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }//if
    }//construct



    /**
     * Does a session variable exist?
     *
     *
     * @param string $k The session to check.
     *
     * @return boolean
    */
    public function has($k){
        if(isset($_SESSION[$k]))
            return true;
        else 
            return false;
    }//has



    /**
     * Set a SESSION variable.
     *
     *
     * @param string $k The key to set the SESSION with.
     * @param mixed $v The value of the $k.
     *
     * @return void
    */
    public function set($k,$v){
        $_SESSION[$k]=$v;
    }//set



    /**
     * Get a SESSION variable.
     *
     *
     * @param string $k The SESSION to get.
     *
     * @return mixed
    */
    public function get($k){
        return $_SESSION[$k];
    }//get



    /**
     * Delete a SESSION variable.
     *
     *
     * @param string $k The SESSION to remove
     *
     * @return void
    */
    public function remove($k){
        unset($_SESSION[$k]);
    }//remove



    /**
     * Regenerate a SESSION id and keep the data.
     *
     *
     * @return void
    */
    public function regen(){
        session_regenerate_id();
    }//regen



    /**
     * Reset a SESSION.
     *
     *
     * @return void
    */
    public function reset(){
        session_regenerate_id(true);
    }//reset



    /**
     * Clear all SESSION variables.
     *
     *
     * @return void
    */
    public function flush(){
        session_unset();
    }//flush



    /**
     * Destroy A SESSION.
     *
     * 
     * @return void
     */
    public function destroy(){
        session_destroy();
    }//destroy

}//Session
?>

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
        session_start();
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


    public function in($k){
        if(is_array($k) && count(array_intersect(array_keys($_SESSION),$k))>0) return true;
        if(in_array($k,array_keys($_SESSION))) return true;
        return false;
    }//in

    public function total(){
        return count($_SESSION);
    }//total


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

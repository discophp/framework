<?php
namespace Disco\classes;
/**
 * This file holds the Session class.
*/


/**
 * Convient wrapper around using $_SESSION variables.
*/
class Session {

    const FLASH_KEY = 'disco_flash';

    /**
     * @var array $newFlash Flash data set during current request.
    */
    private $newFlash = Array();


    /**
     * @var array $flash Flash data from previous request.
    */
    private $flash = Array();



    /**
     * Start up our session by calling `session_start()` and set the flash data if necessary.
     *
     *
     * @return void
    */
    public function __construct(){

        session_start();

        if($this->has(self::FLASH_KEY)){
            $this->flash = json_decode($this->get(self::FLASH_KEY));
        }//if

    }//__construct



    /**
     * Set the current flash if necessary.
    */
    public function __destruct(){

        if(count($this->newFlash)){
            $this->set(self::FLASH_KEY, json_encode($this->newFlash));
        } else if(count($this->flash)){
            $this->remove(self::FLASH_KEY);            
        }//elif

    }//__destruct



    /**
     * Does a session variable exist?
     *
     *
     * @param string $k The session to check.
     *
     * @return boolean
    */
    public function has($k){
        return isset($_SESSION[$k]);
    }//has



    /**
     * Does the session have a value for the key(s)?
     *
     *
     * @param string|array $k The key(s) to check for in the session.
     *
     * @return boolean Whether the key is in the session.
    */
    public function in($k){

        if(is_array($k) && count(array_intersect(array_keys($_SESSION),$k))>0){
            return true;
        }//if

        return isset($_SESSION[$k]);

    }//in



    /**
     * The total number of keys in the session.
     *
     *
     * @return int
    */
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
     * @return false|mixed
    */
    public function get($k){

        if(!isset($_SESSION[$k])){
            return false;
        }//if

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



    /**
     * Set a piece of flash data.
     *
     * 
     * @param string $key The flash data key.
     * @param mixed $value The flash data value.
    */
    public function setFlash($key, $value){
        $this->newFlash[$key] = $value;
    }//flash



    /**
     * Get a piece of flash data.
     *
     *
     * @param string $key The flash data key.
     * @return false|mixed False if not set.
    */
    public function getFlash($key){

        if(!isset($this->flash[$key])){
            return false;
        }//if

        return $this->flash[$key];

    }//getFlash



    /**
     * Get the current flash data.
     *
     * @return array
    */
    public function flash(){
        return $this->flash;
    }//flash


}//Session
?>

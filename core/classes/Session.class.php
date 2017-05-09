<?php
namespace Disco\classes;
/**
 * This file holds the Session class.
*/


/**
 * Convenient wrapper around using $_SESSION variables.
*/
class Session {


    /**
     * @var string FLASH_KEY The key used to store flash data in the session.
    */
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
    */
    public function __construct(){

        session_start();

        if($this->has(self::FLASH_KEY)){
            $this->flash = json_decode($this->get(self::FLASH_KEY),true);
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
     * @return int The total number of session variables.
    */
    public function total(){
        return count($_SESSION);
    }//total



    /**
     * Get all keys being used to store session variables.
     *
     *
     * @return array The keys.
    */
    public function keys(){
        return array_keys($_SESSION);
    }//keys



    /**
     * Set a SESSION variable.
     *
     *
     * @param string $k The key to set the SESSION variable with.
     * @param numeric|string $v The value of the $k.
     *
     * @return void
    */
    public function set($k,$v){
        $_SESSION[$k]=$v;
    }//set



    /**
     * Set a complex SESSION variable, performing serialize on the value passed, allowing for arrays, objects, and 
     * any complex data type to be stored in the session.
     *
     *
     * @param string $k The key to set the SESSION variable with.
     * @param mixed $v The complex data type to store.
     *
     * @return void
    */
    public function setComplex($k,$v){
        $_SESSION[$k] = serialize($v);
    }//setComplex



    /**
     * Get a SESSION variable.
     *
     *
     * @param string $k The SESSION variable to get.
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
     * Get a complex SESSION variable, deserializing it before it is returned.
     *
     *
     * @param string $k The complex SESSION variable to get.
     *
     * @return mixed
    */
    public function getComplex($k){

        if(!isset($_SESSION[$k])){
            return false;
        }//if

        return unserialize($_SESSION[$k]);

    }//getComplex




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
     * Whether a flash session variable is set.
     *
     *
     * @param string $key The flash data key.
     *
     * @return boolean
    */
    public function hasFlash($key){
        return isset($this->flash[$key]);
    }//hasFlash



    /**
     * Get the current flash data.
     *
     * @return array
    */
    public function flash(){
        return $this->flash;
    }//flash



    /**
     * Set a piece of complex flash data.
     *
     * 
     * @param string $key The flash data key.
     * @param mixed $value The flash data value.
    */
    public function setComplexFlash($key, $value){
        $this->newFlash[$key] = serialize($value);
    }//setComplexFlash



    /**
     * Get a piece of flash data.
     *
     *
     * @param string $key The flash data key.
     * @return false|mixed False if not set.
    */
    public function getComplexFlash($key){

        if(!isset($this->flash[$key])){
            return false;
        }//if

        return unserialize($this->flash[$key]);

    }//getComplexFlash



}//Session
?>

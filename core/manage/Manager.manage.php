<?php
namespace Disco\manage;
/**
 * This file holds the Class Manager.
*/


/**
 *  The Manager Class helps us manage our Disco Application. It primarly helps with getting and setting
 *  data in your configuration files http://github.com/discophp/project/blob/master/.config.php .
 *  It also helps you see what jobs you have waiting in your Queue and the ability to kill them. You can also do 
 *  fun stuff with it like calling methods of your Applications objects via the command line to inspect their 
 *  results.
*/
class Manager {


    /**
     * This function is very important to the functioning of the Queue class.
     * It handles executing jobs that are pushed onto the Queue.
     *
     *
     * @param integer $delay The amount of time to wait before executing the job.
     * @param string|\Jeremeamia\SuperClosure\SerializableClosure $obj The \Closure to call the job on or the 
     * name of the Facade to use.
     * @param string $method The method name to call.
     * @param string $vars The serialized form of the variables.
     * @param string $d A serialized representation of our Application the moment the Job was pushed onto the 
     * Queue.
     *
     * @return void
    */
    public static function resolve($delay,$obj,$method,$vars,$d){

        $d = unserialize(base64_decode($d));
        $obj = unserialize(base64_decode($obj));

        if($vars=='disco-no-variable'){
            $vars=null;
        }//if
        else {
            $vars = unserialize(base64_decode($vars));
            if(!is_array($vars)){
                if(is_string($vars)){
                    $vars = html_entity_decode($vars);
                }//if
                $vars = Array($vars);
            }//if
            else {
                foreach($vars as $k=>$v){
                    if(is_string($v)){
                        $vars[$k] = html_entity_decode($v);
                    }//if
                }//foreach
            }//el
        }//el

        \Disco::$facades = array_merge(\Disco::$facades,$d);

        if($obj instanceof \Jeremeamia\SuperClosure\SerializableClosure){
            if($delay!=0){
                sleep($delay);
            }//if
            return call_user_func($obj,$vars);
        }//if

        if(\Disco::$facades[$obj] instanceof \Closure){
            $obj = call_user_func(\Disco::$facades[$obj]);
        }//if
        else {
            $obj = \Disco::$facades[$obj];
        }//el

        if($delay!=0){
            sleep($delay);
        }//if

        \Disco::handle($obj,$method,$vars);

    }//resolve



    /**
     * List the jobs that are currently queued on the system.
     *
     *
     * @return void
    */
    public static function jobs(){

        $j = \Queue::jobs();

        foreach($j as $job){
            echo sprintf("
                ----------- JOB # %1\$s --------------
                Time entered: %2\$s
                Delay: %3\$s
                Object: %4\$s
                Method: %5\$s
                Vars: %6\$s

                Kill this job by running: php disco kill-job %1\$s
            ".PHP_EOL.PHP_EOL,$job->pId,$job->time,$job->delay,$job->object,$job->method,$job->vars);

        }//foreach

    }//jobs



    /**
     * Set or Get the APP_MODE in .config.php .
     *
     *
     * @param string|null $mode The mode to put the app into;either DEV|PROD
     *
     * @return void
    */
    public static function appMode($mode=null){

        if($mode!=null){
            self::setConfig('APP_MODE',$mode);
        }//if
        else {
            return self::getConfig('APP_MODE');
        }//el

    }//appMode



    /**
     * Set or Get the MAINTANANCE_MODE in .config.php .
     *
     *
     * @param string|null $mode Should the app be in maintanance mode? 
     *
     * @return void
    */
    public static function maintananceMode($mode=null){

        if($mode!=null){
            self::setConfig('MAINTANANCE_MODE',$mode);
        }//if
        else {
            return self::getConfig('MAINTANANCE_MODE');
        }//el

    }//appMode



    /**
     * Set a variable in .config.php .
     *
     * @param string $find  The variable to change.
     * @param string $value The value to change the variable to.
     *
     * @return void
    */
    public static function setConfig($find,$value){

        $find.='\'=>\'';
        $f = file_get_contents('./.config.php');
        $i = stripos($f,$find)+strlen($find);
        $ni = stripos($f,'\'',$i);

        $f = substr_replace($f,$value,$i,$ni-$i);
        file_put_contents('./.config.php',$f);
    }//setConfig



    /**
     * Get a variable in .config.php .
     *
     *
     * @param string $find  The variable to find.
     *
     * @return string The value of $find.
    */
    public static function getConfig($find){
        $find.='\'=>\'';
        $f = file_get_contents('./.config.php');
        $i = stripos($f,$find)+strlen($find);
        $ni = stripos($f,'\'',$i);

        return substr($f,$i,$ni-$i);
    }//setConfig


    /**
     * Generate a random number between passed $length.
     *
     *
     * @param integer $length The length of the random string.
     *
     * @return string The random $length string.
    */
    public static function genRand($length){

        $max = ceil($length/32);
        $rand = '';
        for($i=0;$i<$max;$i++){
            $rand.=md5(microtime(true).mt_rand(10000,90000));
        }//for
        $rand = substr($rand,0,$length);

        return $rand;

    }//genRand


    /**
     *  Generate a 32 length AES256 Key
     *
     *
     *  @return string 
    */
    public static function genAES256Key(){
        return self::genRand(32);
    }//genAES256Key



    /**
     *  Set the AES256 Key in .config.php .
     *
     *
     *  @param string $k The key.
     *
     *  @return void 
    */
    public static function setAES256Key($k){
        self::setConfig('AES_KEY256',$k);
    }//setAES256KEY



    /**
     *  Generate a random string of length $l .
     *
     *
     *  @param integer $l The length of the string to generate.
     *
     *  @return string 
    */
    public static function genSalt($l){
        return self::genRand($l);
    }//genAES256Key


    /**
     *  Set the SHA512_SALT_LEAD Key in .config.php .
     *
     *
     *  @param string $s The salt.
     *
     *  @return void 
    */
    public static function setSaltLead($s){
        self::setConfig('SHA512_SALT_LEAD',$s);
    }//genAES256Key


    /**
     *  Set the SHA512_SALT_TAIL Key in .config.php .
     *
     *
     *  @param string $s The salt.
     *
     *  @return void 
    */
    public static function setSaltTail($s){
        self::setConfig('SHA512_SALT_TAIL',$s);
    }//genAES256Key



    /**
     * This function generates and sets the AES256 and SHA512 keys for .config.php after the composer install.
     *
     *
     * @return void
    */
    public static function install(){

        echo 'Setting AES_KEY256...';
        self::setAES256Key(self::genAES256Key());
        echo ' done.'.PHP_EOL;

        echo 'Setting SHA512_SALT_LEAD with key size 12 ...';
        self::setSaltLead(self::genSalt(12));
        echo ' done.'.PHP_EOL;

        echo 'Setting SHA512_SALT_TAIL with key size 12 ...';
        self::setSaltTail(self::genSalt(12));
        echo ' done.'.PHP_EOL;


    }//install

}//Manager
?>

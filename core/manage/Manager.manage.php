<?php

namespace Disco\manage;

class Manager {


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


    public static function appMode($mode=null){

        if($mode!=null){
            self::setConfig('APP_MODE',$mode);
        }//if
        else {
            return self::getConfig('APP_MODE');
        }//el

    }//appMode


    public static function setConfig($find,$value){
        $find.='\'=>\'';
        $f = file_get_contents('./.config.php');
        $i = stripos($f,$find)+strlen($find);
        $ni = stripos($f,'\'',$i);
        $f = substr_replace($f,$value,$i,$ni-$i);
        file_put_contents('./.config.php',$f);
    }//setConfig


    public static function getConfig($find){
        $find.='\'=>\'';
        $f = file_get_contents('./.config.php');
        $i = stripos($f,$find)+strlen($find);
        $ni = stripos($f,'\'',$i);
        return substr($f,$i,$ni-$i);
    }//setConfig




    public static function genRand($length){

        $max = ceil($length/32);
        $rand = '';
        for($i=0;$i<$max;$i++){
            $rand.=md5(microtime(true).mt_rand(10000,90000));
        }//for
        $rand = substr($rand,0,$length);

        return $rand;

    }//genRand


    public static function genAES256Key(){
        return self::genRand(32);
    }//genAES256Key

    public static function setAES256Key($k){
        self::setConfig('AES_KEY256',$k);
    }//setAES256KEY

    public static function genSalt($l){
        return self::genRand($l);
    }//genAES256Key

    public static function setSaltLead($s){
        self::setConfig('SHA512_SALT_LEAD',$s);
    }//genAES256Key

    public static function setSaltTail($s){
        self::setConfig('SHA512_SALT_TAIL',$s);
    }//genAES256Key



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

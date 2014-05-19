<?php

class Queue extends Disco\classes\Facade {

    protected static function returnFacadeId(){
        return 'Queue';
    }//returnFacadeId

    public static function jobs(){
        exec('ps -ef | grep "php[[:space:]]\.\./disco[[:space:]]resolve"',$jobs);

        $f = 'php ../disco resolve';
        $j = Array();
        foreach($jobs as $k=>$v){
            if(stripos($v,$f)!==false){
                $v = preg_replace('!\s+!', ' ', $v);
                $j[]=$v;
            }//if
        }//foreach

        foreach($j as $k=>$job){
            $job = explode(' ',$job);
            $j[$k] = new \stdClass();
            $j[$k]->pId=$job[1];
            $j[$k]->time=$job[4];
            $j[$k]->delay=$job[10];
            $j[$k]->object=$job[12];
            $j[$k]->method=$job[13];
            $j[$k]->vars=($job[14]=='disco-no-variable')?'':unserialize(base64_decode($job[14]));
        }//foreach

        return $j;
       
    }//jobs


    public static function killJob($pId){

        $j = self::jobs();

        global $argv;

        foreach($j as $job){
            if($job->pId==$pId){
                exec('kill '.$job->pId);
                if($argv[1]=='kill-job'){
                    echo 'Killed Job # '.$job->pId.' Action:'.$job->object.'@'.$job->method.PHP_EOL;
                }//if
                return true;
            }//if
        }//foreach

        if($argv[1]=='kill-job'){
            echo 'No Job # '.$pId.PHP_EOL;
        }//if

        return false;
    }//killJob

}//Queue


?>

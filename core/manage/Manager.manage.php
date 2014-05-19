<?php

namespace Disco\manage;

class Manager {


    public static function resolve($delay,$d,$obj,$method,$vars){

        $d = unserialize(base64_decode($d));
        $obj = unserialize(base64_decode($obj));

        if($vars=='disco-no-variable'){
            $vars=null;
        }//if
        else {
            $vars = unserialize(base64_decode($vars));
            if(!is_array($vars)){
                $vars = Array($vars);
            }//if
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


}//Manager



?>

<?php

namespace Disco\classes;


class Queue {

    public function push($job,$delay=0,$vars=null){

        if($vars==null){
            $vars='disco-no-variable';
        }//if
        else {
            $vars = base64_encode(serialize($vars));
        }//el

        $facades = \Disco::$facades;

        $t = Array();
        foreach($facades as $k=>$v){
            if(!$v instanceof \Closure){
                $t[$k]=$v;
            }//if
        }//foreach

        $d = base64_encode(serialize($t));

        if($job instanceof \Closure){
            $obj = new \Jeremeamia\SuperClosure\SerializableClosure($job);
            $method = 'closure';
            //$obj = new MagicContainer();
            //$obj->addMethod('closure',$job);
            //$method = 'closure';
        }//if
        else if(stripos($job,'@')!==false){
            $obj = explode('@',$job); 
            $method = $obj[1];
            $obj = $obj[0];
        }//elif
        else {
            $obj = $job;
            $method = 'work';
        }//el

        $obj = base64_encode(serialize($obj));

        $s = 'php ../disco resolve '.$delay.' '.$d.' '.$obj.' '.$method.' '.$vars.' > /dev/null 2>/dev/null &';
        //$s = 'php ../disco resolve '.$delay.' '.$d.' '.$obj.' '.$method.' '.$vars.'';

        //echo $s.PHP_EOL;
        exec($s);

    }//push





}//Queue



?>

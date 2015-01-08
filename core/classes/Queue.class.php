<?php
namespace Disco\classes;
/**
 * This file holds the Queue class. 
*/

/**
 * Queue class. 
 * Allow simulation of parallel processing of jobs either immediatly or after a set delay.
 * The classes objective is to allow large jobs that would cause lag or generally take a long time and
 * are expensive in processing to be executed without interupting the applications response.
*/
class Queue {


    /**
     *  Push a job onto the Queue for processing. 
     *
     *
     *  @param \Closure|string      $job    Either a \Closure to execute or a Class method pair like 'DB@query'.
     *  @param int                  $delay  The delay to wait before begining execution of the $job.
     *  @param null|string|array    $vars   The variables to pass to the $job.
     *
     *  @return void
    */
    public function push($job,$delay=0,$vars=null){

        if($vars==null){
            $vars='disco-no-variable';
        }//if
        else {
            $vars = base64_encode(serialize($vars));
        }//el

        //$t = Array();
        //$app = \App::$app;
        //$facades = $app->keys();
        //foreach($facades as $k){
        //    //if($k=='App') continue;
        //    $v = $app[$k];
        //    if($v instanceof \Closure){
        //        $v = new \Jeremeamia\SuperClosure\SerializableClosure($v);
        //    }//if
        //    $t[$k]=$v;
        //}//foreach

        //$d = base64_encode(serialize($t));
        $d = '';

        if($job instanceof \Closure){
            $obj = new \Jeremeamia\SuperClosure\SerializableClosure($job);
            $method = 'closure';
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

        $s = 'php '.\App::$app->path.'/disco resolve '.$delay.' '.$obj.' '.$method.' '.$vars.' '.$d.' > /dev/null 2>/dev/null &';

        exec($s);

    }//push

}//Queue
?>

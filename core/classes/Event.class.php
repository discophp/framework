<?php
namespace Disco\classes;
/**
 * This file hold the class Event. 
*/


/**
 * Event class.
 * Implements a Broadcast/Receiver pattern.
*/
class Event {

    /**
     * @var array Events to listen for.
    */
    private $events=Array();


    /**
     * Register an event name with an associated action.
     *
     *
     * @param string            $event      Name of the event.
     * @param \Closure|string   $action     \Closure or class/method to execute denoted like 'Worker@work'.
     * @param int               $priority   The priority of event.
     *
     * @return void
    */
    public final function listen($event,$action,$priority=0){
        if(!isset($this->events[$event])){
            $this->events[$event]=Array('actions'=>Array());
        }//if

        if($action instanceof \Closure){
            $action = new \Jeremeamia\SuperClosure\SerializableClosure($action);
        }//if

        $this->events[$event]['actions'][$priority]=$action;

    }//listen



    /**
     * Fire off a registered event.
     *
     *
     * @param string    $event  Name of the event to fire.
     * @param mixed     $data   The data to be passed to the \Closure or Class method pair.
     *
     * @return void
    */
    public final function fire($event,$data=null){

        if(count(array_intersect_key(Array($event=>Array()),$this->events))>0){

            $keys = array_keys($this->events[$event]['actions']);

            if(count($keys)>1)
                rsort($keys);

            foreach($keys as $k){
                $action = $this->events[$event]['actions'][$k];
                if($action instanceof \Closure){
                    if($data==null){
                        return call_user_func($action);
                    }//if
                    else { 
                        if(!is_array($data)){
                            $data = Array($data);
                        }//if
                        return call_user_func_array($action,$data);
                    }//el
                }//if
                else {
                    $method='work';
                    if(stripos($action,'@')!==false){
                        $method = explode('@',$action);
                        $action = $method[0];
                        $method = $method[1];
                    }//if
                    $instance = new $action();
                    return \Disco::handle($instance,$method,$data);
                }//el
            }//foreach

        }//if
        else {
            $trace = Array();
            $e = debug_backtrace();
            foreach($e as $err){
                if(isset($err['file']) && isset($err['function']) && $err['function']=='fire'){
                    $trace['args']=$err['args'];
                    $trace['line']=$err['line'];
                    $trace['file']=$err['file'];
                }//if
            }//foreach
            $msg = "Event::Error event \"{$event}\" not found - {$trace['args'][0]} @ line {$trace['line']} in File: {$trace['file']} ";

            TRIGGER_ERROR($msg,E_USER_ERROR);

        }//el

    }//fire

}//Event
?>

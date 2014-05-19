<?php

namespace Disco\classes;

/**
 *      This file hold the class BaseEvent
 *
*/


/**
 *
 *      BaseEvent class.
 *      Implements a Broadcast/Receiver pattern.
 *
*/
class Event {



    /**
     *      Events to listen for
    */
    private $events=Array();


    /**
     *      Register an event name with an associated action
     *
     *
     *      @param string $event name of the event
     *      @param mixed $action Closure or class/method to execute
     *      @param int $priority priority of event 
     *
     *      @return void
    */
    public final function listen($event,$action,$priority=0){
        if(!isset($this->events[$event])){
            $this->events[$event]=Array('actions'=>Array());
        }//if

        $this->events[$event]['actions'][$priority]=$action;

    }//listen



    /**
     *      Fire off a registered event 
     *
     *
     *      @param string $event name of event to fire
     *      @param mixed $data data to be carried to closure or method
     *
     *      @return void
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
        else 
            TRIGGER_ERROR('Event:: Event not found '.$event,E_USER_WARNING);
    }//fire



}//BaseEvent


?>

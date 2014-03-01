<?php

class BaseEvent {

    private $events=Array();

    public final function listen($event,$action,$priority=0){
        if(!isset($this->events[$event])){
            $this->events[$event]=Array('actions'=>Array());
        }//if

        $this->events[$event]['actions'][$priority]=$action;

    }//listen

    public final function fire($event,$data=null){

        if(count(array_intersect_key(Array($event=>Array()),$this->events))>0){

            $keys = array_keys($this->events[$event]['actions']);

            if(count($keys)>1)
                $keys = ksort($keys);

            foreach($keys as $k){
                $action = $this->events[$event]['actions'][$k];
                if($action instanceof Closure){
                    call_user_func($action);
                }//if
                else {
                    $method='work';
                    if(stripos($action,'@')!==false){
                        $method = explode('@',$action);
                        $action = $method[0];
                        $method = $method[1];
                    }//if
                    $instance = new $action();
                    Disco::handle($instance,$method,$data);
                }//el
            }//foreach

        }//if
        else 
            TRIGGER_ERROR('Event:: Event not found '.$event,E_USER_WARNING);
    }//fire

}//BaseEvent


?>

<?php

class BaseEvent {

    private $events=Array();
    private $actions=Array();

    public final function listen($event,$action){
        $events[]=$event;
        $actions[]=$action;
    }//listen

    public final function fire($event,$data){
        $events = array_intersect_key(Array($event),$this->events);
        if(count($events)>0){

            var_dump($events);

            //if($instance instanceof Closure){
            //    $instance=call_user_func($instance);
            //}//if

            //return Disco::handle($instance,$method,$args);

        }//if
    }//fire

}//BaseEvent


?>

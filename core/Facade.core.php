<?php

abstract class Facade {

    public static function __callStatic($method,$args){
        global $disco;

        $instance = $disco->facades[static::returnFacadeId()];

        if($instance instanceof Closure){
            $instance=call_user_func($instance);
            $disco->facades[static::returnFacadeId()]=$instance;
        }//if

        return Disco::handle($instance,$method,$args);

        //switch (count($args)) {
        //    case 0:
        //        return $instance->$method();

        //    case 1:
        //        return $instance->$method($args[0]);

        //    case 2:
        //        return $instance->$method($args[0], $args[1]);

        //    case 3:
        //        return $instance->$method($args[0], $args[1], $args[2]);

        //    case 4:
        //        return $instance->$method($args[0], $args[1], $args[2], $args[3]);

        //    default:
        //        return call_user_func_array(array($instance, $method), $args);
        //}//switch

    }//callStatic


}//BaseInstanceController

?>

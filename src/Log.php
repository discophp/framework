<?php
namespace Disco;

class Log extends \Monolog\Logger {

    /**
     * @param string             $name       The logging channel, defaults to `App`.
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct($name = 'App', array $handlers = array(), array $processors = array()) {

        if(!count($handlers)){
            $handlers[] = new \Monolog\Handler\StreamHandler( app()->path() . app()->configOrDefault('LOG', '/log/app.log'));
        }

        $this->name = $name;
        $this->handlers = $handlers;
        $this->processors = $processors;

    }

}
<?php
namespace Disco\exceptions;

/**
 * Abstract Exception which all Disco Exceptions extend.
*/
abstract class AbstractException extends \Exception 
{


    /**
     * @var mixed Additional data that may be set via `$this->setData()` to provide futher insight about the Exception.
    */
    protected $data = null;


    /**
     * Construct Exception.
     *
     *
     * @param null|string $message The error message.
     * @param int $code The error code.
    */
    public function __construct($message = null, $code = 0) {
        if(!$message){
            throw new $this('Unknown '. get_class($this));
        }//if
        parent::__construct($message, $code);
    }//__construct


   
    /**
     * To String method.
     *
     *
     * @return string
    */
    public function __toString() {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n" . "{$this->getTraceAsString()}";
    }//__toString



    /**
     * Set additional data that may aid in the debugging of the Exception.
     *
     *
     * @param mixed $data The data.
     *
     * @return self Returns self to allow chaining
    */
    public function setData($data){
        $this->data = $data;
        return $this;
    }//setData



    /**
     * Get any additional data that may aid in the debugging of the Exception.
     *
     *
     * @return mixed The data.
    */
    public function getData(){
        return $this->data;
    }//getData



}//AbstractException

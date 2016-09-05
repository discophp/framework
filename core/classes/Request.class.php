<?php
namespace Disco\classes;

/**
 * Information about the current request to the application. 
*/
class Request {


    /**
     * @var string $uri The request URI.
    */
    protected $uri;


    /**
     * @var string $url The request URL.
    */
    protected $url;


    /**
     * @var array $pathParts The parts of the request URI.
    */
    protected $pathParts = Array();


    /**
     * @var string $type The request type/method, eg: `POST,GET,DELETE,PUT`.
    */
    protected $type;


    /**
     * @var string $ip The IP address of the user making the request.
    */
    protected $ip;


    /**
     * @var boolean $secure Whether the request was made over HTTPS.
    */
    protected $secure;



    /**
     * Pull necessary information from $_SERVER and make available on the class.
    */
    public function __construct(){

        $this->uri          = $_SERVER['REQUEST_URI'];
        $this->url          = parse_url($this->uri); 
        $this->pathParts    = explode('/',$this->url['path']);
        $this->type         = $_SERVER['REQUEST_METHOD'];
        $this->ip           = $_SERVER['REMOTE_ADDR'];
        $this->secure       = !empty($_SERVER['HTTPS']);

    }//__construct


    /**
     * Get the request URI.
     *
     * @return string
    */
    public function uri(){
        return $this->uri;
    }//uri



    /**
     * Get the request type.
     *
     * @return string
    */
    public function type(){
        return $this->type;
    }//type



    /**
     * Get the request IP address.
     *
     * @return string
    */
    public function ip(){
        return $this->ip;
    }//ip



    /**
     * Whether the request was made via HTTPS.
     *
     * @return boolean
    */
    public function secure(){
        return $this->secure;
    }//secure



    /**
     * Get the url schema of the request.
     *
     * @return string
    */
    public function scheme(){
        return $this->url['scheme'];
    }//scheme



    /**
     * Get the url host of the request.
     *
     * @return string
    */
    public function host(){
        return $this->url['host'];
    }//host



    /**
     * Get the port of the request.
     *
     * @return string
    */
    public function port(){
        return $this->url['port'];
    }//port


    /**
     * Get the user of the request.
     *
     * @return string
    */
    public function user(){
        return $this->url['user'];
    }//user



    /** 
     * Get the password of the request.
     *
     * @return string
    */
    public function pass(){
        return $this->url['pass'];
    }//pass



    /** 
     * Get the path of the request. The string after the domain backslash and before any get variables.
     *
     * @return string
    */
    public function path(){
        return $this->url['path'];
    }//path



    /** 
     * Get a part of the path by index.
     *
     * @param int $i The index of the path part.
     *
     * @return string
    */
    public function pathPart($i){
        if(isset($this->pathParts[$i])){
            return $this->pathParts[$i];
        }//if
        return null;
    }//pathPart



    /**
     * Get the depth of the path.
     *
     * @return int
    */
    public function pathDepth(){
        return count($this->pathParts);
    }//pathDepth



    /**
     * Get the query of the path.
     *
     * @return string
    */
    public function query(){
        return $this->url['query'];
    }//query



    /**
     * Get the framgment of the path.
     *
     * @return string
    */
    public function fragment(){
        return $this->url['fragment'];
    }//fragment



    /**
     * Get a GET variable in the request.
     *
     * @param string $k The key.
     *
     * @return string
    */
    public function get($k = null){
        return \Data::get($k);
    }//get



    /**
     * Get a POST variable in the request.
     *
     * @param string $k The key.
     *
     * @return string
    */
    public function post($k = null){
        return \Data::post($k);
    }//post



    /**
     * Get a PUT variable in the request.
     *
     * @param string $k The key.
     *
     * @return string
    */
    public function put($k = null){
        return \Data::put($k);
    }//put



    /**
     * Get a DELETE variable in the request.
     *
     * @param string $k The key.
     *
     * @return string
    */
    public function delete($k = null){
        return \Data::delete($k);
    }//delete



    /**
     * Get an instance of the \Disco\classes\Data service.
     *
     * @return \Disco\classes\Data
    */
    public function data(){
        return \Data::instance();
    }//data



    /**
     * Get the `$_SERVER` superglobal. Comes in handy when you want access to the server superglobal via Twig 
     * templates.
     *
     * @return array
    */
    public function getServerGlobal(){
        return $_SERVER;
    }//getServerGlobal



    /**
     * Get the `$_REQUEST` superglobal. Comes in handy when you want access to the request superglobal via Twig 
     * templates.
     *
     * @return array
    */
    public function getRequestGlobal(){
        return $_REQUEST;
    }//getServerGlobal



}//Request

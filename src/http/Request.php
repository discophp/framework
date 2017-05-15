<?php
namespace Disco\http;

/**
 * Information about the current request to the application. 
*/
class Request extends \Symfony\Component\HttpFoundation\Request {


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
     * Constructor.
     */
    public function __construct(){

        // With the php's bug #66606, the php's built-in web server
        // stores the Content-Type and Content-Length header values in
        // HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH fields.
        $server = $_SERVER;
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }

        $this->initialize(data()->get()->all(), data()->post()->all(), array(), $_COOKIE, $_FILES, $server);

        if (0 === strpos($this->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($this->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($this->getContent(), $data);
            $this->request = new ParameterBag($data);
        }

        $this->server->get('REMOTE_ADDR');
        $this->uri          = $this->server->get('REQUEST_URI');
        $this->url          = parse_url($this->uri); 
        $this->pathParts    = explode('/',$this->url['path']);
        $this->type         = $this->server->get('REQUEST_METHOD');
        $this->ip           = $this->server->get('REMOTE_ADDR');
        $this->secure       = $this->server->has('HTTPS');

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
     * Is the request a GET.
     *
     * @return boolean
     */
    public function isGet(){
        return $this->type === static::METHOD_GET;
    }

    /**
     * Is the request a POST.
     *
     * @return boolean
     */
    public function isPost(){
        return $this->type === static::METHOD_POST;
    }


    /**
     * Is the request a DELETE.
     *
     * @return boolean
     */
    public function isDelete(){
        return $this->type === static::METHOD_DELETE;
    }


    /**
     * Is the request a PUT.
     *
     * @return boolean
     */
    public function isPut(){
        return $this->type === static::METHOD_PUT;
    }


    /**
     * Is the request a PATCH.
     *
     * @return boolean
     */
    public function isPatch(){
        return $this->type === static::METHOD_PATCH;
    }



    /**
     * Is the request a HEAD.
     *
     * @return boolean
     */
    public function isHead(){
        return $this->type === static::METHOD_HEAD;
    }



    /**
     * Is the request a HEAD.
     *
     * @return boolean
     */
    public function isOptions(){
        return $this->type === static::METHOD_OPTIONS;
    }



    /**
     * Is the request a TRACE.
     *
     * @return boolean
     */
    public function isTrace(){
        return $this->type === static::METHOD_TRACE;
    }


    /**
     * Is the request a PURGE.
     *
     * @return boolean
     */
    public function isPurge(){
        return $this->type === static::METHOD_PURGE;
    }



    /**
     * Is the request a CONNECT.
     *
     * @return boolean
     */
    public function isConnect(){
        return $this->type === static::METHOD_CONNECT;
    }



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

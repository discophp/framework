<?php

namespace Disco\classes;

class Request {

    protected $uri;

    protected $url;

    protected $pathParts = Array();

    protected $type;

    protected $ip;


    public function __construct(){
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->url = parse_url($this->uri); 
        $this->pathParts = explode('/',$this->url['path']);
        $this->type = $_SERVER['REQUEST_METHOD'];
        $this->ip = $_SERVER['REMOTE_ADDR'];
    }//__construct


    public function uri(){
        return $_SERVER['REQUEST_URI'];
    }//uri

    public function type(){
        return $this->type;
    }//type

    public function ip(){
        return $this->ip;
    }//type

    public function scheme(){
        return $this->url['scheme'];
    }//scheme

    public function host(){
        return $this->url['host'];
    }//host

    public function port(){
        return $this->url['port'];
    }//port

    public function user(){
        return $this->url['user'];
    }//user

    public function pass(){
        return $this->url['pass'];
    }//pass

    public function path(){
        return $this->url['path'];
    }//pass

    public function pathPart($i){
        if(isset($this->pathParts[$i])){
            return $this->pathParts[$i];
        }//if
        return null;
    }//pass

    public function pathDepth(){
        return count($this->pathParts);
    }//pathDepth

    public function query(){
        return $this->url['query'];
    }//query

    public function fragment(){
        return $this->url['fragment'];
    }//fragment


    public function get($k = null){
        return \Data::get($k);
    }//get

    public function post($k = null){
        return \Data::post($k);
    }//get

    public function put($k = null){
        return \Data::put($k);
    }//get

    public function delete($k = null){
        return \Data::delete($k);
    }//get


}//Request

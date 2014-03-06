<?php
class BaseMySQLiDatabase extends mysqli {


    public $connected=false;
    private $queryCache = array();
    private $dataCache = array();
    public $last;

    public function __construct() {

        parent::__construct($_SERVER['DB_HOST'], $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD'], $_SERVER['DB_DB']);
        //$this->mysqli = @new mysqli($_SERVER['DB_HOST'], $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD'], $_SERVER['DB_DB']);
        if($this->connect_error){
            TRIGGER_ERROR('DB::Connect Error '.$this->connect_errno.' '.$this->connect_error,E_USER_WARNING);
            Util::death();
            die(0);
        }//if
        else
            $this->connected = true;

    }//end constructor


    public function __destruct(){
        if($this->connected)
            $this->close();
    }//deconstruct


    public function last(){
        return $this->last;
    }//last


    public function clean($inc){
        if(get_magic_quotes_gpc()){
            $inc = stripslashes($inc);
        }//if

        $inc = $this->real_escape_string($inc);

       return $inc; 
    }//clean

    public function query($q,$args=null){

        $q = $this->set($q,$args);

        if(!$result = parent::query($q)){
            trigger_error('DB::Error executing query - '.$this->error,E_USER_ERROR);
            return false;
        }//if
        else{
            $this->last = $result;
            return $result;
        }//el
    }//query

    public function lastId(){
        return $this->insert_id;
    }//lastId

    private function set($q,$args){
        if($args!=null){
            if(is_array($args)){
                foreach($args as $a){
                    $q=implode($this->prepareType($a),explode('?',$q,2));
                }//foreach
            }//if
            else {
                $q=implode($this->prepareType($args),explode('?',$q,2));
            }//el
        }//if

        return $q;

    }//set

    private function prepareType($arg){
        $arg = $this->clean($arg);
        if(!is_numeric($arg))
            return "'$arg'";
        return $arg;
    }//wrapStrings


    public function sp($q,$args=null){
        $rows = Array();

        $q = $this->set($q,$args);

        if(!$this->multi_query($q)){
            return null;
        }//if
        do {
            if($result = $this->store_result()){
        
                while($row = $result->fetch_assoc()){
                    $rows[]= $row;
                }//while
        
                $result->free();
            }//if
            else {
                break;
            }//e
        }//do
        while($this->more_results() && $this->next_result());

        return $rows;
    }//executeSP




}//class DataBase


?>

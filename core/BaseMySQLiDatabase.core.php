<?php
class BaseMySQLiDatabase {


    public $mysqli;
    public $connected;
    private $queryCache = array();
    private $dataCache = array();
    public $last;
    public $lastID=0;
    

    public function __construct() {
        $this->connected = false;

        $this->mysqli = @new mysqli($_SERVER['DB_HOST'], $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD'], $_SERVER['DB_DB']);
        if($this->mysqli->connect_error)
            die('Connect Error '.$this->mysqli->connect_errno.' '.$this->mysqli->connect_error);
        else
            $this->connected = true;

    }//end constructor


    public function __destruct(){
        $id = $this->mysqli->thread_id;
        $this->mysqli->kill($id);
        $this->mysqli->close();
    }//deconstruct


    public function last(){
        return $this->last;
    }//last


    public function clean($inc){
        if(get_magic_quotes_gpc()){
            $inc = stripslashes($inc);
        }//if

        $inc = $this->mysqli->real_escape_string($inc);

       return $inc; 
    }//clean

    public function query($q){
        return $this->executeQuery($q);
    }//query

    public function executeQuery($query){
        if(!$result = $this->mysqli->query($query)){
            echo $this->mysqli->error;
            trigger_error('Error executing query',E_USER_ERROR);
            return false;
        }//if
        else{
            $this->last = $result;
            $this->lastID = $this->mysqli->insert_id;
            return $result;
        }//el
    }//executeQuery



    public function sp($q){
        $rows = Array();

        if(!$this->mysqli->multi_query($q)){
            return false;
        }//if
        do {
            if($result = $this->mysqli->store_result()){
        
                while($row = $result->fetch_assoc()){
                    $rows[]= $row;
                }//while
        
                $result->free();
            }//if
            else {
                return false;
            }//e
        }//do
        while($this->mysqli->more_results() && $this->mysqli->next_result());

        return $rows;
    }//executeSP




}//class DataBase


?>

<?php

namespace Disco\classes;

/**
 *      This file holds the class BaseMySQLiDatabase
 */



/**
 *      Class BaseMySQLiDatabase.
 *      Provides access to a MySQL server and the ability to run DML statements on it. 
 *      Settings in .env.*.json must be set in order to establish a connection to the server.
 *
*/
class BaseMySQLiDatabase extends mysqli {


    /**
     *      Are we connected to the MySQL server?
    */
    public $connected=false;

    /**
     *      Cache of queries we executed
     */
    private $queryCache = array();

    /**
     *      Cache of results we received
     */
    private $dataCache = array();

    /**
     *      The last result of a query
     */
    public $last;



    /**
     *      Connect to the MySQL server 
     *
     *
     *      @return void
     */
    public function __construct($host=null,$user=null,$pw=null,$db=null) {

        if($host==null){
            $host=$_SERVER['DB_HOST'];$user=$_SERVER['DB_USER'];$pw=$_SERVER['DB_PASSWORD'];$db=$_SERVER['DB_DB'];
        }//if

        parent::__construct($host, $user, $pw, $db);

        if($this->connect_error){
            TRIGGER_ERROR('DB::Connect Error '.$this->connect_errno.' '.$this->connect_error,E_USER_WARNING);
            Util::death();
            die(0);
        }//if
        else
            $this->connected = true;

    }//end constructor


    /**
     *      Tear down the connection
     *
     *
     *      @return void
    */
    public function __destruct(){
        if($this->connected)
            $this->close();
    }//deconstruct



    /**
     *      Access the last query resultSet 
     *
     *      
     *      @return object $this->last A MySQL ResultSet
     */
    public function last(){
        return $this->last;
    }//last



    /**
     *      Sanatize a string before trying to use it in the DML
     *
     *
     *      @param string $inc query to clean
     *      @return string $inc cleaned query
    */
    public function clean($inc){
        if(get_magic_quotes_gpc()){
            $inc = stripslashes($inc);
        }//if

        $inc = $this->real_escape_string($inc);

       return $inc; 
    }//clean



    /**
     *      Execute a query
     *
     *
     *      @param string $q the query to execute
     *      @param mixed $args the variables to bind to the query string
     *      @return object $result a mysql resultset
    */
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



    /**
     *      Return the last generated Auto Increment ID
     *
     *
     *      @return int $this->insert_id
    */
    public function lastId(){
        return $this->insert_id;
    }//lastId



    /**
     *      Bind passed variables into a query string and do proper type checking
     *      and escaping before binding
     *
     *
     *      @param string $q the query string
     *      @param mixed $args the variables to bind to the query
     *      @return string $q the query with variables bound into it
    */
    public function set($q,$args){
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



    /**
     *      Determine the type of variable being bound into the query.
     *      Ex String, Int
     *
     *
     *      @param mixed $arg
     *      @return mixed $arg
    */
    private function prepareType($arg){
        if($arg==null || $arg=='null')
            return 'NULL';
        $arg = $this->clean($arg);
        if(!is_numeric($arg))
            return "'$arg'";
        return $arg;
    }//wrapStrings




    /**
     *      Execute a Stored Procedure on the Server
     *
     *
     *      @param string $q the stored procedure to execute
     *      @param mixed $args the variables to bind the the stored procedure
     *      @return array $rows the tuples returned by the stored procedure
     *
    */
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

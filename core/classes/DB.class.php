<?php
namespace Disco\classes;
/**
 * This file holds the class DB which extends \mysqli. 
 */



/**
 * Class DB.
 * Provides access to a MySQL server and the ability to run DML statements on it. 
 * This class depends on settings in [.config.php] in order to establish a connection the the MySQL Server.
*/
class DB extends \mysqli {

    /**
     * @var \mysqli Connected database link.
    */
    public $link;

    /**
     * @var boolean Are we connected to the MySQL server?
    */
    public $connected=false;

    /**
     * @var boolean Are we using autocommit?
    */
    public $autoCommit = true;

    /**
     * @var \mysqli_result The last result of a query.
     */
    public $last;

    private $app;


    /**
     * Connect to the MySQL server.
     * See http://discophp.com/docs/facades/DataBase to learn about extending and multiple DB connections.
     *
     *
     * @param null|string $host The host to connect to.
     * @param null|string $user The user to connect with $host to.
     * @param null|string $pw   The users password to connect with $host to.
     * @param null|string $db   The Schema to connect to on $host.
     *
     * @return void
     */
    public function __construct($host=null,$user=null,$pw=null,$db=null) {

        $this->app = \App::instance();

        if($host==null){
            $host=$this->app->config['DB_HOST'];
            $user=$this->app->config['DB_USER'];
            $pw=$this->app->config['DB_PASSWORD'];
            $db=$this->app->config['DB_DB'];
        }//if

        $this->link = parent::__construct($host, $user, $pw, $db);

        if($this->connect_error){
            TRIGGER_ERROR('DB::Connect Error '.$this->connect_errno.' '.$this->connect_error,E_USER_WARNING);
            $this->app['View']->serve(500);
        }//if
        else
            $this->connected = true;

    }//end constructor



    /**
     * Tear down the connection by calling $this->close() which is a method of the parent \mysqli.
     *
     *
     * @return void
    */
    public function __destruct(){
        if($this->connected)
            $this->close();
    }//deconstruct



    /**
     * Access the last query mysqli_result object.
     *
     * 
     * @return \mysqli_result Returns $this->last A mysqli_result
     */
    public function last(){
        return $this->last;
    }//last



    /**
     * Sanatize a string before trying to use it in the DML.
     *
     *
     * @param string  $inc The query to sanatize.
     * @return string The sanatized query.
    */
    public function clean($inc){
        if(get_magic_quotes_gpc()){
            $inc = stripslashes($inc);
        }//if

        $inc = $this->real_escape_string($inc);

       return $inc; 
    }//clean



    /**
     * Execute a query.
     *
     *
     * @param string       $q    The query to execute.
     * @param null|string|array $args The variables to bind to the query string.
     *
     * @return \mysqli_result 
    */
    public function query($q,$args=null){

        if($args){
            $q = $this->set($q,$args);
        }//if

        if(!$result = parent::query($q)){
            $this->app->error("DB::Error - {$this->error} - query - $q",Array('query'),debug_backtrace(TRUE,4));
            return false;
        }//if

        $this->last = $result;
        return $this->last;

    }//query



    /**
     * Return the last generated Auto Increment ID
     *
     *
     * @return int Return the value $this->insert_id
    */
    public function lastId(){
        return $this->insert_id;
    }//lastId



    /**
     * Bind passed variables into a query string and do proper type checking
     * and escaping before binding.
     *
     *
     * @param string        $q      The query string.
     * @param string|array  $args   The variables to bind to the $q.
     *
     * @return string               The $q with $args bound into it.
    */
    public function set($q,$args){
        if(is_array($args) && isset($args['raw'])){
            $q=implode($args['raw'],explode('?',$q,2));
        }//if
        else if(is_array($args)){
            foreach($args as $a){
                if(is_array($a) && isset($a['raw'])){
                    $q=implode($a['raw'],explode('?',$q,2));
                }//if
                else {
                    $q=implode($this->prepareType($a),explode('?',$q,2));
                }//el
            }//foreach
        }//if
        else {
            $q=implode($this->prepareType($args),explode('?',$q,2));
        }//el

        return $this->resetQuestionMarks($q);

    }//set



    /**
     * Determine the type of variable being bound into the query, either a String or Numeric.
     *
     *
     * @param  string|int|float $arg  The variable to prepare.
     *
     * @return string|int|float The $arg prepared.
    */
    private function prepareType($arg){
        if(($arg===null || $arg=='null') && $arg !== 0)
            return 'NULL';
        $arg = $this->clean($arg);
        if(!is_numeric($arg)){
            $arg = $this->replaceQuestionMarks($arg);
            return "'$arg'";
        }//if
        return $arg;
    }//wrapStrings


    private function replaceQuestionMarks($arg){
        return str_replace('?','+:-|:-+',$arg);
    }//escapeQuestionMarks

    private function resetQuestionMarks($arg){
        return str_replace('+:-|:-+','?',$arg);
    }//escapeQuestionMarks



    /**
     * Execute a Stored Procedure in the Remote Hosts Schema.
     *
     *
     * @param string        $q The stored procedure to execute.
     * @param null|string|array  $args The variables to bind to the $q.
     *
     * @return array The tuples returned by the stored procedure.
    */
    public function sp($q,$args=null){
        $rows = Array();

        if($args){
            $q = $this->set($q,$args);
        }//if

        if(!$this->multi_query($q)){
            $this->app->error("DB::Error - {$this->error} - stored procedure - $q",Array('sp'),debug_backtrace(TRUE,4));
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

}//DB
?>

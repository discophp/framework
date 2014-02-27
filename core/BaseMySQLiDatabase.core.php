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

        $this->mysqli = @new mysqli('localhost', 'root', $_SERVER['DB_PASSWORD'], 'bitcoin');
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



    public function encrypt_AES128($input){
    
        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $key128 = '9324414997743825'; //16 length key (counting from 1)
        $iv =  '1482030241932178'; //16 length key (counting from 1)

        if (mcrypt_generic_init($cipher, $key128, $iv) != -1) {
            $cipherText = mcrypt_generic($cipher,$input);
            mcrypt_generic_deinit($cipher);
        }//if

        return bin2hex($cipherText);
    }//AES128



    public function decrypt_AES128($crypt){
        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $key128 = '9324414997743825'; //16 length key (counting from 1)
        $iv =  '1482030241932178'; //16 length key (counting from 1)

        if (mcrypt_generic_init($cipher, $key128, $iv) != -1) {
            $cipherText = mcrypt_generic($cipher,$crypt);
            mcrypt_generic_deinit($cipher);
        }//end if

        return bin2hex($cipherText);
    }//end AES128



    public function pwHash($pw){
        return hash('sha512',"RANDOMSTRING234234{$pw}MORERANDOM234324");
    }//pwHash

}//class DataBase


//class DB {
//    public static function query($q){
//        Disco::db()->executeQUery($q);
//    }//query
//
//    public static function last(){
//        return Disco::db()->last;
//    }//last
//
//    public static function sp($q){
//        return Disco::db()->sp($q);
//    }////sp
//
//}//DB




?>

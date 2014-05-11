<?php

namespace Disco\classes;

/**
 *      This file holds the BaseData class
*/


/**
 *      BaseData class.
 *      Provides easy wrapper around using HTTP data centric around
 *      the RESTful priciples PUT,POST,GET,DELETE
*/
class BaseData {

    /**
     *      Holds the PUT data
     */
    private $putData=Array();

    /**
     *      Holds the DELETE data
     */
    private $deleteData=Array();

    /**
     *      Type of REST request 
     */
    private $workingDataType;

    /**
     *      Should returned value be sql escaped 
     */
    private $escapeValue=false;



    /**
     *      Construct PUT and DELETE data
     *
     *
     *      @return void
    */
    public function __construct(){
        switch($_SERVER['REQUEST_METHOD']) {
            case 'PUT':
                $this->setData('PUT');
            case 'DELETE':
                $this->setData('DELETE');
        }//switch 
    }//construct



    /**
     *      Set data of the selected type
     *
     *
     *      @param string $type the type of REST request
     *      @return void
    */
    private function setData($type){
        $string='';
        $putStream = fopen('php://input','r');
        while($data = fread($putStream,1024))
            $string.=$data;
        fclose($putStream);

        if($string!=''){
            $vars=explode('&',$string);
            foreach($vars as $kvString){
                $values = explode('=',$kvString,2);
                if($type=='PUT')
                    $this->putData[$values[0]]=$values[1];
                else
                    $this->deleteData[$values[0]]=$values[1];
            }//foreach
        }//if

    }//setPutData



    /**
     *      SQL escape the returned string
     *
     *
     *      @return object $this
    */
    public function escape(){
        $this->escapeValue=true;
        return $this;
    }//escape


    /**
     *      Determine if the specific data matches the found value  
     *
     *
     *      @param string $k the key of the data
     *      @param string $v the regex pattern or default matching condition to use
     *      @return mixed 
    */
    public function where($k,$v){
        $dataType=$this->all();

        if(isset($dataType[$k])){
            $matchCondition = $v;
            if(isset(Disco::$defaultMatchCondition[$v]))
                $matchCondition = Disco::$defaultMatchCondition[$v];
            if(!preg_match("/{$matchCondition}/",$dataType[$k]))
                return false;

            if($this->escapeValue){
                $this->escapeValue=false;
                return DB::clean($dataType[$k]);
            }//if

            return $dataType[$k];
        }//if
        else 
            return false;

    }//where



    /**
     *      return a GET variable
     *
     *
     *      @param string $g the key
     *      @return mixed  
    */
    public function get($g=null){
        if($g==null){
            $this->workingDataType='GET';
            return $this;
        }//if
        else if(isset($_GET[$g])){
            if($this->escapeValue){
                $this->escapeValue=false;
                return DB::clean($_GET[$g]);
            }//if
            return $_GET[$g];
        }//if
        else 
            return false;
    }//get



    /**
     *      return a POST variable
     *
     *
     *      @param string $p the key
     *      @return mixed  
    */
    public function post($p=null){
        if($p==null){
            $this->workingDataType='POST';
            return $this;
        }//if
        else if(isset($_POST[$p])){
            if($this->escapeValue){
                $this->escapeValue=false;
                return DB::clean($_POST[$p]);
            }//if
            return $_POST[$p];
        }//if
        else 
            return false;
    }//post



    /**
     *      return a DELETE variable
     *
     *
     *      @param string $d the key
     *      @return mixed  
    */
    public function delete($d=null){
        if($d==null){
            $this->workingDataType='DELETE';
            return $this;
        }//if
        else if(isset($this->deleteData[$d])){
            if($this->escapeValue){
                $this->escapeValue=false;
                return DB::clean($this->deleteData[$d]);
            }//if
            return $this->deleteData[$d];
        }//if
        else 
            return false;
    }//delete



    /**
     *      return a PUT variable
     *
     *
     *      @param string $p the key
     *      @return mixed  
    */
    public function put($p=null){
        if($p==null){
            $this->workingDataType='PUT';
            return $this;
        }//if
        else if(isset($this->putData[$p])){
            if($this->escapeValue){
                $this->escapeValue=false;
                return DB::clean($this->putData[$p]);
            }//if
            return $this->putData[$p];
        }//if
        else 
            return false;
    }//put



    /**
     *      SET a selected type of REST variable 
     *
     *
     *      @param string $p the key
     *      @return mixed  
    */
    public function set($k=null,$v){
        if($this->workingDataType==null ||  $k==null)
            return;

        switch($this->workingDataType){
            case 'PUT':
                $this->putData[$k]=$v;
            case 'DELETE':
                $this->deleteData[$k]=$v;
            case 'POST':
                $_POST[$k]=$v;
            case 'GET':
                $_GET[$k]=$v;
        }//switch
    }//set



    /**
     *      Return all of the selected type of REST data 
     *
     *
     *      @return array
    */
    public function all(){
        switch($this->workingDataType){
            case 'PUT':
                return $this->putData;
            case 'DELETE':
                return $this->deleteData;
            case 'POST':
                return $_POST;
            case 'GET':
                return $_GET;
        }//switch

    }//all


}//BaseData



?>

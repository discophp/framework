<?php

class BaseData {
    private $putData=Array();
    private $deleteData=Array();
    private $workingDataType;
    private $escapeValue=false;

    public function __construct(){
        switch($_SERVER['REQUEST_METHOD']) {
            case 'PUT':
                $this->setData('PUT');
            case 'DELETE':
                $this->setData('DELETE');
        }//switch 
    }//construct

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

    public function escape(){
        $this->escapeValue=true;
        return $this;
    }//escape

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


}//BaseData



?>

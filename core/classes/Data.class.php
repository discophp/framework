<?php
namespace Disco\classes;
/**
 * This file holds the Data class.
*/


/**
 * Data class.
 * Provides easy wrapper around using HTTP data centric around
 * the RESTful priciples PUT,POST,GET,DELETE.
*/
class Data {

    /**
     * @var array Holds the PUT data
     */
    private $putData=Array();

    /**
     * @var array Holds the DELETE data
     */
    private $deleteData=Array();

    /**
     * @var array Type of REST request 
     */
    private $workingDataType;

    /**
     * @var boolean Should returned value be sql escaped 
     */
    private $escapeValue=false;



    /**
     * Construct PUT and DELETE data if the REQUEST_METHOD is PUT | DELETE. 
     *
     *
     * @return void
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
     * Set data of the selected type from the PUT or DELETE stream php://input .
     * We don't have to worry about handling GET or POST as Apache pre-parses those into $_GET & $_POST.
     *
     *
     * @param string $type the type of REST request either PUT|DELETE
     * @return void
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
     * Request that the value to be returned be SQL escaped.
     *
     *
     * @return self 
    */
    public function escape(){
        $this->escapeValue=true;
        return $this;
    }//escape


    /**
     * Determine if the specific $k data matches the found value using the regular expression $v.
     *
     *
     * @param string $k The key of the data.
     * @param string $v The regex pattern or default matching condition to use.
     *
     * @return string|bool|int|float 
    */
    public function where($k,$v){
        $dataType=$this->all();

        if(isset($dataType[$k])){
            $matchCondition = $v;
            if(isset(\Disco::$defaultMatchCondition[$v]))
                $matchCondition = \Disco::$defaultMatchCondition[$v];
            if(!preg_match("/{$matchCondition}/",$dataType[$k]))
                return false;

            if($this->escapeValue){
                $this->escapeValue=false;
                return \DB::clean($dataType[$k]);
            }//if

            return $dataType[$k];
        }//if
        else 
            return false;

    }//where



    /**
     * Return a GET variable or if $g==null return $this and set method chain to use GET.
     *
     *
     * @param null|string $g The GET key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function get($g=null){
        if($g==null){
            $this->workingDataType='GET';
            return $this;
        }//if
        else if(isset($_GET[$g])){
            if($this->escapeValue){
                $this->escapeValue=false;
                return \DB::clean($_GET[$g]);
            }//if
            return $_GET[$g];
        }//if
        else 
            return false;
    }//get



    /**
     * Return a POST variable or if $p==null return $this and set method chain to use POST.
     *
     *
     * @param null|string $p The POST key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function post($p=null){
        if($p==null){
            $this->workingDataType='POST';
            return $this;
        }//if
        else if(isset($_POST[$p])){
            if($this->escapeValue){
                $this->escapeValue=false;
                return \DB::clean($_POST[$p]);
            }//if
            return $_POST[$p];
        }//if
        else 
            return false;
    }//post



    /**
     * Return a DELETE variable or if $d==null return $this and set method chain to use DELETE.
     *
     *
     * @param null|string $d The DELETE key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function delete($d=null){
        if($d==null){
            $this->workingDataType='DELETE';
            return $this;
        }//if
        else if(isset($this->deleteData[$d])){
            if($this->escapeValue){
                $this->escapeValue=false;
                return \DB::clean($this->deleteData[$d]);
            }//if
            return $this->deleteData[$d];
        }//if
        else 
            return false;
    }//delete



    /**
     * Return a PUT variable or if $p==null return $this and set method chain to use PUT.
     *
     *
     * @param null|string $p The PUT key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function put($p=null){
        if($p==null){
            $this->workingDataType='PUT';
            return $this;
        }//if
        else if(isset($this->putData[$p])){
            if($this->escapeValue){
                $this->escapeValue=false;
                return \DB::clean($this->putData[$p]);
            }//if
            return $this->putData[$p];
        }//if
        else 
            return false;
    }//put



    /**
     * SET a selected type of REST variable.
     *
     *
     * @param null|string $k The key to set the $v with.
     * @param mixed $v The value of $k.
     *
     * @return mixed  
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
     * REMOVE a selected type of REST variable.
     *
     *
     * @param null|string $k The key to remove.
     *
     * @return void 
    */
    public function remove($k){
        switch($this->workingDataType){
            case 'PUT':
                unset($this->putData[$k]);
                break;
            case 'DELETE':
                unset($this->deleteData[$k]);
                break;
            case 'POST':
                unset($_POST[$k]);
                break;
            case 'GET':
                unset($_GET[$k]);
                break;
        }//switch
    }//set




    /**
     * Return all of the selected type of REST data. 
     *
     *
     * @return array
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

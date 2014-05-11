<?php

namespace Disco\classes;

/**
 *      This file hold the BaseModel class
*/



/**
 *      BaseModel class.
 *      Allows the creation of ORM style models through 
 *      extentions of this class.
 *
 *      These extending classes must set $table and $ids to use the ORM.
 *
*/
class BaseModel {

    /**
    *       The SQL Table associated with this model
    */
    public $table;

    /**
    *       The SQL primary key or composite key associated with this model
    */
    public $ids;

    /**
    *       The working select statement
    */
    private $select;

    /**
     *      The working update statement
    */
    private $update;

    /**
     *      The where condition or the working query 
    */
    private $where='';

    /**
     *      The tables we should join on with the working query 
    */
    private $joinOn=Array();

    /**
     *      The result limit of the working query 
    */
    private $limit=Array();

    /**
     *      The ordering of the working query 
    */
    private $order=Array();

    /**
     *      The last query this model executed 
    */
    private $lastQuery;

    /**
     *      The last resultSet from a query 
    */
    private $lastResultSet;



    /**
    *       Reset our model conditions
    *
    *
    *       @return void
    */
    private final function clearData(){
        $this->where='';
        $this->joinOn=Array();
        $this->limit=Array();
        $this->order=Array();
        $this->lastResultSet=null;
        $this->update='';
    }//clearData



    /**
     *      Prepare a SELECT condition. 
     *      Accepts its arguements through func_get_args()
     *
     *
     *      @param string this function takes as many string arguements as you pass to it
     *      @return object $this instance of self 
     */
    public final function select(){
        $this->clearData();
        $this->select=func_get_args();
        return $this;
    }//if



    /**
     *      Prepare an UPDATE statement.
     *      Uses func_get_args() to accept parameters. 
     *
     *
     *      @param string accepts any even number of strings
     *      @return object $this instance of self 
    */
    public final function update(){
        $this->clearData();
        $temp = Array();
        $data = func_get_args();
        $iter=0;

        foreach($data as $k=>$v){
            if($k%2==1){
                $temp[$iter]='=';
                $iter++;
            }//if

            $temp[$iter]=$v;
            $iter++;

        }//foreach

        $this->update.= $this->prepareCondition($temp,',');
        return $this;
    }//update



    /**
     *      Execute an INSERT statement
     *      Accepts its arguements through func_get_args(). Can be an associative array or strings.
     *
     *
     *      @param mixed either pass a number of strings or an associative array 
     *      @return boolean was the insert successful 
     *
    */
    public final function insert(){
        $this->clearData();
        $data = func_get_args(); 
        $insert='';
        $values='';
        $tempValues=Array();
        if(is_array($data[0])){
            foreach($data[0] as $k=>$v){
                $insert.=$k.',';
                $values.='?,';
                $tempValues[]=$v;
            }//foreach
        }//if
        else {
            foreach($data as $k=>$v){
                if($k%2==1){
                    $values.='?,';
                    $tempValues[]=$v;
                }//if
                else 
                    $insert.=$v.',';
            }//foreach
        }//el
        $insert = rtrim($insert,',');
        $values = rtrim($values,',');
        $query = "INSERT INTO {$this->table} ({$insert}) VALUES ({$values})";

        $this->lastQuery = DB::set($query,$tempValues);
        return DB::query($this->lastQuery);

    }//insert



    /**
     *
     *      Execute a DELETE statement. Accepts its arguements thruogh func_get_args(). Takes strings.
     *
     *
     *      @param string any odd number of strings
     *      @return boolean whether or not the delete was successful 
    */
    public final function delete(){
        $this->clearData();
        $this->where = $this->prepareCondition(func_get_args(),'AND');
        $this->lastQuery = "DELETE FROM {$this->table} WHERE {$this->where}";
        return DB::query($this->lastQuery);
    }//delete



    /**
     *      Prepare the WHERE condition for the working query. 
     *      Accepts its arguements through func_get_args(). 
     *      Takes strings and numbers in pairs of 3 like 
     *          ->where('field','>',20)
     *          ->where('field1','=','somevalue')
     *
     *
     *      @param mixed number or string
     *      @return object $this instance of self 
    */
    public final function where(){
        $this->where.= $this->prepareCondition(func_get_args(),'AND');
        return $this;
    }//where



    /**
    *      Prepare an OR statement for the WHERE condition of the working query.
    *      Accepts its arguements through func_get_args().
    *      Takes strings and numbers in pairs of 3 like 
    *          ->otherwise('field','>',20)
    *          ->otherwise('field1','=','somevalue')
    *
    *
    *      @param mixed number or string
    *      @return object $this instance of self 
    */
    public final function otherwise(){
        $this->where.= $this->prepareCondition(func_get_args(),'OR');
        return $this;
    }//or 



    /**
     *     Prepare the JOIN condition for the working SELECT query.
     *     To join throug the ORM you must have a defined Model which extends BaseModel and 
     *     has its $table and $ids values set as this will be used to execute the join on. 
     *
     *
     *     @param string $modelName The name of the Model you will join on
     *     @param string $joinOn The type of JOIN that should be used, this is hidden from the user
     *     @return object $this instance of self
    */
    public final function join($modelName,$joinOn='INNER JOIN'){
        $joinTable = Model::m($modelName)->table;
        $ids = array_intersect($this->ids,Model::m($modelName)->ids);

        $joinOn.=" {$joinTable} ";
        foreach($ids as $id){
            $joinOn.= "ON {$this->table}.{$id}={$joinTable}.{$id} AND ";
        }//id

        $joinOn=trim($joinOn,'AND ');

        $this->joinOn[]=$joinOn;
        return $this;
    }//join


    /**
     *      Read docs on join function first, this simply extends that function and 
     *      passes in a LEFT JOIN as the second arguement.
     *
     *      
     *      @param $modelName The name of the model you will join on
     *      @return object $this instance of self
    */
    public final function ljoin($modelName){
        $this->join($modelName,'LEFT JOIN');
        return $this;
    }//ljoin



    /**
     *      Read docs on join function first, this simply extends that function and 
     *      passes in a RIGHT JOIN as the second arguement.
     *
     *      
     *      @param $modelName The name of the model you will join on
     *      @return object $this instance of self
    */
    public final function rjoin($modelName){
        $this->join($modelName,'RIGHT JOIN');
        return $this;
    }//ljoin



    /**
     *      Set an ORDER BY condition for the current SELECT query.
     *      Accepts its parameters through func_get_args(). Takes strings and numbers.
     *
     *      
     *      @param mixed func_get_args()
     *      @return object $this instace of self
    */
    public final function order(){
        $this->order = array_merge($this->order,func_get_args());
        return $this;
    }//order


    /**
     *      Set a LIMIT condition on the current SELECT query.
     *
     *
     *      @param int $start Starting position of LIMIT or the number of tuples to return contigent upon the 
     *      exsistance of the second parameter $limit
     *      @param int $limit The number of tuples to return, default to 0
     *      @return object $this instance of self
     *
    */
    public final function limit($start,$limit=0){
        $this->limit[]=$start;
        if($limit!=0){
            $this->limit[]=$limit;
        }//if
        return $this;
    }//limit





    /**
     *      Execute the UPDATE statement that was previously prepared.
     *
     *
     *      @return boolean return whether the update was successful
    */
    public final function finalize(){

        $update = $this->update;
        $where = $this->where;
        if($where)
            $where='WHERE '.$where;

        $this->lastQuery ="UPDATE {$this->table} SET {$update} {$where}"; 
        return DB::query($this->lastQuery);

    }//do



    /**
     *      Return the data from the execution of the previous query.
     *
     *      @return resultSet MySQL result set of last query
    */
    public final function data(){
        if($this->lastResultSet)
            return $this->lastResultSet;

        $this->lastResultSet = $this->fetchData();
        return $this->lastResultSet;
    }//data



    /**
     *      If used is working with a direct instance of the Model and not 
     *      through the Facade then they can invoke the class as a method
     *      and get the data return
     *
     *
     *      @return resultSet 
     */
    public function __invoke(){
        return $this->data();
    }//invoke



    /**
     *      Execute the previous query and set the returned data
     *
     *
     *      @return resultSet
    */
    private final function fetchData(){
        $select = implode(',',array_values($this->select));

        $where = $this->where;

        if($where){
            $where='WHERE '.$where;
        }//if

        $joinOn = implode('',$this->joinOn);

        $order='';
        if(count($this->order)>0){
            for($i=0;$i<count($this->order);$i+=2){
                $order.= $this->order[$i].' '.$this->order[$i+1].',';
            }//for
            $order='ORDER BY '.rtrim($order,',');
        }//if


        $limit='';
        if(count($this->limit)==1)
            $limit = "LIMIT {$this->limit[0]}";
        else if(count($this->limit)==2)
            $limit = "LIMIT {$this->limit[0]},{$this->limit[0]}";


        $this->lastQuery = "SELECT {$select} FROM {$this->table} {$joinOn} {$where} {$order} {$limit}";

        DB::query($this->lastQuery); 

        return DB::last();

    }//fetchData



    /**
     *      Prepare a condition to be used in the query.
     *          For example $data contains:
     *              - 'price'
     *              - '>'
     *              - 59.99
     *
     *          We want to make this a DML statement like: 
     *              - tablename.price>59.99
     *
     *          But we also will likely have another condition that will precede the first one we are passed,
     *          that is where the $conjuction paramater comes into play. 
     *          Its value will be a literal conjuction such as 'AND' or 'OR' or ','
     *
     *      This function uses the DB method set() to safely bind the passed variables into the DML statement.
     *
     *
     *
     *      @param array $data pieces of the condition that needs to be prepared
     *      @param string $conjunction The conjuction to be used if more than one condition is present
     *      @return mixed $where Either return the condition or false if there was no condition to prepare
     *
    */
    private final function prepareCondition($data,$conjunction){
        if(count($data)>0){
            $where='';
            $values=Array();

            for($i=0;$i<count($data);$i+=3){

                if($i>0)
                    $where.=" {$conjunction} ";

                $where.=$this->table.'.'.$data[$i].$data[$i+1].'?';
                $values[]=$data[$i+2];

            }//for

            $where = DB::set(rtrim($where,','),$values);

            if($this->where!='')
                $where = " {$conjunction} {$where} ";

            return $where;

        }//count

        return false;

    }//prepareCondition



}//Model


?>

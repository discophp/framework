<?php
namespace Disco\classes;
/**
 * This file hold the Model class.
*/



/**
 * Model class.
 * Allows the creation of ORM style models through extentions of this class.
 * These extending classes must set $table and $ids to use the ORM.
*/
class Model {


    /**
     * @var string The SQL Table associated with this model.
    */
    public $table;


    /**
     * @var string|array The SQL primary key or composite key associated with this model.
    */
    public $ids;


    /**
     * @var string|null An alias to apply to the model when making queries.
    */
    public $alias=null;


    /**
     * @var boolean Was the alias set by method call?
    */
    public $aliasWasSet = false;


    /**
     * @var string The working select statement.
    */
    private $select;


    /**
     * @var string The working update statement.
    */
    private $update='';


    /**
     * @var string The where condition or the working query.
    */
    private $where='';


    /**
     * @var array The tables we should join on with the working query.
    */
    private $joinOn=Array();
    

    /**
     * @var array The result limit of the working query.
    */
    private $limit=Array();


    /**
     * @var array The ordering of the working query. 
    */
    private $order=Array();
    

    /**
     * @var string The last query this model executed.
    */
    public $lastQuery;



    /**
    * Reset our model conditions.
    *
    *
    * @return void
    */
    private final function clearData(){
        $this->where='';
        $this->joinOn=Array();
        $this->limit=Array();
        $this->order=Array();
        $this->update='';
        $this->alias = null;
    }//clearData



    /**
     * Alias the table name of the model. Use when performing joins.
     *
     *
     * @param string $alias The alias.
     *
     * @return self
    */
    public final function alias($alias){
        $this->alias = $alias;
        $this->aliasWasSet = true;
        return $this;
    }//alias



    /**
     * Prepare a SELECT condition. 
     * Accepts its arguements through func_get_args(). 
     *
     *
     * @return self 
     */
    public final function select(){
        $data = func_get_args();
        if(is_array($data[0])){
            foreach($data[0] as $k=>$v){
                $data[0][$k] = $v;
            }//foreach
            $this->select = $data[0];
        }//if
        else if(!isset($data[1])){
            $data[0] = explode(',',$data[0]);
            foreach($data[0] as $k=>$v){
                $data[0][$k] = $v;
            }//foreach
            $this->select = $data[0];
        }//elif
        else {
            foreach($data as $k=>$v){
                $data[$k] = $v;
            }//foreach
            $this->select = $data;
        }//el
        return $this;
    }//if



    /**
     * Prepare an UPDATE statement.
     * Uses func_get_args() to accept parameters. 
     *
     *
     * @return self 
    */
    public final function update(){
        $this->clearData();
        $data = func_get_args();

        if($this->update){
            $this->update = ',' . $this->update;
        }//if

        $this->update .= str_replace('IS NULL','=NULL',$this->prepareCondition($data,','));

        return $this;

    }//update



    /**
     * Execute the UPDATE statement that was previously prepared.
     *
     *
     * @return boolean Return whether the update was successful.
    */
    public final function finalize(){

        if(!$this->update){
            throw new \InvalidArgumentException;
        }//if

        $where = $this->where;
        if($where) {
            $where='WHERE '.$where;
        }//if

        return $this->executeQuery("UPDATE {$this->table} SET {$this->update} {$where}"); 

    }//finalize



    /**
     * Alias of `$this->finalize()`.
    */
    public final function commit(){
        return $this->finalize(); 
    }//commit



    /**
     * Execute an INSERT statement.
     * Accepts its arguements through func_get_args(). 
     *
     *
     * @return boolean Was the insert successful? 
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
        else if(is_array($data[1])){
            $insert = $data[0];
            $tempValues = $data[1];
            $l=count($tempValues);
            $i=0;
            while($i<$l){
                $values.='?,';
                $i++;
            }//while
        }//elif

        $insert = rtrim($insert,',');
        $values = rtrim($values,',');
        $query = "INSERT INTO {$this->table} ({$insert}) VALUES ({$values})";

        if($this->executeQuery(\App::with('DB')->set($query,$tempValues))){
            return \App::with('DB')->lastId();
        }//if

    }//insert



    /**
     * Execute a DELETE statement. 
     * Accepts its arguements thruogh func_get_args(). 
     *
     * Delete records where all the key/value pairs match.
     *
     * @return boolean Whether or not the delete was successful.
    */
    public final function delete(){
        $this->clearData();
        $this->where = $this->prepareCondition(func_get_args(),'AND');
        return $this->executeQuery("DELETE FROM {$this->table} WHERE {$this->where}");
    }//delete



    /**
     * Execute a DELETE statement. 
     * Accepts its arguements thruogh func_get_args(). 
     *
     * Will delete any records that match any of the key/values pairs.
     *
     *
     * @return boolean Whether or not the delete was successful.
    */
    public final function deleteOr(){
        $this->clearData();
        $this->where = $this->prepareCondition(func_get_args(),'OR');
        return $this->executeQuery("DELETE FROM {$this->table} WHERE {$this->where}");
    }//deleteOr



    /**
     * Prepare a DELETE condition for the query.
     *
     * Delete records where a value is in the key/value pairs.
     *
     *
     * @param string $field The field to look in.
     * @param string|array $array A string of comma seperated values, or an array of values.
     *
     * @return self
    */
    public final function deleteIn($field,$array){

        $this->clearData();

        $array = $this->buildWhereInArray($array);

        $this->where = "{$field} IN ({$array})";

        return $this->executeQuery("DELETE FROM {$this->table} WHERE {$this->where}");

    }//deleteIn



    /**
     * Specify a WHERE condition for the working query.
     * Accepts its arguements through func_get_args(). 
     *
     * Will find records where all key/value pairs match.
     *
     * @return self
    */
    public final function where(){
        if($this->where){
            $this->where = "{$this->where} AND ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND');
        return $this;
    }//where



    /**
     * Specify a WHERE condition for the working query.
     * Accepts its arguements through func_get_args(). 
     *
     * Will find records where any of the key/value pairs match.
     *
     * @return self
    */
    public final function whereOr(){
        if($this->where){
            $this->where = "{$this->where} AND ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'OR');
        return $this;
    }//where



    /**
     * Specify a WHERE condition for the working query.
     * Accepts its arguements through func_get_args(). 
     *
     *
     * Will find records where key/values pairs do not match any conditions.
     *
     * @return self
    */
    public final function whereNotOr(){
        if($this->where){
            $this->where = "{$this->where} AND ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'OR','<>');
        return $this;
    }//where



    /**
     * Specify a WHERE condition for the working query.
     * Accepts its arguements through func_get_args(). 
     *
     * Starts a new condition group by wrapping the previous condition in paranethesis and starting the new 
     * condition group with an AND. Then in the new condition group match records where all of the key/value pairs 
     * match.

     * @return self
    */
    public final function whereAlso(){
        if($this->where){
            $this->where = "{$this->where} AND ALSO ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND');
        return $this;
    }//where



    /**
     * Specify a WHERE condition for the working query.
     * Accepts its arguements through func_get_args(). 
     *
     * Find record where none of the values match the key/value pairs.
     *
     * @return self
    */
    public final function whereNot(){
        if($this->where){
            $this->where = "{$this->where} AND ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND','<>');
        return $this;
    }//where



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Starts a new condition group by wrapping the previous condition in paranethesis and starting the new 
     * condition group with an AND. Then in the new condition group match records where none of the key/value pairs 
     * match. 
     *
     * @return self
    */
    public final function whereNotAlso(){
        if($this->where){
            $this->where = "{$this->where} AND ALSO ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND','<>');
        return $this;
    }//where



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Find records that are like the key/value pairs. AKA a regexp.
     *
     * @return self
    */
    public final function whereLike(){
        if($this->where){
            $this->where = "{$this->where} AND ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND',' LIKE ');
        return $this;
    }//where



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Starts a new condition group by wrapping the previous condition in paranethesis and starting the new 
     * condition group with an AND. Then in the new condition group match records that are like the key/value pairs. 
     *
     * @return self
    */
    public final function whereAlsoLike(){
        if($this->where){
            $this->where = "{$this->where} AND ALSO ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND',' LIKE ');
        return $this;
    }//where




    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Find records that are not like the key/value pairs.
     *
     * @return self
    */
    public final function whereNotLike(){
        if($this->where){
            $this->where = "{$this->where} AND ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND',' NOT LIKE ');
        return $this;
    }//where



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Starts a new condition group by wrapping the previous condition in paranethesis and starting the new 
     * condition group with an AND. Then in the new condition group match records that are not like the key/value pairs. 
     *
     * @return self
    */
    public final function whereNotAlsoLike(){
        if($this->where){
            $this->where = "{$this->where} AND ALSO ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND',' NOT LIKE ');
        return $this;
    }//where



    /**
     * Prepare a WHERE condition for the query.
     *
     * Find records where a value is in the key/value pairs.
     *
     *
     * @param string $field The field to look in.
     * @param string|array $array A string of comma seperated values, or an array of values.
     *
     * @return self
    */
    public final function whereIn($field,$array){

        $array = $this->buildWhereInArray($array);

        if($this->where){
            $this->where = "{$this->where} AND ";
        }//if

        $this->where .= "{$field} IN ({$array})";

        return $this;

    }//whereIn



    /**
     * Prepare a WHERE IN condition for the query.
     *
     * Find records where a value is not in the key/value pairs.
     *
     *
     * @param string $field The field to look in.
     * @param string|array $array A string of comma seperated values, or an array of values.
     *
     * @return self
    */
    public final function whereNotIn($field,$array){

        $array = $this->buildWhereInArray($array);

        if($this->where){
            $this->where = "{$this->where} AND ";
        }//if

        $this->where .= "{$field} NOT IN ({$array})";

        return $this;

    }//whereNotIn



    /**
     * Prepare a WHERE IN condition for the query.
     *
     * Find records where a value is in the key/value pairs.
     *
     * @param string $field The field to look in.
     * @param string|array $array A string of comma seperated values, or an array of values.
     *
     * @return self
    */
    public final function whereOrIn($field,$array){

        $array = $this->buildWhereInArray($array);

        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if

        $this->where .= "{$field} IN ({$array})";

        return $this;

    }//whereOrIn



    /**
     * Prepare a WHERE IN condition for the query.
     *
     * Find records where a value is not in the key/value pairs.
     *
     * @param string $field The field to look in.
     * @param string|array $array A string of comma seperated values, or an array of values.
     *
     * @return self
    */
    public final function whereOrNotIn($field,$array){

        $array = $this->buildWhereInArray($array);

        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if

        $this->where .= "{$field} NOT IN ({$array})";

        return $this;

    }//whereOrNotIn



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Find records where the key/value pairs match.
     *
     * @return self
    */
    public final function orWhere(){
        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND');
        return $this;
    }//where



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Find records where any of the key/value pairs match.
     *
     * @return self
    */
    public final function orWhereOr(){
        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'OR');
        return $this;
    }//where



    /**
    * Alias of `$this->orWhereOr()`.
    */
    public final function otherwise(){
        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'OR');
        return $this;
    }//otherwise



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Will find records where key/values pairs do not match any conditions.
     *
     * @return self
    */
    public final function orWhereNotOr(){
        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'OR','<>');
        return $this;
    }//where



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Find record where none of the values match the key/value pairs.
     *
     * @return self
    */
    public final function orWhereNot(){
        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND','<>');
        return $this;
    }//where



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Find records that are like the key/value pairs. AKA a regexp.
     *
     * @return self
    */
    public final function orWhereLike(){
        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND',' LIKE ');
        return $this;
    }//where



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     *
     * Find records that are not like the key/value pairs.
     *
     * @return self
    */
    public final function orWhereNotLike(){
        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if
        $this->where .= $this->prepareCondition(func_get_args(),'AND',' NOT LIKE ');
        return $this;
    }//where



    /**
     * Prepare a WHERE IN condition for the query.
     *
     * Find records where a value is in the key/value pairs.
     *
     * @param string $field The field to look in.
     * @param string|array $array A string of comma seperated values, or an array of values.
     *
     * @return self
    */
    public final function orWhereIn($field,$array){

        $array = $this->buildWhereInArray($array);

        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if

        $this->where .= "{$field} IN ({$array})";

        return $this;

    }//orWhereIn



    /**
     * Prepare a WHERE IN condition for the query.
     *
     * Find records where a value is not in the key/value pairs.
     *
     * @param string $field The field to look in.
     * @param string|array $array A string of comma seperated values, or an array of values.
     *
     * @return self
    */
    public final function orWhereNotIn($field,$array){

        $array = $this->buildWhereInArray($array);

        if($this->where){
            $this->where = "{$this->where} OR ";
        }//if

        $this->where .= "{$field} NOT IN ({$array})";

        return $this;

    }//orWhereNotIn



    /**
     * Get a commas delimited string of values for use in an IN query.
     *
     *
     * @param array $array The array of values.
     * @return string
    */
    private final function buildWhereInArray($array){

        if(!is_array($array)){
            return $array;
        }//if

        $values = '';

        foreach($array as $v){
            $v = \App::with('DB')->clean($v);
            $values .= "{$v},";
        }//foreach

        return rtrim($values,',');

    }//buildWhereInArray


    /**
     * Build the final where condition to be used in the query statement.
     *
     *
     * @return string
    */
    private final function buildWhere(){

        $pieces = explode(' AND ALSO ',$this->where);

        if(!isset($pieces[1])){
            return $this->where;
        }//if

        $where = '';

        foreach($pieces as $part){
            $where .= "({$part}) AND ";
        }//foreach

        $where = substr($where,0,strlen($where)-5);

        return $where;

    }//buildWhere



    /**
     * Return an aliased field name.
     *
     * 
     * @param string $k The field name to alias.
     *
     * @return string 
    */
    private function fieldAlias($k){
        if(stripos($k,'.') === false){
            if($this->alias){
                return $this->alias.'.'.$k;
            }//if
            return $this->table.'.'.$k;
        }//if
        return $k;
    }//fieldAlias



    /**
     * Return an aliased table name.
     *
     * 
     * @param string $k The table name to alias.
     *
     * @return string 
    */
    private function tableAlias(){
        if($this->alias){
            return $this->alias;
        }//if
        return $this->table;
    }//tableAlias



    /**
     * Prepare the JOIN condition for the working SELECT query.
     * To join through the ORM you must have a defined Model which extends BaseModel and 
     * has its $table and $ids values set as this will be used to execute the join on. 
     *
     *
     * @param string $modelName The name of the Model you will join on.
     * @param string|array $on The condition used to join on.
     * @param mixed  $data Data that should be bound into the `$on` condition.
     * @param string $joinOn The type of JOIN that should be used, this is hidden from the user.
     *
     * @return self 
    */
    public final function join($modelName,$on=null,$data=null,$joinType='INNER JOIN'){
        $tableAlias = '';
        if(stripos($modelName,' as ') !== false){
            $modelName = explode(' AS ',$modelName);
            $alias = $modelName[1];
            $tableAlias = " AS $alias";
            $modelName = $modelName[0];
        }//if
        else {
            $alias = $modelName;
        }//el

        $joinTable = \App::with($modelName)->table;
        $table = $this->tableAlias();

        $joinType .= " {$joinTable}{$tableAlias} ";

        if($on !== null){
            if(is_array($on)){
                $joinType .= 'ON ' . $this->prepareCondition(Array($on),'AND');
                //foreach($on as $k=>$v){
                //    $joinType .= "ON {$k}={$v} ";
                //}//foreach
            }//if
            else if($data !== null){
                $joinType .= 'ON ' . \App::with('DB')->set($on,$data).' ';
            }//el
            else {
                $joinType .= "ON {$on} ";
            }//el
        }//if
        else {

            $baseIds = (is_array($this->ids)) ? $this->ids : Array($this->ids);
            $ids = \App::with($modelName)->ids;
            $ids = (is_array($ids)) ? $ids : Array($ids);

            $ids = array_intersect($baseIds,$ids);

            $multipleIds = false;
            foreach($ids as $id){
                $joinType.= "ON {$table}.{$id}={$alias}.{$id} AND ";
                $multipleIds = true;
            }//id

            $jl = strlen($joinType);
            if($multipleIds && substr($joinType,$jl-4,$jl)){
                $joinType = substr($joinType,0,$jl-4);
            }//if

        }//el

        $this->joinOn[]=$joinType;
        return $this;
    }//join



    /**
     * Read docs on join function first, this simply extends that function and 
     * passes in a LEFT JOIN as the second arguement.
     *
     * 
     * @param string $modelName The name of the Model you will join on.
     * @param string|array $on The condition used to join on.
     * @param mixed  $data Data that should be bound into the `$on` condition.
     *
     * @return self 
    */
    public final function ljoin($modelName,$on=null,$data=null){
        $this->join($modelName,$on,$data,'LEFT JOIN');
        return $this;
    }//ljoin



    /**
     * Read docs on join function first, this simply extends that function and 
     * passes in a RIGHT JOIN as the second arguement.
     *
     * 
     * @param string $modelName The name of the Model you will join on.
     * @param string|array $on The condition used to join on.
     * @param mixed  $data Data that should be bound into the `$on` condition.
     *
     * @return self 
    */
    public final function rjoin($modelName,$on=null,$data=null){
        $this->join($modelName,$on,$data,'RIGHT JOIN');
        return $this;
    }//rjoin



    /**
     * Set an ORDER BY condition for the current SELECT query.
     * Accepts its parameters through func_get_args(). 
     * Takes strings and numbers.
     *
     * 
     * @param string $param0 The attributes to order by. 
     * @param string $param1 .... 
     *
     * @return self 
    */
    public final function order(){
        $data = func_get_args();

        if(is_array($data[0])){
            $order = Array();
            foreach($data[0] as $k=>$v){
                $order[] = $k.' '.$v;
            }//foreach
        }//if
        else {
            $order = $data;
        }//el

        foreach($data as $k=>$v){
            $data[$k] = $v;
        }//foreach


        $this->order = array_merge($this->order,$order);
        return $this;
    }//order



    /**
     * Set a LIMIT condition on the current SELECT query.
     *
     *
     * @param int $start Starting position of LIMIT or the number of tuples to return contigent upon the 
     * exsistance of the second parameter $limit.
     * @param int $limit The number of tuples to return, default to 0.
     *
     * @return self 
    */
    public final function limit($start,$limit=0){
        $this->limit[]=$start;
        if($limit!=0){
            $this->limit[]=$limit;
        }//if
        return $this;
    }//limit



    /**
     * Return the data from the execution of the previous query.
     *
     * @return mixed Result of last query.
    */
    public final function data(){
        return $this->executeQuery($this->compile());
    }//data



    /**
     * Return the data from the execution of the previous query.
     *
     * @return array
    */
    public final function asArray(){
        return $this->data()->fetchAll();
    }//data



    /**
     * Get the first row of the query.
     *
     *
     * @return array The first row
    */
    public final function first(){
        return $this->data()->fetch();
    }//first



    /**
     * Compile the select query from the conditions.
     *
     *
     * @return string The compiled raw query.
    */
    public final function compile(){
        
        $select = implode(',',array_values($this->select));

        //$where = $this->where;
        $where = $this->buildWhere();

        if($where){
            $where='WHERE '.$where;
        }//if

        $joinOn = implode('',$this->joinOn);

        $order='';
        if(count($this->order)>0){
            $order = implode(',',$this->order);
            $order='ORDER BY '.$order;
        }//if


        $limit='';
        if(count($this->limit)==1)
            $limit = "LIMIT {$this->limit[0]}";
        else if(count($this->limit)==2)
            $limit = "LIMIT {$this->limit[0]},{$this->limit[1]}";


        $alias = '';
        if($this->alias){
            $alias = " AS {$this->alias}";
        }//if

        return "SELECT {$select} FROM {$this->table}{$alias} {$joinOn} {$where} {$order} {$limit}";

    }//compile



    /**
     * Wrapper thats calls the DB service `query` method. Sets `$this->lastQuery` to the passed param. It also 
     * clears the data on the object to clean its state for a new method chain/query.
     *
     * @param string $query The query to execute.
     *
     * @return array
    */
    private final function executeQuery($query){

        $this->lastQuery = $query;
        $this->clearData();
        return \App::with('DB')->query($query); 

    }//executeQuery



    /**
     * Prepare a condition to be used in the query.
     *     For example $data contains:
     *         - 'price'
     *         - '>'
     *         - 59.99
     *
     *     We want to make this a DML statement like: 
     *         - tablename.price>59.99
     *
     *     But we also will likely have another condition that will precede the first one we are passed,
     *     that is where the $conjuction paramater comes into play. 
     *     Its value will be a literal conjuction such as 'AND' or 'OR' or ','
     *
     * This function uses the DB method set() to safely bind the passed variables into the DML statement.
     *
     *
     *
     * @param array $data Pieces of the condition that needs to be prepared.
     * @param string $conjunction The conjuction to be used if more than one condition is present.
     * @param string $comparator The comparator used to form the comparison condition between the key and the value.
     *
     * @return mixed $where Either return the condition or false if there was no condition to prepare.
    */
    public final function prepareCondition($data,$conjunction,$comparator = '='){

        $return = '';

        //array was passed
        if(is_array($data[0])){
            foreach($data[0] as $k=>$v){
                $return .= \App::with('DB')->set($k . $comparator . '?',$v).' '.$conjunction.' ';
            }//foreach
            $return = rtrim($return,$conjunction.' ');
        }//if
        //just a string was passed
        else if(!isset($data[1]) && is_string($data[0])){
            $return = $data[0];
        }//elif
        //a string with ? placeholders was passed as first arg,
        //and an array was passed as second
        else if(is_string($data[0]) && is_array($data[1])){
            $data[0] = explode(',',$data[0]);
            foreach($data[0] as $k=>$v){
                $data[0][$k] = $v;
            }//foreach
            $data[0] = implode(',',$data[0]);
            $return .= \App::with('DB')->set($data[0],(isset($data[1])) ? $data[1] : null);
        }//elif
        //a single value was passed with a string
        else if(!isset($data[2])){
            $return = \App::with('DB')->set($data[0],$data[1]);
        } else {
            $length = count($data);
            for($i = 0; $i < $length; $i = $i + 3){
                $return .= \App::with('DB')->set($data[$i].$data[$i+1].'?',$data[$i+2]).' '.$conjunction.' ';
            }//for
            $return = rtrim($return,$conjunction.' ');
        }//el

        return str_replace('=NULL',' IS NULL',$return);

    }//prepareCondition



    /**
     * Get schema information about the table.
     *
     *
     * @return Array
    */
    public final function about(){
        return \App::with('DB')->query('
            SELECT *                                                                                                                                                                                                       
            FROM information_schema.tables                                                                  
            WHERE table_type="BASE TABLE" AND table_schema="' . \App::config('DB_DB') . '" AND table_name="' . $this->table.'"
        ')->fetch();
    }//about



    /**
     * Get column information about the table.
     *
     *
     * @return Array
    */
    public final function columns(){
        $result = \App::with('DB')->query('SHOW COLUMNS FROM '.$this->table);
        $columns = Array();
        while($row = $result->fetch()){
            $columns[] = $row;
        }//while
        return $columns;
    }//columns



}//Model
?>

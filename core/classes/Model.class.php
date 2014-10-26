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
    private $update=Array();

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
    private $lastQuery;

    /**
     * @var \mysqli_result The last resultSet from a query.
    */
    private $lastResultSet;



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
        $this->lastResultSet=null;
        $this->update=Array();
        //$this->alias = null;
    }//clearData


    public final function alias($k){
        $this->alias = $k;
        $this->aliasWasSet = true;
        return $this;
    }//alias


    /**
     * Prepare a SELECT condition. 
     * Accepts its arguements through func_get_args(). 
     * This function takes as many string arguements as you pass to it.
     *
     *
     * @param string $attr0 An attribute to select from the table in the working query.
     * @param string $attr1 ...
     * @param string $attr2 ...
     *
     * @return self 
     */
    public final function select(){
        $this->clearData();
        $data = func_get_args();
        if(is_array($data[0])){
            foreach($data[0] as $k=>$v){
                $data[0][$k] = $this->fieldAlias($v);
            }//foreach
            $this->select = $data[0];
        }//if
        else if(!isset($data[1])){
            $data[0] = explode(',',$data[0]);
            foreach($data[0] as $k=>$v){
               $data[0][$k] = $this->fieldAlias($v); 
            }//foreach
            $this->select = $data[0];
        }//elif
        else {
            foreach($data as $k=>$v){
                $data[$k] = $this->fieldAlias($v);
            }//foreach
            $this->select = $data;
        }//el
        return $this;
    }//if



    /**
     * Prepare an UPDATE statement.
     * Uses func_get_args() to accept parameters. 
     * Accepts any even number of strings.
     *
     *
     * @param string $attr0 The attribute to update in the working query.
     * @param string $attr1 ....
     *
     * @return self 
    */
    public final function update(){
        $this->clearData();
        $data = func_get_args();


        if(is_array($data[0])){
            //$this->update = array_merge($this->update,$data[0]);
            $this->update = $data[0];
        }//if
        else if(!isset($data[1])){
            $data[0] = explode(',',$data[0]);
            foreach($data[0] as $k=>$v){
               $data[0][$k] = $v; 
            }//foreach
            $this->update = array_merge($this->update,$data[0]);
        }//elif
        else {
            foreach($data as $k=>$v){
                if(!isset($data[$k+1])){
                    break;
                }//if
                $this->update = array_merge($this->update,Array($k=>$v));
            }//foreach
        }//el

        return $this;
    }//update



    /**
     * Execute an INSERT statement.
     * Accepts its arguements through func_get_args(). 
     * Can be an associative array or strings.
     *
     *
     * @param string|array $param0 A attribute or an Assoc Array.
     * @param string $param1 A attribute.
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

        $this->lastQuery = \DB::set($query,$tempValues);
        \DB::query($this->lastQuery);
        return \DB::lastId();

    }//insert



    /**
     * Execute a DELETE statement. 
     * Accepts its arguements thruogh func_get_args(). 
     * Takes strings.
     *
     *
     * @param string $param0 An attribute.
     * @param string $param1 An attribute.
     *
     * @return boolean Whether or not the delete was successful.
    */
    public final function delete(){
        $this->clearData();
        $data = func_get_args();
        if(is_array($data[0])){
            $this->where = '';
            foreach($data[0] as $k=>$v){
                $this->where .= \DB::set("$k=?",$v).' AND ';
            }//foreach
            $this->where = rtrim($this->where,'AND ');
        }//if
        else if(!isset($data[2])){
           $this->where = \DB::set($data[0],$data[1]); 
        }//if
        else {
            $this->where = $this->prepareCondition(func_get_args(),'AND');
        }//el
        $this->lastQuery = "DELETE FROM {$this->table} WHERE {$this->where}";
        return \DB::query($this->lastQuery);
    }//delete



    /**
     * Prepare the WHERE condition for the working query. 
     * Accepts its arguements through func_get_args(). 
     * Takes strings and numbers in pairs of 3 like 
     *     ->where('field','>',20)
     *     ->where('field1','=','somevalue')
     *
     *
     * @param string|float|int $param0 The attribute.
     * @param string|float|int $param1 The condition.
     * @param string|float|int $param2 The value.
     * @param string|float|int $param3 ....
     * @param string|float|int $param4 .... 
     * @param string|float|int $param5 ....
     *
     * @return self
    */
    public final function where(){
        $data = func_get_args();
        if(is_array($data[0])){
            $this->where = '';
            foreach($data[0] as $k=>$v){
                $this->where .= \DB::set($this->fieldAlias($k).'=?',$v).' AND ';
            }//foreach
            $this->where = rtrim($this->where,'AND ');
        }//if
        else if(!isset($data[2])){
            $data[0] = explode(',',$data[0]);
            foreach($data[0] as $k=>$v){
                $data[0][$k] = $this->fieldAlias($v);
            }//foreach
            $data[0] = implode(',',$data[0]);
            $this->where.= \DB::set($data[0],(isset($data[1])) ? $data[1] : null);
        }//if
        else {
            $data = explode(',',$data);
            foreach($data as $k=>$v){
                $data[$k] = $this->fieldAlias($v);
            }//foreach
            $this->where.= $this->prepareCondition($data,'AND');
        }//el

        $this->where = str_replace('=NULL',' IS NULL',$this->where);

        return $this;

    }//where



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
    }//alias



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
    }//private function



    /**
    * Prepare an OR statement for the WHERE condition of the working query.
    * Accepts its arguements through func_get_args().
    * Takes strings and numbers in pairs of 3 like 
    *     ->otherwise('field','>',20)
    *     ->otherwise('field1','=','somevalue')
    *
    *
    * @param string|float|int $param0 The attribute.
    * @param string|float|int $param1 The condition.
    * @param string|float|int $param2 The value.
    * @param string|float|int $param3 ....
    * @param string|float|int $param4 .... 
    * @param string|float|int $param5 ....
    *
    * @return self 
    */
    public final function otherwise(){
        $this->where.= $this->prepareCondition(func_get_args(),'OR');
        return $this;
    }//or 



    /**
     * Prepare the JOIN condition for the working SELECT query.
     * To join through the ORM you must have a defined Model which extends BaseModel and 
     * has its $table and $ids values set as this will be used to execute the join on. 
     *
     *
     * @param string $modelName The name of the Model you will join on.
     * @param string $joinOn    The type of JOIN that should be used, this is hidden from the user.
     *
     * @return self 
    */
    public final function join($modelName,$on=null,$joinType='INNER JOIN'){
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

        $joinTable = \Model::m($modelName)->table;
        $table = $this->tableAlias();

        $joinType .= " {$joinTable}{$tableAlias} ";

        if($on !== null){
            $joinType .= "ON {$on} ";
        }//if
        else {

            $baseIds = (is_array($this->ids)) ? $this->ids : Array($this->ids);
            $ids = \Model::m($modelName)->ids;
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
     * @param $modelName The name of the model you will join on.
     *
     * @return self 
    */
    public final function ljoin($modelName,$on=null){
        $this->join($modelName,$on,'LEFT JOIN');
        return $this;
    }//ljoin



    /**
     * Read docs on join function first, this simply extends that function and 
     * passes in a RIGHT JOIN as the second arguement.
     *
     * 
     * @param $modelName The name of the model you will join on
     *
     * @return self 
    */
    public final function rjoin($modelName,$on=null){
        $this->join($modelName,$on,'RIGHT JOIN');
        return $this;
    }//ljoin


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
                $order[] = $this->fieldAlias($k).' '.$v;
            }//foreach
        }//if
        else {
            $order = $data;
        }//el

        foreach($data as $k=>$v){
            $data[$k] = $this->fieldAlias($v);
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
     * Execute the UPDATE statement that was previously prepared.
     *
     *
     * @return boolean Return whether the update was successful.
    */
    public final function finalize(){

        if(count($this->update)==0){
            throw new \InvalidArgumentException;
        }//if

        $update = '';
        foreach($this->update as $k=>$v){
            $update .= \DB::set("$k=?,",$v);
        }//foreach
        $update = rtrim($update,',');

        $where = $this->where;
        if($where)
            $where='WHERE '.$where;

        $this->lastQuery ="UPDATE {$this->table} SET {$update} {$where}"; 
        return \DB::query($this->lastQuery);

    }//do



    /**
     * Return the data from the execution of the previous query.
     *
     * @return \mysqli_result MySQLi result set of last query.
    */
    public final function data(){
        if($this->lastResultSet)
            return $this->lastResultSet;

        $this->lastResultSet = $this->fetchData();
        if($this->aliasWasSet){
            $this->alias = null;
        }//if
        return $this->lastResultSet;
    }//data



    /**
     * If working with a direct instance of the Model and not 
     * through the Facade then they can invoke the class as a method
     * and get the data return.
     *
     *
     * @return resultSet 
     */
    public function __invoke(){
        return $this->data();
    }//invoke



    /**
     * Execute the previous query and set the returned data.
     *
     *
     * @return \mysqli_result  
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
            $order = implode(',',$this->order);
            $order='ORDER BY '.$order;
        }//if


        $limit='';
        if(count($this->limit)==1)
            $limit = "LIMIT {$this->limit[0]}";
        else if(count($this->limit)==2)
            $limit = "LIMIT {$this->limit[0]},{$this->limit[0]}";


        $alias = '';
        if($this->alias){
            $alias = " AS {$this->alias}";
        }//if
        $this->lastQuery = "SELECT {$select} FROM {$this->table}{$alias} {$joinOn} {$where} {$order} {$limit}";

        \Disco::$app['DB']->query($this->lastQuery); 

        return \Disco::$app['DB']->last();

    }//fetchData



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
     *
     * @return mixed $where Either return the condition or false if there was no condition to prepare.
    */
    private final function prepareCondition($data,$conjunction){
        if(count($data)>0){
            $where='';
            $values=Array();

            for($i=0;$i<count($data);$i+=3){

                if($i>0)
                    $where.=" {$conjunction} ";

                $where.=$this->tableAlias().'.'.$data[$i].$data[$i+1].'?';
                $values[]=$data[$i+2];

            }//for

            $where = \DB::set(rtrim($where,','),$values);

            if($this->where!='')
                $where = " {$conjunction} {$where} ";

            return $where;

        }//count

        return false;

    }//prepareCondition



    /**
     * Get schema information about the table.
     *
     *
     * @return Array
    */
    public final function about(){
        return \DB::query('
            SELECT *                                                                                                                                                                                                       
            FROM information_schema.tables                                                                  
            WHERE table_type="BASE TABLE" AND table_schema="swell" AND table_name="'.$this->table.'"
        ')->fetch_assoc();
    }//about



    /**
     * Get column information about the table.
     *
     *
     * @return Array
    */
    public final function columns(){
        $result = \DB::query('SHOW COLUMNS FROM '.$this->table);
        $columns = Array();
        while($row = $result->fetch_assoc()){
            $columns[] = $row;
        }//while
        return $columns;
    }//columns


}//Model
?>

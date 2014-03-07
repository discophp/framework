<?php


class BaseModel {

    public $table;
    public $ids;

    private $select;
    private $where='';
    private $joinOn=Array();
    private $limit=Array();
    private $order=Array();

    private $lastQuery;

    private $lastResultSet;

    public final function select(){
        $this->where='';
        $this->joinOn=Array();
        $this->limit=Array();
        $this->order=Array();
        $this->lastResultSet=null;
        $this->select=func_get_args();
        return $this;
    }//if

    public final function where($args){
        $this->where.= $this->prepareCondition(func_get_args(),'AND');
        return $this;
    }//where

    public final function otherwise($args){
        $this->where.= $this->prepareCondition(func_get_args(),'OR');
        return $this;
    }//or 

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

    public final function ljoin($modelName){
        $this->join($modelName,'LEFT JOIN');
        return $this;
    }//ljoin

    public final function rjoin($modelName){
        $this->join($modelName,'LEFT JOIN');
        return $this;
    }//ljoin

    public final function order(){
        $this->order = array_merge($this->order,func_get_args());
        return $this;
    }//order

    public final function limit($start,$limit=0){
        $this->limit[]=$start;
        if($limit!=0){
            $this->limit[]=$limit;
        }//if
        return $this;
    }//limit


    public final function on($args){

    }//on

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

    public final function data(){
        if($this->lastResultSet)
            return $this->lastResultSet;

        $this->lastResultSet = $this->fetchData();
        return $this->lastResultSet;
    }//data

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

    public function __invoke(){
        return $this->data();
    }//invoke

}//Model


?>

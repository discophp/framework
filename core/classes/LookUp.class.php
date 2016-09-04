<?php
namespace Disco\classes;

/**
 * Provides the base functionality for lookup classes. All classes that extend this class 
 * must at a minimum set `$this->Model`, `$this->fields`, `$this->searchableFields`. To extend the functionality 
 * of the fetch method override `$this->preFetch()` and `$this->postFetch`.
 *
 */
abstract class LookUp {


    /**
     * @var array $searchableFields The fields that can have a LIKE expression used on them when searching via the 
     * lookup.
    */
    protected $searchableFields = Array();


    /**
     * @var array $fields The fields that the lookup can be used to query against. The keys of the array become 
     * the magic __call methods used to define the comparison which will actually be compared in the models query 
     * to the value of the key. For example `'fieldName' => 'alias.field_name'`, where `fieldName` will be the 
     * method and `alias.field_name` is the actual field in the database.
    */
    protected $fields = Array();


    /**
     * @var string|array $select The fields to be selected when the lookup is performed. If this isn't set at some 
     * point during `__construct()` or `preFetch()` then when `fetch()` is executed the values from `$this->fields` will be 
     * used.
    */
    protected $select;


    /**
     * @var array $callStack Store of method chain calls and arguements.
    */
    protected $callStack;


    /**
     * @var null|int $limit Limit the number of results returned.
    */
    protected $limit;


    /**
     * @var int $page The current page being looked up.
    */
    protected $page;


    /**
     * @var null|string $search Return only results matching this string.
    */
    protected $search;


    /**
     * @var null|string $order Column to order on.
    */
    protected $order;


    /**
     * @var null|int $total The total number of results.
    */
    protected $total;


    /**
     * @var mixed $Model The instance of the model were fetching results from.
    */
    protected $Model;



    /**
     * Optionally push method and arguements onto the call stack.
     *
     * 
     * @param array $stack Method and arguements and key/value pairs.
     *
     * @return void
    */
    public function __construct($stack = Array()){
        if(is_string($this->Model)){
            $this->Model = \App::with($this->Model);
        }//if
        $this->callStack = $stack;
    }//__construct



    /**
     * Magic call method for calling methods that don't exist but are used to set key/value to push onto the call 
     * stack. The call stack is used to determine the where condition of how to look up results.
     *
     *
     * @param string $method The method that doesn't exist on the class.
     * @param mixed $args The arguements passed to the method.
     *
     *
     * @return mixed
    */
    public function __call($method,$args){

        $this->callStack[$method] = $args;

        return $this;

    }//__call



    /**
     * Set up any pre conditions necessary to perform the lookup ie joins, wheres etc.
    */
    protected function preFetch(){ }//preFetch



    /**
     * Responsible for returing the results of the lookup as an array. By default `$this->Model->asArray()` is 
     * called and returned.
     *
     * @return array The array of results of the lookup.
    */
    protected function postFetch(){
        return $this->Model->asArray();
    }//postFetch



    /**
     * Perform the lookup. Calling `$this->preFetch()` to set up any pre conditions (ie joins, where etc). If the 
     * total number of results of the lookup are being requested ie `total()` was called, the total will be 
     * returned. If not the fields to be selected will be set from `$this->select` on the model and the result of 
     * calling `$this->postFetch()` will be returned.
     *
     * @return int|array Either the total results of the lookup as an integer, or an array of results provided by 
     * `$this->postFetch()`.
    */
    public function fetch(){

        $this->preFetch();

        $this->prepareAll();

        if($this->total){
            return $this->Model->select('COUNT(*) AS total')->first()['total'];
        }//if 

        if(!$this->select){
            $this->select = array_values($this->fields);
        }//if

        $this->Model->select($this->select);

        return $this->postFetch();

    }//fetch



    /**
     * Return the first result from a fetch.
     *
     *
     * @return array
    */
    public final function first(){
        $this->limit(1);
        $res = $this->fetch();
        if(isset($res[0])){
            return $res[0];
        }//if
        return null;
    }//first



    /**
     * Return the last result from a fetch.
     *
     *
     * @return array
    */
    public final function last(){
        $res = $this->fetch();
        $len = count($res)-1;
        if(isset($res[$len])){
            return $res[$len];
        }//if
        return null;
    }//last



    /**
     * Set `$this->search` for use in fetching results.
     *
     *
     * @param string $s The string to search for.
     *
     * @return mixed
    */
    public final function search($s){
        if($s){
            $this->search = '%'.$s.'%';
        }//if
        return $this;
    }//search



    /**
     * Set `$this->limit` for use in fetching results.
     *
     *
     * @param int $limit The limit.
     *
     * @return mixed
    */
    public final function limit($limit){
        $this->limit = $limit;
        return $this;
    }//limit



    /**
     * Set `$this->page` for use in fetching results.
     *
     *
     * @param int $page The page to fetch.
     *
     * @return mixed
    */
    public final function page($page){
        $this->page = $page;
        return $this;
    }//page



    /**
     * Set `$this->order` for use in fetching results.
     *
     *
     * @param string $order The column to order the results on.
     *
     * @return mixed
    */
    public final function order($order){
        $this->order = $order;
        return $this;
    }//order



    /**
     * Make it so the total number of results is fetched from the conditions rather than the actual results.
     *
     *
     * @return mixed
    */
    public final function total(){
        $this->total = true;
        $this->limit = false;
        return $this;
    }//total



    /**
     * Prepare the search on the model.
     *
     *
     * @return void
    */
    protected function prepareSearch(){

        if($this->search && count($this->searchableFields)){
            $fields = implode(' LIKE ? OR ',$this->searchableFields);
            $values = array_fill(0, count($this->searchableFields), $this->search);
            $this->Model->where("($fields LIKE ?)",$values);
        }//if

    }//prepareSearch



    /**
     * Prepare the order on the model.
     *
     *
     * @return void
    */
    protected function prepareOrder(){

        if($this->order){
            $order = explode(' ',$this->order);
            $order[0] = $this->resolveFieldAlias($order[0]);
            $order = implode(' ',$order);
            $this->Model->order($order);
        }//if

    }//prepareOrder



    /**
     * Prepare the limit on the model.
     *
     *
     * @return void
    */
    protected function prepareLimit(){

        if($this->limit){
            if($this->page !== null){
                $this->Model->limit(($this->page - 1) * $this->limit ,$this->limit);
            } else {
                $this->Model->limit($this->limit);
            }//el
        }//if

    }//prepareLimit



    /**
     * Build the conditions to fetch results using the `$this->callStack` making sure that each method/attribute 
     * is found in the field list `$this->fields` of attributes.
     *
     *
     * @return void
    */
    protected final function prepareConditions(){

        $first_call = true;
        foreach($this->callStack as $method=>$args){

            $field = $this->resolveFieldAlias($method);

            if(is_array($args[0])){
                $args = $args[0];
            } else if(is_string($args)){
                $args = Array($args);
            }//elif

            foreach($args as $k=>$v){

                if(is_string($v) && (
                        in_array(substr($v,0,2),Array('< ','> ')) ||
                        in_array(substr($v,0,3),Array('<= ','>= ')) ||
                        substr($v,0,2) == '! ' ||
                        substr($v,0,5) == 'LIKE '
                    )
                ){
                    $conditions = explode(' ',$v);
                    $comparator = array_shift($conditions);
                    $value = implode(' ',$conditions);

                    if($comparator == '!'){
                        $comparator = '<>';
                        if($value == 'null'){
                            $comparator = ' IS NOT ';
                        }//if
                    } else if($comparator == 'LIKE'){
                        $comparator = ' LIKE ';
                        $value = '%'. $value . '%';
                    }//elif

                    if($k == 0){
                        //user specified a "> value" or "<= value" clause
                        if(!$first_call){
                            $this->Model->whereAlso($field,$comparator,$value);
                        } else {
                            $this->Model->where($field,$comparator,$value);
                        }//el
                    } else {
                        $this->Model->otherwise($field,$comparator,$value);
                    }//el
                } else {
                    if($k == 0){
                        if(!$first_call){
                            $this->Model->whereAlso(Array($field => $v));
                        } else {
                            $this->Model->where(Array($field => $v));
                        }//el
                    } else {
                        $this->Model->otherwise(Array($field => $v));
                    }//el
                }//el
            }//foreach

            $first_call = false;

        }//foreach    

    }//prepareConditions



    /**
     * Prepare all possible lookup conditions. Calls 
     * - `$this->prepareConditions()`
     * - `$this->prepareSearch()`
     * - `$this->prepareLimit()`
     * - `$this->prepareOrder()`
     * internally.
    */
    protected function prepareAll(){

        $this->prepareConditions();
        $this->prepareSearch();
        $this->prepareLimit();
        $this->prepareOrder();

    }//prepareAll



    /**
     * Get `$this->limit`.
     *
     *
     * @return null|int
    */
    public final function getLimit(){
        return $this->limit;
    }//getLimit



    /**
     * Resolve an aliased field via `$this->fields`.
     *
     *
     * @param string $alias The aliased field.
     * @return string The unaliased field.
     *
     * @throws \Exception The field doesn't exist.
    */
    protected function resolveFieldAlias($alias){

        if(!array_key_exists($alias,$this->fields)){
            throw new \Twig_Error("Lookup query, field `{$alias}` does not exist!");
        }//if

        return $this->fields[$alias];

    }//resolveFieldAlias



}//AbstractLookUp

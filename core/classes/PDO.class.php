<?php
namespace  Disco\classes;

/**
 * Wrapper around the native \PDO class.
 *
 * Uses options set in your application configuration to make connections to the mysql instance.
*/
class PDO extends \PDO {


    /**
     * @var int $transactionCounter The number of transactions open.
    */
    private $transactionCounter = 0;



    /**
     * Connect to the mysql instance. Uses the application configuration keys:
     * - `PDO_ENGINE`
     * - `DB_HOST`
     * - `DB_USER`
     * - `DB_PASSWORD`
     * - `DB_DB`
     * - `DB_CHARSET`
     *
     * or the passed arguents as credentials.
     *
     * Sets: 
     * - `\PDO::ATTR_PERSISTENT = true`
     * - `\PDO::ATTR_DEFAULT_FETCH_MOD = \PDO::FETCH_ASSOC`
     * - `\PDO::ATTR_ERRMODE = \PDO::ERRMODE_EXCEPTION`
     *
     *
     *
     * @param null|string $host The host to connect to.
     * @param null|user $user The user to connect with.
     * @param null|string $pw The password to connect with.
     * @param null|string $db The schema to connect to.
     * @param string $engine The engine type to use, default is `mysql`.
     * @param string $charset The charset to use, default is `utf8`.
     *
     * @throws \Disco\exceptions\DBConnection If the SQL connection fails.
    */
    public function __construct($host=null,$user=null,$pw=null,$db=null,$engine='mysql',$charset='utf8') {

        $app = \App::instance();

        if($host === null){
            $engine     = $app->config['DB_ENGINE'];
            $host       = $app->config['DB_HOST'];
            $user       = $app->config['DB_USER'];
            $pw         = $app->config['DB_PASSWORD'];
            $db         = $app->config['DB_DB'];
            $charset    = $app->config['DB_CHARSET'];
        }//if

        try {

            parent::__construct($engine . ':dbname=' . $db . ';host=' . $host . ';charset=' . $charset, $user, $pw);
            parent::setAttribute(\PDO::ATTR_PERSISTENT, true);
            parent::setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            parent::setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);


        } catch(\PDOException $e){
            TRIGGER_ERROR('DB::Connect Error | '.$e->getMessage(),E_USER_WARNING);
            throw new \Disco\exceptions\DBConnection($e->getMessage(),$e->getCode());
        }//catch

    }//__construct



    /**
     * Turn on foreign key checks (by default on, unless your local mysql is configured with `FOREIGN_KEY_CHECKS=0`).
     *
     *
     * @return void
    */
    public function foreignKeyChecksOn(){
        $this->query('SET FOREIGN_KEY_CHECKS=1');
    }//foreignKeyChecksOn



    /**
     * Turn off foreign key checks.
     *
     *
     * @return void
    */
    public function foreignKeyChecksOff(){
        $this->query('SET FOREIGN_KEY_CHECKS=0');
    }//foreignKeyChecksOff



    /**
     * Returns the depth of transactions occuring, 0 means none, 1 or more is true.
     *
     *
     * @return int The depth of transactions.
    */
    public function inTransaction(){
        return $this->transactionCounter > 0;
    }//inTransaction



    /**
     * Begin a transaction.
     *
     *
     * @return boolean
     */
    public function beginTransaction() {

        if(!$this->transactionCounter++){
            return parent::beginTransaction();
        }//if

        return $this->transactionCounter >= 0;

    }//beginTransaction
    


    /**
     * Commit a transaction.
     *
     *
     * @return boolean
     */
    public function commit() {

        if(!--$this->transactionCounter){
            return parent::commit();
        }//if

        return $this->transactionCounter >= 0;

    }//commit
    


    /**
     * Roll back a transaction.
     *
     *
     * @return boolean
     */
    public function rollback() {

        if($this->transactionCounter > 0) {
            $this->transactionCounter = 0;
            return parent::rollback();
        }//if

        $this->transactionCounter = 0;
        return false;

    }//rollback



    /**
     * Execute a query binding in any passed `$data` and returning the result.
     *
     * 
     * @param string $query The query to execute.
     * @param null|string|array The data to bind into the query.
     *
     * @return mixed
     *
     * @throws \Disco\exceptions\DBQuery
    */
    public function query($query, $data = null){

        try {

            if(!$data){
                return parent::query($query);
            }//if

            $stmt = parent::prepare($query);

            if(!is_array($data)){
                $data = Array($data);
            }//if

            $stmt->execute($data);

            return $stmt;

        } catch(\PDOException $e){
            TRIGGER_ERROR("DB:: Query Error | {$query}\n".$e->getMessage() . "\n" . $e->getTraceAsString(),E_USER_WARNING);
            throw new \Disco\exceptions\DBQuery($e->getMessage(), (int)$e->getCode());
        }//catch

    }//query



    /**
     * Perform an insert statement working just like `$this->query()` but returning the newly generated Auto 
     * Increment ID.
     *
     *
     * @param string $query The query to execute.
     * @param null|string|array The data to bind into the query.
     *
     * @return null|int 
     *
     * @throws \Disco\exceptions\DBQuery
    */
    public function insert($query, $data = null){

        if($this->query($query, $data)){
            return $this->lastId();
        }//if

        return null;

    }//insert



    /**
     * Perform an INSERT statement.
     *
     * @param string $table The name of the table to insert into.
     * @param array $data The data to insert into the table, must be an associative array.
     *
     * @return mixed
     * @throws \Disco\exceptions\DBQuery
    */
    public function create($table, $data){
        $keys = array_keys($data);
        $values = ':' . implode(',:', $keys);
        $keys = implode(',', $keys);
        return $this->query($this->set("INSERT INTO {$table} ({$keys}) VALUES({$values})", $data));
    }//create



    /**
     * Perform a DELETE statement.
     *
     * @param string $table The name of the table to delete from.
     * @param array $data The conditions specifying what rows to delete from the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the delete statement. Default 
     * is `AND`.
     *
     * @return mixed
     * @throws \Disco\exceptions\DBQuery
    */
    public function delete($table, $data, $conjunction = 'AND'){
        $keys = array_keys($data);
        $pairs = Array();
        foreach($keys as $key){
            $pairs[] = "{$key}=:{$key}";
        }//foreach
        $pairs = implode(" {$conjunction} ", $pairs);
        return $this->query($this->set("DELETE FROM {$table} WHERE {$pairs}",$data));
    }//delete



    /**
     * Perform an UPDATE statement .
     *
     * @param string $table The name of the table to update.
     * @param array $data The data to update the table with, must be an associative array.
     * @param array $where The conditions specifying what rows should be updated in the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the update statement. Default 
     * is `AND`.
     *
     * @return mixed
     * @throws \Disco\exceptions\DBQuery
    */
    public function update($table, $data, $where, $conjunction = 'AND'){
        $values = array_merge(array_values($data),array_values($where));
        $keys = array_keys($data);
        $pairs = Array();
        foreach($keys as $key){
            $pairs[] = "{$key}=?";
        }//foreach
        $pairs = implode(',',$pairs);

        $keys = array_keys($where);
        $condition = Array();
        foreach($keys as $key){
            $condition[] = "{$key}=?";
        }//foreach
        $condition = implode(" {$conjunction} ",$condition);

        return $this->query($this->set("UPDATE {$table} SET {$pairs} WHERE {$condition}",$values));

    }//update



    /**
     * Perform a SELECT statement .
     *
     * @param string $table The name of the table to select from.
     * @param string|array $select The fields to select from the table, can be a string of field or an array of 
     * fields.
     * @param array $where The conditions specifying what rows should be selected from the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the select statement. Default 
     * is `AND`.
     *
     * @return mixed
     * @throws \Disco\exceptions\DBQuery
    */
    public function select($table, $select, $where, $conjunction = 'AND'){

        $keys = array_keys($where);
        $pairs = Array();
        foreach($keys as $key){
            $pairs[] = "{$key}=:{$key}";
        }//foreach
        $pairs = implode(" {$conjunction} ",$pairs);

        if(is_array($select)){
            $select = implode(',',$select);
        }//if

        return $this->query($this->set("SELECT {$select} FROM {$table} WHERE {$pairs}",$where));

    }//select



    /**
     * Get the last generated Auto Increment ID from a previous INSERT statement.
     *
     *
     * @return null|int
    */
    public function lastId(){
        return parent::lastInsertId();
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
     *
     * @throws \Disco\exceptions\DBQuery When the number of arguements doesn't match the numebr of `?` 
     * placeholders.
    */
    public function set($q, $args){

        if(is_array($args) && isset($args['raw'])){
            $q = implode($args['raw'],explode('?',$q,2));;
        }//if
        else if(is_array($args)){

            $first = array_keys($args);
            $first = array_shift($first);

            if(!is_numeric($first)){
                return $this->setAssociativeArrayPlaceHolders($q,$args);
            }//if

            return $this->setQuestionMarkPlaceHolders($q,$args);

        }//if
        else {
            $q = implode($this->prepareType($args), explode('?', $q, 2));
        }//el

        return $q;

    }//set



    /**
     * Set assocative array place holders in the query like `:id` with the corresponding value in the args.
     *
     *
     * @param string        $q      The query string.
     * @param string|array  $args   The variables to bind to the $q.
     *
     * @return string               The $q with $args bound into it.
    */
    private function setAssociativeArrayPlaceHolders($q, $args){

        foreach($args as $key => $value){

            if(is_array($value) && isset($value['raw'])){
                $value = $value['raw'];
            }//if
            else {
                $value = $this->prepareType($value);
            }//el

            $positions = Array();
            $p = -1;
            $offset = 1;
            $keyPlaceHolder = ":{$key}";
            $keyLength = strlen($keyPlaceHolder);

            while(($p = strpos($q,$keyPlaceHolder, $p + $offset)) !== false){
                $positions[] = $p;
                $offset = $keyLength;
            }//while

            //reverse the positions so we dont have to keep track in the changes in str length affected by replacing 
            //the placeholders
            $positions = array_reverse($positions);

            foreach($positions as $p){
                $q = substr_replace($q, $value, $p, $keyLength);
            }//foreach

        }//foreach

        return $q;

    }//setAssociativeArrayPlaceHolders



    /**
     * Set `?` mark value placeholders with the values passed in args in the order they are set in the query and 
     * the args.
     *
     * @param string        $q      The query string.
     * @param string|array  $args   The variables to bind to the $q.
     *
     * @return string               The $q with $args bound into it.
     *
     * @throws \Disco\exceptions\DBQuery When the number of arguements doesn't match the numebr of `?` 
     * placeholders.
    */
    private function setQuestionMarkPlaceHolders($q, $args){

        foreach($args as $k=>$a){
            if(is_array($a) && isset($a['raw'])){
                $args[$k] = $a['raw'];
            }//if
            else {
                $args[$k] = $this->prepareType($a);
            }//el
        }//foreach


        $positions = Array();
        $p = -1;
        while(($p = strpos($q, '?', $p + 1)) !== false){
            $positions[] = $p;
        }//while

        if(count($args) != count($positions)){
            throw new \Disco\exceptions\DBQuery('Number of passed arguements does not match the number of `?` placeholders');
        }//if


        //reverse em so when we do replacements we dont have 
        //to keep track of the change in length to positions
        $args = array_reverse($args);
        $positions = array_reverse($positions);

        foreach($positions as $k=>$pos){
            $q = substr_replace($q,$args[$k],$pos,1);
        }//foreach

        return $q;
       
    }//setQuestionMarkPlaceHolders


    /**
     * Determine the type of variable being bound into the query, either a String or Numeric.
     *
     *
     * @param  string|int|float $arg  The variable to prepare.
     *
     * @return string|int|float The $arg prepared.
    */
    private function prepareType($arg){
        if(($arg===null || $arg=='null') && $arg !== 0){
            return 'NULL';
        }//if
        if(!is_numeric($arg)){
            return $this->quote($arg);
        }//if
        return $arg;
    }//prepareType



    /**
     * Wrap a arguement in quotes if need be.
     *
     *
     * @param mixed $arg The arguement to clean
     *
     * @return mixed The cleaned arguement.
    */
    public function clean($arg){
        if(!is_numeric($arg)){
            return $this->quote($arg);
        }//if
        return $arg;
    }//clean



}//PDO

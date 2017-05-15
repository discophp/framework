<?php
namespace  Disco\database;

/**
 * Wrapper around the native \PDO class.
 *
 * Uses options set in your application configuration to make connections to the mysql instance.
*/
class DB extends \PDO {


    /**
     * @var string $rawValueKey The key used in associative arrays to specify that you are binding a raw SQL function. For
     * example using the `NOW()` sql keyword. Which you could specify like : `['__raw' => 'NOW()']`. This prevents the value
     * from being escaped and interpreted as a string value. So be careful because if not careful this could open you up
     * to SQL injection attacks if you manually string build with unescaped values in the raw function.
     */
    public static $rawValueKey = '__raw';


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
     * or the passed arguments as credentials.
     *
     * Sets: 
     * - `\PDO::ATTR_PERSISTENT = true`
     * - `\PDO::ATTR_DEFAULT_FETCH_MOD = \PDO::FETCH_ASSOC`
     * - `\PDO::ATTR_ERRMODE = \PDO::ERRMODE_EXCEPTION`
     *
     *
     *
     * @param null|string $host The host to connect to.
     * @param null|string $user The user to connect with.
     * @param null|string $pw The password to connect with.
     * @param null|string $db The schema to connect to.
     * @param string $engine The engine type to use, default is `mysql`.
     * @param string $charset The charset to use, default is `utf8`.
     *
     * @throws \Disco\exceptions\DBConnection If the SQL connection fails.
    */
    public function __construct($host=null,$user=null,$pw=null,$db=null,$engine='mysql',$charset='utf8') {

        $app = app();

        if($app->configKeyExists('DB_RAW_KEY_VALUE')){
            static::$rawValueKey = $app->config('DB_RAW_KEY_VALUE');
        }

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
     * @return \PDOStatement
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
     * @return \PDOStatement
     * @throws \Disco\exceptions\DBQuery
    */
    public function create($table, $data){
        return $this->query($this->createCompile($table, $data));
    }//create



    /**
     * Compile an INSERT statement.
     *
     * @param string $table The name of the table to insert into.
     * @param array $data The data to insert into the table, must be an associative array.
     *
     * @return string
    */
    public function createCompile($table, $data){
        $keys = array_keys($data);
        $values = ':' . implode(',:', $keys);
        $keys = implode(',', $keys);
        return $this->set("INSERT INTO {$table} ({$keys}) VALUES({$values})", $data);
    }//createCompile



    /**
     * INSERT multiple records that all have the same keys into the same table.
     *
     * USAGE :
     *
     * ->createMultiple('TEST', [
     *       [
     *           'a' => 'aaa',
     *           'b' => 'bbb',
     *           'c' => 'ccc',
     *       ],
     *       [
     *           'a' => '2a',
     *           'b' => '2b',
     *           'c' => '2c',
     *       ],
     *       [
     *           'a' => '3a',
     *           'b' => '3b',
     *           'c' => '3c',
     *       ]
     *   ]);
     *
     *   Will generate a query that looks like : `INSERT INTO TEST (a,b,c) VALUES ('aaa','bbb','ccc'),('2a','2b','2c'),('3a','3b','3c')`
     *
     *
     * @param string $table The name of the table to insert into.
     * @param array[array...] $data An array of associative arrays that all have the same keys and should be
     * inserted.
     *
     * @return \PDOStatement
     * @throws \Disco\exceptions\DBQuery
     *
     */
    public function createMultiple($table, $data){
        $keys = array_keys($data[0]);
        $values = rtrim(str_repeat('(' . rtrim(str_repeat('?,', count($keys)) , ',') . '),', count($data)), ',');
        $all_data = [];
        foreach($data as $d){
            $all_data = array_merge($all_data, array_values($d));
        }
        $keys = implode(',',$keys);
        return $this->query($this->set("INSERT INTO {$table} ({$keys}) VALUES {$values}", $all_data));
    }//createMultiple


    /**
     * Perform a DELETE statement.
     *
     * @param string $table The name of the table to delete from.
     * @param array $data The conditions specifying what rows to delete from the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the delete statement. Default 
     * is `AND`.
     *
     * @return \PDOStatement
     * @throws \Disco\exceptions\DBQuery
    */
    public function delete($table, $data, $conjunction = 'AND'){
        return $this->query($this->deleteCompile($table, $data, $conjunction));
    }//delete



    /**
     * Compile a DELETE statement.
     *
     * @param string $table The name of the table to delete from.
     * @param array $data The conditions specifying what rows to delete from the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the delete statement. Default
     * is `AND`.
     *
     * @return string
    */
    public function deleteCompile($table, $data, $conjunction = 'AND'){
        $keys = array_keys($data);
        $pairs = Array();
        foreach($keys as $key){
            $pairs[] = "{$key}=:{$key}";
        }//foreach
        $pairs = implode(" {$conjunction} ", $pairs);
        return $this->set("DELETE FROM {$table} WHERE {$pairs}",$data);
    }//deleteCompile



    /**
     * Perform an UPDATE statement.
     *
     * @param string $table The name of the table to update.
     * @param array $data The data to update the table with, must be an associative array.
     * @param array $where The conditions specifying what rows should be updated in the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the update statement. Default 
     * is `AND`.
     *
     * @return \PDOStatement
     * @throws \Disco\exceptions\DBQuery
    */
    public function update($table, $data, $where, $conjunction = 'AND'){
        return $this->query($this->updateCompile($table, $data, $where, $conjunction));

    }//update



    /**
     * Compile an UPDATE statement.
     *
     * @param string $table The name of the table to update.
     * @param array $data The data to update the table with, must be an associative array.
     * @param array $where The conditions specifying what rows should be updated in the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the update statement. Default
     * is `AND`.
     *
     * @return string
    */
    public function updateCompile($table, $data, $where, $conjunction = 'AND'){

        $values = array_merge(array_values($data), array_values($where));
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

        return $this->set("UPDATE {$table} SET {$pairs} WHERE {$condition}", $values);

    }//updateCompile



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
     * @return \PDOStatement
     * @throws \Disco\exceptions\DBQuery
    */
    public function select($table, $select, $where, $conjunction = 'AND'){
        return $this->query($this->selectCompile($table, $select, $where, $conjunction));
    }//select



    /**
     * Perform a SELECT statement returning the first row.
     *
     * @param string $table The name of the table to select from.
     * @param string|array $select The fields to select from the table, can be a string of field or an array of
     * fields.
     * @param array $where The conditions specifying what rows should be selected from the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the select statement. Default
     * is `AND`.
     *
     * @return array
     * @throws \Disco\exceptions\DBQuery
    */
    public function selectFirst($table, $select, $where, $conjunction = 'AND'){
        return $this->query($this->selectCompile($table, $select, $where, $conjunction))->fetch();
    }//select



    /**
     * Perform a SELECT statement returning the first field of the first row.
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
    public function selectFirstField($table, $select, $where, $conjunction = 'AND'){
        $alias = explode(' as ', strtolower($select));
        $alias = array_pop($alias);
        return $this->query($this->selectCompile($table, $select, $where, $conjunction))->fetch()[$alias];
    }//select



    /**
     * Compile a SELECT statement .
     *
     * @param string $table The name of the table to select from.
     * @param string|array $select The fields to select from the table, can be a string of field or an array of
     * fields.
     * @param array $where The conditions specifying what rows should be selected from the table, must be an associative array.
     * @param string $conjunction The conjunction used to form the where condition of the select statement. Default
     * is `AND`.
     *
     * @return string
    */
    public function selectCompile($table, $select, $where, $conjunction = 'AND'){

        $keys = array_keys($where);
        $pairs = Array();
        foreach($keys as $key){
            $pairs[] = "{$key}=:{$key}";
        }//foreach
        $pairs = implode(" {$conjunction} ",$pairs);

        if(is_array($select)){
            $select = implode(',',$select);
        }//if

        return $this->set("SELECT {$select} FROM {$table} WHERE {$pairs}",$where);

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
     * @param string $q The query string.
     * @param string|array $args The variables to bind to the $q.
     *
     * @return string The $q with $args bound into it.
     *
     * @throws \Disco\exceptions\DBQuery When the number of arguments doesn't match the number of `?`
     * placeholders.
    */
    public function set($q, $args){

        if(is_array($args) && isset($args[static::$rawValueKey])){
            $q = implode($args[static::$rawValueKey], explode('?',$q,2));;
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
     * Set associative array place holders in the query like `:id` with the corresponding value in the args.
     *
     *
     * @param string $q The query string.
     * @param string|array $args The variables to bind to the $q.
     *
     * @return string The $q with $args bound into it.
    */
    private function setAssociativeArrayPlaceHolders($q, $args){

        foreach($args as $key => $value){

            if(is_array($value) && isset($value[static::$rawValueKey])){
                $value = $value[static::$rawValueKey];
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
     * @param string $q The query string.
     * @param string|array $args The variables to bind to the $q.
     *
     * @return string The $q with $args bound into it.
     *
     * @throws \Disco\exceptions\DBQuery When the number of arguments doesn't match the number of `?`
     * placeholders.
    */
    private function setQuestionMarkPlaceHolders($q, $args){

        foreach($args as $k=>$a){
            if(is_array($a) && isset($a[static::$rawValueKey])){
                $args[$k] = $a[static::$rawValueKey];
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
     * @param string|int|float $arg The variable to prepare.
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
     * Wrap a argument in quotes if need be.
     *
     *
     * @param mixed $arg The argument to clean
     *
     * @return mixed The cleaned argument.
    */
    public function clean($arg){
        if(!is_numeric($arg)){
            return $this->quote($arg);
        }//if
        return $arg;
    }//clean



}//PDO

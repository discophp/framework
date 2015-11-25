<?php
namespace Disco\classes;

/**
 * Abstract Record class. Treats an object as a table row. Use the Disco CLI tool to generate your records. 
 *
 * Records: 
 *      - protects against missing ids
 *      - making sure fields conform to the data type set forth by the table
 *      - Protect against attempting to update fields that don't exist
 *      - only updating changed fields to prevent redundancy and database load
 *
 *
 * Records throw 
 *      - {@link \Disco\exceptions\Record} All record exceptions extend this exception, general exception.
 *      - {@link \Disco\exceptions\RecordValidation} Validation failed.
 *      - {@link \Disco\exceptions\RecordId} Primary Key Ids are missing or null.
*/
abstract class Record implements \ArrayAccess {


    /**
     * @var string $model The model that owns the record.
    */
    protected $model;


    /**
     * @var array $fields The fields of the record.
    */
    protected $fields = Array();


    /**
     * @var array $cache A Cache of the fields of the record.
    */
    private $cache = Array();


    /**
     * @var boolean $allowKeyUpdates Allow primary keys of the record to be updated?
    */
    private $allowKeyUpdates = false;


    /**
     * @var \Respect\Validation\Validator $nullTypeValidator An instance of a nullType validator.
    */
    private static $nullTypeValidator;



    /**
     * Set the intital fields of the record. Performs an array intersection to only take fields that exist on the 
     * model from the passed fields.
     *
     * 
     * @param array $fields Fields of the record.
     * @param boolean $cache Whether to cache the initial passed fields.
     *
     * @return void
    */
    public function __construct($fields = Array(), $cache = false){

        if(is_object($fields)){
            $fields = (array) $fields;
        }//if

        $this->fields = array_intersect_key($fields,array_flip($this->getFieldNames()));

        if($cache === true){
            $this->cache = $this->fields;
        }//if
    }//__construct



    /**
     * Get a field of the record using object syntax.
     *
     *
     * @param string $key The key.
     *
     * @return mixed
    */
    public function __get($key){
        return $this->fields[$key];
    }//__get



    /**
     * Set a field of the record using object syntax.
     *
     *
     * @param string $key The key.
     * @param mixed $value The value.
    */
    public function __set($key,$value){
        $this->fields[$key] = $value;
    }//__set



    /**
     * Determine whether a field exists in the record.
     *
     *
     * @param string $key The key.
     *
     * @return boolean 
    */
    public function __isset($key){
        return isset($this->fields[$key]);
    }//__isset



    /**
     * Delete a field from the record (Set it to null).
     *
     *
     * @param string $key The key.
    */
    public function __unset($key){
        if(isset($this->fields[$key])){
            $this->fields[$key] = null;
        }//if
    }//__unset



    /**
     * Get a field of the record using array access syntax.
     *
     *
     * @param string $key The key.
     *
     * @return mixed
    */
    public function offsetGet($key){
        return $this->offsetExists($key) ? $this->fields[$key] : null;
    }//offsetGet



    /**
     * Set a field of the record using array access syntax.
     *
     *
     * @param string $key The key.
     * @param mixed $value The value.
    */
    public function offsetSet($key,$value){
        $this->fields[$key] = $value;
    }//offsetSet



    /**
     * Determine whether a field exists in the record.
     *
     *
     * @param string $key The key.
     *
     * @return boolean 
    */
    public function offsetExists($key){
        return isset($this->fields[$key]);
    }//offsetExists



    /**
     * Delete a field from the record (Set it to null).
     *
     *
     * @param string $key The key.
    */
    public function offsetUnset($key){
        if($this->offsetExists($key)){
            unset($this->fields[$key]);
        }//if
    }//offsetUnset



    /**
     * Allow primary keys of the record to be updated. This is by default false, and will be made false again after 
     * every update.
     *
     *
     * @param boolean $allow
     * @return void
    */
    public function allowKeyUpdates($allow = true){
        $this->allowKeyUpdates = $allow; 
    }//allowKeyUpdates



    /**
     * Update the changed fields in the record.
     *
     *
     * @return boolean
     *
     * @throws \Disco\exceptions\RecordId When primary keys are null.
    */
    public function update(){

        $this->validateFields();
        $update = $this->diff();

        if(!count($update)){
            return true;
        }//if

        $ids = $this->primaryKeysWithValidation();

        //without ids
        if(!$this->allowKeyUpdates){
            $update = array_diff_key($update,$ids);
            $this->allowKeyUpdates(false);
        }//if

        $res = \App::with($this->model)->update($update)->where($ids)->finalize();

        if($res){
            $this->cache = array_merge($this->cache,$update);
        }//if

        return $res;

    }//update



    /**
     * Delete the record.
     *
     *
     * @return boolean
     *
     * @throws \Disco\exceptions\RecordId When primary keys are null.
    */
    public function delete(){

        $ids = $this->primaryKeysWithValidation();

        return \App::with($this->model)->delete($ids);

    }//delete



    /**
     * Create the record. If the record has an Auto Increment Primary Key it will be set on the record with the 
     * newly created id.
     *
     *
     * @return int
     *
     * @throws \Disco\exceptions\Record When required fields are null.
    */
    public function insert(){

        $this->validateFields();

        $missing = array_diff($this->getRequiredFieldNames(), array_keys($this->fields));
        if(count($missing)){
            $missing = implode(', ',$missing);
            throw new \Disco\exceptions\Record("Record insert error: fields `{$missing}` cannot be null");
        }//if

        $id = \App::with($this->model)->insert($this->fields);

        if($id){

            $ai = $this->autoIncrementField();

            if($ai){
                $this->fields[$ai] = $id;
            }//if

            $this->cache = array_merge($this->cache,$this->fields);

        }//if

        return $id;

    }//insert



    /**
     * Get all the set fields of the record.
     *
     *
     * @return array An assoc array of the fields and their values.
    */
    public function getFields(){
        return $this->fields;
    }//getFields



    /**
     * Get and set all the fields of the record that are not currently set. Must have the primary keys defined.
     *
     *
     * @return boolean|array False if no missing fields. An assoc array of the missing fields and their values otherwise.
     *
     * @throws \Disco\exceptions\RecordId When ids are null.
    */
    public function fetchMissing(){

        $missing = array_diff($this->getFieldNames(), array_keys($this->fields));

        if(count($missing)){

            $ids = $this->primaryKeys();

            if(!count($ids) || in_array(null,array_values($ids))){
                throw new \Disco\exceptions\RecordId("Record cannot fetch missing fields when there is a NULL id.");
            }//if

            $result = \App::with($this->model)
                ->select($missing)
                ->where($ids)
                ->data();

            if($result->rowCount() === 0){
                throw new \Disco\exceptions\RecordNonExistent('Record does not exist : ' . var_export($ids,true));
            } else if($result->rowCount() > 1){
                throw new \Disco\exceptions\Record('Multiple Records returned with ids : ' . var_export($ids,true));
            }//elif

            $fields = $result->fetch();

            $this->cache = array_merge($this->cache,$fields);

            $this->fields = array_merge($this->fields,$fields);

            return $fields;

        }//if

        return false;

    }//fetchMissing



    /**
     * Get and set fields of the record. Must have the primary keys defined.
     *
     *
     * @param null|string|array $fields The fields to fetch and set on the record. If null is passed all fields 
     * will be fetched.
     *
     * @return array An assoc array of the records field and their values.
     *
     * @throws \Disco\exceptions\RecordId When ids are null.
     * @throws \Disco\exceptions\RecordNonExistent When the record doesn't exist.
     * @throws \Disco\exceptions\Record When multiple records are returned.
    */
    public function fetch($fields = Array()){

        $ids = $this->primaryKeysWithValidation();

        $passed_string = false;

        if(is_array($fields) && !count($fields)){
            $fields = $this->getFieldNames();
        } else {

            if(is_string($fields)){
                $passed_string = true;
                $fields = Array($fields);
            }//if

            $diff = array_diff(array_values($fields), $this->getFieldNames());

            if(count($diff)){
                $fields = implode(', ',$diff);
                throw new \Disco\exceptions\RecordValidation("Record field(s) `{$fields}` do not exist!");
            }//if
   
        }//el

        $result = \App::with($this->model)
            ->select($fields)
            ->where($ids)
            ->data();

        if($result->rowCount() === 0){
            throw new \Disco\exceptions\RecordNonExistent('Record does not exist : ' . var_export($ids,true));
        } else if($result->rowCount() > 1){
            throw new \Disco\exceptions\Record('Multiple Records returned with ids : ' . var_export($ids,true));
        }//elif

        $row = $result->fetch();

        $this->cache = array_merge($this->cache,$row);

        $this->fields = array_merge($this->fields,$row);


        //only wanted to fetch single field? return it.
        if($passed_string){
            return $row[$fields[0]];
        }//if

        return $row;

    }//fetch



    /**
     * Get the primary keys and their current values from the fields.
     *
     *
     * @return array The primary keys.
    */
    public function primaryKeys(){

        $ids = \App::with($this->model)->ids;
        if(!is_array($ids)){
            $ids = Array($ids);
        }//if

        $ids = array_intersect_key($this->fields,array_flip($ids));

        return $ids;

    }//primaryKeys



    /**
     * Get the primary keys and their current values from the fields, validating each one. If not all keys are 
     * present but the auto increment key is present and valid return just that.
     *
     *
     * @return array The primary keys.
    */
    public function primaryKeysWithValidation(){

        $ids = $this->primaryKeys();

        if(!count($ids) || in_array(null,array_values($ids))){
            $ai = $this->autoIncrementField();
            if(!$ai || !$this->validate($ai,$this->fields[$ai])){
                throw new \Disco\exceptions\RecordId("Record use/modification attempted with null id(s)!");
            }//if

            return Array($ai => $this->fields[$ai]);
        }//if

        return $ids;

    }//primaryKeysWithValidation



    /**
     * Validate the current fields of the record.
     *
     *
     * @return void
     *
     * @throws \Disco\exceptions\RecordValidation When a field doesn't exist or a field fails validation.
    */
    public function validateFields(){

        $diff = array_diff(array_keys($this->fields), $this->getFieldNames());

        if(count($diff)){
            $fields = implode(', ',$diff);
            throw new \Disco\exceptions\RecordValidation("Record field(s) `{$fields}` do not exist!");
        }//if

        foreach($this->fields as $field => $value){
            if(!$this->validate($field,$value)){
                throw new \Disco\exceptions\RecordValidation("Record validation error - Field : `{$field}` Value : `{$value}`");
            }//if
        }//foreach

    }//validateFields



    /**
     * Get the fields that have changed since the instantiation of the record or the last update or insert.
     *
     *
     * @return array The fields that have changed.
    */
    public function diff(){

        $diff = Array();

        foreach($this->fields as $field => $value){
            if(!isset($this->cache[$field]) || $this->cache[$field] !== $value){
                $diff[$field] = $value;
            }//if
        }//foreach

        return $diff;

    }//diff



    /**
     * Determine whether a record exists based on its current primary keys.
     *
     *
     * @return boolean The record exists
    */
    public function exists(){

        try {
            $ids = $this->primaryKeysWithValidation();
        } catch(\Disco\exceptions\Record $e){
            return false;
        }//catch

        $result = \App::with($this->model)
            ->select(array_keys($ids)[0])
            ->where($ids)
            ->data();

        if($result->rowCount() !== 1){
            return false;
        }//if

        return true;

    }//exists


    /**
     * Check to see if a value is null.
     *
     *
     * @param mixed $v The value.
     *
     * @return boolean
    */
    public static function nullType($v){

        if(!self::$nullTypeValidator){
            self::$nullTypeValidator = \Respect\Validation\Validator::nullType();
        }//if

        return self::$nullTypeValidator->validate($v);

    }//nullType



    /**
     * Check to see if a value is a raw query array.
     *
     * ex: `Array('raw' => 'NOW()')`.
     *
     *
     * @param mixed $v The value.
     *
     * @return boolean
    */
    public static function rawType($v){
        return is_array($v) && count($v) == 1 && isset($v['raw']);
    }//rawType



    /**
     * Find the first record that matches the where condition.
     *
     *
     * @param array $where The conditions used to find the record.
     * @param null|string|array $select The fields to select from the record.
     *
     * @return boolean|mixed Return false if the record wasn't found, otherwise return an instance of the record.
    */
    public static function find($where, $select = null){

        if(!is_array($where)){
            throw new \InvalidArguementException('Paramater 1 `$where` must be an array');
        }//if

        $class = static::class;

        $record = new $class;

        if($select === null){
            $select = $record->getFieldNames();
        } elseif(is_string($select)){
            $select = Array($select);
        }//elif


        $result = \App::with($record->model)
            ->select($select)
            ->where($where)
            ->limit(1)
            ->data();

        if(!$result->rowCount()){
            return false;
        }//if

        return new $class($result->fetch());

    }//find



}//Record

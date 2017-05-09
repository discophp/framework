<?php
namespace Disco\classes;

/**
 * Abstract Record class. Treats an object as a table row (Active Record/ORM). Use the Disco CLI tool to generate your records. 
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
 *      - {@link \Disco\exceptions\RecordNonExistent} When the record doesn't exist.
*/
abstract class Record implements \ArrayAccess {


    /**
     * @var int VALIDATION_STRICT Field types must be supplied in a way that would not cause loss of data when 
     * being inserted.
    */
    const VALIDATION_STRICT = 2;


    /**
     * @var int VALIDATION_EMIT Error will be emitted if inserting will cause data loss.
    */
    const VALIDATION_EMIT = 1;


    /**
     * @var int VALIDATION_LOOSE Columns must only adhere to base type with no regard for whether inserting would 
     * cause data loss.
    */
    const VALIDATION_LOOSE = 0;


    /**
     * @var int $validationLevel The level of validation to apply to columns when performing data manipulation.
    */
    private static $validationLevel = 0;


    /**
     * @var string $model The model that owns the record.
    */
    protected $model;


    /**
     * @var array $fieldDefinitions The fields definitions for the record.
    */
    protected $fieldDefinitions = Array();


    /**
     * @var boolean|string $autoIncrementField The autoincrement field name.
    */
    protected $autoIncrementField = false;


    /**
     * @var array $fields The fields of the record.
    */
    protected $fields = Array();


    /**
     * @var array $cache A Cache of the fields of the record.
    */
    private $cache = Array();


    /**
     * @var boolean|array $cache The primary keys that were present when the record was constructed. Only used when 
     * allowKeyUpdates is set to true and a record is updated and some of the records primary keys have changed. 
     * This prevents the record from attempting to update its self with the new primary key values, which will 
     * result in the updates where statement using conditions pointing to a record that does not exist, or is wrong
     * all together.
    */
    private $initial_primary_keys = false;


    /**
     * @var boolean $allowKeyUpdates Allow primary keys of the record to be updated?
    */
    private $allowKeyUpdates = false;


    /**
     * @var array $numericalRanges Store mins and maxes for SQL column types. Used when validating fields of 
     * records.
    */
    private static $numericalRanges = Array(
        'tinyint' => Array(
            'signed' => Array(
                'min' => -128,
                'max' => 127,
            ),
            'unsigned' => Array(
                'min' => 0,
                'max' => 255,
            ),
        ),
        'smallint' => Array(
            'signed' => Array(
                'min' => -32768,
                'max' => 32767,
            ),
            'unsigned' => Array(
                'min' => 0,
                'max' => 65535,
            ),
        ),
        'mediumint' => Array(
            'signed' => Array(
                'min' => -8388608,
                'max' => 8388607,
            ),
            'unsigned' => Array(
                'min' => 0,
                'max' => 16777215,
            ),
        ),
        'int' => Array(
            'signed' => Array(
                'min' => -2147483648,
                'max' => 2147483647,
            ),
            'unsigned' => Array(
                'min' => 0,
                'max' => 4294967295,
            ),
        ),
        'bigint' => Array(
            'signed' => Array(
                'min' => -9223372036854775808,
                'max' => 9223372036854775807,
            ),
            'unsigned' => Array(
                'min' => 0,
                'max' => 18446744073709551615,
            ),
        ),
        'tinytext'      => 255,
        'text'          => 65535,
        'mediumtext'    => 16777215,
        'longtext'      => 4294967295,
        'tinyblob'      => 255,
        'blob'          => 65535,
        'mediumblob'    => 16777215,
        'longblob'      => 4294967295,

    );


    /**
     * @var array $regexHelpers Helpers for validating date and time.
    */
    private $regexHelpers = Array(
        'date' => '\d{4}-\d{2}-\d{2}',
        'time' => '\d{2}:\d{2}:\d{2}',
    );



    /**
     * Set the initial fields of the record. Performs an array intersection to only take fields that exist on the
     * model from the passed fields.
     *
     * 
     * @param array $fields Fields of the record.
     * @param boolean $cache Whether to cache the initial passed fields.
    */
    public function __construct($fields = Array(), $cache = false){

        if(is_object($fields)){
            $fields = (array) $fields;
        }//if

        $this->fields = array_intersect_key($fields,array_flip($this->getFieldNames()));

        try {
            $this->initial_primary_keys = $this->primaryKeysWithValidation();
        } catch(\Disco\exceptions\RecordId $e){
            $this->initial_primary_keys = false;
        }//catch

        if($cache === true){
            $this->cache = $this->fields;
        }//if

    }//__construct



    /**
     * Set the level of validation applied to record columns, based on one of the class constants:
     * - VALIDATION_LOOSE
     * - VALIDATION_EMIT
     * - VALIDATION_STRICT
     *
     * @param int $lvl The validation level.
    */
    public static function setValidationLevel($lvl){
        if($lvl === self::VALIDATION_LOOSE){
            self::$validationLevel = self::VALIDATION_LOOSE;
        } else if($lvl === self::VALIDATION_EMIT){
            self::$validationLevel = self::VALIDATION_EMIT;
        } else if($lvl === self::VALIDATION_STRICT){
            self::$validationLevel = self::VALIDATION_STRICT;
        }//elif
    }//setValidationLevel



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
     * @param boolean $using_only_auto_increment_key Perform the update using only the auto-increment key, 
     * regardless of other primary keys specified by the record.
     *
     * @return boolean
     *
     * @throws \Disco\exceptions\RecordId When primary keys are null.
     * @throws \Disco\exceptions\RecordValidation When a field fails validation.
    */
    public function update($using_only_auto_increment_key = false){

        $this->validateFields();
        $update = $this->diff();

        if(!count($update)){
            return true;
        }//if

        $ids = $this->primaryKeysWithValidation();

        //without ids
        if(!$this->allowKeyUpdates){
            $update = array_diff_key($update,$ids);
        }//if
        else {
            $initial_keys = $this->initial_primary_keys;
            if($initial_keys === false){
                $class = get_called_class();
                throw new \Disco\exceptions\RecordId("Record `{$class}` cannot update with `allowKeyUpdates` due to initial primary keys missing/not validating during record instantiation");
            }//if
            $this->initial_primary_keys = $ids;
            $ids = $initial_keys;
            $this->allowKeyUpdates(false);
        }//el

        if($using_only_auto_increment_key === true){
            $ai = $this->autoIncrementField();
            if(!$ai){
                $class = get_called_class();
                throw new \Disco\exceptions\RecordId("Record `{$class}` cannot update using only auto increment key as none are defined");
            }//if
            $ids = Array($ai => $ids[$ai]);
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
     * @param boolean $using_only_auto_increment_key Perform the delete using only the auto-increment key, 
     * regardless of other primary keys specified by the record.
     *
     * @return boolean
     *
     * @throws \Disco\exceptions\RecordId When primary keys are null.
    */
    public function delete($using_only_auto_increment_key = false){

        $ids = $this->primaryKeysWithValidation();

        if($using_only_auto_increment_key === true){
            $ai = $this->autoIncrementField();
            if(!$ai){
                $class = get_called_class();
                throw new \Disco\exceptions\RecordId("Record `{$class}` cannot update using only auto increment key as none are defined");
            }//if
            $ids = Array($ai => $ids[$ai]);
        }//if

        return \App::with($this->model)->delete($ids);

    }//delete



    /**
     * Create the record. If the record has an Auto Increment Primary Key it will be set on the record with the 
     * newly created id.
     *
     *
     * @return int
     *
     * @throws \Disco\exceptions\RecordValidation When a field fails validation or is required and missing.
    */
    public function insert(){

        $ai = $this->autoIncrementField();

        if($ai && array_key_exists($ai,$this->fields) && $this->fields[$ai] === null){
            unset($this->fields[$ai]);
        }//if

        $this->validateFields();

        $missing = array_diff($this->getRequiredFieldNames(), array_keys($this->fields));
        if(count($missing)){
            $missing = implode(', ',$missing);
            $class = get_called_class();
            throw new \Disco\exceptions\RecordValidation("Record `{$class}` insert error: fields `{$missing}` cannot be null");
        }//if

        $id = \App::with($this->model)->insert($this->fields);

        if($id){

            if($ai){
                $this->fields[$ai] = $id;
            }//if

            $this->cache = array_merge($this->cache,$this->fields);

        }//if

        return $id;

    }//insert



    /**
     * INSERT or UPDATE the record based on the presence or lack there of the records primary keys.
     *
     * @return boolean|int
     *
     * @throws \Disco\exceptions\Record When required fields are null.
    */
    public function upsert(){

        if(!$this->exists()){
            return $this->insert();
        }//if

        return $this->update();

    }//upsert



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
     * @throws \Disco\exceptions\RecordNonExistent When the record does not exist.
     * @throws \Disco\exceptions\Record When multiple records are returned for the given id(s).
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
     *
     * @throws \Disco\exceptions\RecordId When any of the primary keys are null.
    */
    public function primaryKeysWithValidation(){

        $ids = $this->primaryKeys();

        if(!count($ids) || in_array(null,array_values($ids))){
            $ai = $this->autoIncrementField();
            if(!$ai || !isset($this->fields[$ai]) || !$this->validate($ai,$this->fields[$ai])){
                $class = get_called_class();
                throw new \Disco\exceptions\RecordId("Record `{$class}` use/modification attempted with null id(s)!");
            }//if

            return Array($ai => $this->fields[$ai]);
        }//if

        return $ids;

    }//primaryKeysWithValidation



    /**
     * Validate the current fields of the record.
     *
     * @throws \Disco\exceptions\RecordValidation When a field doesn't exist or a field fails validation.
    */
    public function validateFields(){

        $diff = array_diff(array_keys($this->fields), $this->getFieldNames());

        if(count($diff)){
            $fields = implode(', ',$diff);
            $class = get_called_class();
            throw new \Disco\exceptions\RecordValidation("Record `{$class}` field(s) `{$fields}` do not exist!");
        }//if

        $errors = Array();

        foreach($this->fields as $field => $value){
            if(!$this->validate($field,$value)){
                $errors[$field] = $value;
            }//if
        }//foreach

        if(count($errors)){
            $errorMsg = '';
            foreach($errors as $k => $v){
                $errorMsg .= "`{$k}` : `{$v}` | ";
            }//foreach
            $errorMsg = rtrim($errorMsg,' | ');
            $class = get_called_class();
            throw (new \Disco\exceptions\RecordValidation("Record validation error - {$class} - {$errorMsg}"))->setData($errors);
        }//if

    }//validateFields



    /**
     * Validate a single field of the record.
     *
     * 
     * @param string $key The field name to validate.
     *
     * @return boolean The field passed validation.
    */
    public function validateField($key){
        return $this->validate($key,$this[$key]);
    }//validateField



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
            ->limit(1)
            ->data();

        if(!$result->rowCount()){
            return false;
        }//if

        return true;

    }//exists



    /**
     * Convert a single field, an array of fields, or all fields which are empty strings to null values.
     *
     *
     * @param null|string|array $fields An optional subset of fields or single field to convert.
     *
     * @throws \Disco\exceptions\RecordValidation If a passed field fails to exist.
    */
    public function convertEmptyStringsToNull($fields = Array()){

        if(is_string($fields)){
            $fields = Array($fields);
        }//if

        if(!count($fields)){
            $fields = $this->fields;
        }//if

        foreach($fields as $key => $value){

            if(!isset($this->fields[$key])){
                $class = get_called_class();
                throw new \Disco\exceptions\RecordValidation("{$class}::convertEmptyStringsToNull() - Record field `{$key}` does not exist!");
            }//if

            if($value === ''){
                $this->fields[$key] = null;
            }//if

        }//foreach

    }//convertEmptyStringsToNull



    /**
     * Find the first record that matches the where condition.
     *
     *
     * @param array $where The conditions used to find the record.
     * @param null|string|array $select The fields to select from the record.
     *
     * @return boolean|mixed Return false if the record wasn't found, otherwise return an instance of the record.
     *
     * @throws \InvalidArgumentException If the $where condition is not an array.
    */
    public static function find($where, $select = null){

        $class = get_called_class();

        if(!is_array($where)){
            throw new \InvalidArguementException("{$class}::find() Paramater 1 `\$where` must be an array");
        }//if

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



    /**
     * Find the all record that match the where condition.
     *
     *
     * @param array $where The conditions used to find the records.
     * @param null|string|array $select The fields to select from the records.
     *
     * @return array An array of the records found.
     *
     * @throws \InvalidArgumentException If the $where condition is not an array.
    */
    public static function findAll($where, $select = null){

        $class = get_called_class();

        if(!is_array($where)){
            throw new \InvalidArguementException("{$class}::findAll() Paramater 1 `\$where` must be an array");
        }//if

        $record = new $class;

        if($select === null){
            $select = $record->getFieldNames();
        } elseif(is_string($select)){
            $select = Array($select);
        }//elif

        $result = \App::with($record->model)
            ->select($select)
            ->where($where)
            ->data();

        $records = Array();

        while($row = $result->fetch()){
            $records[] = new $class($row);
        }//while

        return $records;

    }//findAll



    /**
    * Determine if a value is valid for a records particular field.
    *
    *
    * @param string $field The field of the record.
    * @param string $v The value to test against the field.
    *
    * @return boolean Did it validate?
    *
    * @throws \Disco\exceptions\Record Record doesn't have a field named $field.
    */
    public function validate($field, $v){

        if(!array_key_exists($field,$this->fieldDefinitions)){
            throw new \Disco\exceptions\Record("Record validation exception, record does not have a field `{$field}` to validate against");
        }//if

        $definition = $this->fieldDefinitions[$field];

        if($v === null){
            if(!$definition['null']){
                return false;
            }//if
            return true;
        }//if

        //raw type?
        if(is_array($v) && count($v) == 1 && isset($v['raw'])){
            return true;
        }//if

        if(stripos($definition['type'],'int') !== false){

            if(!is_numeric($v)){
                return false;
            }//if

            if(self::$validationLevel !== self::VALIDATION_LOOSE){

                if(array_key_exists('unsigned',$definition) && $definition['unsigned'] === true){
                    $min = self::$numericalRanges[$definition['type']]['unsigned']['min'];
                    $max = self::$numericalRanges[$definition['type']]['unsigned']['max'];
                } else {
                    $min = self::$numericalRanges[$definition['type']]['signed']['min'];
                    $max = self::$numericalRanges[$definition['type']]['signed']['max'];
                }//el

                if($v < $min || $v > $max){

                    if(self::$validationLevel === self::VALIDATION_STRICT){
                        return false;
                    } else {
                        \App::log("Record VALIDATION_EMIT message : field `{$field}` ({$definition['type']}) with value `{$v}` should be between `{$min}` and `{$max}`, data will be truncated upon insert/update to conform");
                    }//el

                }//if

            }//if

            return true;

        }//if
        else if($definition['type'] === 'float' || $definition['type'] === 'double'){

            if(!is_numeric($v)){
                return false;
            }//if

            if(self::$validationLevel !== self::VALIDATION_LOOSE){

                //unsigned must be positive!
                if(array_key_exists('unsigned',$definition) && $definition['unsigned'] === true && $v > 0){
                    if(self::$validationLevel === self::VALIDATION_STRICT){
                        return false;
                    } else {
                        \App::log("Record VALIDATION_EMIT message : field `{$field}` ({$definition['type']} unsigned) with value `{$v}` should be a positive number, data will be truncated upon insert/update to conform");
                    }//el
                }//if

            }//if

            return true;
           
        }//elif
        else if($definition['type'] === 'decimal'){

            if(!is_numeric($v)){
                return false;
            }//if

            if(self::$validationLevel !== self::VALIDATION_LOOSE){

                $max = str_repeat('9',$definition['wholeLength']) . '.' . str_repeat('9',$definition['decimalLength']);
                $min = '-' . $max;

                if(array_key_exists('unsigned',$definition) && $definition['unsigned'] === true){
                    $min = 0;
                }//if

                if($v < $min || $v > $max){

                    if(self::$validationLevel === self::VALIDATION_STRICT){
                        return false;
                    } else {
                        \App::log("Record VALIDATION_EMIT message : field `{$field}` ({$definition['type']}) with value `{$v}` should be between `{$min}` and `{$max}`, data will be truncated upon insert/update to conform");
                    }//el

                }//if

            }//if

            return true;

        }//elif
        else if($definition['type'] === 'varchar' || $definition['type'] === 'char'){

            if(self::$validationLevel !== self::VALIDATION_LOOSE){

                if(strlen($v) > $definition['length']){

                    if(self::$validationLevel === self::VALIDATION_STRICT){
                        return false;
                    } else {
                        \App::log("Record VALIDATION_EMIT message : field `{$field}` ({$definition['type']}) value should not be longer than `{$definition['length']}`, data will be truncated upon insert/update to conform");
                    }//el

                }//if

            }//if

            return true;

        }//if
        else if(stripos($definition['type'],'text') !== false || stripos($definition['type'],'blob') !== false){

            if(self::$validationLevel !== self::VALIDATION_LOOSE){

                $max = self::$numericalRanges[$definition['type']];

                if(strlen($v) > $max){

                    if(self::$validationLevel === self::VALIDATION_STRICT){
                        return false;
                    } else {
                        \App::log("Record VALIDATION_EMIT message : field `{$field}` ({$definition['type']}) value should not be longer than {$max} bytes, data will be truncated upon insert/update to conform");
                    }//el

                }//if

            }//if

            return true;

        }//elif
        else if(stripos($definition['type'],'binary') !== false){

            if(self::$validationLevel !== self::VALIDATION_LOOSE){

                if(strlen($v) > $definition['length']){

                    if(self::$validationLevel === self::VALIDATION_STRICT){
                        return false;
                    } else {
                        \App::log("Record VALIDATION_EMIT message : field `{$field}` ({$definition['type']}) value should not be longer than {$definition['length']} bytes, data will be truncated upon insert/update to conform");
                    }//el

                }//if

            }//if

            return true;

        }//elif
        else if($definition['type'] === 'datetime'){

            if($v instanceof \DateTime){
                $v = $v->format('Y-m-d H:i:s');
            }//if

            if(!preg_match('/^' . $this->regexHelpers['date'] . ' ' . $this->regexHelpers['time'] . '$/',$v)){
                return false;
            }//if

            return true;

        }//elif
        else if($definition['type'] === 'date'){

            if($v instanceof \DateTime){
                $v = $v->format('Y-m-d');
            }//if

            if(!preg_match('/^' . $this->regexHelpers['date'] . '$/',$v)){
                return false;
            }//if

            return true;

        }//elif
        else if($definition['type'] === 'time'){

            if($v instanceof \DateTime){
                $v = $v->format('H:i:s');
            }//if

            if(!preg_match('/^' . $this->regexHelpers['time'] . '$/',$v)){
                return false;
            }//if

            return true;

        }//elif
        else if($definition['type'] === 'year'){

            if($v instanceof \DateTime){
                $v = $v->format('Y');
            }//if

            if(!is_numeric($v) || strlen($v) !== 4){
                return false;
            }//if

            return true;

        }//elif
        else if($definition['type'] === 'timestamp'){

            if($v instanceof \DateTime){
                $v = $v->getTimestamp();
            }//if

            if(!is_numeric($v)){
                return false;
            }//if

            return true;

        }//elif


        return true;

    }//validate



    /**
     * Get the name of the autoincrement field.
    */
    public function autoIncrementField(){
        return $this->autoIncrementField;
    }//autoIncrementField



    /**
     * Get the fields of the record.
     *
     *
     * @return array The fields.
     */
    public function getFieldNames(){
        return array_keys($this->fieldDefinitions);
    }//getFields



    /**
     * Get the required fields of the record (cannot be null) with the exception of the autoincrement field.
     *
     *
     * @return array The fields.
     */
    public function getRequiredFieldNames(){
        $required = Array();
        foreach($this->fieldDefinitions as $field => $definition){
            if(!$definition['null'] && $this->autoIncrementField() != $field){
                $required[] = $field;
            }//if
        }//foreach
        return $required;
    }//getRequiredFieldNames



}//Record

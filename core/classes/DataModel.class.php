<?php
namespace Disco\classes;
/**
 * This file holds the \Disco\classes\DataModel class.
*/

/**
 * The DataModel class is used to ensure the validity and integrity of data. This is done by a defining a definition for each datum 
 * in the data model. The class ensures that any data defined by the definition conforms to its specified 
 * conditions, and when it does not it sets errors specific to each datum which can then be used to communicate 
 * what type of datum should have been passed.
 *
 * Data models are primarly used to verify incoming user data from forms in the browser, but can also be used as 
 * contracts in class communications.
 *
 * The only thing necessary to implement a data model is defining the data models definition in `protected 
 * $definition = Array()`. You would then use it in real code like:
 * ```
 * $dm = new MyDataModel($data);
 * $dm->someField = 'foo';
 * $dm['otherField'] = 'bar';
 * if(!$dm->verify()){
 *      $errors = $dm->getErrors();
 * }
 * ```
 *
 * Notice that data models can be accessed via object sytanx and array access sytax, this is because the data model 
 * class implements `\ArrayAccess` and implements the necessary methods for object access.
*/
class DataModel implements \ArrayAccess {


    /**
     * @var array $definition Definitions of the data this data model contains.
     *
     * A definition is defined by a key, which is the name of the data/field, and an array which defines the 
     * conditions verifying the data/field.
     *
     * Conditions used to define a data/field:
     *      - `type` : `string` (int, uint, float, ufloat, string, boolean, char, array, object, closure)
     *      - `nullable` : `boolean` (optional) Data can be null.
     *      - `truthy` : `boolean` (optional) Data can be a boolean.
     *      - `in` : `array` (optional) Data must be contained in this array.
     *      - `notin` : `array` (optional) Data must NOT be contained in this array.
     *      - `instanceof` : `string` (optional)(only applies to object) Data must be an instance of class.
     *      - `max` : `numeric` (optional)(only applies to int, uint, float, ufloat) Max value of data.
     *      - `min` : `numeric` (optional)(only applies to int, uint, float, ufloat) Min value of data.
     *      - `maxlen` : `int` (optional)(only applies to string) Max length of data.
     *      - `minlen` : `int` (optional)(only applies to string) Min length of data.
     *
     * Conditions that when set will disregard all conditions listed above (aka are mutually exclusive with any 
     * other condition):
     *      - `regexp` : `string` A regular expression that the data must match, can be one of the default matching 
     *      conditions provided by Disco.
     *      - `method` : `string` The name of a method of the class that is passed the value of the data and returns 
     *      a boolean value indicating whether the data is valid.
     *
     * Conditions that aren't mutually exclusive to any data type and can be used to augment a piece of data:
     *      - `required` : `boolean` Whether the data is required in the data model.
     *      - `default` : `mixed` A default value to give the data when its not set, mutually exclusive with 
     *      `required`.
     *      - `premassage` : `string` The name of the method of the class that can massage the data value prior to validation.
     *      - `postmassage` : `string` The name of the method of the calss that can massage the data value after successfull validation.
     *      - `error` : `string` The error message returned when the data doesn't validate. If this is not set 
     *      a custom one will be generated for you based on the conditions.
    */
    protected $definition = Array();


    /**
     * @var array $data The data set in the data model.
    */
    protected $data = Array();


    /**
     * @var array $errors The errors that occured while validating the data.
    */
    protected $errors = Array();


    /**
     * @var string $defaultErrorMessage The default error message used for general data errors. Use `%1\$s` in the 
     * string to get the key of the data the error message is regarding.
    */
    protected $defaultErrorMessage = "Invalid value provided for `%1\$s`";



    /**
     * Set initial data in the data model. Any keys set in the passed data that do not exist in the definition will 
     * be disregarded.
     *
     * @param array $data Initial data to set in the model, will be merged with any values already set in data.
    */
    public function __construct($data = Array()){

        if(is_object($data)){
            $data = (array) $data;
        }//if

        if(count($data)){
            $this->data = array_merge(array_intersect_key($data,array_flip(array_keys($this->definition))));
        }//if

    }//__construct



    /**
     * Verify the data in the data model. If the data model failed to verify use the method `getErrors()` to get 
     * a list of the errors in the model.
     *
     * @return boolean Whether the data in the data model passed verification.
    */
    public final function verify(){

        foreach($this->definition as $k => $v){
            $this->verifyData($k);
        }//foreach

        if(count($this->errors)){
            return false;
        }//if

        return true;

    }//verify



    /**
     * Verify a single piece of data in the model. If the data fails to validate its corresponding error message 
     * will be set in the errors by the same key.
     *
     * @param string $key The key of the data.
     *
     * @return boolean Whether the data passed verification.
     *
     * @throws \Disco\exceptions\Exception When the data model doesn't define the field specified by `$key`.
    */
    public final function verifyData($key){

        unset($this->errors[$key]);

        if(!array_key_exists($key,$this->data)){

            if(!array_key_exists($key,$this->definition)){
                throw new \Disco\exceptions\Exception("Field `{$key}` is not defined by this data model");
            }//if

            //DEFAULT
            if(array_key_exists('default',$this->definition[$key])){
                $this->data[$key] = $this->definition[$key]['default'];
            }//if
            //REQUIRED
            else if($this->getDefinitionValue($key,'required')){
                return $this->setError($key,"`{$key}` is required");
            }//elif
            else {
                return false;
            }//el

        }//if

        //PREMASSAGE
        if(($massage = $this->getDefinitionValue($key,'premassage')) !== false){
            $this->data[$key] = $this->{$massage}($this->data[$key]);
        }//if

        $value = $this->data[$key];

        //REGEXP
        if(($regexp = $this->getDefinitionValue($key,'regexp')) !== false){
            if(($default = \App::getCondition($regexp)) !== false && !\App::matchCondition($default,$value)){
                return $this->setError($key);
            }//if
            if(!preg_match("/{$regexp}/",$value)){
                return $this->setError($key);
            }//if
            $this->_postMassage($key,$value);
            return true;
        }//if

        //METHOD
        if(($method = $this->getDefinitionValue($key,'method')) !== false){
            if(!$this->{$method}($value)){
                return $this->setError($key);
            }//if
            $this->_postMassage($key,$value);
            return true;
        }//if

        //NULLABLE
        if($this->getDefinitionValue($key,'nullable') && $value === null){
            $this->_postMassage($key,$value);
            return true;
        }//if

        $type = $this->getDefinitionValue($key,'type');

        //TRUTHY
        if($type != 'boolean' && $this->getDefinitionValue($key,'truthy') && is_bool($value)){
            $this->_postMassage($key,$value);
            return true;
        }//if

        switch($type){

            case 'int':

                if(!$this->_isValidInt($key,$value)){
                    return false;
                }//if

                break;

            case 'uint':

                if(!$this->_isValidInt($key,$value)){
                    return false;
                }//if

                if($value < 0){
                    return $this->setError($key,"`{$key}` must be a positive integer");
                }//if

                break;

            case 'float':

                if(!$this->_isValidFloat($key,$value)){
                    return false;
                }//if

                break;

            case 'ufloat':

                if(!$this->_isValidFloat($key,$value)){
                    return false;
                }//if

                if($value < 0){
                    return $this->setError($key,"`{$key}` must be a positive number");
                }//if

                break;

            case 'string':

                if(!is_string($value)){
                    return $this->setError($key,"`{$key}` must be a string");
                }//if

                if(($min = $this->getDefinitionValue($key,'minlen')) !== false && strlen($value) < $min){
                    return $this->setError($key,"`{$key}` must be greater than {$min} characters long");
                }//if

                if(($max = $this->getDefinitionValue($key,'maxlen')) !== false && strlen($value) > $max){
                    return $this->setError($key,"`{$key}` must be less than {$max} characters long");
                }//if

                break;

            case 'char':

                if(!is_string($value) || strlen($value) != 1){
                    return $this->setError($key,"`{$key}` must be a single character");
                }//if

                break;

            case 'boolean': 

                if(!is_bool($value)){
                    return $this->setError($key,"`{$key}` must be a boolean value");
                }//if

                break;

            case 'array':

                if(!is_array($value)){
                    return $this->setError($key,"`{$key}` must be an array");
                }//if

                break;

            case 'object':

                if(is_object($value)){
                    if(($instanceof = $this->getDefinitionValue($key,'instanceof')) !== false && !$value instanceof $instanceof){
                        return $this->setError($key,"`{$key}` must be an instance of `{$instanceof}`");
                    }//if
                } //if
                else {
                    return $this->setError($key,"`{$key}` must be an object");
                }//if

                break;

            case 'closure':

                if(!is_object($value) || !$value instanceof \Closure){
                    return $this->setError($key,"`{$key}` must be a closure");
                }//if 

                break;

            default:
                return $this->setError($key);

        }//switch

        //IN
        if(($in = $this->getDefinitionValue($key,'in')) !== false && !in_array($value,$in)){
            $in = implode(', ',$in);
            return $this->setError($key,"`{$key}` must be a value of `{$in}`");
        }//if

        //NOTIN
        if(($in = $this->getDefinitionValue($key,'notin')) !== false && in_array($value,$in)){
            $in = implode(', ',$in);
            return $this->setError($key,"`{$key}` must not be a value of `{$in}`");
        }//if

        $this->_postMassage($key,$value);

    }//verify



    /**
     * Set an error.
     *
     * @param string $key The data key.
     * @param null|string The error message to set, if null, the `defaultErrorMessage` will be used.
     *
     * @return boolean Always returns false, which eases chaining in the `verifyData` method.
    */
    protected final function setError($key,$error = null){
        if($e = $this->getDefinitionValue($key,'error')){
            $error = $e;
        } else if($error === null){
            $error = sprintf($this->defaultErrorMessage,$key);
        }//elif
        $this->errors[$key] = $error;
        return false;
    }//setError



    /**
     * Get an error.
     *
     * @param string $key The data key.
     *
     * @return boolean|string False if no error for `$key`, otherwise the error string.
    */
    public function getError($key){

        if(!isset($this->errors[$key])){
            return false;
        }//if

        return $this->errors[$key];

    }//getError



    /**
     * Get all the errors in the data model. Errors being set are dependent on calling the methods `verify` and 
     * `verifyData`.
     *
     * @return array
    */
    public final function getErrors(){
        return $this->errors;
    }//getErrors



    /**
     * Get all the data models definitions.
     *
     * @return array The entire data model definition.
    */
    public final function getAllDefinitions(){
        return $this->definition;
    }//getAllDefinitions



    /**
     * Get a datums definition.
     *
     * @param string $key The data key.
     *
     * @return array The definition.
    */
    public final function getDefinition($key){
        return $this->definition[$key];
    }//getDefinition



    /**
     * Set a datums definition. Cannot overwrite previously defined definitions.
     *
     * @param string $key The definition key.
     * @param array $value The definition.
     *
     * @throws \Disco\exceptions\Exception When setting the definition would override a frozen data model definition. 
    */
    public final function setDefinition($key,$value){
        if(isset($this->definition[$key])){
            throw new \Disco\exceptions\Exception("Cannot override frozen data model definition `{$key}`");
        }//if
        $this->definition[$key] = $value;
    }//setDefinition



    /**
     * Get a single value that resides within a single data definition.
     *
     * @param string $key The definition key.
     * @param string $condition The definition key child key.
     *
     * @param mixed False when the condition is not set, otherwise the condition value. 
    */
    public final function getDefinitionValue($key,$condition){
        if(!isset($this->definition[$key][$condition])){
            return false;
        }//if 
        return $this->definition[$key][$condition];
    }//getDefinitionValue



    /**
     * Get all the data set in the data model.
     *
     * @return array
    */
    public final function getData(){
        return $this->data;
    }//getData



    /**
     * Get a field of the data model using object syntax.
     *
     *
     * @param string $key The key.
     *
     * @return mixed
    */
    public function __get($key){
        return $this->data[$key];
    }//__get



    /**
     * Set a field of the data model using object syntax.
     *
     *
     * @param string $key The key.
     * @param mixed $value The value.
     *
     * @throws \Disco\exceptions\Exception When the data model doesn't support that field.
    */
    public function __set($key,$value){
        if(!array_key_exists($key,$this->definition)){
            throw new \Disco\exceptions\Exception("Field `{$key}` is not defined by this data model");
        }//if
        $this->data[$key] = $value;
    }//__set



    /**
     * Determine whether a field exists in the data model.
     *
     *
     * @param string $key The key.
     *
     * @return boolean 
    */
    public function __isset($key){
        return isset($this->data[$key]);
    }//__isset



    /**
     * Delete a field from the data model (Set it to null).
     *
     *
     * @param string $key The key.
    */
    public function __unset($key){
        if(isset($this->data[$key])){
            $this->data[$key] = null;
        }//if
    }//__unset



    /**
     * Get a field of the data model using array access syntax.
     *
     *
     * @param string $key The key.
     *
     * @return mixed
    */
    public function offsetGet($key){
        return $this->offsetExists($key) ? $this->data[$key] : null;
    }//offsetGet



    /**
     * Set a field of the data model using array access syntax.
     *
     *
     * @param string $key The key.
     * @param mixed $value The value.
     *
     * @throws \Disco\exceptions\Exception When the data model doesn't support that field.
    */
    public function offsetSet($key,$value){
        if(!array_key_exists($key,$this->definition)){
            throw new \Disco\exceptions\Exception("Field `{$key}` is not defined by this data model");
        }//if
        $this->data[$key] = $value;
    }//offsetSet



    /**
     * Determine whether a field exists in the data model.
     *
     *
     * @param string $key The key.
     *
     * @return boolean 
    */
    public function offsetExists($key){
        return isset($this->data[$key]);
    }//offsetExists



    /**
     * Delete a field from the data model (Set it to null).
     *
     *
     * @param string $key The key.
    */
    public function offsetUnset($key){
        if($this->offsetExists($key)){
            unset($this->data[$key]);
        }//if
    }//offsetUnset



    /**
     * Apply a post massage function to a data value.
     *
     * @param string $key The data key.
     * @param mixed $value The data value.
    */
    protected final function _postMassage($key,$value){

        //POSTMASSAGE
        if(($massage = $this->getDefinitionValue($key,'postmassage')) !== false){
            $this->data[$key] = $this->{$massage}($value);
        }//if

    }//_postMassage



    /**
     * Determine if the value is a valid int.
     *
     * @param string $key The data key.
     * @param mixed $value The data value.
     *
     * @return boolean
    */
    protected final function _isValidInt($key,$value){

        if(!is_numeric($value) || strpos($value,'.') !== false){
            return $this->setError($key,"`{$key}` must be a integer");
        }//if

        if(!$this->_isBetweenMinAndMax($key,$value)){
            return false;
        }//if

        return true;

    }//_isValidInt



    /**
     * Determine if the value is a valid float.
     *
     * @param string $key The data key.
     * @param mixed $value The data value.
     *
     * @return boolean
    */
    protected final function _isValidFloat($key,$value){

        if(!is_numeric($value)){
            return $this->setError($key,"`{$key}` must be numeric");
        }//if

        if(!$this->_isBetweenMinAndMax($key,$value)){
            return false;
        }//if

        return true;

    }//_isValidFloat



    /**
     * Determine if the value is greater than `min` if set, and less than `max` if set.
     *
     * @param string $key The data key.
     * @param mixed $value The data value.
     *
     * @return boolean
    */
    protected final function _isBetweenMinAndMax($key,$value){

        if(($min = $this->getDefinitionValue($key,'min')) !== false && $value < $min){
            return $this->setError($key,"`{$key}` must be greater than {$min}");
        }//if

        if(($max = $this->getDefinitionValue($key,'max')) !== false && $value > $max){
            return $this->setError($key,"`{$key}` must be less than {$max}");
        }//if

        return true;

    }//_isBetweenMinAndMax



}//DataModel

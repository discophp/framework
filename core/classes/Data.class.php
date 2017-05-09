<?php
namespace Disco\classes;
/**
 * This file holds the Data class.
*/


/**
 * Data class.
 * Provides easy wrapper around using HTTP data centric around
 * the RESTful principles PUT,POST,GET,DELETE.
*/
class Data {


    /**
     * @var array Holds the PUT data
     */
    private $putData=Array();


    /**
     * @var array Holds the DELETE data
     */
    private $deleteData=Array();


    /**
     * @var array Type of REST request 
     */
    private $workingDataType;


    /**
     * @var string Where are we parsing input data from
    */
    private $stream;



    /**
     * Construct PUT and DELETE data if the REQUEST_METHOD is PUT | DELETE. 
     *
     *
     * @param string $stream The stream data is passed to PHP via the webserver, default : `php://input`.
     *
     * @return void
    */
    public function __construct($stream='php://input'){

        $this->stream = $stream;

        switch($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $this->setData('POST');
                break;
            case 'PUT':
                $this->setData('PUT');
                break;
            case 'DELETE':
                $this->setData('DELETE');
                break;
        }//switch 

    }//construct



    /**
     * Set data of the selected type from the POST, PUT, or DELETE stream. Will automatically handle parsing incoming JSON
     * and XML data.
     *
     * @param string $type the type of REST request either PUT|DELETE
     * @return void
    */
    private function setData($type){

        $is_json = strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        $is_xml = strpos($_SERVER['CONTENT_TYPE'], 'application/xml') !== false;

        if($type == 'POST' && !$is_json && !$is_xml){
            return;
        }//if

        $stream = file_get_contents($this->stream);

        if($stream){

            if($is_json){
                $vars = json_decode($stream, true);
            } else if($is_xml){
                $vars = \Disco\classes\XML2Array::createArray($stream);
            } else {
                parse_str($stream, $vars);
            }

            if($type == 'POST'){
                $_POST = $vars;
            } 
            else if($type == 'PUT'){
                $this->putData = $vars;
            } 
            else if($type == 'DELETE'){
                $this->deleteData = $vars;
            }

        }//if

    }//setPutData



    /**
     * Determine if the specific $k data matches the found value using the regular expression $v.
     *
     *
     * @param string|array $k The key of the data, or an assoc array of key/condition pairs.
     * @param string|null $v The regex pattern or default matching condition to use.
     *
     * @return string|bool|int|float 
    */
    public function where($k, $v=null){

        $dataType=$this->all();

        if(!is_array($k)){
            $k = Array($k => $v);
        }//if

        foreach($k as $key => $condition){

            if(isset($dataType[$key])){

                if(\App::getCondition($condition)){
                    $condition = \App::getCondition($condition);
                }//if

                if(!preg_match("/{$condition}/",$dataType[$key])){
                    return false;
                }//if

                return $dataType[$key];
            }//if
            else {
                return false;
            }//el

        }//foreach

    }//where



    /**
     * Return a GET variable or if $g==null return $this and set method chain to use GET.
     *
     *
     * @param null|string $key The GET key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function get($key=null){
        return $this->_get('GET', $_GET, $key);
    }//get



    /**
     * Return a POST variable or if $p==null return $this and set method chain to use POST.
     *
     *
     * @param null|string $key The POST key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function post($key=null){
        return $this->_get('POST', $_POST, $key);
    }//post



    /**
     * Return a DELETE variable or if $d==null return $this and set method chain to use DELETE.
     *
     *
     * @param null|string $key The DELETE key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function delete($key=null){
        return $this->_get('DELETE', $this->deleteData, $key);
    }//delete



    /**
     * Return a PUT variable or if $p==null return $this and set method chain to use PUT.
     *
     *
     * @param null|string $key The PUT key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function put($key=null){
        return $this->_get('PUT', $this->putData, $key);
    }//put



    /**
     * SET a selected type of REST variable.
     *
     *
     * @param string $k The key to set the $v with.
     * @param mixed $v The value of $k.
     *
     * @return mixed
     *
     * @throws \Disco\exceptions\Exception
    */
    public function set($k, $v){

        if($this->workingDataType == null){
            throw new \Disco\exceptions\Exception("Cannot set data without first specifying the data type");
        }

        switch($this->workingDataType){
            case 'PUT':
                $this->putData[$k]=$v;
                break;
            case 'DELETE':
                $this->deleteData[$k]=$v;
                break;
            case 'POST':
                $_POST[$k]=$v;
                break;
            case 'GET':
                $_GET[$k]=$v;
                break;
        }//switch

    }//set



    /**
     * REMOVE a selected type of REST variable.
     *
     *
     * @param null|string $k The key to remove.
     *
     * @return void
     *
     * @throws \Disco\exceptions\Exception
    */
    public function remove($k){

        if($this->workingDataType == null){
            throw new \Disco\exceptions\Exception("Cannot remove data without first specifying the data type");
        }

        switch($this->workingDataType){
            case 'PUT':
                unset($this->putData[$k]);
                break;
            case 'DELETE':
                unset($this->deleteData[$k]);
                break;
            case 'POST':
                unset($_POST[$k]);
                break;
            case 'GET':
                unset($_GET[$k]);
                break;
        }//switch

    }//remove



    /**
     * Return all of the selected type of REST data. 
     *
     *
     * @return array
     *
     * @throws \Disco\exceptions\Exception
    */
    public function all(){

        if($this->workingDataType == null){
            throw new \Disco\exceptions\Exception("Cannot get all data without first specifying the data type");
        }

        switch($this->workingDataType){
            case 'PUT':
                return $this->putData;
            case 'DELETE':
                return $this->deleteData;
            case 'POST':
                return $_POST;
            case 'GET':
                return $_GET;
        }//switch

    }//all



    /**
     * Set a cookie. Parameters are identical to http://php.net/manual/en/function.setcookie.php with the exception 
     * that time can be passed as a string or integer, strings are parsed to time using the `strtotime` function, 
     * and integers will always be added the current timestamp like `time() + $time`.
     *
     * @param string $name The name of the cookie.
     * @param mixed $value The value of the cookie. If you pass an array or an object it will be json encoded.
     * @param int|string $time The time to live for the cookie, default is `+30 days`.
     * @param string $path The path on the server which the cookie will be available on.
     * @param string $domain The (sub)domain that the cookie is available to.
     * @param boolean $secure Indicates that the cookie should only be transmitted over a secure HTTPS connection 
     * from the client
     * @param boolean $httponly When TRUE the cookie will be made accessible only through the HTTP protocol. This 
     * means that the cookie won't be accessible by scripting languages, such as JavaScript
     *
     * @return boolean
    */
    public function setCookie($name, $value, $time = '+30 days', $path = '', $domain = '', $secure = false, $httponly = false){

        if(is_int($time)){
            $time = time() + $time;
        } else if(is_string($time)){
            $time = strtotime($time);
        }//elif

        if(is_array($value) || is_object($value)){
            $value = json_encode($value);
        }//if

        return setcookie($name, $value, $time, $path, $domain, $secure, $httponly);

    }//setCookie



    /**
     * Set a raw cookie. Parameters are identical to http://php.net/manual/en/function.setcookie.php with the exception 
     * that time can be passed as a string or integer, strings are parsed to time using the `strtotime` function, 
     * and integers will always be added the current timestamp like `time() + $time`.
     *
     * @param string $name The name of the cookie.
     * @param mixed $value The value of the cookie. If you pass an array or an object it will be json encoded.
     * @param int|string $time The time to live for the cookie, default is `+30 days`.
     * @param string $path The path on the server which the cookie will be available on.
     * @param string $domain The (sub)domain that the cookie is available to.
     * @param boolean $secure Indicates that the cookie should only be transmitted over a secure HTTPS connection 
     * from the client
     * @param boolean $httponly When TRUE the cookie will be made accessible only through the HTTP protocol. This 
     * means that the cookie won't be accessible by scripting languages, such as JavaScript
     *
     * @return boolean
    */
    public function setRawCookie($name, $value, $time = '+30 days', $path = '', $domain = '', $secure = false, $httponly = false){

        if(is_int($time)){
            $time = time() + $time;
        } else if(is_string($time)){
            $time = strtotime($time);
        }//elif

        if(is_array($value) || is_object($value)){
            $value = json_encode($value);
        }//if

        return setrawcookie($name,$value,$time,$path,$domain,$secure,$httponly);

    }//setCookie



    /**
     * Get a cookie value.
     *
     * @param string $name The name of the cookie.
     *
     * @return mixed The value of the cookie, or false if the cookie is not set.
    */
    public function getCookie($name){

        if(array_key_exists($name, $_COOKIE)){
            return $_COOKIE[$name];
        }//if

        return false;

    }//getCookie



    /**
     * Get a complex cookie value (a cookie that is storing a JSON string).
     *
     * @param string $name The name of the cookie.
     *
     * @return mixed The JSON decoded (array) value of the cookie, or false if the cookie is not set.
    */
    public function getComplexCookie($name){

        if(array_key_exists($name, $_COOKIE)){
            return json_decode($_COOKIE[$name],true);
        }//if

        return false;

    }//getCookie



    /**
     * Is a cookie set.
     *
     * @param string $name The name of the cookie.
     *
     * @return mixed The value of the cookie, or false if the cookie is not set.
    */
    public function hasCookie($name){
        return array_key_exists($name, $_COOKIE);
    }//hasCookie



    /**
     * Delete a cookie.
     *
     * @param string $name The name of the cookie to delete.
     *
     * @return boolean
    */
    public function deleteCookie($name){
        if(array_key_exists($name, $_COOKIE)){
            return setcookie($name, $_COOKIE[$name],1);
        }//if
        return true;
    }//deleteCookie



    /**
     * Delete all cookies.
     *
     * @param array $except Delete all cookies except cookies with keys in the array. By default the array contains 
     * `PHPSESSID`, this avoids deleting the cookie that stores the session.
    */
    public function deleteAllCookies($except = Array('PHPSESSID')){
        $keys = array_diff(array_keys($_COOKIE), $except);
        foreach($keys as $k){
            $this->deleteCookie($k);
        }//foreach
    }//deleteAllCookies



    /**
     * Get all the cookies.
     *
     * @return array
    */
    public function getAllCookies(){
        return $_COOKIE;
    }//allCookies



    /**
     * Determine if posted data size exceeds the `max_post_size` ini limit.
     *
     * @return boolean
    */
    public function isPostedDataSizeOverLimit(){
        return $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0;
    }//isPostedDataOverLimit



    /**
     * Determine if posted files data size exceeds the `max_post_size` ini limit.
     *
     * @return boolean
    */
    public function isPostedFileSizeOverLimit(){
        return $this->isPostedDataSizeOverLimit() && empty($_FILES);
    }//isPostedFileSizeOverLimit



    /**
     * Human friendly file size of `post_max_size` ini value.
     *
     * @return string
    */
    public function iniMaxPostSize(){
        return \FileHelper::iniHumanFriendlySize(ini_get('post_max_size'));
    }//iniMaxPostSize



    /**
     * Human friendly file size of `upload_max_filesize` ini value.
     *
     * @return string
    */
    public function iniMaxFileSize(){
        return \FileHelper::iniHumanFriendlySize(ini_get('upload_max_filesize'));
    }//iniMaxFileSize



    /**
     * Get the value of the current CSRF token, if a token doesn't exist, it will be created. When the token is 
     * created it is stored in in the session variable identified by the key `CSRF_TOKEN_NAME` specified in `app/config/config.php`. 
     *
     * @param boolean $reseed Whether to regenerate a new CSRF Token even if one already exists in the session.
     *
     * @return string The CSRF token.
    */
    public function getCSRFToken($reseed = false){

        $token_name = \App::config('CSRF_TOKEN_NAME');

        if(!\Session::has($token_name) || $reseed === true){
            $token = base64_encode( openssl_random_pseudo_bytes(32));
            \Session::set($token_name, $token);
            return $token;
        }//if

        return \Session::get($token_name);

    }//getCSRFToken



    /**
     * Get a hidden input that contains the name and value of the CSRF Token.
     *
     * @return string The input.
    */
    public function getCSRFTokenInput(){
        return \Html::input(Array(
            'value' => $this->getCSRFToken(),
            'type'  => 'hidden',
            'name'  => \App::config('CSRF_TOKEN_NAME')
        ));
    }//getCSRFTokenInput



    /**
     * Validate the CSRF token passed with the request, either via HTTP Headers (where it should be identified 
     * by the header key `HTTP_X_CSRF_TOKEN`) or in the POST, PUT, or DELETE data (where it should be identified by the 
     * key `CSRF_TOKEN_NAME` specified in `app/config/config.php`). 
     *
     * @return boolean Whether the token validated.
    */
    public function validateCSRFToken(){

        if($_SERVER['REQUEST_METHOD'] == 'GET'){
            return true;
        }//if

        $tokenName = \App::config('CSRF_TOKEN_NAME');

        if(!\Session::has($tokenName)){
            return false;
        }//if

        $tokenValue = \Session::get($tokenName);

        switch($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if($this->post($tokenName) === $tokenValue){
                    $this->post()->remove($tokenName);
                    return true;
                }//if
            case 'PUT':
                if($this->put($tokenName) === $tokenValue){
                    $this->put()->remove($tokenName);
                    return true;
                }//if
            case 'DELETE':
                if($this->delete($tokenName) === $tokenValue){
                    $this->delete()->remove($tokenName);
                    return true;
                }//if
        }//switch

        if(isset($_SERVER['HTTP_X_CSRF_TOKEN']) && $tokenValue === $_SERVER['HTTP_X_CSRF_TOKEN']){
            return true;
        }//if

        return false;

    }//validateCSRFToken



    /**
     * @param $type
     * @param $sourceData
     * @param $key
     * @return $this|mixed
     */
    private function _get($type, &$sourceData, $key){

        if($key == null){
            $this->workingDataType = $type;
            return $this;
        }//if

        if(is_array($key)){
            return array_intersect_key($sourceData, array_flip($key));
        }//if

        if(array_key_exists($key, $sourceData)){
            return $sourceData[$key];
        }//if

        return false;

    }


}//Data
?>

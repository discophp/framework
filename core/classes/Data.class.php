<?php
namespace Disco\classes;
/**
 * This file holds the Data class.
*/


/**
 * Data class.
 * Provides easy wrapper around using HTTP data centric around
 * the RESTful priciples PUT,POST,GET,DELETE.
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
            case 'PUT':
                $this->setData('PUT');
                break;
            case 'DELETE':
                $this->setData('DELETE');
                break;
        }//switch 

    }//construct



    /**
     * Set data of the selected type from the PUT or DELETE stream php://input .
     * We don't have to worry about handling GET or POST as Apache pre-parses those into $_GET & $_POST.
     *
     *
     * @param string $type the type of REST request either PUT|DELETE
     * @return void
    */
    private function setData($type){

        $string = file_get_contents($this->stream);

        if($string){
            parse_str($string,$vars);
            if($type=='PUT')
                $this->putData = $vars;
            else
                $this->deleteData = $vars;
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
    public function where($k,$v=null){

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
     * @param null|string $g The GET key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function get($g=null){
        if($g==null){
            $this->workingDataType='GET';
            return $this;
        }//if
        else if(is_array($g)){
            return array_intersect_key($_GET,array_flip($g));
        }//elif
        else if(isset($_GET[$g])){
            return $_GET[$g];
        }//if
        else 
            return false;
    }//get



    /**
     * Return a POST variable or if $p==null return $this and set method chain to use POST.
     *
     *
     * @param null|string $p The POST key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function post($p=null){
        if($p==null){
            $this->workingDataType='POST';
            return $this;
        }//if
        else if(is_array($p)){
            return array_intersect_key($_POST,array_flip($p));
        }//elif
        else if(isset($_POST[$p])){
            return $_POST[$p];
        }//if
        else 
            return false;
    }//post



    /**
     * Return a DELETE variable or if $d==null return $this and set method chain to use DELETE.
     *
     *
     * @param null|string $d The DELETE key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function delete($d=null){
        if($d==null){
            $this->workingDataType='DELETE';
            return $this;
        }//if
        else if(is_array($d)){
            return array_intersect_key($this->deleteData,array_flip($d));
        }//elif
        else if(isset($this->deleteData[$d])){
            return $this->deleteData[$d];
        }//if
        else 
            return false;
    }//delete



    /**
     * Return a PUT variable or if $p==null return $this and set method chain to use PUT.
     *
     *
     * @param null|string $p The PUT key to return.
     *
     * @return self|string|int|float|bool 
    */
    public function put($p=null){
        if($p==null){
            $this->workingDataType='PUT';
            return $this;
        }//if
        else if(is_array($p)){
            return array_intersect_key($this->putData,array_flip($p));
        }//elif
        else if(isset($this->putData[$p])){
            return $this->putData[$p];
        }//if
        else 
            return false;
    }//put



    /**
     * SET a selected type of REST variable.
     *
     *
     * @param null|string $k The key to set the $v with.
     * @param mixed $v The value of $k.
     *
     * @return mixed  
    */
    public function set($k=null,$v){
        if($this->workingDataType==null ||  $k==null)
            return;

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
    */
    public function remove($k){
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
    */
    public function all(){
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



}//Data
?>

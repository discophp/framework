<?php
namespace Disco\manage;
/**
 * This file holds the Manager class.
*/


/**
 *  The Manager Class helps us manage our Disco Application. It primarly helps with getting and setting
 *  data in your configuration files http://github.com/discophp/project/blob/master/.config.php .
 *  It also helps you see what jobs you have waiting in your Queue and the ability to kill them. You can also do 
 *  fun stuff with it like calling methods of your Applications objects via the command line to inspect their 
 *  results.
*/
class Manager {



    /**
     * This function is very important to the functioning of the Queue class.
     * It handles executing jobs that are pushed onto the Queue.
     *
     *
     * @param integer $delay The amount of time to wait before executing the job.
     * @param string|\Jeremeamia\SuperClosure\SerializableClosure $obj The \Closure to call the job on or the 
     * name of the Facade to use.
     * @param string $method The method name to call.
     * @param string $vars The serialized form of the variables.
     * @param string $d A serialized representation of our Application the moment the Job was pushed onto the 
     * Queue.
     *
     * @return void
    */
    public static function resolve($delay=null,$obj,$method,$vars,$domain){

        if($delay!=0){
            sleep($delay);
        }//if

        \App::instance()->domain = $domain;

        $d = unserialize(base64_decode($d));
        $obj = unserialize(base64_decode($obj));

        if($vars=='disco-no-variable'){

            $vars=null;

        }//if
        else {

            $vars = unserialize(base64_decode($vars));

            if(!is_array($vars)){

                if(is_string($vars)){
                    $vars = html_entity_decode($vars);
                }//if

                $vars = Array($vars);

            }//if
            else {

                foreach($vars as $k=>$v){
                    if(is_string($v)){
                        $vars[$k] = html_entity_decode($v);
                    }//if
                }//foreach

            }//el
        }//el

        if($obj instanceof \Jeremeamia\SuperClosure\SerializableClosure){

            if($vars) {
                return call_user_func_array($obj,$vars);
            }//if

            return call_user_func($obj,$vars);

        }//if

        $app = \App::instance();

        return $app->handle($obj,$method,$vars);

    }//resolve



    /**
     * List the jobs that are currently queued on the system.
     *
     *
     * @return void
    */
    public static function jobs(){

        $j = \Queue::jobs();

        if(!count($j)){
            echo 'No Jobs running' . PHP_EOL;
            return;
        }//if

        foreach($j as $job){

            $object = ($job->object instanceof \Jeremeamia\SuperClosure\SerializableClosure) ? 'closure function' : $job->object;

            echo sprintf("
                ----------- JOB # %1\$s --------------
                Time entered: %2\$s
                Delay: %3\$s
                Object: %4\$s
                Method: %5\$s
                Vars: %6\$s

                Kill this job by running: php disco kill-job %1\$s
            ".PHP_EOL.PHP_EOL,$job->pId,$job->time,$job->delay,$object,$job->method,$job->vars);

        }//foreach

    }//jobs



    /**
     * Set or Get the DEV_MODE in `app/config/config.php`.
     *
     *
     * @param null|boolean $mode Whether or not to place into dev mode.
     *
     * @return void
    */
    public static function devMode($mode=null){

        if($mode !== null){
            self::setConfig('DEV_MODE',$mode);
        }//if
        else {
            return self::getConfig('DEV_MODE');
        }//el

    }//devMode



    /**
     * Set or Get the MAINTENANCE_MODE in `app/config/config.php`.
     *
     *
     * @param string|null $mode Should the app be in maintenance mode? 
     *
     * @return void
    */
    public static function maintenanceMode($mode=null){

        if($mode!=null){
            self::setConfig('MAINTENANCE_MODE',$mode);
        }//if
        else {
            return self::getConfig('MAINTENANCE_MODE');
        }//el

    }//appMode



    /**
     * Set a variable in `app/config/config.php`.
     *
     * @param string $find  The variable to change.
     * @param string $value The value to change the variable to.
     *
     * @return void
    */
    public static function setConfig($key,$value){

        $configPath = \App::path() . '/app/config/config.php';

        $config = require $configPath;

        $config[$key] = $value;

        $output = 
"<?php
return %1\$s
;";

        if(!file_put_contents($configPath,sprintf($output,var_export($config,true)))){
            echo "Unable to write to {$configPath}, please fix this and try again.";
        }//if

    }//setConfig



    /**
     * Get a variable in `app/config/config.php`.
     *
     *
     * @param string $find  The variable to find.
     *
     * @return string The value of $find.
    */
    public static function getConfig($find){
        return \App::config($find);
    }//getConfig


    /**
     * Generate a random number between passed $length.
     *
     *
     * @param integer $length The length of the random string.
     *
     * @return string The random $length string.
    */
    public static function genRand($length){

        $max = ceil($length/32);
        $rand = '';
        for($i=0;$i<$max;$i++){
            $rand.=md5(microtime(true).mt_rand(10000,90000));
        }//for
        $rand = substr($rand,0,$length);

        return $rand;

    }//genRand


    /**
     *  Generate a 32 length AES256 Key
     *
     *
     *  @return string 
    */
    public static function genAES256Key(){
        return \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();
    }//genAES256Key



    /**
     *  Set the AES256 Key in .config.php .
     *
     *
     *  @param string $k The key.
     *
     *  @return void 
    */
    public static function setAES256Key($k){
        self::setConfig('AES_KEY256',$k);
    }//setAES256KEY



    /**
     *  Generate a random string of length `$l`.
     *
     *
     *  @param integer $l The length of the string to generate.
     *
     *  @return string 
    */
    public static function genSalt($l){
        return self::genRand($l);
    }//genSalt


    /**
     *  Set the `SHA512_SALT` Key in `app/config/.config.php`.
     *
     *
     *  @param string $s The salt.
     *
     *  @return void 
    */
    public static function setSalt($s){
        self::setConfig('SHA512_SALT',$s);
    }//setSalt



    /**
     * This function generates and sets the `AES_KEY256` and `SHA512_SALT` keys in `app/config/.config.php` after the composer install.
     *
     *
     * @return void
    */
    public static function install(){

        if(!\App::config('AES_KEY256')){
            echo 'Setting `AES_KEY256`...';
            self::setAES256Key(self::genAES256Key());
            echo ' done.'.PHP_EOL;
        }//if

        if(!\App::config('SHA512_SALT')){
            echo 'Setting `SHA512_SALT` with key size 128...';
            self::setSalt(self::genSalt(128));
            echo ' done.'.PHP_EOL;
        }//if

        $dir = \App::path().'/app/template/.cache/';
        if(!file_exists($dir)){
            echo 'Creating app/template/.cache for Twig cached templates ...';
            mkdir($dir);
            echo ' done.'.PHP_EOL;
        }//if
        chmod($dir,0777);

        echo 'Changing permissions on cache folder for phpfastcache';
        chmod(\App::path() . '/cache/',0777);
        echo ' done'.PHP_EOL;


    }//install



    /**
     * Build the guts of a model class.
     *
     *
     * @param string $table The table to build the model from.
     *
     * @return string The guts of the model.
    */
    public static function buildModel($table){

        $db = \App::instance()->config['DB_DB'];
        $result = \DB::query("SHOW KEYS FROM {$db}.{$table} WHERE Key_name='PRIMARY'");
    
        $keys = Array();
        while($row = $result->fetch()){
            $keys[] = $row['Column_name'];
        }//while
    
        if(count($keys)==1){
            $keys = "'".$keys[0]."'";
        }//if
        else {
            $keys = implode("','",$keys);
            $keys = "'".$keys."'";
            $keys = "Array({$keys})";
        }//el
    
        $className = str_replace(' ','',ucwords(str_replace('_',' ',$table)));
    
        $model = 
"
    public \$table = '{$table}';
    public \$ids = {$keys};
";

        return $model;
    
    }//build_model



    /**
     * Write out a model generated by `self::buildModel()`.
     *
     *
     * @param string $table The table name.
     * @param string $model The model markup generated by `self::buildModel()`.
     * @param string $templatePath The path to get the model template from.
     * @param string $outputPath The path to write the model to.
     *
     * @return boolean
    */
    public static function writeModel($table, $model, $templatePath, $outputPath){

        $format = Array(
            'camelTable'    => str_replace(' ','',ucwords(str_replace('_',' ',$table))),
            'table'         => $table,
            'model'         => $model
        );

        $modelOutputPath = \App::path() . '/' . trim($outputPath,'/') . '/' . $format['camelTable'] . '.php';

        if(is_file($modelOutputPath)){
            echo "Model already exists at `{$modelOutputPath}` do you want to overwrite it?" . PHP_EOL;
            if(!\Disco\classes\Console::yesOrNo()){
                return false;
            }//if
        }//if

        $templatePath = \App::path() . '/' . ltrim($templatePath,'/');

        if(!is_file($templatePath)){
            echo "Model template file {$templatePath} doesn't exist." . PHP_EOL;
            return false;
        }//if

        $template = file_get_contents($templatePath);

        foreach($format as $k=>$v){
            $template = str_replace("{{$k}}",$v,$template);
        }//foreach

        if(!file_put_contents($modelOutputPath, $template)){
            echo "Could not write to {$modelOutputPath}, change the permissions or use sudo and try again." . PHP_EOL;
            return false;
        }//if

        echo "Model for table `{$table}` created @ `{$modelOutputPath}`" . PHP_EOL;

        return true;
        
    }//writeModel






    /**
     * Build the guts of a record class.
     *
     *
     * @param string $table The table to build the record from.
     *
     * @return string The guts of the record.
    */
    public static function buildRecord($table){

        $result = \DB::query('SHOW COLUMNS FROM ' . $table);

        $fields = Array();
        $autoIncrement = 'false';

        while($row = $result->fetch()){

            $rule = Array();

            $rule['null'] = ($row['Null'] == 'YES') ? true : false;

            $type = explode('(',$row['Type'])[0];
            $type = explode(' ',$type)[0];

            $rule['type'] = $type;

            if(stripos($row['Type'],'unsigned') !== false){
                $rule['unsigned'] = true;
            }//if

            $length = explode('(',$row['Type']);

            if(isset($length[1])){
                $length = explode(')',$length[1])[0];
                if(stripos($length,',') === false){
                    $rule['length'] = $length;
                } else {
                    $length = explode(',',$length);
                    $rule['decimalLength'] = $length[1];
                    $rule['wholeLength'] = $length[0] - $length[1];
                }//el
            }//if

            if(stripos($row['Extra'],'auto_increment') !== false){
                $autoIncrement = "'{$row['Field']}'";
            }//if

            $fields[$row['Field']] = $rule;

        }//while

        $fields = var_export($fields,true);

        $rules = "

    /**
     * @var null|string \$autoIncrementField The autoincrement field name.
    */
    protected \$fieldDefinitions = {$fields};

    /**
     * @var null|string \$autoIncrementField The autoincrement field name.
    */
    protected \$autoIncrementField = {$autoIncrement};


";

        return $rules;

    }//buildRecord



    /**
     * Write out a record generated by `self::buildRecord()`.
     *
     *
     * @param string $table The table name.
     * @param string $record The record markup generated by `self::buildRecord()`.
     * @param string $templatePath The path to get the record template from.
     * @param string $outputPath The path to write the record to.
     *
     * @return boolean
    */
    public static function writeRecord($table, $record, $templatePath, $outputPath){

        $format = Array(
            'camelTable'    => str_replace(' ','',ucwords(str_replace('_',' ',$table))),
            'table'         => $table,
            'record'        => $record
        );

        $recordOutputPath = \App::path() . '/' . trim($outputPath,'/') . '/' . $format['camelTable'] . '.php';

        if(is_file($recordOutputPath)){
            echo "Record already exists at `{$recordOutputPath}` do you want to overwrite it?" . PHP_EOL;
            if(!\Disco\classes\Console::yesOrNo()){
                return false;
            }//if
        }//if

        $templatePath = \App::path() . '/' . ltrim($templatePath,'/');

        if(!is_file($templatePath)){
            echo "Record template file {$templatePath} doesn't exist." . PHP_EOL;
            return false;
        }//if

        $template = file_get_contents($templatePath);

        foreach($format as $k=>$v){
            $template = str_replace("{{$k}}",$v,$template);
        }//foreach

        if(!file_put_contents($recordOutputPath, $template)){
            echo "Could not write to {$recordOutputPath}, change the permissions or use sudo and try again." . PHP_EOL;
            return false;
        }//if

        echo "Record for table `{$table}` created @ `{$recordOutputPath}`" . PHP_EOL;
        
        return true;

    }//writeRecord



    /**
     * Get the length between paranethesis.
     * ex: int(9) return 9
     *
     *
     * @param string $type A mysql data type.
     *
     * @return mixed Whats between paranethis.
    */
    private static function getLengthBetweenParanthesis($type){

        preg_match('|[a-zA-Z]+\(([0-9]+)\)|',$type,$matches);

        return $matches[1];

    }//getLengthBetweenParanthesis



}//Manager
?>

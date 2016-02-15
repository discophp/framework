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
     * @var array $routes User defined routes.
    */
    public static $routes = Array();

    /**
     * @var string $routerInFile What file is the router being called in?.
    */
    public static $routerInFile;


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
        return self::genRand(32);
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
     *  Generate a random string of length $l .
     *
     *
     *  @param integer $l The length of the string to generate.
     *
     *  @return string 
    */
    public static function genSalt($l){
        return self::genRand($l);
    }//genAES256Key


    /**
     *  Set the SHA512_SALT_LEAD Key in .config.php .
     *
     *
     *  @param string $s The salt.
     *
     *  @return void 
    */
    public static function setSaltLead($s){
        self::setConfig('SHA512_SALT_LEAD',$s);
    }//genAES256Key


    /**
     *  Set the SHA512_SALT_TAIL Key in .config.php .
     *
     *
     *  @param string $s The salt.
     *
     *  @return void 
    */
    public static function setSaltTail($s){
        self::setConfig('SHA512_SALT_TAIL',$s);
    }//genAES256Key



    /**
     * Get all the files of a specified extension in a directory and all its sub-directories.
     *
     *
     * @param string $ext The type of file extensions to look for.
     * @param string $startDir The directory to start the search in.
     * @param Array $initFiles A group of files that has already been found from parent directories.
     *
     * @return Array The files from the directory.
    */
    private static function getFilesRec($ext,$startDir,$initFiles){
        if(($scan = scandir($startDir)) == false){
            return $initFiles; 
        }//if
        unset($scan[0]);unset($scan[1]);

        $files = Array();
        $dirs = Array();
        foreach($scan as $s){
            $testDir = $startDir.'/'.$s;
            if(is_dir($testDir)){
                $dirs[]=$testDir;
            }//if
            else {
                $tExt = substr($s,-strlen($ext));
                if($tExt==$ext){
                    $files[]= $testDir;
                }//elif
            }//if
        }//foreach

        foreach($dirs as $dir){
            $initFiles = array_merge($initFiles,self::getFilesRec($ext,$dir,$files));
        }//foreach

        if(count($dirs)==0){
            $initFiles = array_merge($initFiles,$files);
        }//if

        return $initFiles;

    }//getFilesRev


    /**
     * This function generates and sets the AES256 and SHA512 keys for .config.php after the composer install.
     *
     *
     * @return void
    */
    public static function install(){

        echo 'Setting AES_KEY256...';
        self::setAES256Key(self::genAES256Key());
        echo ' done.'.PHP_EOL;

        echo 'Setting SHA512_SALT_LEAD with key size 12 ...';
        self::setSaltLead(self::genSalt(12));
        echo ' done.'.PHP_EOL;

        echo 'Setting SHA512_SALT_TAIL with key size 12 ...';
        self::setSaltTail(self::genSalt(12));
        echo ' done.'.PHP_EOL;

        echo 'Creating app/template/.cache for Twig cached templates ...';
        $dir = \App::path().'/app/template/.cache/';
        if(!file_exists($dir)){
            mkdir($dir);
        }//if
        chmod($dir,0777);
        echo ' done.'.PHP_EOL;

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

        $modelOutputPath = $outputPath . $format['camelTable'] . '.php';

        if(is_file($modelOutputPath)){
            echo "Model already exists at `{$modelOutputPath}` do you want to overwrite it?" . PHP_EOL;
            if(!disco_console_question()){
                return false;
            }//if
        }//if

        $template = file_get_contents(\App::path() . '/' . $templatePath);

        if(!$template){
            echo "Model template file {$templatePath} doesn't exist." . PHP_EOL;
            return false;
        }//if

        foreach($format as $k=>$v){
            $template = str_replace("{{$k}}",$v,$template);
        }//foreach

        if(!file_put_contents($modelOutputPath, $template)){
            echo "Could not write to {$modelOutputPath}, change the permissions or use sudo and try again." . PHP_EOL;
            return false;
        }//if

        return true;
        
    }//writeModel



    /**
     * Mock a request to the application and swap the \Disco\classes\Router with a \Disco\manage\Router
     * in order to profile all router requests. Also include all defined router files for processing.
     *
     *
     * @param string|null $outputType The type of output the user desires (html,csv)
     * 
     * @return void
    */
    public static function routes($outputType=null){

        self::$routerInFile = 'index.php';

        require('public/index.php');

        $routers = self::getFilesRec('.router.php','app/router',Array());
        foreach($routers as $k=>$v){
            self::$routerInFile = $v;
            $v = basename($v);
            $v = explode('.',$v)[0];
            \Router::useRouter($v);
        }//foreach

        if($outputType == 'csv'){
            self::csv_routes(self::$routes);
        }//if
        else if($outputType == 'html'){
            self::html_routes(self::$routes);

        }//el
        else {
            print_r(self::$routes);
        }//el

    }//routes


    /**
     * Format the provided routes as a csv file.
     *
     *
     * @param Array $routes The found routes
     *
     * @return void
    */
    private static function csv_routes($routes){

        $keys = array_keys($routes[0]);
        $out = implode(',',$keys)."\n";
        foreach($routes as $rowK=>$row){
            if(is_array($row['variables'])){
                $temp = '';
                foreach($row['variables'] as $vk=>$vv){
                    $temp .= $vk.' => '.$vv.' , ';
                }//foreach
                $row['variables'] = rtrim($temp,', ');
            }//if
            $out .= implode(',',$row)."\n";
        }//foreach
        if(file_put_contents('routes.csv',$out)){
            echo 'Routes stored in routes.csv'.PHP_EOL;
        }//if
        else {
            echo 'Could not write routes.csv (use sudo)'.PHP_EOL;
        }//el

    }//csv_routes



    /**
     * Format the provided routes as a html file.
     *
     *
     * @param Array $routes The found routes
     *
     * @return void
    */
    private static function html_routes($routes){

        $keys = array_keys($routes[0]);
        $out = "<table style='width:80%%;margin:0 auto;'><thead style='background-color:#DDD;'><tr>%1\$s</tr></thead><tbody>%2\$s</tbody></table>";
        $head = '';
        $body = '';
        foreach($keys as $k){
            $head .= "<td>$k</td>";
        }//foreach
        foreach($routes as $rowK=>$row){
            if(is_array($row['variables'])){
                $temp = '';
                foreach($row['variables'] as $vk=>$vv){
                    $temp .= $vk.' => '.$vv.' , ';
                }//foreach
                $row['variables'] = rtrim($temp,', ');
            }//if
            $body .= '<tr>';
            foreach($row as $k=>$v){
                $v = ($v!='' && $v!=' ') ? $v : '&nbsp;';
                $body .= "<td style='padding-top:4px;padding-bottom:4px;border-bottom:1px solid #DDD;'>{$v}</td>";        
            }//foreach
            $body .= '</tr>';
        }//foreach
        $out = sprintf($out,$head,$body);
        if(file_put_contents('routes.html',$out)){
            echo 'Routes stored in routes.html'.PHP_EOL;
        }//if
        else {
            echo 'Could not write routes.html (use sudo)'.PHP_EOL;
        }//el

    }//html_routes





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

        $rules = '';
        $validators = '';

        $map = Array(
            'int'   => function($type){

                $rule = 'intType()';
                $length = \Disco\manage\Manager::getLengthBetweenParanthesis($type); 
                if($length){
                    $length = str_repeat('9',$length);
                    $rule .= "->between(0,{$length})";
                }//if

                return $rule;
            },
            'char' => function($type){
                $rule = 'stringType()';
                $length = \Disco\manage\Manager::getLengthBetweenParanthesis($type); 
                if($length){
                    $rule .= "->length(0,{$length})";
                }//if

                return $rule;

            },
            'datetime' => function($type){
                return 'date()';
            },
            'date' => function($type){
                return 'date()';
            },
            'time' => function($type){
                return 'date()';
            },
            'year' => function($type){
                $length = \Disco\manage\Manager::getLengthBetweenParanthesis($type); 
                if(!$length){
                    $length = 4;
                }//if
                return "stringType()->length({$length})";
            },
            'text' => function($type){
                return 'alwaysValid()';
            },
            'blob' => function($type){
                return 'alwaysValid()';
            },
            'binary' => function($type){
                return 'alwaysValid()';
            }


        );


        $fields = '';
        $required = '';
        $autoIncrement = 'false';

        while($row = $result->fetch()){

            $fields .= "
                '{$row['Field']}',";

            $rule = '';

            foreach($map as $type=>$closure){
                if(stripos($row['Type'],$type) !== false){
                    $rule = $closure($row['Type']);
                    break;
                }//if
            }//foreach

            $orNull = '';
            if($row['Null'] == 'YES' || stripos($row['Extra'],'auto_increment') !== false){
                $orNull = ' || parent::nullType($v)';
            } else {
                $required .= "
                    '{$row['Field']}',";
            }//el

            if(stripos($row['Extra'],'auto_increment') !== false){
                $autoIncrement = "'{$row['Field']}'";
            }//if

            $validators .= "
                '{$row['Field']}' => \Respect\Validation\Validator::{$rule},
            "; 

            $rules .= "
                case '{$row['Field']}' : 
                    return self::\$validators['{$row['Field']}']->validate(\$v){$orNull} || parent::rawType(\$v);
            "; 

        }//while

        $fields     = rtrim($fields,',');
        $required   = rtrim($required,',');
        $rules      = rtrim($rules,',');

        $rules = "
    /**
    * @var Array \$validators The validation rules for the fields.
    */
    public static \$validators = null;


    public static function autoIncrementField(){
        return {$autoIncrement};
    }//autoIncrementField


    /**
     * Get the fields of the record.
     *
     *
     * @return array The fields.
     */
    public static function fieldNames(){
        return Array(
    {$fields}
        );
    }//fields



    /**
     * Get the fields of the record. (Wrapper for `self::fields()`).
     *
     *
     * @return array The fields.
     */
    public function getFieldNames(){
        return self::fieldNames();
    }//getFields



    /**
     * Get the required fields of the record (cannot be NULL).
     *
     *
     * @return array The fields.
     */
    public static function requiredFieldNames(){
        return Array(
    {$required}
        );
    }//requiredFields



    /**
     * Get the required fields of the record. (Wrapper for `self::requiredFields()`).
     *
     *
     * @return array The fields.
     */
    public function getRequiredFieldNames(){
        return self::requiredFieldNames();
    }//getRequiredFields



    /**
    * Register the records validators into `self::\$validators`.
    *
    *
    * @return void
    */
    public static function registerValidators(){
        if(!self::\$validators){
            self::\$validators = Array(
    {$validators}
            );
        }//if
    }//__registerValidators



    /**
    * Determine if a value is valid for a records particular field.
    *
    *
    * @param string \$field The field of the record.
    * @param string \$v The value to test against the field.
    *
    * @return boolean Did it validate?
    */
    public static function validate(\$field, \$v){
        self::registerValidators();

        if(is_numeric(\$v)){
            if(stripos(\$v,'.') !== false){
                \$v = (float)\$v;
            } else {
                \$v = (int)\$v;
            }//el
        }//if

        switch(\$field){
{$rules}
                default:
                    throw new \Disco\\exceptions\Record(\"Record validation exception, record does not have a field `{\$field}` to validate against\");
        }//switch
    }//validate
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

        $recordOutputPath = $outputPath . $format['camelTable'] . '.php';

        if(is_file($recordOutputPath)){
            echo "Record already exists at `{$recordOutputPath}` do you want to overwrite it?" . PHP_EOL;
            if(!disco_console_question()){
                return false;
            }//if
        }//if

        $template = file_get_contents(\App::path() . '/' . $templatePath);

        if(!$template){
            echo "Record template file {$templatePath} doesn't exist." . PHP_EOL;
            return false;
        }//if

        foreach($format as $k=>$v){
            $template = str_replace("{{$k}}",$v,$template);
        }//foreach

        if(!file_put_contents($recordOutputPath, $template)){
            echo "Could not write to {$recordOutputPath}, change the permissions or use sudo and try again." . PHP_EOL;
            return false;
        }//if
        
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

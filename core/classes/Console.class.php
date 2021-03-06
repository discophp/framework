<?php
namespace Disco\classes;
/**
 * This file holds the Console class.
*/


/**
 * Bridge for executing CLI requests.
 *
 * Its used by calling `public/index.php` via the CLI and passing an arguement(s). Each method is actually 
 * a command eg `php public/index.php with Crypt hash 'kitty kat'`.
 *
 * Extend it by calling `\Disco\classes\Console::extendConsoleWithClass(new \YourClassName)` prior to setting up 
 * the framework in `public/index.php`. Each class that extends this class (the Console) will have its public 
 * methods made available for execution directly from the CLI. For example if you extended this class with a class 
 * that had a public method `doOurReallyBigJob` that method can be executed by calling 
 * `php public/index.php doOurReallyBigJob` or even `php public/index.php do-our-really-big-job`. Any arguments 
 * that come after the first parameter will be passed to the method in an indexed array. So for example you call
 * `php public/index.php doOurReallyBigJob 1552 test` the method `doOurReallyBigJob` will be passed an array as the 
 * first argument that will contain these values `Array(1552,'test')`.
*/
class Console {


    /**
     * @var array $extendedCommands Holds classes that extend the functionality of the Console.
    */
    private static $extendedCommands = Array();



    /**
     * Extend the default console commands with other classes.
     *
     * @param \StdClass $class The class to extend the console functionality with.
    */
    public static function extendConsoleWithClass($class){
        static::$extendedCommands[] = $class;
    }//extendConsoleWithClass


    /**
     * Call the appropriate method of the class based on the arguements passed to the CLI.
     *
     * Passes all arguemnts to the appropriate method via an array.
    */
    public function __construct(){

        global $argv;

        if(!isset($argv[1])){
            echo 'You must supply a command!' . PHP_EOL;
            exit;
        }//if

        $args = $argv;

        $scriptName = array_shift($args);
        $method = array_shift($args);

        $method = explode('-',$method);
        foreach($method as $k => $v){
            if($k != 0){
                $method[$k] = ucwords($v);
            }//if
        }//foreach

        $method = implode('',$method);

        $resolved = false;

        if(method_exists($this,$method)){
            call_user_func(Array($this,$method), $args);
            $resolved = true;
        }//if
        else {
            foreach(static::$extendedCommands as $commandClass){
                if(method_exists($commandClass,$method)){
                    call_user_func(Array($commandClass,$method), $args);
                    $resolved = true;
                    break;
                }//if
            }//foreach
        }//el

        if(!$resolved){
            echo "`{$method}` is not a valid CLI command!";
        }//if

        echo PHP_EOL;
        exit;

    }//__construct



    /**
     * Run after install via composer.
    */
    public function postInstallCmd(){

        \Disco\manage\Manager::install();

    }//postInstallCmd



    /**
     * Execute a class/service/facade and method.
     *
     * eg: `with Cache set cache-key 'Some Value' 60
     *
     * @param array $args The arguements.
    */
    public function with($args){

        $service = array_shift($args);

        $method = array_shift($args);

        var_dump(\App::handle($service,$method,$args));

    }//with



    /**
     * @ignore
    */
    public function resolve($args){

        \Disco\manage\Manager::resolve($args[0],$args[1],$args[2],$args[3],$args[4]);

    }//resolve



    /**
     * View the queued jobs.
    */
    public function jobs(){

        \Disco\manage\Manager::jobs();

    }//jobs



    /**
     * Kill a queued job by passing the PID.
     *
     * eg: `kill-job 203949`
     *
     * @param array $args The arguements.
    */
    public function killJob($args){
        \Queue::killJob($args[0]);
    }//killJob



    /**
     * Generate a secure key for use in cryptographic methods, optionally setting it in `app/config/config.php`.
     *
     * eg: `gen aes set`
     * eg: `gen sha 32 set-lead`
     * eg: `gen sha 32 set-tail`
     *
     * @param array $args The arguements.
    */
    public function gen($args){

        if($args[0]=='aes'){
            $r = \Disco\manage\Manager::genAES256Key();
            if(isset($args[1]) && $args[1]=='set'){
                \Disco\manage\Manager::setAES256Key($r);
                echo '`AES_KEY256` now set to : ' . $r . PHP_EOL;
            }//if
            else {
                echo $r . PHP_EOL;
            }//el
        }//if
        else if($args[0]=='sha'){

            if(empty($args[1])){
                echo 'You must specify a length for the SHA512 salt' . PHP_EOL;
                exit;
            }//if

            $s = \Disco\manage\Manager::genSalt($args[1]);

            if(!empty($args[2]) && $args[2]=='set'){
                \Disco\manage\Manager::setSalt($s);
                echo '`SHA512_SALT` now set to : ' . $s . PHP_EOL;
            }//if
            else {
                echo $s . PHP_EOL;
            }//el

        } else {
            echo 'You must specify what to gen, either `aes` or `sha`' . PHP_EOL;
        }//el


    }//gen



    /**
     * Get the current dev mode, optionally setting it.
     *
     * eg: `dev-mode`
     * eg: `dev-mode true`
     * eg: `dev-mode false`
     *
     * @param array $args The arguements.
    */
    public function devMode($args){

        if(empty($args[0])){
            $mode = \Disco\manage\Manager::devMode();
            echo 'DEV_MODE : ' . (($mode) ? 'true' : 'false') . PHP_EOL;
            exit;
        }//if
        else if($args[0] != 'true' && $args[0] != 'false'){
            echo 'Mode takes one of two values: true | false' . PHP_EOL . 'Please supply a correct value' . PHP_EOL;
            exit;
        }//if

        \Disco\manage\Manager::devMode(($args[0] == 'true') ? true : false);
        echo 'DEV_MODE now set to: ' . $args[0] . PHP_EOL;

    }//devMode



    /**
     * Get the current maintenance mode, optionally setting it.
     *
     * eg: `maintenance-mode`
     * eg: `maintenance-mode true`
     * eg: `maintenance-mode false`
     *
     * @param array $args The arguements.
    */
    public function maintenanceMode($args){

        if(empty($args[0])){
            $mode = \Disco\manage\Manager::maintenanceMode();
            echo 'MAINTENANCE_MODE : ' . (($mode) ? 'true' : 'false') .PHP_EOL;
            exit;
        }//if
        else if($args[0] != 'true' && $args[0] != 'false'){
            echo 'Maintenance Mode takes one of two values: true | false' . PHP_EOL . 'Please supply a correct value' . PHP_EOL;
            exit;
        }//if

        \Disco\manage\Manager::maintenanceMode(($args[0] == 'true') ? true : false);
        echo 'MAINTENANCE_MODE now set to: ' . $args[0] . PHP_EOL;
        if($args[0] == 'true'){
            echo 'Users will being seeing the result of the file' . PHP_EOL . ' -  app/maintenance.php' . PHP_EOL;
        }//if

    }//maintenanceMode



    /**
     * Create a new backup of the DB structure.
     *
     * eg: `db-backup`
     * eg: `db-backup /app/db/`
     * eg `db-backup /app/db/ BACKUP.sql`
     *
     * @param array $args The arguements.
    */
    public function dbBackupStructure($args){

        $this->dbBackup($args, true);

    }//dbBackupStructure



    /**
     * Create a new backup of the DB. If you want to alter the default dumping behavior create a configuration file 
     * at `app/config/db-backup-settings.php` and return an array with the settings you want to alter. The full 
     * list of settings can be found at https://github.com/ifsnop/mysqldump-php#dump-settings . By default if you 
     * do not specify a value for `add-drop-table` it will be set to `true`.
     *
     * eg: `db-backup`
     * eg: `db-backup /app/db/`
     * eg `db-backup /app/db/ BACKUP.sql`
     *
     * @param array $args The arguements.
     * @param boolean $structureOnly Whether to backup only the structure.
    */
    public function dbBackup($args, $structureOnly = false){

        $path = '/app/db/';
        if(isset($args[0]) && $args[0]){
            $path = $args[0];
        }//if

        if($structureOnly == true){
            $fileName = \App::config('DB_DB') . '_STRUCTURE.sql';
        }//if
        else {
            $fileName = \App::config('DB_DB') . '.sql';
        }//el

        if(isset($args[1])){
            $fileName = $args[1];
        }//if

        $path = \App::path() . '/' . trim($path,'/') . '/';

        if(!is_dir($path)){
            echo 'Directory ' . $path . ' does not exsist, exiting.' . PHP_EOL;
            exit;
        }//if

        if(!is_writable($path)){
            echo 'Directory ' . $path . ' is not writable, exiting.' . PHP_EOL;
            exit;
        }//if

        $fileName = $path . $fileName;

        $connect = sprintf("%1\$s:host=%2\$s;dbname=%3\$s",
                \App::config('DB_ENGINE'),
                \App::config('DB_HOST'),
                \App::config('DB_DB')
            );

        $config_path = \App::path() . '/app/config/db-backup-settings.php';

        if(is_file($config_path)){
            $settings = require $config_path;
            if(!is_array($settings)){
                $settings = Array();
            }//if
        }//if
        else {
            $settings = Array();
        }//el

        if(!array_key_exists('add-drop-table',$settings)){
            $settings['add-drop-table'] = true;
        }//if

        if($structureOnly == true){
            $settings['no-data'] = true;
        }//if

        $dump = new \Ifsnop\Mysqldump\Mysqldump($connect, \App::config('DB_USER'), \App::config('DB_PASSWORD'),$settings);


        $dump->start($fileName);

        echo "Backup successfully created at `{$fileName}`" . PHP_EOL;

    }//dbBackup



    /**
     * Restore the DB from a backup.
     *
     * eg: `db-restore`
     * eg: `db-restore /app/db/`
     * eg `db-restore /app/db/ BACKUP.sql`
     *
     * @param array $args The arguements.
    */
    public function dbRestore($args){

        $path = '/app/db/';
        if(isset($args[0])){
            $path = $args[0];
        }//if

        $path = \App::path() . '/' . trim($path,'/') . '/';

        $fileName = \App::config('DB_DB');
        if(isset($args[1])){
            $fileName = $args[1];
        }//if

        $fileName = $path . $fileName . '.sql';

        if(!is_file($fileName)){
            echo "Backup `{$fileName}` does not exist, exiting." . PHP_EOL;
            exit;
        }//if

        $e = "mysql -u %1\$s -p'%2\$s' -h %3\$s %4\$s < %5\$s;";
        $e = sprintf($e,
            \App::config('DB_USER'),
            \App::config('DB_PASSWORD'),
            \App::config('DB_HOST'),
            \App::config('DB_DB'),
            $fileName
        );

        $error = exec($e);

        if(!$error){
            echo 'DB `' . \App::config('DB_DB') . "` successfully restored from `{$fileName}`";
        } else {
            echo 'Unable to restore! : ' . $error;
        }//el

        echo PHP_EOL;

    }//dbRestore



    /**
     * Create a model or a record. Use the special keyword `all` in place of a table name to generate records or 
     * models for all tables.
     *
     * eg: `create model user`
     * eg: `create model user /app/config/model.format /app/model/`
     *
     * eg: `create record user`
     * eg: `create record user /app/config/record.format /app/record/`
     *
     * @param array $args The arguements.
    */
    public function create($args){

        if($args[0] == 'model'){

            if(!isset($args[1])){
                echo 'You must specify a table to build the model from' . PHP_EOL;
                exit;
            }//if

            $table = $args[1];

            $templatePath = isset($args[2]) ? $args[2] : 'app/config/model.format';
            $outputPath = isset($args[3]) ? $args[3] : 'app/model';

            if($table=='all'){
                $result = $this->getDBSchema();
                while($row = $result->fetch()){
                    $model = \Disco\manage\Manager::buildModel($row['table_name']);
                    \Disco\manage\Manager::writeModel($row['table_name'],$model,$templatePath,$outputPath);
                }//while
            }//if
            else {
                $model = \Disco\manage\Manager::buildModel($table);
                \Disco\manage\Manager::writeModel($table,$model,$templatePath,$outputPath);
            }//el

        }//if
        else if($args[0] == 'record'){

            if(!isset($args[1])){
                echo 'You must specify a table to build the record from' . PHP_EOL;
                exit;
            }//if

            $table = $args[1];

            $templatePath = isset($args[2]) ? $args[2] : 'app/config/record.format';
            $outputPath = isset($args[3]) ? $args[3] : 'app/record';

            if($table=='all'){
                $result = $this->getDBSchema();
                while($row = $result->fetch()){
                    $record = \Disco\manage\Manager::buildRecord($row['table_name']);
                    \Disco\manage\Manager::writeRecord($row['table_name'],$record,$templatePath,$outputPath);
                }//while
            }//if
            else {
                $record = \Disco\manage\Manager::buildRecord($table);
                \Disco\manage\Manager::writeRecord($table,$record,$templatePath,$outputPath);
            }//el

        }//elif

    }//create



    /**
     * Used to get the database schema for building all models and records.
     *
     *
     * @return \PDOStatement
    */
    private function getDBSchema(){

        return \DB::query('
            SELECT table_name 
            FROM information_schema.tables
            WHERE table_type="BASE TABLE" AND table_schema="'.\App::config('DB_DB').'"
        ');

    }//getDBSchema



    /**
     * Prompt the user at the console to supply a `Y` or `N` value.
     *
     *
     * @return boolean
    */
    public static function yesOrNo(){

        @ob_flush();

        exec('
        while true; do
            read -p "Y/N?" yn
            case $yn in
                [Yy]* ) echo "Y"; break;;
                [Nn]* ) echo "N"; break;;
            esac
        done
            ',
            $answer
        );
    
        if(strtoupper($answer[0]) != 'N'){
            return true;
        }//if
    
        return false;

    }//yesOrNo



    /**
     * Prompt the user at the console with a question and the valid options that serve as an answer to that 
     * question.
     *
     * @param string $question The question being asked.
     * @param array $options The possible answers to the question being asked, where the keys are the anwsers and 
     * the values are the description of the answer.
     *
     * @return mixed The selected key from $options param.
    */
    public static function consoleQuestion($question,$options){

        @ob_flush();

        $orgQuestion = $question;

        $opts = '';

        foreach($options as $value => $statement){
            $opts .= "'{$value}') echo '{$value}'; break;;";
            $question .= PHP_EOL . "($value) - {$statement}";
        }//foreach

        $question .= PHP_EOL . 'Your answer? : ';

        exec("
        while true; do
            read -p '{$question}' answer 
            case \$answer in
                {$opts}
                *) echo ''; break;;
            esac
        done
            ",
            $answer
        );

        if(!array_key_exists($answer[0],$options)){
            echo PHP_EOL . 'Please enter a valid option and try again!' . PHP_EOL . PHP_EOL;
            return self::consoleQuestion($orgQuestion,$options);
        }//if
    
        return $answer[0];

    }//ConsoleQuestion



    /**
     * Prompt the user at the console to enter a free form text response to a question.
     *
     * @param string $question The question that needs a response.
     * @param boolean $cannotBeBlank The response to the question cannot be blank.
     *
     * @return string The response to the question.
    */
    public function consolePrompt($question,$cannotBeBlank = false){

        @ob_flush();

        exec("read -p '{$question} ' answer; echo \$answer;",$answer);

        if(!$answer[0] && $cannotBeBlank === true){
            echo PHP_EOL . 'Answer cannot be blank! Try again...' . PHP_EOL . PHP_EOL; 
            return self::consolePrompt($question,$cannotBeBlank);
        }//if

        return $answer[0];

    }//consolePrompt



}//Console

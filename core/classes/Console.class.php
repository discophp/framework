<?php
namespace Disco\classes;
/**
 * This file holds the Console class.
*/


/**
 * Bridge between {@link \Disco\manager\Manager} and CLI requests.
 *
 * Its used by calling `public/index.php` via the CLI and passing an arguement(s). Each method is actually 
 * a command eg `php public/index.php with Crypt hash 'kitty kat'`.
*/
class Console {


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

        call_user_func(Array($this,$method), $args);

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

        \Queue::killJob($argv[0]);

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
                    echo 'AES_KEY256 now set to : ' . $r . PHP_EOL;
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

                if(!empty($args[2]) && $args[2]=='set-lead'){
                    \Disco\manage\Manager::setSaltLead($s);
                    echo 'SHA512_SALT_LEAD now set to : ' . $s . PHP_EOL;
                }//if
                else if(!empty($args[2]) && $args[2]=='set-tail'){
                    \Disco\manage\Manager::setSaltTail($s);
                    echo 'SHA512_SALT_TAIL now set to : ' . $s . PHP_EOL;
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

        Disco\manage\Manager::maintenanceMode(($args[0] == 'true') ? true : false);
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
     * Create a new backup of the DB.
     *
     * eg: `db-backup`
     * eg: `db-backup /app/db/`
     * eg `db-backup /app/db/ BACKUP.sql`
     *
     * @param array $args The arguements.
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

        $settings = Array();

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
     * Get the routes used by your application.
     *
     * eg: `routes routes.txt`
    */
    public function routes($args){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/1093489sdker';
        \Disco\manage\Manager::routes($args[0]);

    }//routes



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
    
        if($answer[0] != 'N'){
            return true;
        }//if
    
        return false;

    }//yesOrNo


}//Console

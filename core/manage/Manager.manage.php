<?php
namespace Disco\manage;
/**
 * This file holds the Class Manager.
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
    public static function resolve($delay=null,$obj,$method,$vars,$d){

        if($delay!=0){
            sleep($delay);
        }//if

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
            if($vars)
                return call_user_func_array($obj,$vars);
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

        foreach($j as $job){
            echo sprintf("
                ----------- JOB # %1\$s --------------
                Time entered: %2\$s
                Delay: %3\$s
                Object: %4\$s
                Method: %5\$s
                Vars: %6\$s

                Kill this job by running: php disco kill-job %1\$s
            ".PHP_EOL.PHP_EOL,$job->pId,$job->time,$job->delay,$job->object,$job->method,$job->vars);

        }//foreach

    }//jobs



    /**
     * Set or Get the APP_MODE in .config.php .
     *
     *
     * @param string|null $mode The mode to put the app into;either DEV|PROD
     *
     * @return void
    */
    public static function appMode($mode=null){

        if($mode!=null){
            self::setConfig('APP_MODE',$mode);
        }//if
        else {
            return self::getConfig('APP_MODE');
        }//el

    }//appMode



    /**
     * Set or Get the MAINTENANCE_MODE in .config.php .
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
     * Set a variable in .config.php .
     *
     * @param string $find  The variable to change.
     * @param string $value The value to change the variable to.
     *
     * @return void
    */
    public static function setConfig($find,$value){

        $find.='\'=>\'';
        $f = file_get_contents('./.config.php');
        $i = stripos($f,$find)+strlen($find);
        $ni = stripos($f,'\'',$i);

        $f = substr_replace($f,$value,$i,$ni-$i);
        file_put_contents('./.config.php',$f);
    }//setConfig



    /**
     * Get a variable in .config.php .
     *
     *
     * @param string $find  The variable to find.
     *
     * @return string The value of $find.
    */
    public static function getConfig($find){
        $find.='\'=>\'';
        $f = file_get_contents('./.config.php');
        $i = stripos($f,$find)+strlen($find);
        $ni = stripos($f,'\'',$i);

        return substr($f,$i,$ni-$i);
    }//setConfig


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
     * Generate a listing of router and template files specified by any addons
     * and store them in addon-autoloads.php in a serialized form.
     *
     *
     * @return void
    */
    public static function addonAutoloads(){

        $app = \App::instance();

        $dir = $app->path.'/'.$app->config['COMPOSER_PATH'];

        $dirMap = Array('template','router');
        $fileMap = Array('.template.html','.router.php');
        $files = Array('.template.html'=>Array(),'.router.php'=>Array());
        if(is_dir($dir)){
            $dirs = scandir($dir);
            unset($dirs[0]);unset($dirs[1]);
            foreach($dirs as $d){
                if(!is_dir($dir.'/'.$d))
                    continue;
                $packageDirs = scandir($dir.'/'.$d);
                unset($packageDirs[0]);unset($packageDirs[1]);
                foreach($packageDirs as $pDir){
                    $testDir = $dir.'/'.$d.'/'.$pDir.'/addon';
                    if(is_dir($testDir)){
                        foreach($fileMap as $fileExt){
                            $files[$fileExt] = array_merge($files[$fileExt],self::getFilesRec($fileExt,$testDir,Array())); 
                        }//foreach
                    }//if
                }//foreach
            }//foreach

        }//if

        $put = $app->path.'/'.$app->config['COMPOSER_PATH'].'/discophp/framework/addon-autoloads.php';
        file_put_contents($put,serialize($files));
        
    }//addonAutoloads


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

        echo 'Generating autoload file for Addons ...';
        self::addonAutoloads();
        echo ' done.'.PHP_EOL;


    }//install



    /**
     * Build a model from a specified table in the configured database.
     *
     *
     * @param string $table The name of the table the model should be built from.
     *
     * @return void
    */
    public static function buildModel($table){
        $db = \App::instance()->config['DB_DB'];
        $result = \DB::query("SHOW KEYS FROM {$db}.{$table} WHERE Key_name='PRIMARY'");
    
        $keys = Array();
        while($row = $result->fetch_assoc()){
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
    
        $date = date(DATE_RFC822);
    
        $model = 
"<?php
//This Model Class was generated with Disco on: {$date}
Class {$table} extends Disco\classes\Model {

    public \$table = '{$table}';
    public \$ids = {$keys};

}//{$table}   
?>";
    
        $out = "app/model/{$table}.model.php";

        if(file_exists($out)){
            echo "Model $out already exists! Aborted".PHP_EOL;
        }//if
        else if(file_put_contents($out,$model)){
            echo "Created $out".PHP_EOL;    
        }//if
        else {
            echo "Failed! $out ".PHP_EOL."insufficient permissions for writing to app/model/  ( use sudo )".PHP_EOL;    
        }//el

    
    
    }//build_model



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

}//Manager
?>

<?php
namespace  Disco\classes;

class PDO extends \PDO {


    private $app;


    public function __construct($host=null,$user=null,$pw=null,$db=null,$engine='mysql') {

        $this->app = \App::instance();

        if($host==null){
            $engine = $this->app->config['PDO_ENGINE'];
            $host=$this->app->config['DB_HOST'];
            $user=$this->app->config['DB_USER'];
            $pw=$this->app->config['DB_PASSWORD'];
            $db=$this->app->config['DB_DB'];
            $charset = $this->app->config['DB_CHARSET'];
        }//if

        try {

            parent::__construct($engine . ':dbname=' . $db . ';host=' . $host . ';charset=' . $charset, $user, $pw,Array(
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ));

        } catch(\PDOException $e){
            TRIGGER_ERROR('DB::Connect Error | '.$e->getMessage(),E_USER_WARNING);
            $this->app['View']->serve(500);
        }//catch

    }//end constructor



    public function query($query, $data = null){

        try {

            if(!$data){
                return parent::query($query);
            }//if

            $query = parent::prepare($query);

            if(is_string($data)){
                $data = Array($data);
            }//if

            $query->execute($data);

            return $query;

        } catch(\PDOException $e){
            TRIGGER_ERROR('DB:: Query Error | '.$e->getMessage(),E_USER_WARNING);
            $this->app['View']->serve(500);
        }//catch

    }//query



    public function lastId(){
        $this->lastInsertId;
    }//lastId



    /**
     * Bind passed variables into a query string and do proper type checking
     * and escaping before binding.
     *
     *
     * @param string        $q      The query string.
     * @param string|array  $args   The variables to bind to the $q.
     *
     * @return string               The $q with $args bound into it.
    */
    public function set($q,$args){
        if(is_array($args) && isset($args['raw'])){
            $q=implode($args['raw'],explode('?',$q,2));
        }//if
        else if(is_array($args)){
            foreach($args as $a){
                if(is_array($a) && isset($a['raw'])){
                    $q=implode($a['raw'],explode('?',$q,2));
                }//if
                else {
                    $q=implode($this->prepareType($a),explode('?',$q,2));
                }//el
            }//foreach
        }//if
        else {
            $q=implode($this->prepareType($args),explode('?',$q,2));
        }//el

        return $this->resetQuestionMarks($q);

    }//set



    /**
     * Determine the type of variable being bound into the query, either a String or Numeric.
     *
     *
     * @param  string|int|float $arg  The variable to prepare.
     *
     * @return string|int|float The $arg prepared.
    */
    private function prepareType($arg){
        if(($arg===null || $arg=='null') && $arg !== 0)
            return 'NULL';
        if(!is_numeric($arg)){
            $arg = $this->quote($arg);
            $arg = $this->replaceQuestionMarks($arg);
            return $arg;
        }//if
        return $arg;
    }//wrapStrings


    private function replaceQuestionMarks($arg){
        return str_replace('?','+:-|:-+',$arg);
    }//escapeQuestionMarks

    private function resetQuestionMarks($arg){
        return str_replace('+:-|:-+','?',$arg);
    }//escapeQuestionMarks



    public function clean($arg){
        if(!is_numeric($arg)){
            return $this->quote($arg);
        }//if
        return $arg;
    }//clean




}//PDO

<?php
class Queue extends Disco\classes\Facade {

    protected static function returnFacadeId(){
        return 'Queue';
    }//returnFacadeId

    /**
     * Get the jobs that are currently queued. Returns the jobs as objects
     * structured like :
     * 
     *
     *  - pId = process ID of job.
     *  - time = time job was enetered.
     *  - delay = delay the job was told to wait before execution.
     *  - object = the actual object or Closure that will execute the job.
     *  - method = the method to call on the object.
     *  - vars = the variables to be passed to the method.
     *
     *
     *  @return array An array of job objects.
    */
    public static function jobs(){
        exec('ps -ef | grep "php[[:space:]]\.\./disco[[:space:]]resolve"',$jobs);

        $f = 'php ../disco resolve';
        $j = Array();
        foreach($jobs as $k=>$v){
            if(stripos($v,$f)!==false){
                $v = preg_replace('!\s+!', ' ', $v);
                $j[]=$v;
            }//if
        }//foreach

        foreach($j as $k=>$job){
            $job = explode(' ',$job,15);
            $j[$k] = new \stdClass();
            $j[$k]->pId=$job[1];
            $j[$k]->time=$job[4];
            $j[$k]->delay=$job[10];
            $j[$k]->object=unserialize(base64_decode($job[11]));
            $j[$k]->method=$job[12];
            $j[$k]->vars=($job[13]=='disco-no-variable')?'':unserialize(base64_decode($job[13]));
        }//foreach

        return $j;
       
    }//jobs



    /**
     * Kill a Queued job by passing its process ID number
     *
     *
     * @param integer $pId The jobs process ID.
     *
     * @return boolean Whether or not the job was killed. 
    */
    public static function killJob($pId){

        $j = self::jobs();

        global $argv;

        foreach($j as $job){
            if($job->pId==$pId){
                exec('kill '.$job->pId);
                if($argv[1]=='kill-job'){
                    echo 'Killed Job # '.$job->pId.' Action:'.$job->object.'@'.$job->method.PHP_EOL;
                }//if
                return true;
            }//if
        }//foreach

        if($argv[1]=='kill-job'){
            echo 'No Job # '.$pId.PHP_EOL;
        }//if

        return false;
    }//killJob

}//Queue
?>

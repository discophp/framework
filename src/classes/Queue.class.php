<?php
namespace Disco\classes;
/**
 * This file holds the Queue class. 
*/

/**
 * Queue class. 
 * Allow simulation of parallel processing of jobs either immediately or after a set delay.
 * The classes objective is to allow large jobs that would cause lag or generally take a long time and
 * are expensive in processing to be executed without interrupting the applications response.
*/
class Queue {



    /**
     *  Push a job onto the Queue for processing. 
     *
     *  @param \Closure|string $job Either a `\Closure` to execute or a Class method pair like `DB@query`.
     *  @param int $delay The delay to wait before beginning execution of the `$job`.
     *  @param null|string|array $vars The variables to pass to the `$job`.
     *
     *  @return void
    */
    public function push($job, $delay = 0, $vars = null){

        if($vars==null){
            $vars='disco-no-variable';
        }//if
        else {
            $vars = base64_encode(serialize($vars));
        }//el

        $domain = \App::domain();

        if($job instanceof \Closure){
            $obj = new \Jeremeamia\SuperClosure\SerializableClosure($job);
            $method = 'closure';
        }//if
        else if(stripos($job, '@') !== false){
            $obj = explode('@', $job);
            $method = $obj[1];
            $obj = $obj[0];
        }//elif
        else {
            $obj = $job;
            $method = 'work';
        }//el

        $obj = base64_encode(serialize($obj));

        $command = "php %1\$s/public/index.php resolve %2\$s '%3\$s' '%4\$s' %5\$s %6\$s > /dev/null 2>/dev/null &";

        $command = sprintf($command,
            \App::path(),
            $delay,
            $obj,
            $method,
            $vars,
            $domain
        );

        exec($command);

    }//push



    /**
     * Get the jobs that are currently queued. Returns the jobs as objects
     * structured like :
     * 
     *
     *  - pId = process ID of job.
     *  - time = time job was entered.
     *  - delay = delay the job was told to wait before execution.
     *  - object = the actual object or Closure that will execute the job.
     *  - method = the method to call on the object.
     *  - vars = the variables to be passed to the method.
     *
     *
     *  @return array An array of job objects.
    */
    public function jobs(){

        exec('ps -ef | grep "php[[:space:]]' . \App::path() . '/public/index\.php[[:space:]]resolve"',$jobs);

        $f = 'php ' . \App::path(). '/public/index.php resolve';
        $j = Array();
        foreach($jobs as $k => $v){
            if(stripos($v,$f) !== false){
                $v = preg_replace('!\s+!', ' ', $v);
                $j[] = $v;
            }//if
        }//foreach

        foreach($j as $k => $job){
            $job = explode(' ',$job,15);
            $j[$k] = new \stdClass();
            $j[$k]->pId = $job[1];
            $j[$k]->time = $job[4];
            $j[$k]->delay = $job[10];
            $j[$k]->object = unserialize(base64_decode($job[11]));
            $j[$k]->method = $job[12];
            $j[$k]->vars = $job[13] == 'disco-no-variable' ? '' : unserialize(base64_decode($job[13]));
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
    public function killJob($pId){

        $j = $this->jobs();

        global $argv;

        foreach($j as $job){
            if($job->pId == $pId){
                exec('kill ' . $job->pId);
                if($argv[1] == 'kill-job'){
                    echo 'Killed Job # ' . $job->pId . ' Action:' . $job->object . '@' . $job->method . PHP_EOL;
                }//if
                return true;
            }//if
        }//foreach

        if($argv[1] == 'kill-job'){
            echo 'No Job # ' . $pId . PHP_EOL;
        }//if

        return false;

    }//killJob



}//Queue
?>

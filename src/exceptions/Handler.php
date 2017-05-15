<?php
namespace Disco\exceptions;

class Handler {

    protected $defaultErrorMessage = 'Internal Server Error';

    protected $dontReport = [];

    /**
     * @param \Throwable $e
     */
    public function report(\Throwable $e){
        if(array_key_exists(get_class($e), $this->dontReport)){
            return;
        }
        echo $e->getMessage() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
        exit;
        app()->with('Log')->error('Error : {}' . $e->getMessage());
    }

    /**
     * @param \Disco\http\Request $request
     * @param \Throwable $e
     *
     * @return \Disco\http\Response
     */
    public function render($request, $e){

        $errorFile = app()->path() . '/' . trim(app()->errorDir,'/') . '/' . $e->getCode(). '.php';
        if(is_file($errorFile)){
            $callable = require $errorFile;
            return $callable($request, $e);
        }

        $defaultMsg = $this->defaultErrorMessage;

        $errorTemplate = trim(app()->errorTemplateDir,'/') . '/' . $e->getCode();
        if(template()->isTemplate($errorTemplate)){
            $defaultMsg = template()->render($errorTemplate, [ 'exception' => $e, 'request' => $request]);
        }

        return new \Disco\http\Response($defaultMsg, 500);

    }

}
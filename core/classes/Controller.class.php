<?php
namespace Disco\classes;

/**
 * Provides some simple functionality to controllers for returning requests. Using these methods short circuits the 
 * request, aka terminates it immeditatly, not allowing other routes or controllers to be executed.
*/
class Controller {



    /**
     * HTML to add to the view and return as a AJAX response.
     *
     * @param string $html HTML to load into the view.
    */
    public function ajax($html){
        \View::ajax();
        \View::html($html);
        \View::serve();
    }//ajax



    /**
     * Return a JSON response.
     *
     * @param array|\stdClass $data An array or stdClass to encode and return.
    */
    public function json($data){
        \View::json($data);
    }//json



    /**
     * A template to add to the view and return.
     *
     * @param string $template The template name to load into the view.
    */
    public function template($template){
        \Template::with($template);
        \View::serve();
    }//template



    /**
     * Add HTML to the view.
     *
     * @param string $html HTML to load into the view.
    */
    public function html($html){
        \View::html($template);
        \View::serve();
    }//html



    /**
     * Echo a string without loading it into the view.
     *
     * @param string $string The simple string response.
    */
    public function simple($string){
        echo $string;
        exit;
    }//simple



    /**
     * Serve a file as a resouce for use by the browser eg: css,js,png,jpg,jpeg files.
     *
     * @param string $file The file to serve.
    */
    public function file($file){
        \FileHelper::serveFile($file);
        exit;
    }//file


    /**
     * Serve a file as a X-resouce for use by the browser eg: css,js,png,jpg,jpeg files.
     *
     * @param string $file The file to serve.
    */
    public function xfile($file){

        \FileHelper::XServeFile($file);
        exit;

    }//xfile



    /**
     * Serve a file as a download.
     *
     * @param string $file The file to make available for download.
    */
    public function download($file){
        \FileHelper::serveAsDownload($file);
        exit;
    }//download



    /**
     * Serve a file as a download using XSendFile apache module.
     *
     * @param string $file The file to make available for download.
    */
    public function xdownload($file){
        \FileHelper::XServeAsDownload($file);
        exit;
    }//xdownload



    /**
     * Redirect the request to a different path.
     *
     * @param string $path The new path.
    */
    public function redirect($path){
        \View::redirect($path);
    }//redirect



    /**
     * Abort the request with a 404.
     *
     * @param boolean|string|\Closure $action Optional template or Closure function.
    */
    public function abort($action = false){
        \View::abort(404,$action);
    }//abort



    /**
     * Abort the request with an error, default is 500.
     *
     * @param int $code The error code to return in the response.
     * @param boolean|string|\Closure $action Optional template or Closure function.
    */
    public function error($code = 500, $action = false){
        \View::error($code,$action);
    }//error



}//Controller

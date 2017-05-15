<?php
namespace Disco\http;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Provides some simple functionality to controllers for returning requests. Using these methods short circuits the 
 * request, aka terminates it immediately, not allowing other routes or controllers to be executed.
*/
abstract class Controller {



    /**
     * Return a JSON response.
     *
     * @param array|\stdClass $data An array or stdClass to encode and return.
     * @param int $responseCode The HTTP Response code.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
    */
    public function json($data, $responseCode = 200){
        return new \Symfony\Component\HttpFoundation\JsonResponse($data, $responseCode);
    }//json



    /**
     * Return a template (not using the current View).
     *
     * @param string $template The template name to load into the view.
     * @param array $data The data to bind into the template. Defaults to empty array.
     * @param int $responseCode The HTTP response code.
     *
     * @return \Disco\http\Response
    */
    public function template($template, $data = [], $responseCode = 200){
        return new Response(template()->render($template, $data), $responseCode);
    }//template



    /**
     * Return the entire current view, adding the template to it.
     *
     * @param string $template The template name to load into the view.
     * @param array $data The data to bind into the template. Defaults to empty array.
     * @param int $responseCode The HTTP response code.
     *
     * @return \Disco\http\Response
    */
    public function view($template, $data = [], $responseCode = 200){
        app()->with('Template')->with($template, $data);
        return new Response(app()->with('View')->render(), $responseCode);
    }//view



    /**
     * Return the entire current view.
     *
     * @param int $responseCode The HTTP response code.
     * @return Response
     */
    public function currentView($responseCode = 200){
        return new Response(app()->with('View')->render(), $responseCode);
    }



    /**
     * Return the entire current view, adding the html to it.
     *
     * @param string $html HTML to load into the view.
     * @param int $responseCode The HTTP response code.
     *
     * @return \Disco\http\Response
    */
    public function html($html, $responseCode = 200){
        app()->with('View')->html($html, $responseCode = 200);
        return new Response(app()->with('View')->render(), $responseCode);
    }//html



    /**
     * Echo a string without loading it into the view.
     *
     * @param string $string The simple string response.
     * @param int $responseCode The HTTP response code.
     *
     * @return \Disco\http\Response
    */
    public function simple($string, $responseCode = 200){
        return new Response($string, $responseCode);
    }//simple



    /**
     * Redirect to another URL or slug.
     *
     * @param $url The URL or path to redirect to.
     * @param int $responseCode The HTTP response code.
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($url, $responseCode = 200){
        return new \Symfony\Component\HttpFoundation\RedirectResponse($url, $responseCode);
    }



    /**
     * Serve a file as a resource for use by the browser eg: css,js,png,jpg,jpeg files.
     *
     * @param string $file The file path to serve.
     * @param int $responseCode The HTTP response code.
     *
     * @return BinaryFileResponse
    */
    public function file($file, $responseCode = 200){
        return new BinaryFileResponse($file, $responseCode);
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
     * Serve a file as a attachment for download.
     *
     * @param $file The file path.
     * @param int $responseCode The HTTP response code.
     * @return DownloadFileResponse
     */
    public function download($file, $responseCode = 200){
        return new DownloadFileResponse($file, $responseCode);
    }//download



    /**
     * Serve a file as a attachment for download.
     *
     * @param $file The file path.
     */
    public function xdownload($file){
        \FileHelper::XServeAsDownload($file);
        exit;
    }//download



    /**
     * Abort the request with a 404.
     *
     * @param int $errorCode
     * @param string|null $msg
     * @throws \Disco\exceptions\HttpError
     */
    public function abort($errorCode, $msg = null){
        throw new \Disco\exceptions\HttpError($msg, $errorCode);
    }//abort



}//Controller

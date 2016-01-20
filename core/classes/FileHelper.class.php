<?php
namespace Disco\classes;

/**
 * Helper for interacting with file system files.
*/
class FileHelper {


    /**
     * @var array $mimeCache MIME types cache.
    */
    private $mimeCache;



    /**
     * Get the file extension type from a path.
     *
     *
     * @param string $path The file.
     *
     * @return string The extension.
    */
    public function getExtension($path){
        return pathinfo($path,PATHINFO_EXTENSION);
    }//getExtension



    /**
     * Get a file type based on the file MIME info.
     *
     *
     * @param string $file The file.
     *
     * @return string The type.
    */
    public function getMimeType($file){

        if(function_exists('finfo_open')) {

            $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
            $info = finfo_open($const);
            if($info && ($result = finfo_file($info,$file,$const)) !== false){
                finfo_close($info);
                return $result;
            }//if
        }//if

        if(function_exists('mime_content_type') && ($result = mime_content_type($file)) !== false) {
            return $result;
        }//if

    }//getMimeType



    /**
     * Get a file extension type based on the file MIME info.
     *
     *
     * @param string $file The file.
     *
     * @return null|string The extension.
    */
    public function getMimeTypeByExtension($path){

        if(!$this->mimeCache){
            $this->mimeCache = require \App::getAlias('disco.mime');
        }//if

        if(isset($this->mimeCache[$this->getExtension($path)])){
            return $this->mimeCache[$this->getExtension($path)];
        }//if

        return null;

    }//getMimeTypeByExtension



    /**
     * Iterate over a diretory and all its children including other directories.
     *
     *
     * @param string $path The path of the directory to iterate through.
     *
     * @return array
    */
    public function recursiveIterate($path){
        return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
    }//revursiveIterate



    /**
     * Serve a file with the proper `Content-type` header value set for use in a browser context. The content type 
     * is determined by calling `$this->getMimeTypeByExtension($path)`. If the file doesn't exist then it will 
     * serve a 404.
     *
     *
     * @param string $path The file to serve.
     *
     * @return void
    */
    public function serveFile($path){

        $this->isFileOrDie($path);

        header('Content-type: ' . $this->getMimeTypeByExtension($path));
        $this->serve($path);

    }//serveFile



    /**
     * Serve a file using the Apache module XSendFile `https://tn123.org/mod_xsendfile/` with the proper `Content-type` header value set for use in a browser context. The content type 
     * is determined by calling `$this->getMimeTypeByExtension($path)`. If the file doesn't exist then it will 
     * serve a 404.
     *
     *
     * @param string $path The file to serve.
     *
     * @return void
    */
    public function XServeFile($path){

        $this->isFileOrDie($path);

        header('Content-type: ' . $this->getMimeTypeByExtension($path));
        $this->Xserve($path);

    }//XServeFile



    /**
     * Serve a file as a download.
     *
     *
     * @param string $path The file to be downloaded.
     *
     * @return void
    */
    public function serveAsDownload($path){

        $this->isFileOrDie($path);

        $this->downloadHeaders($path);
        $this->serve($path);

    }//serveAsDownload



    /**
     * Serve a file as a download using the Apache module XSendFile.
     *
     *
     * @param string $path The file to be downloaded.
     *
     * @return void
    */
    public function XServeAsDownload($path){

        $this->isFileOrDie($path);

        $this->downloadHeaders($path);
        $this->XServe($path);

    }//XServeAsDownload



    /**
     * Set the header values necessary for serving a file as a dowload.
     *
     *
     * @param string $path The file thats being downloaded.
     *
     * @return void
    */
    private function downloadHeaders($path){

        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename='.basename($path));

    }//downloadHeaders



    /**
     * Serve a file.
     *
     *
     * @param string $path The file thats being downloaded.
     *
     * @return void
    */
    private function serve($path){

        ob_end_clean();

        header('Content-Length: ' . filesize($path) );
        readfile($path); 

    }//serve



    /**
     * Serve a file using the Apache XSendFile module.
     *
     *
     * @param string $path The file thats being downloaded.
     *
     * @return void
    */
    private function XServe($path){

        ob_end_clean();

        header('Content-Length: ' . filesize($path) );
        header("X-Sendfile: $path");

    }//XServe



    /**
     * If the file doesn't exist serve a 404 and exit.
     *
     *
     * @param string $path The file.
     *
     * @return void
    */
    private function isFileOrDie($path){
        if(!is_file($path)){
            http_response_code(404);
            exit;
        }//if
    }//isFileOrDie



}//FileHelper

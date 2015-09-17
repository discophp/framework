<?php

namespace Disco\classes;

class FileHelper {

    private $mimeCache;


    public function getExtension($path){
        return pathinfo($path,PATHINFO_EXTENSION);
    }//getExtension



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



    public function getMimeTypeByExtension($path){

        if(!$this->mimeCache){
            $this->mimeCache = require \App::getAlias('disco.mime');
        }//if

        if(isset($this->mimeCache[$this->getExtension($path)])){
            return $this->mimeCache[$this->getExtension($path)];
        }//if

        return null;

    }//getMimeTypeByExtension



    public function recursiveIterate($path){
        return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
    }//revursiveIterate



    public function serveFile($path){

        $this->isFileOrDie($path);

        header('Content-type: ' . $this->getMimeTypeByExtension($path));
        $this->serve($path);

    }//serveFile



    public function XServeFile($path){

        $this->isFileOrDie($path);

        header('Content-type: ' . $this->getMimeTypeByExtension($path));
        $this->Xserve($path);

    }//serveFile



    public function serveAsDownload($path){

        $this->isFileOrDie($path);

        $this->downloadHeaders($path);
        $this->serve($path);

    }//serveFile



    public function XServeAsDownload($path){

        $this->isFileOrDie($path);

        $this->downloadHeaders($path);
        $this->XServe($path);

    }//XServeAsDownload



    private function downloadHeaders($path){

        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename='.basename($path));

    }//downloadHeaders



    private function serve($path){

        header('Content-Length: ' . filesize($path) );
        readfile($path); 

    }//server


    private function XServe($path){

        header('Content-Length: ' . filesize($path) );
        header("X-Sendfile: $path");

    }//XServe


    private function isFileOrDie($path){
        if(!is_file($path)){
            http_response_code(404);
            exit;
        }//if
    }//isFileOrDie



}//FileHelper

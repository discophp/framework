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
     * Format a number of bytes as human friendly text.
     *
     * @param int $bytes    The number of bytes to format
     * @param int $decimals The number of decimal places for the returned format.
     *
     * @return string
     */
    public function humanFileSize($bytes, $decimals = 2) {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }//humanFileSize



    /**
     * Human friendly file size of a value using a convention supported in php ini values.
     *
     * @param string $size The ini size.
     *
     * @return string
    */
    public function iniHumanFriendlySize($size){

        switch( substr($size,-1) ) {
            case 'G':
                $size = $size * 1024;
            case 'M':
                $size = $size * 1024;
            case 'K':
                $size = $size * 1024;
        }//switch

        return $this->humanFileSize($size);

    }//iniHumanFriendlySize



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
     * Empty a directory.
     *
     *
     * @param string $path The direcotry to empty.
     * @param array $except Dont remove paths included in this array.
     *
     * @return boolean
    */
    public function emptyDir($path,$except = Array()){

        try {

            $iterator = new \DirectoryIterator($path);

            foreach ($iterator as $file){

                if($file->isDot() || in_array($file->getPathname(),$except)){
                    continue;
                }//if

                if($file->isDir() && (!$this->emptyDir($file->getPathname()) || !rmdir($file->getPathname()))){
                    return false;
                }//if

                if($file->isFile() && !unlink($file->getPathname())){
                    return false;
                }//if

            }//foreach

        } catch (\Exception $e){
            error_log($e->getMessage());
            return false;
        }//catch

        return true;

    }//emptyDir



    /**
     * Remove a directory.
     *
     *
     * @param string $path The directory to remove.
     * @return boolean
    */
    public function removeDir($path){

        if(!$this->emptyDir($path)){
            return false;
        }//if 

        return rmdir($path);

    }//removeDir


    private $copyDirBasePathLen = 0;

    /**
     * Copy a directory.
     *
     *
     * @param string $path The direcotry to copy.
     * @param string $toPath The directory to copy to.
     * @param boolean $nested Whether to create the $path directory in $toPath or not.
     *
     * @return boolean
    */
    public function copyDir($path,$toPath, $nested = false){

        $path = rtrim($path,'/') . '/';
        $toPath = rtrim($toPath,'/') . '/';
        $pathLen = strlen($path);

        if(!$nested){
            if(!is_dir($toPath) && !mkdir($toPath)){
                return false;
            }//if
            $this->copyDirBasePathLen = strlen($path);
        }//if


        try {

            $iterator = new \DirectoryIterator($path);

            foreach ($iterator as $file) {

                if($file->isDot()){
                    continue;
                }//if

                $newDirPath = $toPath . substr($file->getPathname(),$this->copyDirBasePathLen);

                if($file->isDir() && ((!is_dir($newDirPath) && !mkdir($newDirPath)) || !$this->copyDir($file->getPathname(),$toPath, true))){
                    return false;
                }//if

                if($file->isFile() && !copy($file->getPathname(),$newDirPath)){
                    return false;
                }//if

            }//foreach

        } catch (\Exception $e){
            error_log($e->getMessage());
            return false;
        }//catch

        return true;

    }//copyDir



    /**
     * Change file mode for directory and all contents.
     *
     *
     * @param string $path The directory to chmod recursivly.
     * @param int $mode The file mode to apply.
    */
    public function chmodRecursive($path, $mode){

        $items = $this->recursiveIterate($path);

        foreach($items as $item){
            chmod($item,$mode);
        }//foreach

    }//chmodRecursive



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

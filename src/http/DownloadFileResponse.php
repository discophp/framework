<?php
namespace Disco\http;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadFileResponse extends BinaryFileResponse {

    public function __construct($file, $status = 200, array $headers = array(), $public = true, $contentDisposition = null, $autoEtag = false, $autoLastModified = true)
    {
        parent::__construct($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);
        $this->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($file));
    }

}
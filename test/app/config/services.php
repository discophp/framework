<?php

return Array(

    'Cache'         => function(){
        $config = require \App::path() . '/app/config/cache.php';
        \phpFastCache::setup($config);
        return phpFastCache();
    },
    'Crypt'         => 'Disco\classes\Crypt',
    'Data'          => 'Disco\classes\Data',
    'DB'            => 'Disco\classes\PDO',
    'Event'         => 'Disco\classes\Event',
    'Email'         => 'Disco\classes\Email',
    'FileHelper'    => 'Disco\classes\FileHelper',
    'Form'          => 'Disco\classes\Form',
    'Html'          => 'Disco\classes\Html',
    'Model'         => 'Disco\classes\ModelFactory',
    'Queue'         => 'Disco\classes\Queue',
    'Request'       => 'Disco\classes\Request',
    'Session'       => 'Disco\classes\Session',
    'Template'      => 'Disco\classes\Template',
    'View'          => 'Disco\classes\View'

);

?>

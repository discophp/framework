<?php

return Array(

    'DEV_MODE'              => false,
    'MAINTENANCE_MODE'      => false,

    'DOMAIN'                => '',
    'FORCE_HTTPS'           => false,

    'CSRF_TOKEN_NAME'       => 'APP_CSRF_TOKEN',

    'TEMPLATE_EXTENSION'    => Array('.html','.twig'),
    'TEMPLATE_PATH'         => '/app/template/',

    'DB_ENGINE'             => 'mysql',
    'DB_CHARSET'            => 'utf8',

    'DB_HOST'               => '127.0.0.1',
    'DB_USER'               => 'root',
    'DB_PASSWORD'           => '',
    'DB_DB'                 => 'test_db',

    'AES_KEY256'            => 'def00000440d830145f0e6d2870a144b04582f3c4dce4dbeb3a934743530135060d3ccd897850f23d843d74b43d19d10e67ee743a53de6ed04d80b45c20e803679a1f703',

    'SHA512_SALT'           => '8ef4ddd7384ce58d9ef4b41d2ed34f46631264b650c77ccb6c94ee61c72d481902d1116315941ec10b10b6ec378411f9fad2966a916c056cc9696d1706955190',

);

?>

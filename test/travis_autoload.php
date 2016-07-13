<?php
require('autoload.php');

\App::config('DB_DB','test_db');
\App::config('DB_USER','root');
\App::config('DB_PASSWORD','');
\App::config('DB_HOST','127.0.0.1');

\App::config('AES_KEY256',\Disco\manage\Manager::genAES256Key());
\App::config('SHA512_SALT_LEAD',\Disco\manage\Manager::genSalt(12));
\App::config('SHA512_SALT_TAIL',\Disco\manage\Manager::genSalt(12));

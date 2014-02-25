<?php

$time = microtime();

require_once('../core/Disco.core.php');


Disco::useView('bitcoin/bitcoin');


Disco::useRouter('bitcoin/bitcoinRouter');


View::printPage();


echo '<script>console.log("'.(microtime()-$time).'");</script>';

?>

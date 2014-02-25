<?php

require_once('/var/www/disco/core/Database.class.php');

require_once('/var/www/disco/support_libraries/phpquery/phpQuery/phpQuery.php');

$db = new Database();

$html = file_get_contents('http://www.bitcoincharts.com/markets');

$doc = phpQuery::newDocument($html);


$traders = Array(new Trader('bitstampUSD'),new Trader('mtgoxUSD'),new Trader('btceUSD'),new Trader('localbtcUSD'),new Trader('cbxUSD'));

foreach($traders as $trader){
    $ele = "table#markets tr#{$trader->id}";
    $trader->price = pq("$ele td:nth-child(3)")->text();
    $trader->price = trim(substr($trader->price,0,stripos($trader->price,' ')-2));
    $trader->lastUpdated = pq("$ele td:nth-child(1)")->attr('latest_trade');
    $trader->average = pq("$ele td:nth-child(5)")->text();
    $trader->average = trim(substr($trader->average,0,stripos($trader->average,' ')-2));
    $trader->symbol =  pq("$ele td.symbol nobr a")->text();
    $trader->link =  pq("$ele td.symbol nobr a")->attr('href');

    $trader->price = number_format($trader->price,7);
    $trader->average = number_format($trader->average,7);
}//foreach

foreach($traders as $trader){
    //echo "
    //    <div>
    //        <p>{$trader->symbol}</p>
    //        <p>{$trader->link}</p>
    //        <p>{$trader->price}</p>
    //        <p>{$trader->lastUpdated}</p>
    //        <p>{$trader->average}</p>
    //    </div>
    //    <br>
    //";

    //$trader->create();

    $trader->update();
    //echo 'Updated trader: '.$trader->symbol.PHP_EOL;
}

class Trader {
    public $price=0;
    public $symbol='';
    public $link='';
    public $average=0;
    public $id='';
    public $lastUpdated=0;
    public function __construct($id){
        $this->id=$id;
    }//construct

    public function create(){
        global $db;
        $db->executeQuery("
            INSERT INTO trader VALUES('{$this->id}','{$this->symbol}','{$this->link}')
        ");
    }//create

    public function update(){
        global $db;
        $db->executeQuery("
            INSERT INTO price VALUES(null,'{$this->id}',{$this->price},{$this->average},{$this->lastUpdated})
        ");
    }//update
}//Trader

?>

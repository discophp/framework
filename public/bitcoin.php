<?php

require_once('../core/Disco.class.php');

//extend the view class with 
//your own extended view class
//$disco->useView('bitcoin/bitcoin');



//or keep the standard view class 
//and set the header through a template
$view->setHeader($template->build('bitcoin/header'));


$view->setTitle('BitCoin Prices');


$db->executeQuery("
    SELECT 
    t.trader_id,
    t.symbol,
    p.latest,
    p.average,
    p.time
    FROM trader t
    INNER JOIN price p ON p.trader_id=t.trader_id
    ORDER BY t.symbol ASC,p.time DESC 
");


$prices=Array();
$priceIter=0;
$traders=Array();

for($currentRow=0;$currentRow<$db->last->num_rows;$currentRow++){
    $db->last->data_seek($currentRow);
    $row =$db->last->fetch_assoc(); 
    $id=$row['trader_id'];

    $prices[$priceIter]['latest']=rtrim($row['latest'],0);
    $prices[$priceIter]['average']=rtrim($row['average'],0);
    $prices[$priceIter]['time']=$util->buildTime($row['time']);


    if($currentRow+1<$db->last->num_rows){
        $db->last->data_seek($currentRow+1);
        $trow = $db->last->fetch_assoc();
        if($trow['trader_id']==$id){
            $priceIter++;
            continue;
        }//if
    }//if

    $traders[]=Array('symbol'=>$row['symbol'],'prices'=>$prices);
    $prices=Array();

}//while


//$traders=Array('traders'=>$traders);
$data=Array(
    'traders'=>$traders,
    'footerInfo'=>Array('footerMessage'=>date('m-d-Y H:m:s'))
);

$template->with('bitcoin/bitcoin',$data);



$view->printPage();


?>

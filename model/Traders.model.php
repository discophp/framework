<?php

class Traders {

    public function latestTrades(){
        DB::query("
            SELECT 
            t.trader_id,
            t.symbol,
            p.latest,
            p.average,
            p.time
            FROM (
                    SELECT 
                    p.latest,
                    p.average,
                    p.trader_id,
                    p.time
                    FROM price p
                    ORDER BY p.price_id DESC 
                    LIMIT 5
            ) AS p
            INNER JOIN trader t ON t.trader_id=p.trader_id
            ORDER BY p.time DESC
        ");
        
        
        $prices=Array();
        $priceIter=0;
        $traders=Array();
        
        for($currentRow=0;$currentRow<DB::last()->num_rows;$currentRow++){
            Disco::db()->last->data_seek($currentRow);
            $row =Disco::db()->last->fetch_assoc(); 
            $id=$row['trader_id'];
        
            $prices[$priceIter]['latest']=rtrim($row['latest'],0);
            $prices[$priceIter]['average']=rtrim($row['average'],0);
            $prices[$priceIter]['time']=Disco::util()->buildTime($row['time']);
        
        
            if($currentRow+1<Disco::db()->last->num_rows){
                Disco::db()->last->data_seek($currentRow+1);
                $trow = Disco::db()->last->fetch_assoc();
                if($trow['trader_id']==$id){
                    $priceIter++;
                    continue;
                }//if
            }//if
        
            //$traders[]=Array('symbol'=>$row['symbol'],'trader_id'=>$row['trader_id'],'prices'=>$prices);
            $traders[]=Array('symbol'=>$row['symbol'],'prices'=>$prices,'trader_id'=>$row['trader_id']);
            $prices=Array();
        
        }//while

        return Array('traders'=>$traders);

    }//latestTrades

    public function single($id){
        Disco::db()->executeQuery("
            SELECT 
            t.symbol,
            p.latest,
            p.average,
            p.time
            FROM trader t 
            INNER JOIN price p ON p.trader_id=t.trader_id
            WHERE t.trader_id='{$id}'
            ORDER BY p.time
        ");

        $data = Array('symbol'=>'','prices'=>Array());
        while($row = Disco::db()->last->fetch_assoc()){
            $data['symbol']=$row['symbol'];
            $data['prices'][]=Array(
                'latest'=>$row['latest'],
                'average'=>$row['average'],
                'time'=>Disco::util()->buildTime($row['time']),
                'unixTime'=>$row['time']
            );
        }//while

        return $data;
    }//single

}//Traders

?>

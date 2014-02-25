<?php


Router::get('/',function(){

    View::title('USD Bitcoin Prices');

    $data = Model::m('Traders')->latestTrades();

    Template::with('bitcoin/bitcoin',$data);

});


Router::get('/trader/{id}',function($id){

    $data = Model::m('Traders')->single($id);

    $data['message']='Yo';

    Template::with('bitcoin/single',$data);

    View::scriptSrc('jquery.dataTables.min');
    View::script('$(function(){$("#price-table").dataTable({"iDisplayLength":50,"aaSorting":[[3,"desc"]]});})');

});


Router::get('/nested/{extend}',function($extend){
    View::html('<p>hey1</p>');
    Disco::useRouter('bitcoin/nested');
})->where('extend','[a-z]+');


Router::post('/charts',function(){
    View::html($_POST['name']);   
});



?>

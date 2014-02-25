<?php

Disco::router()->get('/nested/{type}',function($type){
    View::html("<p>hey $type</p>");
});


?>

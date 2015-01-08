<?php

Class DiscoPhpTestFactory {

    private $factoryAddOneTest=0;

    public function addOne(){
        $this->factoryAddOneTest++;
        return $this->factoryAddOneTest;
    }//addOne

}

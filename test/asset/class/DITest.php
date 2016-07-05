<?php

class DITest {

    public $Data;
    public $DB;

    public function __construct(\Disco\classes\Data $Data,\Disco\classes\PDO $DB){
        $this->Data = $Data;
        $this->DB = $DB;
    }//__construct

}

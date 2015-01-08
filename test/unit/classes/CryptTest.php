<?php

Class CryptTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $this->Crypt = new \Disco\classes\Crypt(\App::instance());
    }//setUp

    public function testSha(){
        $org = 'String to test sha on 52309';
        $sha = $this->Crypt->encrypt($org);
        $plain = $this->Crypt->decrypt($sha);
        $this->assertEquals($org,$plain);
    }//testSha

    public function testHash(){
        $v = 'test@email.com';
        $this->assertEquals($this->Crypt->hash($v),$this->Crypt->hash($v));
    }//testHash

}//CryptTest

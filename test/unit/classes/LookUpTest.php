<?php

Class LookUpTest extends PHPUnit_Framework_TestCase {

    public function setUp(){

        $this->dbRef = new DBTest;
        $this->dbRef->setUp();
        $this->DB = $this->dbRef->DB;

    }//setUp

    public function tearDown(){
        $this->dbRef->tearDown();
    }//tearDown


    public function testLookUp(){

        //Test basics
        $PersonLookUp = new PersonLookUp;
        $result = $PersonLookUp->name('Person One')->fetch();
        $this->assertTrue(is_array($result));
        $result = $result[0];
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('person_id',$result);
        $this->assertArrayHasKey('name',$result);
        $this->assertArrayHasKey('age',$result);
        $this->assertArrayHasKey('email',$result);
        $this->assertEquals('Person One',$result['name']);
        $this->assertEquals('test1@email.com',$result['email']);

        //Test Condition
        $PersonLookUp = new PersonLookUp;
        $result = $PersonLookUp->age('> 23')->fetch();
        $this->assertEquals(3,count($result));

        //Test Search
        $PersonLookUp = new PersonLookUp;
        $result = $PersonLookUp->search('Four')->fetch();
        $this->assertEquals(1,count($result));

        //Test limit
        $PersonLookUp = new PersonLookUp;
        $result = $PersonLookUp->limit(1)->fetch();
        $this->assertEquals(1,count($result));

        //Test Order + Page + Limit
        $PersonLookUp = new PersonLookUp;
        $result = $PersonLookUp
            ->name('Person One')
            ->order('email')
            ->limit(1)
            ->page(1)
            ->fetch();
        $this->assertEquals(1,count($result));
        $this->assertEquals('test11@email.com',$result[0]['email']);


    }//testLookUp


}//LookUpTest

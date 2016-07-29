<?php

Class ModelTest extends PHPUnit_Framework_TestCase {


    public function setUp(){

        $this->dbRef = new DBTest;
        $this->dbRef->setUp();
        $this->DB = $this->dbRef->DB;

        $this->Person = new PersonModelTest(\App::instance());

    }//setUp

    public function tearDown(){
        $this->dbRef->tearDown();
    }//tearDown

    public function testSelect(){

        $result = $this->Person->select('name')->data();
        $this->assertEquals(6,$result->rowCount());

        $row = $this->Person->select('name')->where('person_id=:id',Array('id' => 1))->first();
        $this->assertEquals('Person One',$row['name']);

        $row = $this->Person->select('name')->where('person_id=?',Array(2))->first();
        $this->assertEquals('Person Two',$row['name']);

        $row = $this->Person->select('name')->where('person_id=?',3)->first();
        $this->assertEquals('Person Three',$row['name']);


    }//testSelect


    /**
     * @depends testSelect
    */
    public function testWhereAndOr(){

        $result = $this->Person->select('name')
            ->where('person_id=?',1)
            ->data()->fetch();

        $this->assertEquals('Person One',$result['name']);

        $result = $this->Person->select('name')
            ->where(Array('person_id'=>1))
            ->data()->fetch();

        $this->assertEquals('Person One',$result['name']);

        $result = $this->Person->select('name')
            ->where(Array('person_id'=>11,'person_id'=>22))
            ->otherwise(Array('person_id'=>12,'person_id'=>1))
            ->data()->fetch();

        $this->assertEquals('Person One',$result['name']);

        $result = $this->Person->select('name')
            ->where('person_id=? AND person_id=?',Array(22,20))
            ->otherwise('person_id=?',1)
            ->data()->fetch();

        $this->assertEquals('Person One',$result['name']);

    }//testWheres


    /**
     * @depends testSelect
    */
    public function testLimitAndOrder(){

        $result = $this->Person->select('person_id')->limit(2)->order('person_id')->data();

        $this->assertEquals(2,$result->rowCount());

        $row = $result->fetch();
        $this->assertEquals(1,$row['person_id']);

        $row = $result->fetch();
        $this->assertEquals(2,$row['person_id']);


    }//testLimit


    /**
     * @depends testSelect
    */
    public function testInsert(){

        $id = $this->Person->insert(Array('name'=>'Person Seven','age'=>26));
        $this->assertEquals(7,$id);

        $id = $this->Person->insert('name,age',Array('Person Eight',24));
        $this->assertEquals(8,$id);


    }//testInsert

    /**
     * @depends testInsert
    */
    public function testUpdate(){

        $this->Person->update(Array('age'=>10))->where('person_id=?',1)->finalize();
        $row = $this->Person->select('age')->where('person_id=?',1)->data()->fetch();

        $this->assertEquals(10,$row['age']);

    }//testUpdate


    /**
     * @depends testInsert
     * @depends testUpdate
    */
    public function testDelete(){

        $this->Person->delete('person_id=?',7);
        $result = $this->Person->select('name')->where(Array('person_id'=>7))->data();
        $this->assertEquals(0,$result->rowCount());

        $this->Person->delete(Array('person_id'=>8));
        $result = $this->Person->select('name')->where(Array('person_id'=>8))->data();
        $this->assertEquals(0,$result->rowCount());

    }//testDelete


    /**
     * @depends testSelect
    */
    public function testJoin(){

        $result = $this->Person->alias('p')
            ->select('e.email')
            ->where('p.person_id=?',1)
            ->join('PersonEmailModelTest AS e','p.person_id=e.person_id')
            ->data();

        $row = $result->fetch();

        $this->assertEquals('test1@email.com',$row['email']);

    }//testJoin


}//ModelTest

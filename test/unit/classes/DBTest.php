<?php

Class DBTest extends PHPUnit_Framework_TestCase {

    public function setUp(){

        $this->DB = new \Disco\classes\DB;

        //CREATE THE TEST SCHEMA
        $this->DB->multi_query(file_get_contents('vendor/discophp/framework/test/asset/mock-database-schema.sql'));

        while ($this->DB->next_result()) {;} // flush multi_queries

        //INSERT SOME TEST DATA
        $this->DB->multi_query(file_get_contents('vendor/discophp/framework/test/asset/mock-database-data.sql'));

        while ($this->DB->next_result()) {;} // flush multi_queries

        //CREATE A STORED PROCEDURE 
        $this->DB->query(file_get_contents('vendor/discophp/framework/test/asset/mock-database-stored-procedure.sql'));

    }//setUp

    public function tearDown(){

        $this->DB->query('DROP TABLE discophp_test_person');
        $this->DB->query('DROP TABLE discophp_test_person_email');
        $this->DB->query('DROP PROCEDURE discophp_test_sp');

    }//tearDown

    public function testSet(){

        $q = $this->DB->set('? ? ? ? ?',Array(200,'Test',Array('raw'=>'NOW()'),'Whats up?',40));

        $this->assertEquals("200 'Test' NOW() 'Whats up?' 40",$q);

    }//testSet

    /*
     * @depends testSet
    */
    public function testQuery(){

        $result = $this->DB->query('SELECT name,age FROM discophp_test_person WHERE person_id=?',1);

        $this->assertEquals(1,$result->num_rows);

        $row = $result->fetch_assoc();

        $this->assertEquals(Array('name'=>'Person One','age'=>30),$row);

    }//testQuery

    /*
     * @depends testSet
    */
    public function testLastId(){

        $this->DB->query('INSERT INTO discophp_test_person (person_id,name,age) VALUES(NULL,?,?)',Array('Test Seven',55));

        $this->assertEquals(7,$this->DB->lastId());

    }//testLastId


    public function testStoredProcedure(){

        $result = $this->DB->sp('CALL discophp_test_sp()');

        $this->assertEquals(1,count($result));

        $this->assertEquals(Array('name'=>'Person One'),$result[0]);

    }//testStoredProcedure

}//DBTest

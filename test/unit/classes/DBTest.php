<?php

Class DBTest extends PHPUnit_Framework_TestCase {

    public function setUp(){

        $this->DB = \DB::instance();

        //CREATE THE TEST SCHEMA
        $this->DB->query("
CREATE  TABLE `discophp_test_person` (
      `person_id` INT NOT NULL AUTO_INCREMENT ,
      `name` VARCHAR(120) NOT NULL ,
      `age` INT NULL ,
      PRIMARY KEY (`person_id`) 
);

CREATE  TABLE `discophp_test_person_email` (
      `email_id` INT NOT NULL AUTO_INCREMENT ,
      `person_id` INT NOT NULL ,
      `email` VARCHAR(180) NOT NULL ,
      PRIMARY KEY (`email_id`, `person_id`) 
);
INSERT INTO discophp_test_person (person_id,name,age) VALUES (NULL,'Person One',30);
INSERT INTO discophp_test_person (person_id,name,age) VALUES (NULL,'Person Two',20);
INSERT INTO discophp_test_person (person_id,name,age) VALUES (NULL,'Person Three',22);
INSERT INTO discophp_test_person (person_id,name,age) VALUES (NULL,'Person Four',23);
INSERT INTO discophp_test_person (person_id,name,age) VALUES (NULL,'Person Five',24);
INSERT INTO discophp_test_person (person_id,name) VALUES (NULL,'Person Six');

INSERT INTO discophp_test_person_email(email_id,person_id,email) VALUES (NULL,1,'test1@email.com');
INSERT INTO discophp_test_person_email(email_id,person_id,email) VALUES (NULL,1,'test11@email.com');
INSERT INTO discophp_test_person_email(email_id,person_id,email) VALUES (NULL,2,'test2@email.com');
INSERT INTO discophp_test_person_email(email_id,person_id,email) VALUES (NULL,3,'test3@email.com');
INSERT INTO discophp_test_person_email(email_id,person_id,email) VALUES (NULL,3,'test31@email.com');
INSERT INTO discophp_test_person_email(email_id,person_id,email) VALUES (NULL,3,'test32@email.com');
INSERT INTO discophp_test_person_email(email_id,person_id,email) VALUES (NULL,5,'test5@email.com');
");

    }//setUp

    public function tearDown(){

        $this->DB->query('DROP TABLE discophp_test_person');
        $this->DB->query('DROP TABLE discophp_test_person_email');

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

        $this->assertEquals(1,$result->rowCount());

        $row = $result->fetch();

        $this->assertEquals(Array('name'=>'Person One','age'=>30),$row);

    }//testQuery

    /*
     * @depends testSet
    */
    public function testLastId(){

        $this->DB->query('INSERT INTO discophp_test_person (person_id,name,age) VALUES(NULL,?,?)',Array('Test Seven',55));

        $this->assertEquals(7,$this->DB->lastId());

    }//testLastId


}//DBTest

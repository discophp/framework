<?php

Class RecordTest extends PHPUnit_Framework_TestCase {


    public function setUp(){

        $this->dbRef = new DBTest;
        $this->dbRef->setUp();
        $this->DB = $this->dbRef->DB;

    }//setUp

    public function tearDown(){
        $this->dbRef->tearDown();
    }//tearDown


    public function testRecord(){

        $record = new PersonRecordTest(Array('age' => 20));

        //must fail because `name` field is null 
        try {
            $record->insert();
            $this->assertTrue(false);
        } catch(\Disco\exceptions\Record $e){
            $this->assertTrue(true);
        }//catch

        $record = new PersonRecordTest();

        $record['name'] = 'Brad';

        $person_id = $record->insert();

        $this->assertTrue(is_numeric($person_id));

        $find = PersonRecordTest::find(Array('name' => 'Brad'),'person_id');

        $this->assertEquals($person_id,$find['person_id']);

        $record = new PersonRecordTest();

        $record->person_id = $person_id;

        $this->assertTrue($record->exists());

        $record->name = 'Tyler';
        $record->age = 21;

        $record->update();

        $find = PersonRecordTest::find(Array('person_id' => $person_id),Array('person_id','name'));

        $this->assertEquals('Tyler',$find['name']);

        $find->fetchMissing();

        $this->assertEquals(21,$find['age']);

        $find = new PersonRecordTest(Array('person_id' => $person_id));

        $find->fetch(Array('name','age'));

        $this->assertEquals('Tyler',$find->name);
        $this->assertEquals(21,$find->age);

        $record['age'] = 'twenty';

        $this->assertFalse($record->validateField('age'));

        $record = new PersonRecordTest(Array('person_id' => $person_id,'name' => 'Bobby'));

        $this->assertArrayHasKey('name',$record->diff());

        $record['name'] = '';

        $record->convertEmptyStringsToNull();

        $this->assertEquals(null,$record['name']);


        $record = new PersonRecordTest(Array('name' => 'Brad', 'age' => 'twenty'));

        try {
            $record->insert();
            $this->assertTrue(false);
        } catch(\Disco\exceptions\RecordValidation $e){
            $this->assertTrue(true);
        }//catch

        try {
            $record['age'] = 20;
            $record->update();
            $this->assertTrue(false);
        } catch(\Disco\exceptions\RecordId $e){
            $this->assertTrue(true);
        }//catch


        $record = new PersonRecordTest(Array('person_id' => $person_id));

        $record->delete();

        $this->assertFalse(PersonRecordTest::find(Array('person_id' => $person_id)));

        \Disco\classes\Record::setValidationLevel(\Disco\classes\Record::VALIDATION_STRICT);

        $record = new PersonRecordTest();

        $record['name'] = str_repeat('a',121);

        try {
            $record->insert();
            $this->assertTrue(false);
        } catch(\Disco\exceptions\RecordValidation $e){
            $this->assertTrue(true);
        }//catch

    }//testInsert

}//RecordTest


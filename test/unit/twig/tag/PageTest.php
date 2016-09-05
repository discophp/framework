<?php

Class PageTest extends PHPUnit_Framework_TestCase {



    public function setUp(){

        $this->DBRef = new DBTest;
        $this->DBRef->setUp();

    }//setUp


    public function tearDown(){

        $this->DBRef->tearDown();

    }//setUp



    public function testPageTag(){

        Template::render('page-lookup-test.html');

        $pageTagTestLookUp = View::get('pageTagTestLookUp');
        $pageTagTestPaginate = View::get('pageTagTestPaginate');

        $this->assertTrue(is_array($pageTagTestLookUp));
        $this->assertEquals(1,count($pageTagTestLookUp));
        $this->assertTrue($pageTagTestPaginate instanceof \Disco\classes\Paginate);
        $this->assertEquals(2,$pageTagTestPaginate->totalPages);


        Template::render('page-model-test.html');

        $pageTagTestModel = View::get('pageTagTestModel');
        $pageTagTestPaginate = View::get('pageTagTestPaginate');

        $this->assertTrue(is_array($pageTagTestModel));
        $this->assertEquals(1,count($pageTagTestModel));
        $this->assertEquals('Person Two',$pageTagTestModel[0]['name']);
        $this->assertTrue($pageTagTestPaginate instanceof \Disco\classes\Paginate);
        $this->assertEquals(5,$pageTagTestPaginate->totalPages);

        
    }//testPageTag



}//TemplateTest


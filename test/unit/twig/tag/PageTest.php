<?php

Class PageTest extends PHPUnit_Framework_TestCase {



    public function setUp(){

        $this->TemplateRef = new TemplateTest;
        $this->DBRef = new DBTest;

        $this->TemplateRef->setUp();
        $this->DBRef->setUp();

    }//setUp


    public function tearDown(){

        $this->TemplateRef->tearDown();
        $this->DBRef->tearDown();

    }//setUp



    public function testPageTag(){

        $this->TemplateRef->Template->render('page-test.html');

        $pageTagTestLookUp = View::get('pageTagTestLookUp');
        $pageTagTestPaginate = View::get('pageTagTestPaginate');

        $this->assertTrue(is_array($pageTagTestLookUp));
        $this->assertEquals(1,count($pageTagTestLookUp));
        $this->assertTrue($pageTagTestPaginate instanceof \Disco\classes\Paginate);
        $this->assertEquals(2,$pageTagTestPaginate->totalPages);
        
    }//testPageTag



}//TemplateTest


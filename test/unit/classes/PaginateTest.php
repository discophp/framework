<?php

class PaginateTest extends PHPUnit_Framework_TestCase {


    public function testPaginate(){

        $Paginate = new \Disco\classes\Paginate(3, 48, 8);

        //current page
        $this->assertEquals(3, $Paginate->currentPage);

        //first item in result set
        $this->assertEquals(17, $Paginate->first);

        //last item in result set
        $this->assertEquals(24, $Paginate->last);

        //per page
        $this->assertEquals(8, $Paginate->perPage);

        //total results
        $this->assertEquals(48, $Paginate->total);

        //total displayed
        $this->assertEquals(8, $Paginate->totalDisplayed);

        //total pages
        $this->assertEquals(6, $Paginate->totalPages);

        //current url
        $this->assertEquals('/page/3', $Paginate->currentUrl);

        //first url
        $this->assertEquals('/page/1', $Paginate->firstUrl);

        //last url
        $this->assertEquals('/page/6', $Paginate->lastUrl);

        //prev url
        $this->assertEquals('/page/2', $Paginate->prevUrl);

        //next url
        $this->assertEquals('/page/4', $Paginate->nextUrl);

        $uriCache = $_SERVER['REQUEST_URI'];
        $qsCache = $_SERVER['QUERY_STRING'];
        $_SERVER['REQUEST_URI'] = '/test-page?foo=bar';
        $_SERVER['QUERY_STRING'] = 'foo=bar';

        $Paginate = new \Disco\classes\Paginate(2, 5, 3);

        //current page
        $this->assertEquals(2, $Paginate->currentPage);

        //first item in result set
        $this->assertEquals(4, $Paginate->first);

        //last item in result set
        $this->assertEquals(5, $Paginate->last);

        //per page
        $this->assertEquals(3, $Paginate->perPage);

        //total results
        $this->assertEquals(5, $Paginate->total);

        //total displayed
        $this->assertEquals(2, $Paginate->totalDisplayed);

        //total pages
        $this->assertEquals(2, $Paginate->totalPages);

        //current url
        $this->assertEquals('/test-page/page/2?foo=bar', $Paginate->currentUrl);

        //first url
        $this->assertEquals('/test-page/page/1?foo=bar', $Paginate->firstUrl);

        //last url
        $this->assertEquals('/test-page/page/2?foo=bar', $Paginate->lastUrl);

        //prev url
        $this->assertEquals('/test-page/page/1?foo=bar', $Paginate->prevUrl);

        //next url
        $this->assertEquals(null, $Paginate->nextUrl);

        $_SERVER['REQUEST_URI'] = $uriCache;
        $_SERVER['QUERY_STRING'] = $qsCache;;

    }//testPaginate


}//PaginateTest


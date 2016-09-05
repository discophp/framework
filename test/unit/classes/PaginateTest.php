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


        //previous urls
        $urls = $Paginate->getPrevUrls(5);

        //only 2 pages come before page 3 (the current page)
        $this->assertEquals(2,count($urls));
        $this->assertEquals('/page/1',$urls[0]);
        $this->assertEquals('/page/2',$urls[1]);

        //next urls
        $urls = $Paginate->getNextUrls(5);

        //only 3 pages come after page 3 (the current page)
        $this->assertEquals(3,count($urls));
        $this->assertEquals('/page/4',$urls[0]);
        $this->assertEquals('/page/5',$urls[1]);
        $this->assertEquals('/page/6',$urls[2]);

        //all urls
        $urls = $Paginate->getAllUrls();
        $this->assertEquals(6,count($urls));


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


        //current page `3` doesn't exist in result set.
        $Paginate = new \Disco\classes\Paginate(3, 5, 3);

        $this->assertTrue($Paginate->pageDoesNotExist());


    }//testPaginate


}//PaginateTest


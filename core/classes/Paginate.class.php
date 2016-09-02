<?php
namespace Disco\classes;

/**
 * This class becomes available as the variable `page` inside {{ page }}{{ endpage}} tags in twig templates. It 
 * provides the functionality for creating navigation in paged content, as well as context awareness such as the 
 * current page and total pages.
 */
class Paginate {

    /**
     * @var boolean $paginateUsed Whether the template served actually used pagination or not.
    */
    public static $paginateUsed = false;


    /**
     * @var string $format The pagination format as defined in `app/config/config.php` as key `paginate`, or by 
     * default `/page/?`. Must contain a question mark which will be replaced with the current page.
    */
    protected $format = '/page/?';


    /**
     * @var string $uri The current page URI.
    */
    private $uri;


    /**
     * @var int $totalItems The total number of items in the result set.
    */
    public $totalItems;


    /**
     * @var int $limit The limit used in the query.
    */
    public $limit;


    /**
     * @var int $currentPage The current page being paginated.
    */
    public $currentPage = 1;


    /**
     * @var int $total The total number of results/items available for pagination.
    */
    public $total;


    /**
     * @var int $first The position of the first item in the paginated result set, ex: if you have 5 pages of 10 
     * items each, and your on page 3, first will be 31.
    */
    public $first;


    /**
     * @var int $last The position of the last item in the paginated result set, ex: if you have 5 pages of 10 
     * items each, and your on page 5, last will be 60. If there were less items than the limit, then last will be that 
     * number, ex: your on page 5 but there are only 58 items, last will be 58 and not 60.
    */
    public $last;


    /**
     * @var int $perPage The number of results per page.
    */
    public $perPage;


    /**
     * @var int $totalDisplayed The number of results being displayed on the current page.
    */
    public $totalDisplayed;


    /**
     * @var int $totalPages The total number of pages for the paginated results.
    */
    public $totalPages;


    /**
     * @var null|string $prevUrl The url to the previous page in the paginated results.
    */
    public $prevUrl;


    /**
     * @var null|string $nextUrl The url to the next page in the paginated results.
    */
    public $nextUrl;


    /**
     * @var string $firstUrl The url to the first page in the paginated results.
    */
    public $firstUrl;


    /**
     * @var string $currentUrl The url to the current page in the paginated results.
    */
    public $currentUrl;


    /**
     * @var string $lastUrl The url to the last page in the paginated results.
    */
    public $lastUrl;



    /**
     * From the passed arguements all public variables of this class can be derived and will be set.
     *
     *
     * @param int $currentPage The current page being paginated.
     * @param int $totalItems The total items being paginated.
     * @param int $limit The limit used in the query for results to be paginated.
    */
    public function __construct($currentPage,$totalItems,$limit){

        self::$paginateUsed = true;

        if(\App::configKeyExists('paginate')){
            $this->format = \App::config('paginate');
        }//if

        $this->uri = explode('?',$_SERVER['REQUEST_URI'])[0];

        if(substr($this->format,0,1) == '/' && substr($this->uri,-1) == '/'){
            $this->uri = rtrim($this->uri,'/');
        }//if

        if($currentPage == 0){
            $currentPage = 1;
        }//if

        //public vars
        $this->currentPage = $currentPage;
        $this->limit        = $limit;
        $this->totalItems  = $totalItems;
        $this->totalPages  = ceil($this->totalItems / $this->limit);
        $this->perPage      = $limit;
        $this->total        = $this->totalItems;
        $this->first        = ($this->currentPage - 1) * $this->limit + 1;
        if($this->totalPages != $this->currentPage){
            $this->totalDisplayed = $this->limit;
        } else {
            $this->totalDisplayed = $this->totalItems % $this->limit;
            if($this->totalDisplayed == 0){
                $this->totalDisplayed = $this->limit;
            }//if
        }//el
        $this->last         = $this->first + $this->totalDisplayed - 1;


        if(strpos($this->uri,$this->fromFormat($this->currentPage)) === false){
            $this->uri .= $this->fromFormat($this->currentPage);
        }//if

        $domain = \App::domain();

        if($currentPage != 1){
            $this->prevUrl = $this->getPageUrl($currentPage - 1);
            \View::headExtra("<link rel='prev' href='{$domain}{$this->prevUrl}'>");
        }//if

        if($this->currentPage != $this->totalPages && $this->totalPages != 0){
            $this->nextUrl = $this->getPageUrl($currentPage + 1);
            \View::headExtra("<link rel='next' href='{$domain}{$this->nextUrl}'>");
        }//if


        $this->firstUrl = $this->getPageUrl(1);
        $this->currentUrl = $this->getPageUrl($this->currentPage);
        $this->lastUrl = $this->getPageUrl($this->totalPages);

        if($currentPage == 1 && REQUEST_URI != $this->currentUrl){
            \View::headExtra("<link rel='canonical' href='{$domain}{$this->currentUrl}'/>");
        }//if

    }//__construct



    /**
     * Create the pagination portion of the URI for links using `$this->format`.
     *
     *
     * @param int $page The page number to format.
     *
     * @return string
    */
    private function fromFormat($page){
        return str_replace('?',$page,$this->format);
    }//fromFormat



    /**
     * Get the url for a specified page number.
     *
     *
     * @param int $page The page number to build a link to.
     *
     * @return string
    */
    public function getPageUrl($page){
        $qs = '';
        if($_SERVER['QUERY_STRING']){
            $qs = '?' . $_SERVER['QUERY_STRING'];
        }//if
        return str_replace($this->fromFormat($this->currentPage),$this->fromFormat($page),$this->uri) . $qs;
    }//getPageUrl



    /**
     * Get paginated urls that come before the current page.
     *
     *
     * @param null|int $floor The max number of links to generate.
     *
     * @return string[] 
    */
    public function getPrevUrls($floor = null){

        if($floor){
            $floor = $this->currentPage - $floor;
        } else {
            $floor = 1;
        }//el

        return $this->getRangeUrls($floor,$this->currentPage - 1);

    }//getPrevUrls



    /**
     * Get paginated urls that come after the current page.
     *
     *
     * @param null|int $max The max number of links to generate.
     *
     * @return string[] 
    */
    public function getNextUrls($max = null){

        if($max){
            $max = $this->currentPage + $max;
        } else {
            $max = $this->totalPages;
        }//el

        return $this->getRangeUrls($this->currentPage + 1,$max);

    }//getNextUrls



    /**
     * Get all the urls for pages in the results.
     *
     *
     * @return string[]
    */
    public function getAllUrls(){

        return $this->getRangeUrls(1,$this->totalPages);

    }//getAllUrls



    /**
     * Return the urls for the pages between `$start` and `$end`.
     *
     *
     * @param int $start The page to start from.
     * @param int $end The page to end on.
     *
     * @return string[]
    */
    public function getRangeUrls($start,$end){

        $urls = Array();

        if($end > $this->totalPages){
            $end = $this->totalPages;
        }//if

        if($start < 1){
            $start = 1;
        }//if

        for($i = $start; $i <= $end; $i++){
            $urls[$i] = $this->getPageUrl($i);
        }//for

        return $urls;
       
    }//getRangeUrls



    /**
     * Get a simple markup for a pagination feed.
     *
     * @return string The pagination markup.
    */
    public function getEasyMarkup(){

        $markup = '';

        if($this->prevUrl){
            $markup .= "<li class='pagination-previous' title='View previous page'><a href='{$this->prevUrl}'>prev</a></li>";
        }//if

        $pages = $this->getPrevUrls(5);

        foreach($pages as $i => $page){
            $markup .= "<li><a href='{$page}' title='View page {$i} of {$this->totalPages}'>{$i}</a></li>";
        }//foreach

        if($this->totalPages){
            $markup .= "<li class='current' title='Your viewing page {$this->currentPage} of {$this->totalPages}'><a>{$this->currentPage}</a></li>";
        }//if

        $pages = $this->getNextUrls(5);

        foreach($pages as $i => $page){
            $markup .= "<li><a href='{$page}' title='View page {$i} of {$this->totalPages}'>{$i}</a></li>";
        }//foreach

        if($this->nextUrl){
            $markup .= "<li class='pagination-next' title='View next page'><a href='{$this->nextUrl}'>next</a></li>";
        }//if

        return "<ul class='pagination'>{$markup}</ul>";

    }//getEasyMarkup



}//Paginate
?>

<?php
namespace Disco\classes;

/**
 * This is the Html class.
 * It helps you build html elements with a logical and simple syntax.
 * This class doesn't not contain any actual methods, but instead using 
 * the magic method __call($method,$args) we can build any specified html
 * element from a mocked method.
 * For example:
 * $img = Html::img(Array('src'=>'/example.png'));
 * $div = Html::div('cool div');
*/
Class Html {

    /**
     * @var string The base string we build markup from.
    */
    public $base = "<%1\$s>%2\$s</%3\$s>";

    /**
     * @var string The base string we build markup from for elements that are singletons or non closing.
    */
    public $noCloseBase = "<%1\$s/>";

    /**
     * @var string The base string we build a single properties from.
    */
    public $prop = "%1\$s=\"%2\$s\" ";

    /**
     * @var Array Html elements that are singletons and do not use a closing tag.
    */
    public $noClose = Array('area','base','br','col','command','embed','hr','img','input','link','meta','param','source');

    public $stack = false;


    private $app;

    public function __construct(){
        $this->app = \App::instance();
    }//__construct


    /**
     * The magic method __call() allows us to treat any method that is called as the intented
     * html element to be created.
     *
     * If $args[0] is an Array then $args[0] will be treated as properties to be included on the element
     * containing content specified in $args[1]. Otherwise $args[0] will be treated as the content of the element.
     *
     *
     * @param string $method The method name called and also the intented html element to return.
     * @param Array $args The arguements passed to $method.
     *
     * @return string
    */
    public function __call($method,$args){
        $ele = $method;
        $props = '';
        if(is_array($args[0])){
            foreach($args[0] as $k=>$v){
                $props .= sprintf($this->prop,$k,$v);
            }//foreach
            rtrim($props);

            if(!isset($args[1]) && in_array($method,$this->noClose)){
                $out = sprintf($this->noCloseBase,$method.' '.$props);
                if($this->stack){
                    $this->app['View']->html($out);
                    $this->stack = false;
                }//if
                else {
                    return $out;
                }//el
            }//if

            $method .= ' '.$props;

            $html = $args[1];
        }//if
        else {
            $html = $args[0];
        }//el

        $out = sprintf($this->base,$method,$html,$ele);

        if($this->stack){
            $this->app['View']->html($out);
            $this->stack = false;
        }//if
        else {
            return $out;
        }//el

    }//__call



    public function push(){
        $this->stack = true;
        return $this;
    }//stack


}//Html

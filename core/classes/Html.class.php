<?php
namespace Disco\classes;

/**
 * This is the Html class.
 * It helps you build html elements with a logical and simple syntax.
 * This class does not contain any actual methods, but instead using
 * the magic method __call($method,$args) we can build any specified html
 * element from a mocked method.
 * For example:
 * `
 * $img = Html::img(Array('src'=>'/example.png'));
 * $div = Html::div('cool div');
 * `
*/
Class Html {


    /**
     * @var string The base string we build markup from.
    */
    protected $base = "<%1\$s>%2\$s</%3\$s>";


    /**
     * @var string The base string we build markup from for elements that are singletons or non closing.
    */
    protected $noCloseBase = "<%1\$s/>";


    /**
     * @var string The base string we build a single properties from.
    */
    protected $prop = "%1\$s=\"%2\$s\" ";


    /**
     * @var Array Html elements that are singletons and do not use a closing tag.
    */
    protected $noClose = Array('area','base','br','col','command','embed','hr','img','input','link','meta','param','source');


    /**
     * @var boolean $stack Wil the next built element be pushed onto the current application Views stack.
    */
    public $stack = false;



    /**
     * The magic method __call() allows us to treat any method that is called as the intented
     * html element to be created.
     *
     * If $args[0] is an Array then $args[0] will be treated as properties to be included on the element
     * containing content specified in $args[1]. Otherwise $args[0] will be treated as the content of the element.
     *
     *
     * @param string $method The method name called and also the intented html element to return.
     * @param Array $args The arguments passed to $method.
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
            $props = rtrim($props);

            if(!isset($args[1]) && in_array($method,$this->noClose)){
                $out = sprintf($this->noCloseBase,$method.' '.$props);
                if($this->stack){
                    \View::html($out);
                    $this->stack = false;
                }//if
                else {
                    return $out;
                }//el
            }//if

            $method .= ' '.$props;

            $html = '';
            if(isset($args[1])){
                $html = $args[1];
            }//if

        }//if
        else {
            $html = $args[0];
        }//el

        $out = sprintf($this->base,$method,$html,$ele);

        if($this->stack){
            \View::html($out);
            $this->stack = false;
        }//if
        else {
            return $out;
        }//el

    }//__call



    /**
     * If called in the begging of the method chain it will push the built html onto the current 
     * applications View html stack.
     *
     *
     * @return self
    */
    public function push(){
        $this->stack = true;
        return $this;
    }//push


}//Html

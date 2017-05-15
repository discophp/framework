<?php
namespace Disco\classes;
/**
 * This file holds the Class MockBox.
*/


/**
 *  This little class helps empower our Router. 
 *  Essentially it only does one thing by default without extension.
 *  It will always return itself from methods calls it doesn't actually have.
 *  WTF? Why would we need to do this? When we are dealing with large method chains 
 *  and some action happens in which we want to purge an object, prevent instantiation of an object or generally
 *  let the method chain fail gracefully this really comes in handy. Instead of returning $this from the method in 
 *  the chain we can return a MockBox that was only ever instantiated once, thus preventing extraneous class
 *  instantiation and speeding up your application. 
 *
 *  Trust us, we're from the Internet.
*/
class MockBox {



    /**
     * When a method is called that does not exist, just return $this back.
     *
     * @param string $method The method name being called.
     * @param array $args The args passed to the method.
     *
     * @return self
    */
    public function __call($method, $args){
        return $this;        
    }//__call



}//MockBox
?>

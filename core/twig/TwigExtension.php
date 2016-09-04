<?php
namespace Disco\twig;

/**
 * Disco Twig Extension. Provides globals, token parsers, functions etc.
 * About twig extensions: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
 *
 * @author    WebYoke <support@webyoke.com>
 * @copyright Copyright (c) 2015, WebYoke 
 */
class TwigExtension extends \Twig_Extension {



    /**
     * The name of the extension.
     *
     * @return string
    */
    public function getName(){
        return 'App';
    }//getName



    /**
     * The globals provided by the extension.
     *
     * @return array
    */
    public function getGlobals(){

        return Array(
            'App'       => \App::instance(),
            'View'      => \App::with('View'),
            'Request'   => \App::with('Request'),
        );

    }//getGlobals



    /**
     * The token parsers provided by the extension.
     *
     * @return array
    */
    public function getTokenParsers(){

        return Array(
            new \Disco\twig\CacheTokenParser,
            new \Disco\twig\ScriptTokenParser,
            new \Disco\twig\StyleTokenParser,
            new \Disco\twig\PageTokenParser
        );

    }//getTokenParsers



    /**
     * The functions provided by the extension.
     *
     * @return array
    */
    public function getFunctions(){

        return Array(
                new \Twig_SimpleFunction('call',function(){
                    $args = func_get_args();
                    $func = array_shift($args);
                    return call_user_func_array($func,$args);
                }),
            );

    }//getFunctions



}//TwigExtension

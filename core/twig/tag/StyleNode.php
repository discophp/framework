<?php
namespace Disco\twig;

/**
 * Node for `style` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag 
 * Docs</a>.
 *
 *
 * @author    WebYoke <support@webyoke.com>
 * @copyright Copyright (c) 2015, WebYoke 
 */
class StyleNode extends \Twig_Node {


    /**
     * Provide the functionality for compiling a {{style}} {{endstyle}} tag.
     *
     *
     * @param \Twig_Compiler $compiler A \Twig_compliler instance.
     *
     *
     * @return void
    */
    public function compile(\Twig_Compiler $compiler) {

        $compiler
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("\$style = ob_get_clean();\n")
            ->write('\View::style($style);');

    }//compile



}//StyleNode

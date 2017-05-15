<?php
namespace Disco\twig;

/**
 * Node for `script` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag 
 * Docs</a>.
 *
 *
 * @author    WebYoke <support@webyoke.com>
 * @copyright Copyright (c) 2015, WebYoke 
 */
class ScriptNode extends \Twig_Node {


    /**
     * Provide the functionality for compiling a {{script}} {{endscript}} tag.
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
            ->write("\$script= ob_get_clean();\n")
            ->write('\View::script($script);');

    }//compile



}//ScriptNode

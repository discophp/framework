<?php
namespace Disco\twig;

/**
 * Compliler for `page` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag 
 * Docs</a>.
 */
class PageNode extends \Twig_Node {


    /**
     * Provide the functionality for compiling a {{page}} {{endpage}} tag.
     *
     *
     * @param \Twig_Compiler $compiler A \Twig_compliler instance.
     *
     *
     * @return void
    */
    public function compile(\Twig_Compiler $compiler) {

        $compiler->addDebugInfo($this)
            ->write("list(\$context['page'], ")
            ->subcompile($this->getNode('elementsTarget'))
            ->raw(') = \Disco\twig\PageNode::page(')
            ->subcompile($this->getNode('criteria'))
            ->raw(");\n")
            ->subcompile($this->getNode('body'), false)
            ->write('unset($context[\'page\'], ')
            ->subcompile($this->getNode('elementsTarget'))
            ->raw(");\n");

    }//compile



    /**
     * The method the twig tag `page` uses to perform pagination on lookups.
     *
     *
     * @param mixed $lookup A instance of a class that extends {@link \Disco\classes\AbstractLookUp}.
     *
     * @return array The first element is the {@link \Disco\classes\Paginate}, the second is the results of the 
     * lookup.
    */
    public static function page($lookup){

        $page = \Disco\classes\Router::$paginateCurrentPage;

        $lookup->page($page);

        $copy = clone $lookup;

        return Array(
            (new \Disco\classes\Paginate($page, $copy->total()->fetch(), $lookup->getLimit())),
            $lookup->fetch(),
        );

    }//page



}//PageNode

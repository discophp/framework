<?php
namespace Disco\twig;

/**
 * Compiler for `page` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag
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
     * The method the twig tag `page` uses to perform pagination on lookups and models.
     *
     *
     * @param \Disco\classes\Lookup|\Disco\classes\Model $object A instance of a class that extends {@link \Disco\classes\LookUp} or {@link
     * \Disco\classes\Model}.
     *
     * @return array The first element is the {@link \Disco\classes\Paginate}, the second is the results of the 
     * lookup or model query.
     *
     * @throws \Disco\exceptions\Exception When trying to use the lookup tag on a class that does not extend {@link \Disco\classes\LookUp} or {@link \Disco\classes\Model}.
    */
    public static function page($object){

        $page = \Disco\classes\Router::$paginateCurrentPage;

        $parent = get_parent_class($object);

        if($parent === 'Disco\classes\LookUp'){

            $object->page($page);

            $copy = clone $object;

            return Array(
                (new \Disco\classes\Paginate($page, $copy->total()->fetch(), $object->getLimit())),
                $object->fetch(),
            );

        } else if($parent === 'Disco\classes\Model'){

            $limit = $object->getLimit();

            $object->limit($page - 1,$limit['limit']);

            $copy = clone $object;

            $copy->limit(0,1)->select('COUNT(*) AS total');

            return Array(
                (new \Disco\classes\Paginate($page, $copy->first()['total'], $limit['limit'])),
                $object->asArray(),
            );

        }//elif

        throw new \Disco\exceptions\Exception('The page tag can only be applied to classes that extend `\Disco\classes\LookUp` and `\Disco\classes\Model`');

    }//page



}//PageNode

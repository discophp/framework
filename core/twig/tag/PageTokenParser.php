<?php
namespace Disco\twig;

/**
 * Token Parser for `page` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag 
 * Docs</a>.
 */
class PageTokenParser extends \Twig_TokenParser {



    /**
     * Define how our page tag should be parsed.
     *
     *
     * @param \Twig_Token $token An instance of a \Twig_Token.
     *
     * @return \Disco\twig\PageNode
    */
    public function parse(\Twig_Token $token) {

        $lineno = $token->getLine();

        $nodes['criteria'] = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect('as');
        $targets = $this->parser->getExpressionParser()->parseAssignmentExpression();
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        $nodes['body'] = $this->parser->subparse(array($this, 'endOfTag'), true);
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        $elementsTarget = $targets->getNode(0);
        $nodes['elementsTarget'] = new \Twig_Node_Expression_AssignName($elementsTarget->getAttribute('name'), $elementsTarget->getLine());

        return new PageNode($nodes, array(), $lineno, $this->getTag());

    }//parse



    /**
     * Define how a page tag begins. Returns `page`.
     *
     *
     * @return string
    */
    public function getTag() {
        return 'page';
    }//getTag



    /**
     * Define how a page tag ends. Returns `endpage`.
     *
     *
     * @param \Twig_Token $token An instance of a \Twig_Token.
     *
     * @return string
    */
    public function endOfTag(\Twig_Token $token) {
        return $token->test('endpage');
    }//endOfTag



}//PageTokenParser

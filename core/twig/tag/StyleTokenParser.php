<?php
namespace Disco\twig;

/**
 * Token Parser for `style` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag 
 * Docs</a>.
 *
 *
 * @author    WebYoke <support@webyoke.com>
 * @copyright Copyright (c) 2015, WebYoke 
 */
class StyleTokenParser extends \Twig_TokenParser {



    /**
     * Define how our style tag should be parsed.
     *
     *
     * @param \Twig_Token $token An instance of a \Twig_Token.
     *
     * @return \Disco\twig\StyleNode
    */
    public function parse(\Twig_Token $token) {

        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $nodes = Array(
                'body'      => null,
            );

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        $nodes['body'] = $this->parser->subparse(array($this, 'endOfTag'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new StyleNode($nodes, Array(), $lineno, $this->getTag());

    }//parse



    /**
     * Define how a page tag begins. Returns `style`.
     *
     *
     * @return string
    */
    public function getTag() {
        return 'style';
    }//getTag



    /**
     * Define how a page tag ends. Returns `endstyle`.
     *
     *
     * @param \Twig_Token $token An instance of a \Twig_Token.
     *
     * @return string
    */
    public function endOfTag(\Twig_Token $token) {
        return $token->test('endstyle');
    }//endOfTag



}//StyleTokenParser

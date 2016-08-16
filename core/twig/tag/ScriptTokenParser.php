<?php
namespace Disco\twig;

/**
 * Token Parser for `script` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag 
 * Docs</a>.
 *
 *
 * @author    WebYoke <support@webyoke.com>
 * @copyright Copyright (c) 2015, WebYoke 
 */
class ScriptTokenParser extends \Twig_TokenParser {



    /**
     * Define how our script tag should be parsed.
     *
     *
     * @param \Twig_Token $token An instance of a \Twig_Token.
     *
     * @return \Disco\twig\ScriptNode
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

        return new ScriptNode($nodes, Array(), $lineno, $this->getTag());

    }//parse



    /**
     * Define how a page tag begins. Returns `script`.
     *
     *
     * @return string
    */
    public function getTag() {
        return 'script';
    }//getTag



    /**
     * Define how a page tag ends. Returns `endscript`.
     *
     *
     * @param \Twig_Token $token An instance of a \Twig_Token.
     *
     * @return string
    */
    public function endOfTag(\Twig_Token $token) {
        return $token->test('endscript');
    }//endOfTag



}//ScriptTokenParser

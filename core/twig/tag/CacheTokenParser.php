<?php
namespace Disco\twig;

/**
 * Token Parser for `cache` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag 
 * Docs</a>.
 *
 *
 * @author    WebYoke <support@webyoke.com>
 * @copyright Copyright (c) 2015, WebYoke 
 */
class CacheTokenParser extends \Twig_TokenParser {



    /**
     * Define how our cache tag should be parsed.
     *
     *
     * @param \Twig_Token $token An instance of a \Twig_Token.
     *
     * @return \Disco\twig\CacheNode
    */
    public function parse(\Twig_Token $token) {

        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $attributes = Array(
                'key'       => null,
                'duration'  => null
            );

        $nodes = Array(
                'body'      => null,
                'until'     => null,
                'if'        => null,
                'unless'    => null
            );

        if($stream->test(\Twig_Token::NAME_TYPE, 'key')){

            $stream->next();
            $attributes['key'] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();

        }//if


        if($stream->test(\Twig_Token::NAME_TYPE, 'if')){

            $stream->next();
            $nodes['if'] = $this->parser->getExpressionParser()->parseExpression();

        } else if($stream->test(\Twig_Token::NAME_TYPE, 'unless')){

            $stream->next();
            $nodes['unless'] = $this->parser->getExpressionParser()->parseExpression();

        }//if


        if($stream->test(\Twig_Token::NAME_TYPE, 'for')){

            $stream->next();
            $attributes['duration'] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();

        } else if($stream->test(\Twig_Token::NAME_TYPE, 'until')){

            $stream->next();
            $nodes['until'] = $this->parser->getExpressionParser()->parseExpression();

        }//if


        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        $nodes['body'] = $this->parser->subparse(array($this, 'endOfTag'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new CacheNode($nodes, $attributes, $lineno, $this->getTag());

    }//parse



    /**
     * Define how a page tag begins. Returns `cache`.
     *
     *
     * @return string
    */
    public function getTag() {
        return 'cache';
    }//getTag



    /**
     * Define how a page tag ends. Returns `endcache`.
     *
     *
     * @param \Twig_Token $token An instance of a \Twig_Token.
     *
     * @return string
    */
    public function endOfTag(\Twig_Token $token) {
        return $token->test('endcache');
    }//endOfTag



}//CacheTokenParser

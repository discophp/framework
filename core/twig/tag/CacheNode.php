<?php
namespace Disco\twig;

/**
 * Compliler for `page` twig template tag. <a href='http://twig.sensiolabs.org/doc/advanced.html#tags'>Twig Tag 
 * Docs</a>.
 *
 *
 * @author    WebYoke <support@webyoke.com>
 * @copyright Copyright (c) 2015, WebYoke 
 */
class CacheNode extends \Twig_Node {


    /**
     * Provide the functionality for compiling a {{cache}} {{endcache}} tag.
     *
     *
     * @param \Twig_Compiler $compiler A \Twig_compliler instance.
     *
     *
     * @return void
    */
    public function compile(\Twig_Compiler $compiler) {

        $duration = $this->getAttribute('duration');
        $key = $this->getAttribute('key');

        $until = $this->getNode('until');
        $if = $this->getNode('if');
        $unless = $this->getNode('unless');

        if($duration === null){
            //10 minutes for default
            $duration = 600;
        }//if
        else {
            $duration = $this->duration($duration);
        }//el

        if($key !== null){
            $key = md5($key);
        } else {
            $key = md5($this->getNode('body'));
        }//el

        $compiler->addDebugInfo($this);

        $compiler
            ->write("if(");

        if($if){

            $compiler
                ->subcompile($if);

        }//if
        else if($unless){

            $compiler
                ->write('!')
                ->subcompile($unless);

        }//elif
        else {

            $compiler
                ->write('true');

        }//el

            $compiler
                ->write("){\n")
                ->write("\$cache = \Cache::get('{$key}');\n")
                ->write("if(\$cache === null){\n")
                    ->write("ob_start();\n")
                    ->subcompile($this->getNode('body'))
                    ->write("\$cache = ob_get_clean();\n");

            if($until){

                $until = $this->duration($until);

                $compiler
                    ->write("\$duration = {$until};\n");

            } else {

                $compiler
                    ->write("\$duration = {$duration};\n");

            }//el

            $compiler
                ->write("\Cache::set('{$key}',\$cache,\$duration);\n")
                ->write("}\n")
                ->write("echo \$cache;\n");

        $compiler
            ->write("} else {\n")
                ->subcompile($this->getNode('body'))
            ->write("}\n");

    }//compile



    /**
     * Convert a potential string date like `+30 days` to seconds.
     *
     * @param int|string
     *
     * @return int
    */
    private function duration($time){
        if(is_numeric($time)){ 
            return $time;
        }//if
        return (new \DateTime($time))->getTimestamp() - (new \DateTime('now'))->getTimestamp();
    }//duration



}//CacheNode

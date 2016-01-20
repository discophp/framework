<?php
namespace Disco\classes;

/**
 * Extension of \Twig_Loader_Filesystem which allows for extension-less template execution.
*/
class TemplateLoader extends \Twig_Loader_Filesystem {



    /**
     * Get the final name where a template lives, regardless of whether it was called with an extension or not.
     *
     *
     * @param string $name The template name.
     * @return string The final template name.
     *
     * @throws \Twig_Error_Loader
    */
    public function getFinalName($name){

        try { 

            return parent::findTemplate($name);

        } catch(\Twig_Error_Loader $e){ 

            $exts = \App::config('TEMPLATE_EXTENSION');

            if($exts){

                if(!is_array($exts)){
                    $exts = Array($exts);
                }//if

                foreach($exts as $e){

                    try {
                        return parent::findTemplate($name . $e);
                    } catch(\Twig_Error_Loader $e){ }

                }//foreach

            }//if

        }//catch

        throw new \Twig_Error_Loader($name);

    }//getFinalName



    /**
     * Get the contents of a template.
     *
     *
     * @param string $name The template name.
     * @return string The contents of the template.
    */
    public function getSource($name){
        return file_get_contents($this->getFinalName($name));
    }//getSource



    /**
     * Get the cache name of a template.
     *
     *
     * @param string $name The template name.
     * @return string The final name of the template.
    */
    public function getCacheKey($name){
        return $this->getFinalName($name);
    }//getCacheKey



    /**
     * Determine whether a template has been updated since the last cache.
     *
     *
     * @param string $name The template name.
     * @return boolean Whether the template is fresh.
     */
    public function isFresh($name, $time){
        return filemtime($this->getFinalName($name)) <= $time;
    }//isFresh



}//TemplateLoader

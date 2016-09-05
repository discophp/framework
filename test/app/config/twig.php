<?php

return Array(
    'debug'                 => false,
    'charset'               => 'utf-8',
    'base_template_class'   => 'Twig_Template',
    'cache'                 => \App::path() . '/app/template/.cache/',
    'auto_reload'           => true,
    'strict_variables'      => false,
    'autoescape'            => false,
    'optimizations'         => -1
);

?>

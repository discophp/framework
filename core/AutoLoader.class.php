<?php
class AutoLoader {

    public static $loader;

    public static function init() {
        if (self::$loader == NULL)
            self::$loader = new self();

        return self::$loader;
    }//init

    public function __construct() {
        spl_autoload_register(array($this,'class'));
    }

    public function class($class) {
        set_include_path(get_include_path().PATH_SEPARATOR.'/classes/');
        spl_autoload_extensions('.class.php');
        spl_autoload($class);
    }


}//AutoLoader

//call
autoloader::init();
?>

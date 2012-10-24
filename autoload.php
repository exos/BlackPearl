<?php


function blackpearl_classes_autoload ($class) {

    $classPath = explode('\\',$class);

    if ($classPath[0] == 'BlackPearl') {

        array_shift($classPath);

        $file = dirname(__FILE__) . "/" . strtolower(implode('/',$classPath)) .".php";

        if (file_exists($file)) {
            require_once($file);
        }
    }
};

spl_autoload_register('blackpearl_classes_autoload');

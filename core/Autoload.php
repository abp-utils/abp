<?php

function autoload($classname)
{
    $classname = str_replace( '\\', '/', $classname);
    $tempClassname = explode('/', $classname);
    if ($tempClassname[0] == Abp::NAME) {
        unset($tempClassname[0]);
        $tempClassname = implode('/', $tempClassname);
        if (file_exists(__DIR__. "/../$tempClassname.php")) {
            include_once __DIR__. "/../$tempClassname.php";
            return;
        }
    }

    if (php_sapi_name() !== 'cli') {
        $root = $_SERVER['DOCUMENT_ROOT'];
    } else {
        $exp = explode('/', $_SERVER['SCRIPT_FILENAME']);
        if (count($exp) > 1) {
            unset($exp[count($exp) - 1]);
        }
        $root = implode('/', $exp);
    }

    if (file_exists( "$root/$classname.php")) {
        include_once "$root/$classname.php";
    }
}
spl_autoload_register('autoload');
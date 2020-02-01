<?php

function autoload($classname)
{
    $classname = str_replace( '\\', '/', $classname);
    echo $classname . '<br>';
    $tempClassname = explode('/', $classname);
    if ($tempClassname[0] == Abp::NAME) {
        unset($tempClassname[0]);
        $tempClassname = implode('/', $tempClassname);
        echo $tempClassname . ' 1<br>';
        if (file_exists(__DIR__. "/../$tempClassname.php")) {
            include_once __DIR__. "/../$tempClassname.php";
            return;
        }
    }

    if (file_exists( "{$_SERVER['DOCUMENT_ROOT']}/$classname.php")) {
        include_once "{$_SERVER['DOCUMENT_ROOT']}/$classname.php";
    } else
        echo $classname . '<br>';
}
spl_autoload_register('autoload');
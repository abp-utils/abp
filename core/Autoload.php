<?php

function autoload($classname)
{
    $classname = str_replace( '\\', '/', $classname);
    if (file_exists(__DIR__. "/../$classname.php")) {
        include_once __DIR__. "/../$classname.php";
    } elseif (file_exists( "{$_SERVER['DOCUMENT_ROOT']}/$classname.php")) {
        include_once "{$_SERVER['DOCUMENT_ROOT']}/$classname.php";
    } else
        echo $classname . '<br>';
}
spl_autoload_register('autoload');
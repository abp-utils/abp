<?php

function autoload($classname)
{
    $classname = str_replace( '\\', '/', $classname);
    if (file_exists("$classname.php")) {
        include_once "Autoload.php";
    }
}
spl_autoload_register('autoload');
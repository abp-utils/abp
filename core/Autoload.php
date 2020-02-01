<?php

function autoload($classname)
{
    $classname = str_replace( '\\', '/', $classname);
    print_r($classname); echo '<br>';
    if (file_exists("$classname.php")) {
        include_once "$classname.php";
    }
}
spl_autoload_register('autoload');
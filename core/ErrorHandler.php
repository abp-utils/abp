<?php

function parseArray($array)
{
    if (empty($array)) {
        return '';
    }

    return parseArgs($array);
}

function parseArgs($args)
{
    $argsString = '';
    foreach ($args as $key => $arg) {
        $argString = '';
        if (!is_int($key)) {
            $argString = "'$key' => ";
        }
        switch (true) {
            case is_object($arg);
                $argString .= get_class($arg);
                break;
            case is_bool($arg);
                $argString .= $arg;
                break;
            case is_array($arg);
                $argString .= '[' . parseArray($arg) . ']';
                break;
            default:
                $argString .= "'$arg'";
        }
        $argsString.= $argString . ', ';
    }
    return mb_substr($argsString, 0, -2);
}

function exception_handler($exception)
{
    $dir = $_SERVER['DOCUMENT_ROOT'];
    echo '<link rel="shortcut icon" type="image/x-icon" href="/resourse/img/logo.png">';
    echo '<link href="https://fonts.googleapis.com/css?family=Satisfy&display=swap" rel="stylesheet">';
    echo '<meta charset="utf-8">';
    echo '<link rel="stylesheet" type="text/css" href="/resourse/style.css?ver=1.0">';
    $exceptionName = get_class($exception);
    $exceptionText = $exception->getMessage();
    $exceptionTraceDebug = $exception->getTrace();
    $trace = [];
    $trace[0]['text'] = 'in ' . $exception->getFile();
    $trace[0]['line'] = $exception->getLine();
    foreach ($exceptionTraceDebug as $key => $exceptionTrace) {
        $trace[($key + 1)]['text'] = 'in ' . $exceptionTrace['file']. ' – ' . $exceptionTrace['class'] . $exceptionTrace['type'] . $exceptionTrace['function'] . '(' . parseArgs($exceptionTrace['args']) . ')';
        $trace[($key + 1)]['line'] = $exceptionTrace['line'];
    }
    require "$dir/app/view/ErrorHandler.php";
    exit();
}
set_exception_handler('exception_handler');
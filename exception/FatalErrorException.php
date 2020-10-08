<?php

namespace abp\exception;

class FatalErrorException
{
    private $message;
    private $file;
    private $line;
    private $traceString;

    public function __construct(array $error)
    {
        $this->file = $error['file'];
        $this->line = $error['line'];
        echo '<pre>';
        print_r($error['message']);
        echo '</pre>';
        exit();
    }

    public function getMessage()
    {

    }

    public function getFile()
    {

    }

    public function getLine()
    {

    }

    public function getTraceAsString()
    {

    }
}
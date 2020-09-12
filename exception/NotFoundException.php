<?php

namespace abp\exception;

use Throwable;

class NotFoundException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if ($message == '') {
            $message = 'Страница не найдена.';
        }
        parent::__construct($message, $code, $previous);
    }
}
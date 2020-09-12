<?php

namespace abp\exception;

use Throwable;

class DatabaseSaveException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = 'Произошла ошибка при сохранении, попробуйте позже.';
        parent::__construct($message, $code, $previous);
    }
}
<?php

namespace abp\exception;

use Throwable;

class DatabaseException extends \Exception
{
    private $state;
    private $dbCode;
    private $textError;
    private $sql;

    public function __construct($message, $sql)
    {
        $messageParts = explode(':', $message);
        $this->state = trim(explode('[', $messageParts[0])[1], ']');
        $sqlErrorCodeText = explode(' ', trim($messageParts[2]));
        $this->dbCode = $sqlErrorCodeText[0];
        unset($sqlErrorCodeText[0]);
        $this->textError = implode(' ', $sqlErrorCodeText);
        $this->sql = $sql;
        parent::__construct($message);
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getTextError(): string
    {
        return $this->textError;
    }

    public function getDbCode(): string
    {
        return $this->dbCode;
    }

    public function getSql()
    {
        return $this->sql;
    }
}
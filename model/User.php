<?php

namespace abp\model;

use abp\database\ActiveQuery;

class User extends ActiveQuery
{
    private static $tableName;

    private $ip;
    private $userAgent;

    public function __construct($tableName)
    {
        self::$tableName = $tableName;
        parent::__construct(get_called_class());
    }

    public static function tableName()
    {
        return self::$tableName;
    }

    public function loginCookie()
    {

    }

    public function has()
    {

    }

    public function getId()
    {

    }

    public function getIp(): ?string
    {
        if ($this->ip === null) {
            $this->ip = \Abp::server()['REMOTE_ADDR'] ?? null;
        }
        return $this->ip;
    }

    public function getUserAgent(): ?string
    {
        if ($this->userAgent === null) {
            $this->userAgent = \Abp::server()['HTTP_USER_AGENT'] ?? null;
        }
        return $this->userAgent;
    }

    public function parseSessionInfo(?string $session): array
    {
        if ($session === null) {
            return [];
        }
        $session = explode('.', $session);
        if (count($session !== 2)) {
            return [];
        }
        return [
            'identity' => $session[0],
            'token' => $session[1],
        ];
    }
}
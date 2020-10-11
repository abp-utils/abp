<?php

namespace abp\model;

use abp\component\Security;
use abp\database\ActiveQuery;

class User extends ActiveQuery
{
    private const USER_ID_COLUMN = 'user_id';
    private const TOKEN_COLUMN = 'token';

    private const SESSION_AUTH_DETERMITER = '.';
    public const SESSION_AUTH_ID = 'SID';
    public const TOKEN_API_ID = 'token';

    private static $tableName;

    private $ip;
    private $userAgent;

    private $isGuest = true;

    private $id;

    public function __construct(string $tableName)
    {
        if (self::$tableName === null) {
            self::$tableName = $tableName;
        }
        parent::__construct(get_called_class());
        $this->loginCookie();
    }

    public static function tableName(): string
    {
        return self::$tableName;
    }

    public function loginCookie(): void
    {
        $cookieSession = \Abp::getCookie(self::SESSION_AUTH_ID);
        if ($cookieSession === null) {
            return;
        }
        [$identity, $token] = $this->parseSessionInfo(\Abp::getCookie(self::SESSION_AUTH_ID));
        if (empty($identity) || empty($token)) {
            return;
        }
        $hashToken = Security::generateHash($token);
        $this->isGuest = !(new UserSession())->whereContidion([
            [self::USER_ID_COLUMN => $identity],
            'and',
            [self::TOKEN_COLUMN => $hashToken],
        ])->where(['is_active' => true])->exist();
        if ($this->isGuest) {
            return;
        }
        $this->id = $identity;
    }

    public function logoutCookie(): void
    {
        \Abp::dropCookie(
            self::SESSION_AUTH_ID
        );
        \Abp::dropCookie(self::TOKEN_API_ID);
    }

    public function has()
    {

    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isGuest(): bool
    {
        return $this->isGuest;
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

    /**
     * @return "UserIdentity.Token"
     */
    public function parseSessionInfo(?string $session): array
    {
        if ($session === null) {
            return [];
        }
        $session = explode(self::SESSION_AUTH_DETERMITER, $session);
        if (count($session) !== 2) {
            return [];
        }
        return [$session[0], $session[1]];
    }

    public function setCookieAuthInfo(string $userId, string $sessionToken, ?string $userToken = null): void
    {
        \Abp::setCookie(
            self::SESSION_AUTH_ID,
            $userId . self::SESSION_AUTH_DETERMITER . $sessionToken
        );
        if ($userToken !== null) {
            \Abp::setCookie(self::TOKEN_API_ID, $userToken);
        }
    }
}
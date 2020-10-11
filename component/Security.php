<?php

namespace abp\component;

class Security
{
   public static function generateRandomString(): string
   {
       return sha1(uniqid('', true));
   }

   public static function generateHashWithSalt(string $string, string $algo = PASSWORD_ARGON2ID): string
   {
        return password_hash($string, $algo);
   }

   public static function generateHash(string $string, string $hash = 'sha1'): string
   {
        return $hash($string);
   }
}

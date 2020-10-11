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

   public static function verifyPassword(string $password, string $hash): bool
   {
       return password_verify($password, $hash);
   }

   public static function generateHash(string $string, string $hash = 'sha1'): string
   {
        return $hash($string);
   }

   public static function generateRandomInt(int $length): int
   {
       if ($length < 1) {
           throw new \InvalidArgumentException('Length must be better 0');
       }
       $baseMin = '0';
       $baseMax = '9';
       $min = '1';
       $max = '9';
       for ($i = 0 ; $i < $length - 1; $i++) {
           $min .= $baseMin;
           $max .= $baseMax;
       }
       return mt_rand($min, $max);
   }
}

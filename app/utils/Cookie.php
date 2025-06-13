<?php

namespace App\utils;

class Cookie
{
    public static function generateCookie(string $token, int $expires): string
    {
        $secure = getenv('PHP_ENV') === 'production';
        return "token={$token}; Path=/; Max-Age={$expires}; HttpOnly; SameSite=Lax" . ($secure ? '; Secure' : '');
    }
}

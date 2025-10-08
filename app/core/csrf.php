<?php
declare(strict_types=1);

namespace App\core;

class Csrf
{
    private const SESSION_KEY = '_csrf_tokens';

    public static function token(string $form = 'default'): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $tokens = $_SESSION[self::SESSION_KEY] ?? [];
        $token = bin2hex(random_bytes(32));
        $tokens[$form] = $token;
        $_SESSION[self::SESSION_KEY] = $tokens;
        return $token;
    }

    public static function validate(string $token, string $form = 'default'): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $tokens = $_SESSION[self::SESSION_KEY] ?? [];
        $valid = isset($tokens[$form]) && hash_equals((string)$tokens[$form], (string)$token);
        if ($valid) {
            unset($_SESSION[self::SESSION_KEY][$form]);
        }
        return $valid;
    }
}


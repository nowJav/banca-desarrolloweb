<?php
declare(strict_types=1);

namespace App\config;

class App
{
    public const DEFAULT_TIMEZONE = 'UTC';
    public const DEFAULT_LOCALE = 'es_ES';

    public static function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    public static function environment(): string
    {
        return (string) self::env('APP_ENV', 'local');
    }

    public static function debug(): bool
    {
        $value = self::env('APP_DEBUG', false);
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public static function timezone(): string
    {
        return (string) self::env('APP_TIMEZONE', self::DEFAULT_TIMEZONE);
    }

    public static function locale(): string
    {
        return (string) self::env('APP_LOCALE', self::DEFAULT_LOCALE);
    }

    public static function init(): void
    {
        date_default_timezone_set(self::timezone());
        if (function_exists('setlocale')) {
            @setlocale(LC_ALL, self::locale() . '.utf8', self::locale());
        }
    }
}


<?php
declare(strict_types=1);

namespace App\helpers;

class Validator
{
    public static function required($value): bool
    {
        return !(is_null($value) || (is_string($value) && trim($value) === ''));
    }

    public static function email(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function stringLen(string $value, int $min, int $max): bool
    {
        $len = mb_strlen($value);
        return $len >= $min && $len <= $max;
    }

    public static function positiveNumber($num, bool $strict = true): bool
    {
        if (!is_numeric($num)) { return false; }
        $n = (float) $num;
        return $strict ? ($n > 0) : ($n >= 0);
    }

    public static function maxNumber($num, float $max): bool
    {
        if (!is_numeric($num)) { return false; }
        return (float)$num <= $max;
    }

    public static function digits(string $value, int $min, int $max): bool
    {
        if (!preg_match('/^\d+$/', $value)) { return false; }
        return self::stringLen($value, $min, $max);
    }
}


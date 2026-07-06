<?php

namespace Helpers;

class Money
{
    public static function add(string $a, string $b): string
    {
        return bcadd($a, $b, 2);
    }

    public static function sub(string $a, string $b): string
    {
        return bcsub($a, $b, 2);
    }

    public static function mul(string $a, string $b): string
    {
        return bcmul($a, $b, 2);
    }

    public static function div(string $a, string $b): string
    {
        return bcdiv($a, $b, 2);
    }

    public static function normalize(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}

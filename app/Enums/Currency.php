<?php

namespace Enums;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case RUB = 'RUB';

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public static function targets(string $current): array
    {
        return array_values(array_diff(self::values(), [$current]));
    }

    public static function default(): string
    {
        return self::EUR->value;
    }

    public static function isBase(string $value): bool
    {
        return $value === self::EUR->value;
    }

    public static function fromValue(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        return null;
    }
}

<?php

namespace App\Enums;

enum Currency: string {
    case BOLIVIANOS = 'BOB';
    case USD = 'USD';

    /**
     * Convert the enum to an associative array.
     *
     * @return array
     */
    public static function toArray(): array {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->name] = $case->value;
        }
        return $array;
    }
}

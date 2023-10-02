<?php

namespace App\Enums;

enum Condition: string {
    case NEW = 'Nuevo';
    case USED = 'Usado';
    case DAMAGED = 'Dañado';

    /**
     * @return array<string,string>
     */
    public static function getLabels(): array
    {
        return [
            self::NEW => 'Nuevo',
            self::USED => 'Usado',
            self::DAMAGED => 'Dañado',
        ];
    }

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

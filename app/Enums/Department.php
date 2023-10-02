<?php

namespace App\Enums;

enum Department: string {
    case LA_PAZ = 'La Paz';
    case COCHABAMBA = 'Cochabamba';
    case SANTA_CRUZ = 'Santa Cruz';
    case POTOSI = 'Potosí';
    case CHUQUISACA = 'Chuquisaca';
    case ORURO = 'Oruro';
    case TARIJA = 'Tarija';
    case PANDO = 'Pando';
    case BENI = 'Beni';

    /**
     * @return array
     */
    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->name] = $case->value;
        }
        return $array;
    }
}

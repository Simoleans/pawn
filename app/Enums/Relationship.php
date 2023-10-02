<?php

namespace App\Enums;

enum Relationship: string {
    case FATHER = 'Padre';
    case MOTHER = 'Madre';
    case SIBLING = 'Hermano(a)';
    case FRIEND = 'Amigo(a)';
    case EMPLOYER = 'Empleador(a)';
    case SPOUSE = 'Cónyuge';
    case IN_LAW = 'Cuñado(a)';
    case GRANDPARENT = 'Abuelo(a)';
    case AUNT_UNCLE = 'Tío(a)';
    case COUSIN = 'Primo(a)';
    case NEIGHBOR = 'Vecino(a)';
    case COLLEAGUE = 'Colega';
    case EMPLOYEE = 'Empleado(a)';
    case ACQUAINTANCE = 'Conocido(a)';
    case RELATIVE = 'Pariente';
    case OTHER = 'Otro';

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

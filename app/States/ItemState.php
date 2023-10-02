<?php

namespace App\States;

class ItemState {
    const PENDING = 'pending';
    const PAWNED = 'pawned';
    const RETRIEVABLE = 'retrievable';
    const WITHDRAWN = 'withdrawn';
    const LOST = 'lost';
    const FOR_SALE = 'for_sale';
    const SOLD = 'sold';

    /**
     * @return array<string,mixed>
     */
    public static function transitions(): array
    {
        return [
            self::PENDING => [self::PAWNED],
            self::PAWNED => [self::RETRIEVABLE, self::LOST],
            self::RETRIEVABLE => [self::WITHDRAWN, self::PAWNED],
            self::LOST => [self::FOR_SALE],
            self::FOR_SALE => [self::SOLD],
            self::SOLD => [],
            self::WITHDRAWN => [],
        ];
    }

    /**
     * @return array<int,array<string,string>>
     */
    public static function getStateDescriptions(): array
    {
        return [
            [
                'state' => self::PENDING,
                'label' => 'Pendiente',
                'description' => 'Ingresado en el sistema pero aún no ha sido empeñado',
            ],
            [
                'state' => self::PAWNED,
                'label' => 'Empeñado',
                'description' => 'Artículo actualmente empeñado',
            ],
            [
                'state' => self::RETRIEVABLE,
                'label' => 'Retirable',
                'description' => 'Artículo puede ser retirado por el cliente',
            ],
            [
                'state' => self::WITHDRAWN,
                'label' => 'Retirado',
                'description' => 'Artículo retirado por el cliente',
            ],
            [
                'state' => self::LOST,
                'label' => 'Perdido',
                'description' => 'Artículo perdido por impago',
            ],
            [
                'state' => self::FOR_SALE,
                'label' => 'En Venta',
                'description' => 'Artículo disponible para la venta',
            ],
            [
                'state' => self::SOLD,
                'label' => 'Vendido',
                'description' => 'Artículo vendido',
            ],
        ];
    }

    public static function getLabel(string $state): string {
        $descriptions = self::getStateDescriptions();
        foreach ($descriptions as $desc) {
            if ($desc['state'] === $state) {
                return $desc['label'];
            }
        }
        throw new \InvalidArgumentException("Estado no válido: {$state}");
    }

    public static function getDescription(string $state): string {
        $descriptions = self::getStateDescriptions();
        foreach ($descriptions as $desc) {
            if ($desc['state'] === $state) {
                return $desc['description'];
            }
        }
        throw new \InvalidArgumentException("Estado no válido: {$state}");
    }

    public static function canTransition(string $fromState, string $toState): bool {
        return in_array($toState, self::transitions()[$fromState] ?? []);
    }
}

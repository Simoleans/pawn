<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Joyería', 'description' => 'Anillos, collares, pulseras, etc.'],
            ['name' => 'Electrónica', 'description' => 'Teléfonos, laptops, cámaras, etc.'],
            ['name' => 'Instrumentos Musicales', 'description' => 'Guitarras, baterías, teclados, etc.'],
            ['name' => 'Herramientas', 'description' => 'Taladros, sierras, herramientas eléctricas, etc.'],
            ['name' => 'Deportes y Recreación', 'description' => 'Equipos de deporte, bicicletas, etc.'],
            ['name' => 'Muebles', 'description' => 'Mesas, sillas, sofás, etc.'],
            ['name' => 'Arte y Antigüedades', 'description' => 'Pinturas, esculturas, antigüedades, etc.'],
            ['name' => 'Vehículos', 'description' => 'Automóviles, motocicletas, etc.'],
            ['name' => 'Videojuegos y Consolas', 'description' => 'Videojuegos, consolas, accesorios, etc.'],
            ['name' => 'Hogar y Cocina', 'description' => 'Electrodomésticos, utensilios de cocina, etc.'],
            ['name' => 'Otros', 'description' => 'Otros artículos diversos.']
        ];

        foreach ($categories as $category) {
            \App\Models\Category::query()->create($category);
        }
    }
}

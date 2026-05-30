<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Lenguaje;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear un Administrador de prueba
        // (Solo para correos @testmind.com)
        Admin::create([
            'name'     => 'Admin TestMind',
            'email'    => 'admin@testmind.com',
            'password' => Hash::make('abc123..'),
        ]);

        // 2. Crear un Usuario estándar de prueba
        User::create([
            'name'     => 'Usuario de Prueba',
            'nickname' => 'tester01',
            'email'    => 'user@test.com',
            'password' => Hash::make('abc123..'),
            'avatar'   => null,
        ]);

        // 3. Crear Idiomas soportados
        $es = Lenguaje::firstOrCreate(['Codigo' => 'es']);
        $en = Lenguaje::firstOrCreate(['Codigo' => 'en']);
        $gl = Lenguaje::firstOrCreate(['Codigo' => 'gl']);

        // 4. Crear Categoría: Informática
        $catInformatica = Categoria::firstOrCreate(
            ['Codigo' => 'INF_01'],
            ['valorUsado' => true]
        );

        // Asociar traducciones para Informática
        $catInformatica->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Informática y Tecnología'],
            $en->id => ['descripcion' => 'Computing and Technology'],
            $gl->id => ['descripcion' => 'Informática e Tecnoloxía'],
        ]);

        // 5. Crear Categoría: Salud
        $catSalud = Categoria::firstOrCreate(
            ['Codigo' => 'SAL_01'],
            ['valorUsado' => true]
        );

        // Asociar traducciones para Salud
        $catSalud->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Ciencias de la Salud'],
            $en->id => ['descripcion' => 'Health Sciences'],
            $gl->id => ['descripcion' => 'Ciencias da Saúde'],
        ]);

        $this->command->info('Base de datos de TestMind (Usuarios, Idiomas y Categorías) poblada con éxito.');
    }
}
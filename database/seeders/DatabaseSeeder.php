<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Categoria;
use App\Models\Lenguaje;
use App\Models\TablaApoyo;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Admin TestMind',
            'email' => 'admin@testmind.com',
            'password' => Hash::make('abc123..'),
        ]);

        $es = Lenguaje::firstOrCreate(['codigo' => 'es']);
        $en = Lenguaje::firstOrCreate(['codigo' => 'en']);
        $gl = Lenguaje::firstOrCreate(['codigo' => 'gl']);

        $tierFree = Tier::firstOrCreate(
            ['codigo' => 'FREE'],
            [
                'conf' => '{}',
                'valorUsado' => true,
            ]
        );

        $tierFree->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Free'],
            $en->id => ['descripcion' => 'Free'],
            $gl->id => ['descripcion' => 'Free'],
        ]);

        $catInformatica = Categoria::firstOrCreate(
            ['codigo' => 'INF_01'],
            ['valorUsado' => true]
        );

        $catInformatica->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Informática y Tecnología'],
            $en->id => ['descripcion' => 'Computing and Technology'],
            $gl->id => ['descripcion' => 'Informática e Tecnoloxía'],
        ]);

        $catSalud = Categoria::firstOrCreate(
            ['codigo' => 'SAL_01'],
            ['valorUsado' => true]
        );

        $catSalud->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Ciencias de la Salud'],
            $en->id => ['descripcion' => 'Health Sciences'],
            $gl->id => ['descripcion' => 'Ciencias da Saúde'],
        ]);

        TablaApoyo::create([
            'nombreTA' => 'TablaApoyo',
            'descripcion' => 'Meta-Tabla del Sistema',
        ]);

        User::create([
            'name' => 'Usuario de Prueba',
            'nickname' => 'tester01',
            'email' => 'user@test.com',
            'password' => Hash::make('abc123..'),
            'avatar' => null,
        ]);

        User::create([
            'name' => 'Usuario de Prueba 2',
            'nickname' => 'tester02',
            'email' => 'user2@test.com',
            'password' => Hash::make('abc123..'),
            'avatar' => null,
        ]);

        $this->command->info('Base de datos de TestMind poblada con éxito.');
    }
}
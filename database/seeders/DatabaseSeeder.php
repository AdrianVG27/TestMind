<?php

namespace Database\Seeders;

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
            'password' => Hash::make('abc123..'), // Encriptada automáticamente
        ]);

        // 2. Crear un Usuario estándar de prueba
        User::create([
            'name'     => 'Usuario de Prueba',
            'nickname' => 'tester01',
            'email'    => 'user@test.com',
            'password' => Hash::make('abc123..'),
            'avatar'   => null,
        ]);
        
        $this->command->info('Usuarios de prueba creados con éxito.');
    }
}
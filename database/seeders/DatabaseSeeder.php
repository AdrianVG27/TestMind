<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\InterfazTraduccion;
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

        $es = Lenguaje::firstOrCreate(
            ['codigo' => 'es'],
            ['descripcion' => 'Castellano']
        );

        $en = Lenguaje::firstOrCreate(
            ['codigo' => 'en'],
            ['descripcion' => 'English']
        );

        $gl = Lenguaje::firstOrCreate(
            ['codigo' => 'gl'],
            ['descripcion' => 'Galego']
        );

        $tierFree = Tier::firstOrCreate(
            ['codigo' => 'FREE'],
            [
                'conf' => [
                    'precio' => 0.00,
                    'maxTests' => 2,
                    'maxExportaciones' => 0,
                    'maxPaginas' => 15,
                    'maxPreguntas' => 10,
                ],
                'valorUsado' => true,
            ]
        );

        $tierFree->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Free'],
            $en->id => ['descripcion' => 'Free'],
            $gl->id => ['descripcion' => 'Free'],
        ]);

        $estadoPendiente = Estado::firstOrCreate(
            ['codigo' => 'P'],
            ['valorUsado' => true]
        );

        $estadoPendiente->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Pendiente'],
            $en->id => ['descripcion' => 'Pending'],
            $gl->id => ['descripcion' => 'Pendente'],
        ]);

        $estadoEnProceso = Estado::firstOrCreate(
            ['codigo' => 'EP'],
            ['valorUsado' => true]
        );

        $estadoEnProceso->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'En Proceso'],
            $en->id => ['descripcion' => 'In Progress'],
            $gl->id => ['descripcion' => 'En Progreso'],
        ]);

        $estadoCompletado = Estado::firstOrCreate(
            ['codigo' => 'C'],
            ['valorUsado' => true]
        );

        $estadoCompletado->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Completado'],
            $en->id => ['descripcion' => 'Completed'],
            $gl->id => ['descripcion' => 'Completado'],
        ]);

        $estadoFallo = Estado::firstOrCreate(
            ['codigo' => 'F'],
            ['valorUsado' => true]
        );

        $estadoFallo->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Fallo'],
            $en->id => ['descripcion' => 'Failed'],
            $gl->id => ['descripcion' => 'Fallo'],
        ]);

        $catInformatica = Categoria::firstOrCreate(
            ['codigo' => 'INF'],
            ['valorUsado' => true]
        );

        $catInformatica->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Informática y Tecnología'],
            $en->id => ['descripcion' => 'Computing and Technology'],
            $gl->id => ['descripcion' => 'Informática e Tecnoloxía'],
        ]);

        $catSalud = Categoria::firstOrCreate(
            ['codigo' => 'SAL'],
            ['valorUsado' => true]
        );

        $catSalud->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Ciencias de la Salud'],
            $en->id => ['descripcion' => 'Health Sciences'],
            $gl->id => ['descripcion' => 'Ciencias da Saúde'],
        ]);

        TablaApoyo::create([
            'nombreTA' => 'TablaApoyo',
            'descripcion' => 'Tabla sistema tablas apoyo',
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

        $jsonEs = [
            'nav' => [
                'home' => 'INICIO',
                'documents' => 'DOCUMENTOS',
                'tests' => 'TESTS',
                'logout' => 'Cerrar sesión',
                'login' => 'INICIAR SESIÓN',
                'profile' => 'PERFIL',
                'adminDashboard' => 'GENERAL',
                'adminGestionTA' => 'GESTION TA',
            ],
            'auth' => [
                'login_title' => 'INICIAR SESIÓN',
                'register_title' => 'NUEVO USUARIO',
                'name' => 'NOMBRE',
                'email' => 'CORREO',
                'remember_me' => 'RECORDAR SESIÓN',
                'password' => 'CONTRASEÑA',
                'confirm_password' => 'CONFIRMAR CONTRASEÑA',
                'login_btn' => 'INICIAR SESIÓN',
                'register_btn' => 'CREAR CUENTA',
                'no_account' => '¿No tienes cuenta?',
                'have_account' => '¿Ya tienes cuenta?',
                'register_link' => 'REGÍSTRATE',
                'login_link' => 'INICIAR SESIÓN',
            ],
        ];

        $jsonGl = [
            'nav' => [
                'home' => 'INICIO',
                'documents' => 'DOCUMENTOS',
                'tests' => 'TESTS',
                'logout' => 'Pechar sesión',
                'login' => 'INICIAR SESIÓN',
                'profile' => 'PERFIL',
                'adminDashboard' => 'XERAL',
                'adminGestionTA' => 'XESTION TA',
            ],
            'auth' => [
                'login_title' => 'INICIAR SESIÓN',
                'register_title' => 'NOVO USUARIO',
                'name' => 'NOME',
                'email' => 'CORREO',
                'remember_me' => 'RECORDAR SESIÓN',
                'password' => 'CONTRASINAL',
                'confirm_password' => 'CONFIRMAR CONTRASINAL',
                'login_btn' => 'INICIAR SESIÓN',
                'register_btn' => 'CREAR CONTA',
                'no_account' => 'Non tes conta?',
                'have_account' => 'Xa tes conta?',
                'register_link' => 'REXÍSTRATE',
                'login_link' => 'INICIAR SESIÓN',
            ],
        ];

        $jsonEn = [
            'nav' => [
                'home' => 'HOME',
                'documents' => 'DOCUMENTS',
                'tests' => 'TESTS',
                'logout' => 'Logout',
                'login' => 'LOGIN',
                'profile' => 'PROFILE',
                'adminDashboard' => 'DASHBOARD',
                'adminGestionTA' => 'AT MANAGEMENT',
            ],
            'auth' => [
                'login_title' => 'LOGIN',
                'register_title' => 'NEW USER',
                'name' => 'NAME',
                'email' => 'EMAIL',
                'remember_me' => 'REMEMBER SESSION',
                'password' => 'PASSWORD',
                'confirm_password' => 'CONFIRM PASSWORD',
                'login_btn' => 'LOGIN',
                'register_btn' => 'CREATE ACCOUNT',
                'no_account' => "Don't have an account?",
                'have_account' => 'Already have an account?',
                'register_link' => 'REGISTER',
                'login_link' => 'LOGIN',
            ],
        ];

        $this->seedDiccionario($es->id, $jsonEs);
        $this->seedDiccionario($gl->id, $jsonGl);
        $this->seedDiccionario($en->id, $jsonEn);

        $this->command->info('Base de datos de TestMind poblada con éxito.');
    }

    private function seedDiccionario(int $lenguajeId, array $datos, string $prefijo = '')
    {
        foreach ($datos as $clave => $valor) {
            $claveCompleta = $prefijo ? "{$prefijo}.{$clave}" : $clave;

            if (is_array($valor)) {
                $this->seedDiccionario($lenguajeId, $valor, $claveCompleta);
            } else {
                InterfazTraduccion::firstOrCreate([
                    'lenguaje_id' => $lenguajeId,
                    'clave' => $claveCompleta,
                ], [
                    'valor' => $valor,
                ]);
            }
        }
    }
}

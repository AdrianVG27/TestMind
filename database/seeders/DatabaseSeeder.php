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
        $this->command->info('Iniciando el volcado completo de la base de datos de TestMind...');

        $tiempoMilisegundos = \Illuminate\Support\Benchmark::measure(function () {

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

            $this->Tiers($es, $en, $gl);

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

            $this->Transloco($es, $en, $gl);
        });

        $tiempoSegundos = round($tiempoMilisegundos / 1000, 2);

        $this->command->info("¡Base de datos de TestMind poblada con éxito! Tiempo de ejecución total: {$tiempoSegundos} segundos.");
    }

    private function Tiers($es, $en, $gl)
    {
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

        $tierPremium = Tier::firstOrCreate(
            ['codigo' => 'PREMIUM'],
            [
                'conf' => [
                    'precio' => 5.00,
                    'maxTests' => 20,
                    'maxExportaciones' => 5,
                    'maxPaginas' => 50,
                    'maxPreguntas' => 30,
                ],
                'paypal_id' => 'P-0GM635368E456774SNISAAQA',
                'valorUsado' => true,
            ]
        );

        $tierPremium->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Premium'],
            $en->id => ['descripcion' => 'Premium'],
            $gl->id => ['descripcion' => 'Premium'],
        ]);

        $tierPremiumPlus = Tier::firstOrCreate(
            ['codigo' => 'PREMIUM_PLUS'],
            [
                'conf' => [
                    'precio' => 9.00,
                    'maxTests' => 50,
                    'maxExportaciones' => 99999,
                    'maxPaginas' => 150,
                    'maxPreguntas' => 90,
                ],
                'paypal_id' => 'P-1RW47621JA801854MNISAAXI',
                'valorUsado' => true,
            ]
        );

        $tierPremiumPlus->lenguajes()->syncWithoutDetaching([
            $es->id => ['descripcion' => 'Premium+'],
            $en->id => ['descripcion' => 'Premium+'],
            $gl->id => ['descripcion' => 'Premium+'],
        ]);
    }

    private function Transloco($es, $en, $gl)
    {
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
            'ERRORS' => [
                'TITLES' => [
                    'SERVER_ERROR' => 'Error del Servidor',
                    'ATTENTION' => 'Atención',
                ],
            ],
            'TEST' => [
                'STATUS' => [
                    'ERROR' => 'ERROR DE IA',
                ],
            ],
            'DETALLES_CUESTIONARIO' => 'DETALLES CUESTIONARIO',
            'NUEVO TEST' => 'NUEVO TEST',
            'PROCESANDO...' => 'PROCESANDO...',
            'START_GENERATION ➔' => 'INICIAR GENERACIÓN ➔',
            'error' => [
                'AdminDashboardController_userSegmentation' => [
                    '500' => 'Error al recuperar las métricas de segmentación de membresías en el panel de control.',
                ],
                'AdminDashboardController_testsCreadosTimeline' => [
                    '500' => 'Fallo al procesar la línea temporal de actividad de contenidos en el servidor.',
                ],
                'AdminDashboardController_testsByCategory' => [
                    '500' => 'Fallo al procesar el reparto analítico por categorías del panel de control.',
                ],
                'AuthController_login' => [
                    '500' => 'Error interno del servidor al procesar las credenciales de inicio de sesión.',
                ],
                'AuthController_logout' => [
                    '500' => 'Error interno del servidor al intentar destruir la sesión activa.',
                ],
                'AuthController_register' => [
                    '500' => 'Error interno del servidor al intentar dar de alta el nuevo perfil de alumno.',
                ],
                'AuthController_registerAdmin' => [
                    '500' => 'Error interno del servidor al registrar las nuevas credenciales de administración.',
                ],
                'AuthController_me' => [
                    '500' => 'Error interno del servidor al sincronizar los datos de tu perfil activo.',
                ],
                'CategoriaController_index' => [
                    '500' => 'No se pudieron recuperar las categorías de estudio disponibles.',
                ],
                'CategoriaController_show' => [
                    '404' => 'La categoría académica solicitada no existe.',
                    '500' => 'Error interno al recuperar los detalles de la categoría seleccionada.',
                ],
                'DocumentoController_indexPublic' => [
                    '500' => 'Error al recuperar el repositorio de documentos compartidos de la comunidad.',
                ],
                'DocumentoController_index' => [
                    '500' => 'Error al recuperar tus apuntes y documentos personales en la nube.',
                ],
                'DocumentoController_store' => [
                    '500' => 'No se pudo guardar el archivo físico del PDF en el disco del servidor.',
                ],
                'DocumentoController_show' => [
                    '403' => 'Acceso denegado. No tienes permisos para visualizar este documento privado.',
                    '500' => 'Error interno al recuperar los metadatos del documento.',
                ],
                'DocumentoController_update' => [
                    '403' => 'Acceso denegado. No tienes permisos para editar las propiedades de este documento.',
                    '500' => 'Error al actualizar el nombre del archivo en el sistema.',
                ],
                'DocumentoController_destroy' => [
                    '403' => 'Acceso denegado. No tienes permisos para eliminar este documento.',
                    '500' => 'Error al eliminar el archivo físico y sus registros del servidor.',
                ],
                'DocumentoController_descargar' => [
                    '403' => 'Acceso denegado. No tienes permisos para descargar este archivo binario.',
                    '404' => 'El archivo físico en formato PDF no se encuentra en la ruta del servidor.',
                    '500' => 'Error interno al preparar la descarga del documento.',
                ],
                'EstadoController_index' => [
                    '500' => 'No se pudieron recuperar los estados lógicos de la base de datos.',
                ],
                'EstadoController_show' => [
                    '404' => 'El estado solicitado no existe en los diccionarios maestros.',
                    '500' => 'Error interno al recuperar los detalles del estado seleccionado.',
                ],
                'ExportacionController_exportarAMoodleGift' => [
                    '404' => 'El cuestionario que intentas exportar no existe.',
                    '422' => 'El campo preguntas no contiene una colección de datos válida para la exportación.',
                    '500' => 'Ocurrió un error interno en el motor de cadenas al compilar el formato GIFT.',
                ],
                'IntentoController_index' => [
                    '500' => 'Error interno al calcular las métricas académicas y recuperar tu historial de exámenes.',
                ],
                'IntentoController_show' => [
                    '403' => 'Acceso denegado. No tienes permisos para visualizar la corrección de este intento.',
                    '500' => 'Error interno al recuperar el desglose de fallos y aciertos del intento.',
                ],
                'InterfaceTranslationController_getJson' => [
                    '404' => 'El idioma solicitado para el diccionario de la interfaz no existe.',
                    '500' => 'Fallo crítico al compilar dinámicamente el archivo de localización para Transloco.',
                ],
                'InterfaceTranslationController_index' => [
                    '500' => 'Error al recuperar el catálogo maestro de literales de la interfaz.',
                ],
                'InterfaceTranslationController_updateKey' => [
                    '500' => 'Fallo al sincronizar, instanciar o propagar la clave en los diccionarios del sistema.',
                ],
                'InterfaceTranslationController_destroyKey' => [
                    '404' => 'La clave idiomática de la interfaz que intentas eliminar no existe.',
                    '500' => 'Error de consistencia interna al purgar el literal del sistema.',
                ],
                'InterfaceTranslationController_destroyLanguage' => [
                    '500' => 'Fallo al eliminar el idioma del sistema y sus tablas de traducción vinculadas.',
                ],
                'PayPalWebhookController_handleWebhook' => [
                    '500' => 'Fallo crítico interno en el procesador de eventos asíncronos de PayPal.',
                ],
                'PayPalWebhookController_vincularSuscripcion' => [
                    '500' => 'Error interno al pre-vincular la suscripción de PayPal a tu cuenta.',
                ],
                'PayPalWebhookController_cancelarSuscripcionActiva' => [
                    '400' => 'La pasarela de PayPal rechazó la solicitud de baja de tu renovación automática.',
                    '422' => 'No se localizó ninguna suscripción de PayPal activa vinculada a este perfil.',
                    '502' => 'Fallo de conexión externa con la pasarela de PayPal. Inténtalo de nuevo.',
                    '500' => 'Error interno de servidor al tramitar la baja de tu renovación automática.',
                ],
                'TablaApoyoController_indexTablas' => [
                    '500' => 'Error al recuperar el catálogo maestro de tablas de apoyo.',
                ],
                'TablaApoyoController_readRows' => [
                    '404' => 'La tabla física o su mapa de filas no existe en el motor de base de datos.',
                    '500' => 'Error al leer los registros dinámicos de la tabla de apoyo seleccionada.',
                ],
                'TablaApoyoController_createRow' => [
                    '400' => 'No se han enviado campos ni datos válidos para procesar la inserción.',
                    '404' => 'La tabla de apoyo parametrizada no se encuentra disponible.',
                    '422' => 'El campo código es obligatorio para dar de alta registros maestros.',
                    '422_duplicate' => 'El código enviado ya se encuentra registrado en este diccionario.',
                    '500' => 'Error al insertar el registro maestro en el motor dinámico.',
                ],
                'TablaApoyoController_updateRow' => [
                    '404' => 'Estructura de la tabla de apoyo o mapa dinámico de filas no localizado.',
                    '422_valorUsado' => 'Operación denegada: El estado de los niveles de suscripción está gestionado por PayPal.',
                    '422_root' => 'Operación denegada: No se permite alterar la identidad de la tabla relacional maestra.',
                    '500' => 'Error crítico al procesar la actualización del registro protegido.',
                ],
                'TablaApoyoController_deleteRow' => [
                    '404' => 'El registro maestro que intentas eliminar no existe en la base de datos.',
                    '422_root' => 'Operación cancelada: No se permite purgar nodos raíz para no romper el panel dinámico.',
                    '500' => 'No se pudo eliminar el registro debido a dependencias activas en el modelo relacional.',
                ],
                'TablaApoyoController_getRowLanguages' => [
                    '404_schema' => 'La estructura multiidioma para la tabla física especificada no existe.',
                    '404' => 'Estructura relacional o mapa dinámico de traducciones no localizado.',
                    '500' => 'No se pudieron recuperar las traducciones de la fila seleccionada.',
                ],
                'TablaApoyoController_updateRowLanguages' => [
                    '400' => 'No se han enviado traducciones válidas para procesar el lote dinámico.',
                    '404' => 'La tabla de traducción parametrizada no existe en el motor relacional.',
                    '500' => 'Error interno al persistir el lote idiomático en la base de datos.',
                ],
                'TestController_indexPublic' => [
                    '500' => 'Error al recuperar los cuestionarios públicos generados por la comunidad.',
                ],
                'TestController_index' => [
                    '500' => 'Error al recuperar tu repositorio personal de cuestionarios privados.',
                ],
                'TestController_store' => [
                    '404_document' => 'El documento base referenciado no existe o no eres su legítimo propietario.',
                    '500' => 'Fallo de infraestructura al encolar tu petición de generación con Inteligencia Artificial.',
                ],
                'TestController_show' => [
                    '403' => 'Acceso denegado. No tienes permisos para ver la configuración de este cuestionario.',
                    '500' => 'No se pudo recuperar la configuración del cuestionario académico seleccionado.',
                ],
                'TestController_update' => [
                    '403' => 'Acceso denegado. No tienes permisos para actualizar este cuestionario.',
                    '500' => 'Error interno al actualizar la configuración y relanzar las colas de la IA.',
                ],
                'TestController_destroy' => [
                    '403' => 'Acceso denegado. No tienes permisos para eliminar este cuestionario.',
                    '500' => 'Error de consistencia interna al purgar el test seleccionado de la base de datos.',
                ],
                'TestController_realizar' => [
                    '500' => 'Fallo al inicializar la instancia para realizar el examen interactivo.',
                ],
                'TestController_corregir' => [
                    '422_empty' => 'Este cuestionario no cuenta con preguntas estructuradas para poder corregirse.',
                    '500' => 'Error crítico al procesar la calificación y el guardado de tu intento de examen.',
                ],
                'CheckTierLimits_handle' => [
                    '403_no_tier' => 'Tu usuario no tiene ningún nivel de suscripción asociado en el sistema.',
                    '403_max_tests' => 'Límite de plan excedido. Tu nivel actual ({{ plan }}) solo permite generar {{ maxTests }} tests cada 24 horas.',
                    '403_max_pages' => 'El documento excede el tamaño permitido. Tu plan ({{ plan }}) permite procesar PDFs de hasta {{ maxPaginas }} páginas (Aprox. {{ pesoMaximoKB }} KB). El archivo subido equivale a unas {{ paginasEstimadas }} páginas.',
                    '403_max_questions' => 'Límite de plan excedido. Tu nivel actual ({{ plan }}) solo permite generar tests de hasta {{ maxPreguntas }} preguntas (Has solicitado {{ solicitadas }}).',
                    '403_max_exports' => 'Función Premium bloqueada. La exportación directa al formato estándar Moodle GIFT no está permitida en el nivel {{ plan }}.',
                    '500' => 'Error interno en la infraestructura de control de limitaciones y suscripciones.',
                ],
                'RefreshTokenTimeout_handle' => [
                    '500' => 'Error interno al refrescar y alargar el tiempo de expiración de tu sesión activa.',
                ],
                'SetLocale_handle' => [
                    '500' => 'Error interno al sincronizar tus preferencias idiomáticas con las cabeceras del servidor.',
                ],
                'GlobalHandler_NotFound' => [
                    '404' => 'El endpoint de la API solicitado o el recurso relacional no existe en TestMind.',
                ],
                'GlobalHandler_Unauthorized' => [
                    '401' => 'Tu sesión ha expirado o el token de acceso es inválido. Por favor, vuelve a iniciar sesión.',
                ],
                'GlobalHandler_Forbidden' => [
                    '403' => 'Acceso restringido. No tienes los privilegios o roles requeridos para ejecutar esta petición.',
                ],
                'GlobalHandler_MethodNotAllowed' => [
                    '405' => 'Error en la arquitectura de red: El método HTTP utilizado no está admitido para esta ruta.',
                ],
                'GlobalHandler_ServerError' => [
                    '500' => 'Se ha producido un error inesperado en los servicios globales del servidor de TestMind.',
                ],
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
            'ERRORS' => [
                'TITLES' => [
                    'SERVER_ERROR' => 'Erro do Servidor',
                    'ATTENTION' => 'Atención',
                ],
            ],
            'TEST' => [
                'STATUS' => [
                    'ERROR' => 'ERRO DE IA',
                ],
            ],
            'DETALLES_CUESTIONARIO' => 'DETALLES DO CUESTIONARIO',
            'NUEVO TEST' => 'NOVO TEST',
            'PROCESANDO...' => 'PROCESANDO...',
            'START_GENERATION ➔' => 'INICIAR XERACIÓN ➔',
            'error' => [
                'AdminDashboardController_userSegmentation' => [
                    '500' => 'Erro ao recuperar as métricas de segmentación de membresías no panel de control.',
                ],
                'AdminDashboardController_testsCreadosTimeline' => [
                    '500' => 'Fallo ao procesar a liña temporal de actividade de contidos no servidor.',
                ],
                'AdminDashboardController_testsByCategory' => [
                    '500' => 'Fallo ao procesar a repartición analítica por categorías do panel de control.',
                ],
                'AuthController_login' => [
                    '500' => 'Erro interno do servidor ao procesar as credenciais de inicio de sesión.',
                ],
                'AuthController_logout' => [
                    '500' => 'Erro interno do servidor ao intentar destruír a sesión activa.',
                ],
                'AuthController_register' => [
                    '500' => 'Erro interno do servidor ao intentar dar de alta o novo perfil de alumno.',
                ],
                'AuthController_registerAdmin' => [
                    '500' => 'Erro interno do servidor ao rexistrar as novas credenciales de administración.',
                ],
                'AuthController_me' => [
                    '500' => 'Erro interno do servidor ao sincronizar os datos do teu perfil activo.',
                ],
                'CategoriaController_index' => [
                    '500' => 'Non se puideron recuperar as categorías de estudo dispoñibles.',
                ],
                'CategoriaController_show' => [
                    '404' => 'A categoría académica solicitada non existe.',
                    '500' => 'Erro interno ao recuperar os detalles da categoría seleccionada.',
                ],
                'DocumentoController_indexPublic' => [
                    '500' => 'Erro ao recuperar o repositorio de documentos compartidos da comunidade.',
                ],
                'DocumentoController_index' => [
                    '500' => 'Erro ao recuperar os teus apuntamentos e documentos persoais na nube.',
                ],
                'DocumentoController_store' => [
                    '500' => 'Non se puido gardar o arquivo físico do PDF no disco do servidor.',
                ],
                'DocumentoController_show' => [
                    '403' => 'Acceso denegado. Non tes permisos para visualizar este documento privado.',
                    '500' => 'Erro interno ao recuperar os metadatos do documento.',
                ],
                'DocumentoController_update' => [
                    '403' => 'Acceso denegado. Non tes permisos para editar as propiedades deste documento.',
                    '500' => 'Erro ao actualizar o nome do arquivo no sistema.',
                ],
                'DocumentoController_destroy' => [
                    '403' => 'Acceso denegado. Non tes permisos para eliminar este documento.',
                    '500' => 'Erro ao eliminar o arquivo físico e os seus rexistros do servidor.',
                ],
                'DocumentoController_descargar' => [
                    '403' => 'Acceso denegado. Non tes permisos para descargar este arquivo binario.',
                    '404' => 'O arquivo físico en formato PDF non se atopa na ruta del servidor.',
                    '500' => 'Erro interno ao preparar a descarga do documento.',
                ],
                'EstadoController_index' => [
                    '500' => 'Non se puideron recuperar os estados lógicos da base de datos.',
                ],
                'EstadoController_show' => [
                    '404' => 'O estado solicitado non existe nos dicionarios mestres.',
                    '500' => 'Erro interno ao recuperar los detalles do estado seleccionado.',
                ],
                'ExportacionController_exportarAMoodleGift' => [
                    '404' => 'O cuestionario que intentas exportar non existe.',
                    '422' => 'O campo preguntas non contén unha colección de datos válida para a exportación.',
                    '500' => 'Ocorreu un erro interno no motor de cadeas ao compilar o formato GIFT.',
                ],
                'IntentoController_index' => [
                    '500' => 'Erro interno ao calcular as métricas académicas e recuperar o teu historial de exames.',
                ],
                'IntentoController_show' => [
                    '403' => 'Acceso denegado. Non tes permisos para visualizar a corrección deste intento.',
                    '500' => 'Erro interno ao recuperar o desglose de fallos e acertos do intento.',
                ],
                'InterfaceTranslationController_getJson' => [
                    '404' => 'O idioma solicitado para o dicionario da interface non existe.',
                    '500' => 'Fallo crítico ao compilar dinámicamente o arquivo de localización para Transloco.',
                ],
                'InterfaceTranslationController_index' => [
                    '500' => 'Erro ao recuperar o catálogo mestre de literais da interface.',
                ],
                'InterfaceTranslationController_updateKey' => [
                    '500' => 'Fallo al sincronizar, instanciar ou propagar a clave nos dicionarios do sistema.',
                ],
                'InterfaceTranslationController_destroyKey' => [
                    '404' => 'A clave idiomática da interface que intentas eliminar non existe.',
                    '500' => 'Erro de consistencia interna ao purgar o literal do sistema.',
                ],
                'InterfaceTranslationController_destroyLanguage' => [
                    '500' => 'Fallo ao eliminar o idioma do sistema e as súas táboas de tradución vinculadas.',
                ],
                'PayPalWebhookController_handleWebhook' => [
                    '500' => 'Fallo crítico interno no procesador de eventos asíncronos de PayPal.',
                ],
                'PayPalWebhookController_vincularSuscripcion' => [
                    '500' => 'Erro interno ao pre-vincular a subscrición de PayPal á túa conta.',
                ],
                'PayPalWebhookController_cancelarSuscripcionActiva' => [
                    '400' => 'A pasarela de PayPal rexeitou a solicitude de baixa da túa renovación automática.',
                    '422' => 'Non se localizou ningunha subscrición de PayPal activa vinculada a este perfil.',
                    '502' => 'Fallo de conexión externa coa pasarela de PayPal. Inténtao de novo.',
                    '500' => 'Erro interno de servidor ao tramitar a baixa da túa renovación automática.',
                ],
                'TablaApoyoController_indexTablas' => [
                    '500' => 'Erro ao recuperar o catálogo mestre de táboas de apoio.',
                ],
                'TablaApoyoController_readRows' => [
                    '404' => 'A táboa física ou o seu mapa de filas non existe no motor de base de datos.',
                    '500' => 'Erro ao ler os rexistros dinámicos da táboa de apoio seleccionada.',
                ],
                'TablaApoyoController_createRow' => [
                    '400' => 'Non se enviaron campos nin datos válidos para procesar a inserción.',
                    '404' => 'A táboa de apoio parametrizada non se atopa dispoñible.',
                    '422' => 'O campo código é obrigatorio para dar de alta rexistros mestres.',
                    '422_duplicate' => 'O código enviado xa se atopa rexistrado neste dicionario.',
                    '500' => 'Erro ao insertar o rexistro mestre no motor dinámico.',
                ],
                'TablaApoyoController_updateRow' => [
                    '404' => 'Estrutura da táboa de apoio ou mapa dinámico de filas non localizado.',
                    '422_valorUsado' => 'Operación denegada: O estado dos niveis de subscrición está xestionado por PayPal.',
                    '422_root' => 'Operación denegada: Non se permite alterar a identidade da táboa relacional mestra.',
                    '500' => 'Erro crítico ao procesar a actualización del rexistro protexido.',
                ],
                'TablaApoyoController_deleteRow' => [
                    '404' => 'O rexistro mestre que intentas eliminar non existe na base de datos.',
                    '422_root' => 'Operación cancelada: Non se permite purgar nodos raíz para non romper o panel dinámico.',
                    '500' => 'Non se puido eliminar o rexistro debido a dependencias activas no modelo relacional.',
                ],
                'TablaApoyoController_getRowLanguages' => [
                    '404_schema' => 'A estrutura multiidioma para a táboa física especificada non existe.',
                    '404' => 'Estrutura relacional ou mapa dinámico de traducións non localizado.',
                    '500' => 'Non se puideron recuperar as traducións da fila seleccionada.',
                ],
                'TablaApoyoController_updateRowLanguages' => [
                    '400' => 'Non se enviaron traducións válidas para procesar o lote dinámico.',
                    '404' => 'A táboa de tradución parametrizada non existe no motor relacional.',
                    '500' => 'Erro interno ao persistir o lote idiomático na base de datos.',
                ],
                'TestController_indexPublic' => [
                    '500' => 'Erro ao recuperar os cuestionarios públicos xerados pola comunidade.',
                ],
                'TestController_index' => [
                    '500' => 'Erro ao recuperar o teu repositorio persoal de cuestionarios privados.',
                ],
                'TestController_store' => [
                    '404_document' => 'O documento base referenciado non existe ou non es o seu lexítimo propietario.',
                    '500' => 'Fallo de infraestrutura ao encolar a túa petición de xeración con Intelixencia Artificial.',
                ],
                'TestController_show' => [
                    '403' => 'Acceso denegado. Non tes permisos para ver a configuración deste cuestionario.',
                    '500' => 'Non se puido recuperar a configuración do cuestionario académico seleccionado.',
                ],
                'TestController_update' => [
                    '403' => 'Acceso denegado. Non tes permisos para actualizar este cuestionario.',
                    '500' => 'Erro interno al actualizar a configuración e relanzar as colas da IA.',
                ],
                'TestController_destroy' => [
                    '403' => 'Acceso denegado. Non tes permisos para eliminar este cuestionario.',
                    '500' => 'Erro de consistencia interna ao purgar o test seleccionado da base de datos.',
                ],
                'TestController_realizar' => [
                    '500' => 'Fallo ao inicializar a instancia para realizar o exame interactivo.',
                ],
                'TestController_corregir' => [
                    '422_empty' => 'Este cuestionario non conta con preguntas estruturadas para poder corrixirse.',
                    '500' => 'Erro crítico ao procesar a cualificación e o gardado do teu intento de exame.',
                ],
                'CheckTierLimits_handle' => [
                    '403_no_tier' => 'O teu usuario non ten ningún nivel de subscrición asociado no sistema.',
                    '403_max_tests' => 'Límite de plan excedido. O teu nivel actual ({{ plan }}) só permite xerar {{ maxTests }} tests cada 24 horas.',
                    '403_max_pages' => 'O documento excede o tamaño permitido. O teu plan ({{ plan }}) permite procesar PDFs de ata {{ maxPaginas }} páxinas (Aprox. {{ pesoMaximoKB }} KB). O arquivo subido equivale a unhas {{ paginasEstimadas }} páxinas.',
                    '403_max_questions' => 'Límite de plan excedido. O teu nivel actual ({{ plan }}) só permite xerar tests de ata {{ maxPreguntas }} preguntas (Solicitaches {{ solicitadas }}).',
                    '403_max_exports' => 'Función Premium bloqueada. A exportación directa ao formato estándar Moodle GIFT non está permitida no nivel {{ plan }}.',
                    '500' => 'Erro interno na infraestrutura de control de limitacións e subscricións.',
                ],
                'RefreshTokenTimeout_handle' => [
                    '500' => 'Erro interno ao refrescar e alargar o tempo de expiración da túa sesión activa.',
                ],
                'SetLocale_handle' => [
                    '500' => 'Erro interno ao sincronizar as túas preferencias idiomáticas coas cabeceiras do servidor.',
                ],
                'GlobalHandler_NotFound' => [
                    '404' => 'O endpoint da API solicitado ou o recurso relacional non existe en TestMind.',
                ],
                'GlobalHandler_Unauthorized' => [
                    '401' => 'A túa sesión expirou ou o token de acceso é inválido. Por favor, volve iniciar sesión.',
                ],
                'GlobalHandler_Forbidden' => [
                    '403' => 'Acceso restrinxido. Non tes os privilexios ou roles requiridos para executar esta petición.',
                ],
                'GlobalHandler_MethodNotAllowed' => [
                    '405' => 'Erro na arquitectura de rede: O método HTTP utilizado non está admitido para esta ruta.',
                ],
                'GlobalHandler_ServerError' => [
                    '500' => 'Produciuse un erro inesperado nos servizos globais do servidor de TestMind.',
                ],
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
            'ERRORS' => [
                'TITLES' => [
                    'SERVER_ERROR' => 'Server Error',
                    'ATTENTION' => 'Attention',
                ],
            ],
            'TEST' => [
                'STATUS' => [
                    'ERROR' => 'AI ERROR',
                ],
            ],
            'DETALLES_CUESTIONARIO' => 'QUIZ DETAILS',
            'NUEVO TEST' => 'NEW QUIZ',
            'PROCESANDO...' => 'PROCESSING...',
            'START_GENERATION ➔' => 'START GENERATION ➔',
            'error' => [
                'AdminDashboardController_userSegmentation' => [
                    '500' => 'Error retrieving membership segmentation metrics in the dashboard.',
                ],
                'AdminDashboardController_testsCreadosTimeline' => [
                    '500' => 'Failed to process the content activity timeline on the server.',
                ],
                'AdminDashboardController_testsByCategory' => [
                    '500' => 'Failed to process the analytical breakdown by categories in the dashboard.',
                ],
                'AuthController_login' => [
                    '500' => 'Internal server error while processing login credentials.',
                ],
                'AuthController_logout' => [
                    '500' => 'Internal server error while attempting to terminate the active session.',
                ],
                'AuthController_register' => [
                    '500' => 'Internal server error while attempting to register the new student profile.',
                ],
                'AuthController_registerAdmin' => [
                    '500' => 'Internal server error while registering the new admin credentials.',
                ],
                'AuthController_me' => [
                    '500' => 'Internal server error while synchronizing your active profile data.',
                ],
                'CategoriaController_index' => [
                    '500' => 'Could not retrieve the available study categories.',
                ],
                'CategoriaController_show' => [
                    '404' => 'The requested academic category does not exist.',
                    '500' => 'Internal error while retrieving the details of the selected category.',
                ],
                'DocumentoController_indexPublic' => [
                    '500' => 'Error retrieving the community shared documents repository.',
                ],
                'DocumentoController_index' => [
                    '500' => 'Error retrieving your personal notes and documents from the cloud.',
                ],
                'DocumentoController_store' => [
                    '500' => 'Could not save the physical PDF file onto the server storage.',
                ],
                'DocumentoController_show' => [
                    '403' => 'Access denied. You do not have permissions to view this private document.',
                    '500' => 'Internal error while retrieving the document metadata.',
                ],
                'DocumentoController_update' => [
                    '403' => 'Access denied. You do not have permissions to edit this document properties.',
                    '500' => 'Error updating the file name in the system.',
                ],
                'DocumentoController_destroy' => [
                    '403' => 'Access denied. You do not have permissions to delete this document.',
                    '500' => 'Error deleting the physical file and its records from the server.',
                ],
                'DocumentoController_descargar' => [
                    '403' => 'Access denied. You do not have permissions to download this binary file.',
                    '404' => 'The physical PDF file could not be found on the server path.',
                    '500' => 'Internal error while preparing the document download.',
                ],
                'EstadoController_index' => [
                    '500' => 'Could not retrieve the logical states from the database.',
                ],
                'EstadoController_show' => [
                    '404' => 'The requested state does not exist in the master dictionaries.',
                    '500' => 'Internal error while retrieving the details of the selected state.',
                ],
                'ExportacionController_exportarAMoodleGift' => [
                    '404' => 'The quiz you are trying to export does not exist.',
                    '422' => 'The questions field does not contain a valid collection of data for export.',
                    '500' => 'An internal error occurred in the string engine while compiling the GIFT format.',
                ],
                'IntentoController_index' => [
                    '500' => 'Internal error while calculating academic metrics and retrieving your quiz history.',
                ],
                'IntentoController_show' => [
                    '403' => 'Access denied. You do not have permissions to view this attempt correction.',
                    '500' => 'Internal error while retrieving the breakdown of correct and incorrect answers.',
                ],
                'InterfaceTranslationController_getJson' => [
                    '404' => 'The requested language for the interface dictionary does not exist.',
                    '500' => 'Critical failure while dynamically compiling the localization file for Transloco.',
                ],
                'InterfaceTranslationController_index' => [
                    '500' => 'Error retrieving the interface strings master catalog.',
                ],
                'InterfaceTranslationController_updateKey' => [
                    '500' => 'Failed to synchronize, instantiate, or propagate the key into the system dictionaries.',
                ],
                'InterfaceTranslationController_destroyKey' => [
                    '404' => 'The interface language key you are trying to delete does not exist.',
                    '500' => 'Internal consistency error while purging the literal string from the system.',
                ],
                'InterfaceTranslationController_destroyLanguage' => [
                    '500' => 'Failed to delete the language from the system and its linked translation tables.',
                ],
                'PayPalWebhookController_handleWebhook' => [
                    '500' => 'Critical internal failure within the PayPal asynchronous event processor.',
                ],
                'PayPalWebhookController_vincularSuscripcion' => [
                    '500' => 'Internal error while pre-linking the PayPal subscription to your account.',
                ],
                'PayPalWebhookController_cancelarSuscripcionActiva' => [
                    '400' => 'The PayPal gateway rejected the request to unsubscribe your automatic renewal.',
                    '422' => 'No active PayPal subscription was found linked to this profile.',
                    '502' => 'External connection failure with the PayPal gateway. Please try again.',
                    '500' => 'Internal server error while processing your automatic renewal cancellation.',
                ],
                'TablaApoyoController_indexTablas' => [
                    '500' => 'Error retrieving the master catalog of support tables.',
                ],
                'TablaApoyoController_readRows' => [
                    '404' => 'The physical table or its row mapping does not exist in the database engine.',
                    '500' => 'Error reading the dynamic records of the selected support table.',
                ],
                'TablaApoyoController_createRow' => [
                    '400' => 'No valid fields or data have been sent to process the insertion.',
                    '404' => 'The parameterized support table is not available.',
                    '422' => 'The code field is mandatory to register master entries.',
                    '422_duplicate' => 'The sent code is already registered in this dictionary.',
                    '500' => 'Error inserting the master record into the dynamic engine.',
                ],
                'TablaApoyoController_updateRow' => [
                    '404' => 'Support table structure or dynamic row map not found.',
                    '422_valorUsado' => 'Operation denied: The status of subscription tiers is automatically managed by PayPal.',
                    '422_root' => 'Operation denied: Altering the physical identity of the master relational table is not allowed.',
                    '500' => 'Critical error while processing the protected record update.',
                ],
                'TablaApoyoController_deleteRow' => [
                    '404' => 'The master record you are trying to delete does not exist in the database.',
                    '422_root' => 'Operation canceled: Purging root nodes is not allowed to protect the dashboard integrity.',
                    '500' => 'Could not delete the record due to active dependencies in the relational model.',
                ],
                'TablaApoyoController_getRowLanguages' => [
                    '404_schema' => 'The multi-language structure for the specified physical table does not exist.',
                    '404' => 'Relational structure or dynamic translation map not found.',
                    '500' => 'Could not retrieve the translations for the selected row.',
                ],
                'TablaApoyoController_updateRowLanguages' => [
                    '400' => 'No valid translations have been sent to process the dynamic batch.',
                    '404' => 'The parameterized translation table does not exist in the relational engine.',
                    '500' => 'Internal error while persisting the language batch into the database.',
                ],
                'TestController_indexPublic' => [
                    '500' => 'Error retrieving public quizzes generated by the community.',
                ],
                'TestController_index' => [
                    '500' => 'Error retrieving your personal repository of private quizzes.',
                ],
                'TestController_store' => [
                    '404_document' => 'The referenced base document does not exist or you are not its rightful owner.',
                    '500' => 'Infrastructure failure while enqueueing your AI generation request.',
                ],
                'TestController_show' => [
                    '403' => 'Access denied. You do not have permissions to view this quiz configuration.',
                    '500' => 'Could not retrieve the configuration of the selected academic quiz.',
                ],
                'TestController_update' => [
                    '403' => 'Access denied. You do not have permissions to update this quiz.',
                    '500' => 'Internal error while updating the configuration and restarting AI queues.',
                ],
                'TestController_destroy' => [
                    '403' => 'Access denied. You do not have permissions to delete this quiz.',
                    '500' => 'Internal consistency error while purging the selected quiz from the database.',
                ],
                'TestController_realizar' => [
                    '500' => 'Failure initializing the instance to perform the interactive exam.',
                ],
                'TestController_corregir' => [
                    '422_empty' => 'This quiz does not have structured questions to be graded.',
                    '500' => 'Critical error while processing the grading and saving of your exam attempt.',
                ],
                'CheckTierLimits_handle' => [
                    '403_no_tier' => 'Your user does not have any subscription tier associated in the system.',
                    '403_max_tests' => 'Tier limit exceeded. Your current tier ({{ plan }}) only allows generating {{ maxTests }} quizzes every 24 hours.',
                    '403_max_pages' => 'The document exceeds the allowed size. Your plan ({{ plan }}) allows processing PDFs up to {{ maxPaginas }} pages (Approx. {{ pesoMaximoKB }} KB). The uploaded file equals around {{ paginasEstimadas }} pages.',
                    '403_max_questions' => 'Tier limit exceeded. Your current tier ({{ plan }}) only allows generating quizzes with up to {{ maxPreguntas }} questions (You requested {{ solicitadas }}).',
                    '403_max_exports' => 'Premium function locked. Direct export to standard Moodle GIFT format is not allowed in the {{ plan }} tier.',
                    '500' => 'Internal error within the tier limitations and subscriptions control infrastructure.',
                ],
                'RefreshTokenTimeout_handle' => [
                    '500' => 'Internal error while refreshing and extending the expiration timeout of your active session.',
                ],
                'SetLocale_handle' => [
                    '500' => 'Internal error while synchronizing your language preferences with the server headers.',
                ],
                'GlobalHandler_NotFound' => [
                    '404' => 'The requested API endpoint or relational resource does not exist in TestMind.',
                ],
                'GlobalHandler_Unauthorized' => [
                    '401' => 'Your session has expired or the access token is invalid. Please log in again.',
                ],
                'GlobalHandler_Forbidden' => [
                    '403' => 'Restricted access. You do not possess the required privileges or roles to execute this request.',
                ],
                'GlobalHandler_MethodNotAllowed' => [
                    '405' => 'Network architecture error: The HTTP method used is not supported for this route.',
                ],
                'GlobalHandler_ServerError' => [
                    '500' => 'An unexpected error occurred within the global services of the TestMind server.',
                ],
            ],
        ];

        $this->seedDiccionario($es->id, $jsonEs);
        $this->seedDiccionario($gl->id, $jsonGl);
        $this->seedDiccionario($en->id, $jsonEn);
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

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
                ['codigo' => 'E'],
                ['valorUsado' => true]
            );

            $estadoFallo->lenguajes()->syncWithoutDetaching([
                $es->id => ['descripcion' => 'Error'],
                $en->id => ['descripcion' => 'Error'],
                $gl->id => ['descripcion' => 'Error'],
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
                'newTest' => 'CREAR TEST',
                'adminDashboard' => 'GENERAL',
                'adminGestionTA' => 'GESTION TA',
                'adminGestionLenguajes' => 'GESTION LENGUAJES',
                'adminNewAdmin' => 'CREAR ADMIN',
                'planTag' => 'PLAN: {{ nombrePlan }}',
            ],
            'admin' => [
                'changed_password' => [
                    'titulo' => 'CAMBIAR CONTRASEÑA',
                    'actual' => 'CONTRASEÑA ACTUAL',
                    'nueva' => 'NUEVA CONTRASEÑA',
                    'confirm' => 'CONFIRMAR NUEVA CONTRASEÑA',
                    'errorPasswords' => 'Las nuevas contraseñas no coinciden.',
                    'procesando' => 'PROCESANDO...',
                    'confirmar' => 'GUARDAR',
                    'cancelar' => 'CANCELAR',
                ],
                'dashboard' => [
                    'titulo' => 'ESTADISTICAS GENERALES',
                    'titulo_tiers' => 'SEGMENTACIÓN DE USUARIOS (TIERS)',
                    'load_tiers' => 'CONECTANDO CON EL REGISTRO DE MEMBRESÍAS...',
                    'titulo_categorias' => 'DISTRIBUCIÓN DE TESTS POR CATEGORÍA',
                    'load_categorias' => 'EXTRAYENDO VOLUMEN DE CATEGORÍAS...',
                    'titulo_timeline' => 'EVOLUCIÓN: TESTS CREADOS',
                    'load_timeline' => 'SINCRONIZANDO HISTÓRICO DE CREACIÓN...',
                ],
                'gestionTA' => [
                    'titulo' => 'Panel de Gestión: Tablas de Apoyo (GestionTA)',
                    'subtitulo' => 'Herramienta polimórfica dinámica para la administración del sistema TestMind.',
                    'labelCombo' => 'Selecciona la tabla auxiliar a modificar:',
                    'generalCombo' => '-- Elige una tabla de soporte --',
                    'loadTA' => 'Procesando consulta en base de datos...',
                    'titleTA' => 'Contenido de:',
                    'nuevoRegistro' => 'Añadir Registro',
                    'botonera' => 'ACCIONES',
                    'btnEdit' => 'Editar',
                    'btnLang' => 'Idiomas',
                    'btnDel' => 'Eliminar',
                    'btnSave' => 'Guardar',
                    'btnCancel' => 'Cancelar',
                    'ejemploCodigo' => 'Ej: INF_01...',
                    'escribir' => 'Escribir...',
                    'btnConfirmar' => 'Confirmar',
                    'noData' => 'Selecciona una tabla del listado superior para inicializar el mapeador dinámico de campos.',
                    'titleIdiomas' => 'Gestión Multiidioma Dinámica',
                    'subtitleIdiomas' => 'Modificando traducciones para la clave de negocio:',
                    'labelComboIdiomas' => 'Visualizar Idioma:',
                    'conTraduccion' => '✓',
                    'sinTraduccion' => '(SIN TRADUCIR)',
                    'labelEditLang' => 'Editar Contenido Actual:',
                    'labelNewLang' => 'Crear Nueva Traducción (En Caliente):',
                    'newLang' => 'Introduce la traducción para guardar este idioma...',
                    'titleDel' => 'ADVERTENCIA DE SISTEMA',
                    'warnDel' => 'CRITICAL WARNING: ACCIÓN DE BORRADO DESACTIVADA.',
                    'errorBorradoProhibido' => 'Estás intentando eliminar el registro base {{ registro }} de la meta-tabla. Borrar este mapa de metadatos invalidaría el sistema CRUD polimórfico integrado de {{ app }} de inmediato.',
                    'confirmarBorrado' => '¿Estás completamente seguro de eliminar el registro auxiliar con ID: #{{ id }}?',
                    'confirmDel' => 'Esta acción es irreversible y podría violar restricciones d integridad referencial si el registro está vinculado a otras tablas activas (ej: documentos).',
                    'volverDashboard' => 'Volver al Panel',
                ],
                'new_admin' => [
                    'titulo' => 'CREAR ADMIN',
                    'labelName' => 'NOMBRE COMPLETO',
                    'placeholderName' => 'Escribe el nombre...',
                    'email' => 'CORREO CORPORATIVO',
                    'placeholderEmail' => 'admin@testmind.com',
                    'password' => 'CONTRASEÑA DE ACCESO',
                    'confirmPassword' => 'CONFIRMAR CONTRASEÑA',
                    'errorPasswords' => 'Las contraseñas no coinciden.',
                    'procesando' => 'PROCESANDO...',
                    'crear' => 'DAR DE ALTA',
                ],
                'gestionLenguajes' => [
                    'titulo' => 'GESTION LENGUAJES',
                    'seleccionado' => 'DICCIONARIO ACTIVO:',
                    'loading' => 'SINCRONIZANDO...',
                    'filtrado' => 'FILTRAR POR VARIABLE O CONTENIDO...',
                    'new' => 'NUEVA CLAVE',
                    'key' => 'VARIABLE (CLAVE)',
                    'descripcion' => 'LITERAL TEXTUAL (VALOR EN BD)',
                    'botonera' => 'ACCIÓN',
                    'noData' => 'NO SE DETECTARON LITERALES QUE COINCIDAN CON LOS CRITERIOS.',
                    'titleNew' => 'REGISTRAR NUEVA ETIQUETA',
                    'keyNew' => 'IDENTIFICADOR (CLAVE):',
                    'ejemploNew' => 'ej: auth.welcome_message',
                    'descripcionNew' => 'CONTENIDO TEXTUAL (VALOR):',
                    'ejemploDescripcionNew' => 'Texto que se renderizará...',
                    'btnSave' => 'Guardar',
                    'btnCancel' => 'Cancelar',
                    'confirmarEliminar' => '¿Estás seguro de que deseas eliminar permanentemente la clave "{{ clave }}" y todas sus traducciones asociadas?',
                ],
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
            'home' => [
                'title' => 'Transforma tus apuntes en evaluaciones en segundos',
                'subTitle' => 'TestMind: Inteligencia artificial para la creación de tests educativos automáticos.',
                'btnCrearTest' => 'Probar Ahora',
                'registrarse' => 'Registrarse',
                'titleSection' => '¿Por qué TestMind?',
                'card1' => [
                    'title' => 'Generación IA',
                    'subTitle' => 'Análisis avanzado de documentos con IA.',
                ],
                'card2' => [
                    'title' => 'Configuración',
                    'subTitle' => 'Elige dificultad, tipo de pregunta y cantidad.',
                ],
                'card3' => [
                    'title' => 'Exportación Moodle',
                    'subTitle' => 'Interoperabilidad total con plataformas educativas.',
                ],
            ],
            'resolver' => [
                'loading' => 'Cargando cuestionario académico...',
                'headerPreguntas' => 'Preguntas',
                'headerPregunta' => 'Pregunta',
                'btnClear' => 'Limpiar',
                'placeholderWrite' => 'Escribe tu respuesta aquí...',
                'finalizar' => 'Finalizar',
            ],
            'listas' => [
                'textoVacio' => 'No se encontraron cuestionarios con los filtros aplicados.',
                'textoVacioDoc' => 'No se encontraron cuestionarios con los filtros aplicados.',
            ],
            'pdfReader' => [
                'loading' => 'CARGANDO DOCUMENTO ACADÉMICO EN TESTMIND...',
            ],
            'profile' => [
                'loading' => 'SINCRONIZANDO PERFIL ACADÉMICO...',
                'conf' => 'Configuración',
                'mediaAcademica' => 'MEDIA ACADÉMICA',
                'cantTest' => 'TESTS COMPLETADOS',
                'topCategoria' => 'TOP CATEGORÍA',
                'historial' => 'Historial de Intentos',
                'verTodo' => 'VER TODO',
                'noHistorial' => 'No has realizado ningún examen todavía.',
                'misTests' => 'Mis Cuestionarios',
                'noCreados' => 'Aún no has generado tests',
                'config' => [
                    'title' => 'EDITAR PERFIL ACADÉMICO',
                    'name' => 'Nombre Completo',
                    'nickname' => 'Nickname',
                    'email' => 'Correo Electrónico',
                    'cambiarPassword' => 'Cambiar Contraseña (Opcional)',
                    'password' => 'Nueva Contraseña',
                    'passwordConfirm' => 'Confirmar Contraseña',
                    'errorPasswords' => 'Las contraseñas no coinciden',
                    'btnSave' => 'Guardar',
                    'btnCancel' => 'Cancelar',
                ],
            ],
            'suscriptionList' => [
                'title' => 'SISTEMA DE SUSCRIPCIONES',
                'loading' => 'CONECTANDO CON SERVIDORES DE PAYPAL SANDBOX...',
                'noData' => 'ERROR CRÍTICO: No se han localizado niveles de tarificación activos en las tablas de apoyo locales.',
            ],
            'testCreator' => [
                'loading' => 'Sincronizando registros académicos...',
                'existenteTitle' => 'DETALLES CUESTIONARIO',
                'newTitle' => 'NUEVO TEST',
                'upload' => 'SUBIR NUEVO PDF',
                'select' => 'REUTILIZAR EXISTENTE',
                'titleTest' => 'TÍTULO DEL TEST',
                'categoria' => 'CATEGORÍA',
                'selectCategoria' => 'SELECCIONAR...',
                'autoDetectada' => 'Auto-detectada...',
                'inputDoc' => 'ARCHIVO PDF',
                'publicDoc' => 'DOCUMENTO PÚBLICO',
                'docAsociado' => 'DOCUMENTO ASOCIADO',
                'placeholderFiltrado' => 'FILTRAR POR NOMBRE...',
                'allFiltradoCategorias' => 'TODAS',
                'docSeleccionado' => 'SELECTED',
                'seleccionarDoc' => 'SELECT',
                'docNoValido' => 'El archivo debe ser un PDF válido.',
                'docNoData' => 'No se encontraron apuntes asociados.',
                'dificultad' => 'NIVEL',
                'facil' => 'FÁCIL',
                'medio' => 'MEDIO',
                'dificil' => 'DIFÍCIL',
                'cantidadPreguntas' => 'TOTAL PREGUNTAS',
                'minOptions' => 'MÍN OPCIONES',
                'propUnic' => 'PROP ÚNICA (%)',
                'propMulti' => 'PROP MULTI (%)',
                'propWrite' => 'PROP ESCRIBIR (%)',
                'errorProp' => 'La suma de las proporciones debe ser exactamente 100%.',
                'indicaciones' => 'INDICACIONES EXTRAS IA (OPCIONAL)',
                'placeholderIndicaciones' => 'Ej: Enfócate más en el capítulo 3...',
                'procesando' => 'PROCESANDO...',
                'generar' => 'GENERAR',
                'reintentar' => 'REINTENTAR',
                'exportando' => 'EXPORTANDO...',
                'exportar' => 'EXPORTAR A MOODLE GIFT',
                'docReader' => '',
                'docReaderError' => 'Tu navegador no soporta la vista del PDF.',
                'docReaderOpen' => 'Haz clic aquí para abrirlo',
                'exportMoodleTitle' => 'MOODLE GIFT EXPORT',
                'exportMoodleSubtitle' => 'Copia este texto y pégalo directamente en la herramienta de importación de Moodle.',
                'btnCopiar' => 'COPIAR AL PORTAPAPELES',
            ],
            'testResult' => [
                'title' => 'CUESTIONARIO FINALIZADO',
                'aciertos' => 'ACIERTOS:',
                'total' => 'TOTAL:',
                'titleRevision' => 'REVISIÓN ACADÉMICA',
                'pregunta' => 'PREGUNTA',
                'pass' => 'CORRECTA',
                'fallo' => 'INCORRECTA',
                'respuesta' => 'Tu respuesta:',
                'enBlanco' => '[Pregunta dejada en blanco]',
                'correcta' => 'Solución correcta:',
                'volver' => 'Volver',
            ],
            'catalogo' => [
                'placeholderNombre' => 'BUSCAR POR NOMBRE...',
                'comboAllCategorias' => 'TODAS LAS CATEGORIAS',
                'paginaActual' => 'PÁGINA {{ actual }} DE {{ total }}',
            ],
            'tierCard' => [
                'precio' => '{{ valor }}/mes',
                'features' => [
                    'generacion' => 'Generación: {{ tests }} tests / 24h',
                    'exportacion' => 'Exportación:',
                    'ilimitada' => 'Ilimitada',
                    'noDisponible' => 'No disponible',
                    'exportacionLimite' => '{{ exportaciones }} / 24h',
                    'limitePdf' => 'Límite: {{ paginas }} pág. por PDF',
                    'preguntasIa' => 'IA: {{ preguntas }} preguntas por test',
                ],
                'activo' => 'PLAN ACTIVO',
                'btnCancel' => 'CANCELAR SUSCRIPCION',
                'modalCancel' => [
                    'title' => '¡ADVERTENCIA SISTEMA!',
                    'pregunta' => '¿Confirmas que deseas cancelar tu suscripción a TestMind?',
                    'info' => 'Conservarás tus ventajas hasta el final del periodo de facturación actual.',
                    'btnReturn' => 'VOLVER',
                    'btnConfirm' => 'CONFIRMAR BAJA',
                ],
            ],
            'pipes' => [
                'categoriaNoData' => 'No encontrada',
                'estadoNoData' => 'No encontrado',
                'tierNoData' => 'No encontrado',
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
                'newTest' => 'CREAR TEST',
                'adminDashboard' => 'XERAL',
                'adminGestionTA' => 'XESTIÓN TA',
                'adminGestionLenguajes' => 'XESTIÓN LINGUAXES',
                'adminNewAdmin' => 'CREAR ADMIN',
                'planTag' => 'PLAN: {{ nombrePlan }}',
            ],
            'admin' => [
                'changed_password' => [
                    'titulo' => 'CAMBIAR CONTRASINAL',
                    'actual' => 'CONTRASINAL ACTUAL',
                    'nueva' => 'CONTRASINAL NOVA',
                    'confirm' => 'CONFIRMAR CONTRASINAL NOVA',
                    'errorPasswords' => 'As contrasinais novas no coinciden.',
                    'procesando' => 'PROCESANDO...',
                    'confirmar' => 'GARDAR',
                    'cancelar' => 'CANCELAR',
                ],
                'dashboard' => [
                    'titulo' => 'ESTADÍSTICAS XERAIS',
                    'titulo_tiers' => 'SEGMENTACIÓN DE USUARIOS (TIERS)',
                    'load_tiers' => 'CONECTANDO CO REXISTRO DE MEMBRESÍAS...',
                    'titulo_categorias' => 'DISTRIBUCIÓN DE TESTS POR CATEGORÍA',
                    'load_categorias' => 'EXTRAENDO VOLUME DE CATEGORÍAS...',
                    'titulo_timeline' => 'EVOLUCIÓN: TESTS CREADOS',
                    'load_timeline' => 'SINCROINIZANDO HISTÓRICO DE CREACIÓN...',
                ],
                'gestionTA' => [
                    'titulo' => 'Panel de Xestión: Táboas de Apoio (GestionTA)',
                    'subtitulo' => 'Ferramenta polimórfica dinámica para a administración do sistema TestMind.',
                    'labelCombo' => 'Selecciona a táboa auxiliar a modificar:',
                    'generalCombo' => '-- Elixe unha táboa de soporte --',
                    'loadTA' => 'Procesando consulta na base de datos...',
                    'titleTA' => 'Contido de:',
                    'nuevoRegistro' => 'Engadir Rexistro',
                    'botonera' => 'ACCIONES',
                    'btnEdit' => 'Editar',
                    'btnLang' => 'Idiomas',
                    'btnDel' => 'Eliminar',
                    'btnSave' => 'Gardar',
                    'btnCancel' => 'Cancelar',
                    'ejemploCodigo' => 'Ex: INF_01...',
                    'escribir' => 'Escribir...',
                    'btnConfirmar' => 'Confirmar',
                    'noData' => 'Selecciona unha táboa da listaxe superior para inicializar o mapeador dinámico de campos.',
                    'titleIdiomas' => 'Xestión Multiidioma Dinámica',
                    'subtitleIdiomas' => 'Modificando traducións para a clave de negocio:',
                    'labelComboIdiomas' => 'Visualizar Idioma:',
                    'conTraduccion' => '✓',
                    'sinTraduccion' => '(SEN TRADUCIR)',
                    'labelEditLang' => 'Editar Contido Actual:',
                    'labelNewLang' => 'Crear Nova Tradución (En Quente):',
                    'newLang' => 'Introduce a tradución para gardar este idioma...',
                    'titleDel' => 'ADVERTENCIA DE SISTEMA',
                    'warnDel' => 'CRITICAL WARNING: ACCIÓN DE BORRADO DESACTIVADA.',
                    'errorBorradoProhibido' => 'Estás tentando eliminar o rexistro base {{ registro }} da meta-táboa. Borrar este mapa de metadatos invalidaría o sistema CRUD polimórfico integrado de {{ app }} de inmediato.',
                    'confirmarBorrado' => 'Estás completamente seguro de eliminar o rexistro auxiliar con ID: #{{ id }}?',
                    'confirmDel' => 'Esta acción é irreversible e podría violar restricións de integridade referencial se o rexistro está vinculado a outras táboas activas (ex: documentos).',
                    'volverDashboard' => 'Volver ao Panel',
                ],
                'new_admin' => [
                    'titulo' => 'CREAR ADMIN',
                    'labelName' => 'NOME COMPLETO',
                    'placeholderName' => 'Escribe o nome...',
                    'email' => 'CORREO CORPORATIVO',
                    'placeholderEmail' => 'admin@testmind.com',
                    'password' => 'CONTRASINAL DE ACCESO',
                    'confirmPassword' => 'CONFIRMAR CONTRASINAL',
                    'errorPasswords' => 'As contrasinais non coinciden.',
                    'procesando' => 'PROCESANDO...',
                    'crear' => 'DAR DE ALTA',
                ],
                'gestionLenguajes' => [
                    'titulo' => 'XESTIÓN LINGUAXES',
                    'seleccionado' => 'DICIONARIO ACTIVO:',
                    'loading' => 'SINCRONIZANDO...',
                    'filtrado' => 'FILTRAR POR VARIABLE OU CONTIDO...',
                    'new' => 'NOVA CLAVE',
                    'key' => 'VARIABLE (CLAVE)',
                    'descripcion' => 'LITERAL TEXTUAL (VALOR EN BD)',
                    'botonera' => 'ACCIÓN',
                    'noData' => 'NON SE DETECTARON LITERAIS QUE COINCIDAN COS CRITERIOS.',
                    'titleNew' => 'REXISTRAR NOVA ETIQUETA',
                    'keyNew' => 'IDENTIFICADOR (CLAVE):',
                    'ejemploNew' => 'ex: auth.welcome_message',
                    'descripcionNew' => 'CONTIDO TEXTUAL (VALOR):',
                    'ejemploDescripcionNew' => 'Texto que se renderizará...',
                    'btnSave' => 'Gardar',
                    'btnCancel' => 'Cancelar',
                    'confirmarEliminar' => 'Estás seguro de que desexas eliminar permanentemente la clave "{{ clave }}" y todas as súas traducións asociadas?',
                ],
            ],
            'auth' => [
                'login_title' => 'INICIAR SESIÓN',
                'register_title' => 'NOVO USUARIO',
                'name' => 'NOME',
                'email' => 'CORREO',
                'remember_me' => 'LEMBRAR SESIÓN',
                'password' => 'CONTRASINAL',
                'confirm_password' => 'CONFIRMAR CONTRASINAL',
                'login_btn' => 'INICIAR SESIÓN',
                'register_btn' => 'CREAR CONTA',
                'no_account' => 'Non tes conta?',
                'have_account' => 'Xa tes conta?',
                'register_link' => 'REXÍSTRATE',
                'login_link' => 'INICIAR SESIÓN',
            ],
            'home' => [
                'title' => 'Transforma os teus apuntamentos en avaliacións en segundos',
                'subTitle' => 'TestMind: Intelixencia artificial para a creación de tests educativos automáticos.',
                'btnCrearTest' => 'Probar Agora',
                'registrarse' => 'Rexistrarse',
                'titleSection' => 'Por que TestMind?',
                'card1' => [
                    'title' => 'Xeración IA',
                    'subTitle' => 'Análise avanzada de documentos con IA.',
                ],
                'card2' => [
                    'title' => 'Configuración',
                    'subTitle' => 'Elixe dificultade, tipo de pregunta e cantidade.',
                ],
                'card3' => [
                    'title' => 'Exportación Moodle',
                    'subTitle' => 'Interoperabilidade total con plataformas educativas.',
                ],
            ],
            'resolver' => [
                'loading' => 'Cargando cuestionario académico...',
                'headerPreguntas' => 'Preguntas',
                'headerPregunta' => 'Pregunta',
                'btnClear' => 'Limpar',
                'placeholderWrite' => 'Escribe a túa resposta aquí...',
                'finalizar' => 'Finalizar',
            ],
            'listas' => [
                'textoVacio' => 'Non se atoparon cuestionarios cos filtros aplicados.',
                'textoVacioDoc' => 'Non se atoparon cuestionarios cos filtros aplicados.',
            ],
            'pdfReader' => [
                'loading' => 'CARGANDO DOCUMENTO ACADÉMICO EN TESTMIND...',
            ],
            'profile' => [
                'loading' => 'SINCRONIZANDO PERFIL ACADÉMICO...',
                'conf' => 'Configuración',
                'mediaAcademica' => 'MEDIA ACADÉMICA',
                'cantTest' => 'TESTS COMPLETADOS',
                'topCategoria' => 'TOP CATEGORÍA',
                'historial' => 'Historial de Intentos',
                'verTodo' => 'VER TODO',
                'noHistorial' => 'Non realizaches ningún exame aínda.',
                'misTests' => 'Os meus Cuestionarios',
                'noCreados' => 'Aínda non xeraches tests',
                'config' => [
                    'title' => 'EDITAR PERFIL ACADÉMICO',
                    'name' => 'Nome Completo',
                    'nickname' => 'Alcume',
                    'email' => 'Correo Electrónico',
                    'cambiarPassword' => 'Cambiar Contrasinal (Opcional)',
                    'password' => 'Nova Contrasinal',
                    'passwordConfirm' => 'Confirmar Contrasinal',
                    'errorPasswords' => 'As contrasinais non coinciden',
                    'btnSave' => 'Gardar',
                    'btnCancel' => 'Cancelar',
                ],
            ],
            'suscriptionList' => [
                'title' => 'SISTEMA DE SUBSCRICIÓNS',
                'loading' => 'CONECTANDO CON SERVIDORES DE PAYPAL SANDBOX...',
                'noData' => 'ERROR CRÍTICO: Non se localizaron niveis de tarificación activos nas táboas de apoio locais.',
            ],
            'testCreator' => [
                'loading' => 'Sincronizando rexistros académicos...',
                'existenteTitle' => 'DETALLES CUESTIONARIO',
                'newTitle' => 'NOVO TEST',
                'upload' => 'SUBIR NOVO PDF',
                'select' => 'REUTILIZAR EXISTENTE',
                'titleTest' => 'TÍTULO DO TEST',
                'categoria' => 'CATEGORÍA',
                'selectCategoria' => 'SELECCIONAR...',
                'autoDetectada' => 'Auto-detectada...',
                'inputDoc' => 'ARQUIVO PDF',
                'publicDoc' => 'DOCUMENTO PÚBLICO',
                'docAsociado' => 'DOCUMENTO ASOCIADO',
                'placeholderFiltrado' => 'FILTRAR POR NOME...',
                'allFiltradoCategorias' => 'TODAS',
                'docSeleccionado' => 'SELECTED',
                'seleccionarDoc' => 'SELECT',
                'docNoValido' => 'O arquivo debe ser un PDF válido.',
                'docNoData' => 'Non se atoparon apuntamentos asociados.',
                'dificultad' => 'NIVEL',
                'facil' => 'FÁCIL',
                'medio' => 'MEDIO',
                'dificil' => 'DIFÍCIL',
                'cantidadPreguntas' => 'TOTAL PREGUNTAS',
                'minOptions' => 'MÍN OPCIONS',
                'propUnic' => 'PROP ÚNICA (%)',
                'propMulti' => 'PROP MULTI (%)',
                'propWrite' => 'PROP ESCRIBIR (%)',
                'errorProp' => 'A suma das proporcións debe ser exactamente 100%.',
                'indicaciones' => 'INDICACIÓNS EXTRAS IA (OPCIONAL)',
                'placeholderIndicaciones' => 'Ex: Enfócate máis no capítulo 3...',
                'procesando' => 'PROCESANDO...',
                'generar' => 'XERAR',
                'reintentar' => 'REINTENTAR',
                'exportando' => 'EXPORTANDO...',
                'exportar' => 'EXPORTAR A MOODLE GIFT',
                'docReader' => '',
                'docReaderError' => 'O teu navegador non soporta a vista do PDF.',
                'docReaderOpen' => 'Fai clic aquí para abrilo',
                'exportMoodleTitle' => 'MOODLE GIFT EXPORT',
                'exportMoodleSubtitle' => 'Copia este texto e pégao directamente na ferramenta de importación de Moodle.',
                'btnCopiar' => 'COPIAR AO PORTAPAPELES',
            ],
            'testResult' => [
                'title' => 'CUESTIONARIO FINALIZADO',
                'aciertos' => 'ACERTOS:',
                'total' => 'TOTAL:',
                'titleRevision' => 'REVISIÓN ACADÉMICA',
                'pregunta' => 'PREGUNTA',
                'pass' => 'CORRECTA',
                'fallo' => 'INCORRECTA',
                'respuesta' => 'A túa resposta:',
                'enBlanco' => '[Pregunta deixada en blanco]',
                'correcta' => 'Solución correcta:',
                'volver' => 'Volver',
            ],
            'catalogo' => [
                'placeholderNombre' => 'BUSCAR POR NOME...',
                'comboAllCategorias' => 'TODAS AS CATEGORIAS',
                'paginaActual' => 'PÁXINA {{ actual }} DE {{ total }}',
            ],
            'tierCard' => [
                'precio' => '{{ valor }}/mes',
                'features' => [
                    'generacion' => 'Xeración: {{ tests }} tests / 24h',
                    'exportacion' => 'Exportación:',
                    'ilimitada' => 'Ilimitada',
                    'noDisponible' => 'Non dispoñible',
                    'exportacionLimite' => '{{ exportaciones }} / 24h',
                    'limitePdf' => 'Límite: {{ paginas }} páx. por PDF',
                    'preguntasIa' => 'IA: {{ preguntas }} preguntas por test',
                ],
                'activo' => 'PLAN ACTIVO',
                'btnCancel' => 'CANCELAR SUBSCRICIÓN',
                'modalCancel' => [
                    'title' => '¡ADVERTENCIA SISTEMA!',
                    'pregunta' => 'Confirmas que desexas cancelar a túa subscrición a TestMind?',
                    'info' => 'Conservarás as túas vantaxes ata o final do período de facturación actual.',
                    'btnReturn' => 'VOLVER',
                    'btnConfirm' => 'CONFIRMAR BAIXA',
                ],
            ],
            'pipes' => [
                'categoriaNoData' => 'Non atopada',
                'estadoNoData' => 'Non atopado',
                'tierNoData' => 'Non atopado',
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
            'error' => [
                'AdminDashboardController_userSegmentation' => [
                    '500' => 'Erro ao recuperar as métricas de segmentación de membresías no panel de control.',
                ],
                'AdminDashboardController_testsCreadosTimeline' => [
                    '500' => 'Fallo al procesar a liña temporal de actividade de contidos no servidor.',
                ],
                'AdminDashboardController_testsByCategory' => [
                    '500' => 'Fallo ao procesar a repartición analítica por categorías do panel de control.',
                ],
                'AuthController_login' => [
                    '500' => 'Erro interno del servidor ao procesar as credenciais de inicio de sesión.',
                ],
                'AuthController_logout' => [
                    '500' => 'Erro interno do servidor ao tentar destruír a sesión activa.',
                ],
                'AuthController_register' => [
                    '500' => 'Erro interno do servidor ao tentar dar de alta o novo perfil de alumno.',
                ],
                'AuthController_registerAdmin' => [
                    '500' => 'Erro interno do servidor ao rexistrar as novas credenciais de administración.',
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
                    '500' => 'Erro interno ao recuperar os metadatos del documento.',
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
                    '404' => 'O arquivo físico en formato PDF non se atopa na ruta do servidor.',
                    '500' => 'Erro interno ao preparar a descarga do documento.',
                ],
                'EstadoController_index' => [
                    '500' => 'Non se puideron recuperar os estados lógicos da base de datos.',
                ],
                'EstadoController_show' => [
                    '404' => 'O estado solicitado non existe nos dicionarios mestres.',
                    '500' => 'Erro interno ao recuperar os detalles do estado seleccionado.',
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
                    '500' => 'Fallo crítico ao compilar dinamicamente o arquivo de localización para Transloco.',
                ],
                'InterfaceTranslationController_index' => [
                    '500' => 'Erro ao recuperar o catálogo mestre de literais da interface.',
                ],
                'InterfaceTranslationController_updateKey' => [
                    '500' => 'Fallo ao sincronizar, instanciar ou propagar a clave nos dicionarios do sistema.',
                ],
                'InterfaceTranslationController_destroyKey' => [
                    '404' => 'A clave idiomática da interface que intentas eliminar non existe.',
                    '500' => 'Erro de consistencia interna al purgar o literal do sistema.',
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
                    '502' => 'Fallo de conexión externa coa pasarela de PayPal. Téntao de novo.',
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
                    '500' => 'Erro crítico ao procesar a actualización do rexistro protexido.',
                ],
                'TablaApoyoController_deleteRow' => [
                    '404' => 'O rexistro mestre que intentas eliminar non existe na base de datos.',
                    '422_root' => 'Operación cancelada: Non se permite purgar nodos raíz para no romper o panel dinámico.',
                    '500' => 'Non se puido eliminar o rexistro debido a dependencias activas no modelo relacional.',
                ],
                'TablaApoyoController_getRowLanguages' => [
                    '404_schema' => 'A estrutura multiidioma para a táboa física especificada non existe.',
                    '404' => 'Estrutura relacional ou mapa dinámico de traducións no localizado.',
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
                    '500' => 'Fallo de infraestrutura al encolar a túa petición de xeración con Intelixencia Artificial.',
                ],
                'TestController_show' => [
                    '403' => 'Acceso denegado. Non tes permisos para ver a configuración deste cuestionario.',
                    '500' => 'Non se puido recuperar a configuración do cuestionario académico seleccionado.',
                ],
                'TestController_update' => [
                    '403' => 'Acceso denegado. Non tes permisos para actualizar este cuestionario.',
                    '500' => 'Erro interno ao actualizar a configuración e relanzar as colas da IA.',
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
                'logout' => 'Log out',
                'login' => 'LOG IN',
                'profile' => 'PROFILE',
                'newTest' => 'CREATE TEST',
                'adminDashboard' => 'OVERVIEW',
                'adminGestionTA' => 'MANAGE DATA',
                'adminGestionLenguajes' => 'MANAGE LANGUAGES',
                'adminNewAdmin' => 'CREATE ADMIN',
                'planTag' => 'PLAN: {{ nombrePlan }}',
            ],
            'admin' => [
                'changed_password' => [
                    'titulo' => 'CHANGE PASSWORD',
                    'actual' => 'CURRENT PASSWORD',
                    'nueva' => 'NEW PASSWORD',
                    'confirm' => 'CONFIRM NEW PASSWORD',
                    'errorPasswords' => 'The new passwords do not match.',
                    'procesando' => 'PROCESSING...',
                    'confirmar' => 'SAVE',
                    'cancelar' => 'CANCEL',
                ],
                'dashboard' => [
                    'titulo' => 'GENERAL STATISTICS',
                    'titulo_tiers' => 'USER SEGMENTATION (TIERS)',
                    'load_tiers' => 'CONNECTING TO MEMBERSHIP RECORDS...',
                    'titulo_categorias' => 'TEST DISTRIBUTION BY CATEGORY',
                    'load_categorias' => 'EXTRACTING CATEGORY VOLUMES...',
                    'titulo_timeline' => 'TRENDING: TESTS CREATED',
                    'load_timeline' => 'SYNCHRONISING CREATION HISTORY...',
                ],
                'gestionTA' => [
                    'titulo' => 'Management Panel: Support Tables (GestionTA)',
                    'subtitulo' => 'Dynamic polymorphic tool for TestMind system administration.',
                    'labelCombo' => 'Select the auxiliary table to modify:',
                    'generalCombo' => '-- Choose a support table --',
                    'loadTA' => 'Processing database query...',
                    'titleTA' => 'Content of:',
                    'nuevoRegistro' => 'Add Record',
                    'botonera' => 'ACTIONS',
                    'btnEdit' => 'Edit',
                    'btnLang' => 'Languages',
                    'btnDel' => 'Delete',
                    'btnSave' => 'Save',
                    'btnCancel' => 'Cancel',
                    'ejemploCodigo' => 'Ex: INF_01...',
                    'escribir' => 'Type here...',
                    'btnConfirmar' => 'Confirm',
                    'noData' => 'Select a table from the list above to initialise the dynamic field mapper.',
                    'titleIdiomas' => 'Dynamic Multi-language Management',
                    'subtitleIdiomas' => 'Modifying translations for business key:',
                    'labelComboIdiomas' => 'View Language:',
                    'conTraduccion' => '✓',
                    'sinTraduccion' => '(UNTRANSLATED)',
                    'labelEditLang' => 'Edit Current Content:',
                    'labelNewLang' => 'Create New Translation (On the fly):',
                    'newLang' => 'Enter the translation to save this language...',
                    'titleDel' => 'SYSTEM WARNING',
                    'warnDel' => 'CRITICAL WARNING: DELETE ACTION DISABLED.',
                    'errorBorradoProhibido' => 'You are attempting to delete the base record {{ registro }} from the meta-table. Deleting this metadata map would immediately invalidate the integrated polymorphic CRUD system of {{ app }}.',
                    'confirmarBorrado' => 'Are you completely sure you want to delete the auxiliary record with ID: #{{ id }}?',
                    'confirmDel' => 'This action is irreversible and could violate referential integrity constraints if the record is linked to other active tables (e.g., documents).',
                    'volverDashboard' => 'Return to Panel',
                ],
                'new_admin' => [
                    'titulo' => 'CREATE ADMIN',
                    'labelName' => 'FULL NAME',
                    'placeholderName' => 'Type the name...',
                    'email' => 'CORPORATE EMAIL',
                    'placeholderEmail' => 'admin@testmind.com',
                    'password' => 'ACCESS PASSWORD',
                    'confirmPassword' => 'CONFIRM PASSWORD',
                    'errorPasswords' => 'Passwords do not match.',
                    'procesando' => 'PROCESSING...',
                    'crear' => 'REGISTER ADMIN',
                ],
                'gestionLenguajes' => [
                    'titulo' => 'LANGUAGE MANAGEMENT',
                    'seleccionado' => 'ACTIVE DICTIONARY:',
                    'loading' => 'SYNCHRONISING...',
                    'filtrado' => 'FILTER BY VARIABLE OR CONTENT...',
                    'new' => 'NEW KEY',
                    'key' => 'VARIABLE (KEY)',
                    'descripcion' => 'TEXTUAL LITERAL (DB VALUE)',
                    'botonera' => 'ACTION',
                    'noData' => 'NO LITERALS MATCHING THE CRITERIA WERE DETECTED.',
                    'titleNew' => 'REGISTER NEW LABEL',
                    'keyNew' => 'IDENTIFIER (KEY):',
                    'ejemploNew' => 'ex: auth.welcome_message',
                    'descripcionNew' => 'TEXTUAL CONTENT (VALUE):',
                    'ejemploDescripcionNew' => 'Text to be rendered...',
                    'btnSave' => 'Save',
                    'btnCancel' => 'Cancel',
                    'confirmarEliminar' => 'Are you sure you want to permanently delete the key "{{ clave }}" and all its associated translations?',
                ],
            ],
            'auth' => [
                'login_title' => 'LOG IN',
                'register_title' => 'NEW USER',
                'name' => 'NAME',
                'email' => 'EMAIL',
                'remember_me' => 'REMEMBER ME',
                'password' => 'PASSWORD',
                'confirm_password' => 'CONFIRM PASSWORD',
                'login_btn' => 'LOG IN',
                'register_btn' => 'CREATE ACCOUNT',
                'no_account' => 'Don\'t have an account?',
                'have_account' => 'Already have an account?',
                'register_link' => 'REGISTER',
                'login_link' => 'LOG IN',
            ],
            'home' => [
                'title' => 'Transform your notes into assessments in seconds',
                'subTitle' => 'TestMind: Artificial intelligence for automated educational test creation.',
                'btnCrearTest' => 'Try It Now',
                'registrarse' => 'Register',
                'titleSection' => 'Why TestMind?',
                'card1' => [
                    'title' => 'AI Generation',
                    'subTitle' => 'Advanced document analysis powered by AI.',
                ],
                'card2' => [
                    'title' => 'Configuration',
                    'subTitle' => 'Choose difficulty, question types, and quantity.',
                ],
                'card3' => [
                    'title' => 'Moodle Export',
                    'subTitle' => 'Full interoperability with educational platforms.',
                ],
            ],
            'resolver' => [
                'loading' => 'Loading academic quiz...',
                'headerPreguntas' => 'Questions',
                'headerPregunta' => 'Question',
                'btnClear' => 'Clear',
                'placeholderWrite' => 'Type your answer here...',
                'finalizar' => 'Finish',
            ],
            'listas' => [
                'textoVacio' => 'No quizzes found matching the applied filters.',
                'textoVacioDoc' => 'No quizzes found matching the applied filters.',
            ],
            'pdfReader' => [
                'loading' => 'LOADING ACADEMIC DOCUMENT INTO TESTMIND...',
            ],
            'profile' => [
                'loading' => 'SYNCHRONISING ACADEMIC PROFILE...',
                'conf' => 'Settings',
                'mediaAcademica' => 'ACADEMIC AVERAGE',
                'cantTest' => 'COMPLETED TESTS',
                'topCategoria' => 'TOP CATEGORY',
                'historial' => 'Attempt History',
                'verTodo' => 'VIEW ALL',
                'noHistorial' => 'You haven\'t taken any exams yet.',
                'misTests' => 'My Quizzes',
                'noCreados' => 'You haven\'t generated any tests yet',
                'config' => [
                    'title' => 'EDIT ACADEMIC PROFILE',
                    'name' => 'Full Name',
                    'nickname' => 'Nickname',
                    'email' => 'Email Address',
                    'cambiarPassword' => 'Change Password (Optional)',
                    'password' => 'New Password',
                    'passwordConfirm' => 'Confirm Password',
                    'errorPasswords' => 'Passwords do not match',
                    'btnSave' => 'Save',
                    'btnCancel' => 'Cancel',
                ],
            ],
            'suscriptionList' => [
                'title' => 'SUBSCRIPTION SYSTEM',
                'loading' => 'CONNECTING TO PAYPAL SANDBOX SERVERS...',
                'noData' => 'CRITICAL ERROR: No active pricing tiers located in the local support tables.',
            ],
            'testCreator' => [
                'loading' => 'Synchronising academic records...',
                'existenteTitle' => 'QUIZ DETAILS',
                'newTitle' => 'NEW TEST',
                'upload' => 'UPLOAD NEW PDF',
                'select' => 'REUSE EXISTING',
                'titleTest' => 'TEST TITLE',
                'categoria' => 'CATEGORY',
                'selectCategoria' => 'SELECT...',
                'autoDetectada' => 'Auto-detected...',
                'inputDoc' => 'PDF FILE',
                'publicDoc' => 'PUBLIC DOCUMENT',
                'docAsociado' => 'ASSOCIATED DOCUMENT',
                'placeholderFiltrado' => 'FILTER BY NAME...',
                'allFiltradoCategorias' => 'ALL',
                'docSeleccionado' => 'SELECTED',
                'seleccionarDoc' => 'SELECT',
                'docNoValido' => 'The file must be a valid PDF.',
                'docNoData' => 'No associated notes found.',
                'dificultad' => 'LEVEL',
                'facil' => 'EASY',
                'medio' => 'MEDIUM',
                'dificil' => 'HARD',
                'cantidadPreguntas' => 'TOTAL QUESTIONS',
                'minOptions' => 'MIN OPTIONS',
                'propUnic' => 'SINGLE CHOICE (%)',
                'propMulti' => 'MULTIPLE CHOICE (%)',
                'propWrite' => 'SHORT ANSWER (%)',
                'errorProp' => 'The sum of the proportions must equal exactly 100%.',
                'indicaciones' => 'EXTRA AI INSTRUCTIONS (OPTIONAL)',
                'placeholderIndicaciones' => 'Ex: Focus more on chapter 3...',
                'procesando' => 'PROCESSING...',
                'generar' => 'GENERATE',
                'reintentar' => 'RETRY',
                'exportando' => 'EXPORTING...',
                'exportar' => 'EXPORT TO MOODLE GIFT',
                'docReader' => '',
                'docReaderError' => 'Your browser does not support PDF embedding views.',
                'docReaderOpen' => 'Click here to open it',
                'exportMoodleTitle' => 'MOODLE GIFT EXPORT',
                'exportMoodleSubtitle' => 'Copy this text and paste it directly into the Moodle import wizard.',
                'btnCopiar' => 'COPY TO CLIPBOARD',
            ],
            'testResult' => [
                'title' => 'QUIZ COMPLETED',
                'aciertos' => 'CORRECT:',
                'total' => 'TOTAL:',
                'titleRevision' => 'ACADEMIC REVIEW',
                'pregunta' => 'QUESTION',
                'pass' => 'CORRECT',
                'fallo' => 'INCORRECT',
                'respuesta' => 'Your answer:',
                'enBlanco' => '[Question left blank]',
                'correcta' => 'Correct solution:',
                'volver' => 'Go Back',
            ],
            'catalogo' => [
                'placeholderNombre' => 'SEARCH BY NAME...',
                'comboAllCategorias' => 'ALL CATEGORIES',
                'paginaActual' => 'PAGE {{ actual }} OF {{ total }}',
            ],
            'tierCard' => [
                'precio' => '{{ valor }}/month',
                'features' => [
                    'generacion' => 'Generation: {{ tests }} tests / 24h',
                    'exportacion' => 'Export:',
                    'ilimitada' => 'Unlimited',
                    'noDisponible' => 'Not available',
                    'exportacionLimite' => '{{ exportaciones }} / 24h',
                    'limitePdf' => 'Limit: {{ paginas }} pages per PDF',
                    'preguntasIa' => 'AI: {{ preguntas }} questions per test',
                ],
                'activo' => 'ACTIVE PLAN',
                'btnCancel' => 'CANCEL SUBSCRIPTION',
                'modalCancel' => [
                    'title' => '!SYSTEM WARNING!',
                    'pregunta' => 'Are you sure you want to cancel your TestMind subscription?',
                    'info' => 'You will retain your benefits until the end of the current billing cycle.',
                    'btnReturn' => 'GO BACK',
                    'btnConfirm' => 'CONFIRM CANCELLATION',
                ],
            ],
            'pipes' => [
                'categoriaNoData' => 'Not found',
                'estadoNoData' => 'Not found',
                'tierNoData' => 'Not found',
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
            'NUEVO TEST' => 'NEW TEST',
            'PROCESANDO...' => 'PROCESSING...',
            'START_GENERATION ➔' => 'START GENERATION ➔',
            'error' => [
                'AdminDashboardController_userSegmentation' => [
                    '500' => 'Error retrieving membership user segmentation metrics in the dashboard.',
                ],
                'AdminDashboardController_testsCreadosTimeline' => [
                    '500' => 'Failed to process the content activity timeline on the server.',
                ],
                'AdminDashboardController_testsByCategory' => [
                    '500' => 'Failed to process the category analytical breakdown on the dashboard.',
                ],
                'AuthController_login' => [
                    '500' => 'Internal server error while processing login credentials.',
                ],
                'AuthController_logout' => [
                    '500' => 'Internal server error while attempting to terminate the active session.',
                ],
                'AuthController_register' => [
                    '500' => 'Internal server error while trying to register the new student profile.',
                ],
                'AuthController_registerAdmin' => [
                    '500' => 'Internal server error while registering the new administration credentials.',
                ],
                'AuthController_me' => [
                    '500' => 'Internal server error while synchronising your active profile details.',
                ],
                'CategoriaController_index' => [
                    '500' => 'Could not retrieve available study categories.',
                ],
                'CategoriaController_show' => [
                    '404' => 'The requested academic category does not exist.',
                    '500' => 'Internal error while retrieving the details of the selected category.',
                ],
                'DocumentoController_indexPublic' => [
                    '500' => 'Error retrieving the community shared document repository.',
                ],
                'DocumentoController_index' => [
                    '500' => 'Error retrieving your personal notes and documents in the cloud.',
                ],
                'DocumentoController_store' => [
                    '500' => 'Could not save the physical PDF file onto the server disk.',
                ],
                'DocumentoController_show' => [
                    '403' => 'Access denied. You do not have permission to view this private document.',
                    '500' => 'Internal error while retrieving document metadata.',
                ],
                'DocumentoController_update' => [
                    '403' => 'Access denied. You do not have permission to edit this document\'s properties.',
                    '500' => 'Error updating the filename in the system.',
                ],
                'DocumentoController_destroy' => [
                    '403' => 'Access denied. You do not have permission to delete this document.',
                    '500' => 'Error deleting the physical file and its records from the server.',
                ],
                'DocumentoController_descargar' => [
                    '403' => 'Access denied. You do not have permission to download this binary file.',
                    '404' => 'The physical PDF file could not be found in the server path.',
                    '500' => 'Internal error while preparing the document download.',
                ],
                'EstadoController_index' => [
                    '500' => 'Could not retrieve logical states from the database.',
                ],
                'EstadoController_show' => [
                    '404' => 'The requested state does not exist in the master dictionaries.',
                    '500' => 'Internal error while retrieving the details of the selected state.',
                ],
                'ExportacionController_exportarAMoodleGift' => [
                    '404' => 'The quiz you are trying to export does not exist.',
                    '422' => 'The questions field does not contain a valid data collection for export.',
                    '500' => 'An internal error occurred in the string engine while compiling the GIFT format.',
                ],
                'IntentoController_index' => [
                    '500' => 'Internal error while calculating academic metrics and retrieving your exam history.',
                ],
                'IntentoController_show' => [
                    '403' => 'Access denied. You do not have permission to view this attempt\'s correction.',
                    '500' => 'Internal error while retrieving the breakdown of correct and incorrect answers.',
                ],
                'InterfaceTranslationController_getJson' => [
                    '404' => 'The requested language for the interface dictionary does not exist.',
                    '500' => 'Critical failure while dynamically compiling the localisation file for Transloco.',
                ],
                'InterfaceTranslationController_index' => [
                    '500' => 'Error retrieving the master catalogue of interface literals.',
                ],
                'InterfaceTranslationController_updateKey' => [
                    '500' => 'Failed to synchronise, instantiate, or propagate the key across the system dictionaries.',
                ],
                'InterfaceTranslationController_destroyKey' => [
                    '404' => 'The interface language key you are trying to delete does not exist.',
                    '500' => 'Internal consistency error while purging the literal from the system.',
                ],
                'InterfaceTranslationController_destroyLanguage' => [
                    '500' => 'Failed to delete the language from the system and its linked translation tables.',
                ],
                'PayPalWebhookController_handleWebhook' => [
                    '500' => 'Critical internal failure in the PayPal asynchronous event processor.',
                ],
                'PayPalWebhookController_vincularSuscripcion' => [
                    '500' => 'Internal error while pre-linking the PayPal subscription to your account.',
                ],
                'PayPalWebhookController_cancelarSuscripcionActiva' => [
                    '400' => 'The PayPal gateway rejected the automatic renewal cancellation request.',
                    '422' => 'No active PayPal subscription was found linked to this profile.',
                    '502' => 'External connection failure with the PayPal gateway. Please try again.',
                    '500' => 'Internal server error while processing your automatic renewal cancellation.',
                ],
                'TablaApoyoController_indexTablas' => [
                    '500' => 'Error retrieving the support tables master catalogue.',
                ],
                'TablaApoyoController_readRows' => [
                    '404' => 'The physical table or its row map does not exist in the database engine.',
                    '500' => 'Error reading dynamic records from the selected support table.',
                ],
                'TablaApoyoController_createRow' => [
                    '400' => 'No valid fields or data were sent to process the insertion.',
                    '404' => 'The configured support table is not available.',
                    '422' => 'The code field is mandatory to register master records.',
                    '422_duplicate' => 'The submitted code is already registered in this dictionary.',
                    '500' => 'Error inserting the master record into the dynamic engine.',
                ],
                'TablaApoyoController_updateRow' => [
                    '404' => 'Support table structure or dynamic row map not located.',
                    '422_valorUsado' => 'Operation denied: Subscription level states are handled directly by PayPal.',
                    '422_root' => 'Operation denied: Altering the identity of the master relational table is not allowed.',
                    '500' => 'Critical error while processing the protected record update.',
                ],
                'TablaApoyoController_deleteRow' => [
                    '404' => 'The master record you are trying to delete does not exist in the database.',
                    '422_root' => 'Operation cancelled: Purging root nodes is not allowed to prevent breaking the dynamic panel.',
                    '500' => 'Could not delete the record due to active dependencies within the relational model.',
                ],
                'TablaApoyoController_getRowLanguages' => [
                    '404_schema' => 'The multi-language structure for the specified physical table does not exist.',
                    '404' => 'Relational structure or dynamic translation map not located.',
                    '500' => 'Could not retrieve translations for the selected row.',
                ],
                'TablaApoyoController_updateRowLanguages' => [
                    '400' => 'No valid translations have been sent to process the dynamic batch.',
                    '404' => 'The configured translation table does not exist in the relational engine.',
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
                    '500' => 'Infrastructure failure while queueing your generation request with Artificial Intelligence.',
                ],
                'TestController_show' => [
                    '403' => 'Access denied. You do not have permission to view this quiz\'s configuration.',
                    '500' => 'Could not retrieve the configuration for the selected academic quiz.',
                ],
                'TestController_update' => [
                    '403' => 'Access denied. You do not have permission to update this quiz.',
                    '500' => 'Internal error while updating the configuration and restarting AI queues.',
                ],
                'TestController_destroy' => [
                    '403' => 'Access denied. You do not have permission to delete this quiz.',
                    '500' => 'Internal consistency error while purging the selected test from the database.',
                ],
                'TestController_realizar' => [
                    '500' => 'Failure while initialising the instance to take the interactive exam.',
                ],
                'TestController_corregir' => [
                    '422_empty' => 'This quiz does not have structured questions to be graded.',
                    '500' => 'Critical error while processing grading and saving your exam attempt.',
                ],
                'CheckTierLimits_handle' => [
                    '403_no_tier' => 'Your user does not have any subscription level linked in the system.',
                    '403_max_tests' => 'Plan limit exceeded. Your current level ({{ plan }}) only allows generating {{ maxTests }} tests every 24 hours.',
                    '403_max_pages' => 'The document exceeds the maximum size allowed. Your plan ({{ plan }}) allows processing PDFs up to {{ maxPaginas }} pages (Approx. {{ pesoMaximoKB }} KB). The uploaded file is equivalent to about {{ paginasEstimadas }} pages.',
                    '403_max_questions' => 'Plan limit exceeded. Your current level ({{ plan }}) only allows generating tests up to {{ maxPreguntas }} questions (You have requested {{ solicitadas }}).',
                    '403_max_exports' => 'Premium function locked. Direct export to the Moodle GIFT standard format is not permitted on the {{ plan }} tier.',
                    '500' => 'Internal error within the constraints and subscriptions control infrastructure.',
                ],
                'RefreshTokenTimeout_handle' => [
                    '500' => 'Internal error while refreshing and extending the expiration timeout of your active session.',
                ],
                'SetLocale_handle' => [
                    '500' => 'Internal error while synchronising your language preferences with the server headers.',
                ],
                'GlobalHandler_NotFound' => [
                    '404' => 'The requested API endpoint or relational resource does not exist in TestMind.',
                ],
                'GlobalHandler_Unauthorized' => [
                    '401' => 'Your session has expired or the access token is invalid. Please log in again.',
                ],
                'GlobalHandler_Forbidden' => [
                    '403' => 'Restricted access. You do not have the required privileges or roles to execute this request.',
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

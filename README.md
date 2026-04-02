<p align="center">
  <img src="public/assets/logos/logo_horizontal_sinFondo.png" alt="TestMind Logo" width="500">
</p>

# 🧠 TestMind - Backend (Laravel + Gemini AI)

<p align="center">
  <strong>IDIOMA</strong><br>
  <strong>ES</strong> &nbsp;|&nbsp; 
  <a href="README.gl.md"><strong>GL</strong></a> &nbsp;|&nbsp; 
  <a href="README.en.md"><strong>EN</strong></a>
</p>

---

**TestMind** es el núcleo de una plataforma inteligente diseñada para la generación automática de evaluaciones académicas. Mediante el uso de la API de **Google Gemini**, el sistema procesa documentos PDF y los transforma en cuestionarios estructurados, optimizando el tiempo de creación de contenido educativo.

Este repositorio contiene la lógica del servidor, la gestión de la base de datos y la integración con la IA.

---

## 🏗️ Arquitectura del Sistema

El proyecto se basa en una arquitectura de **Frontend y Backend desacoplados**:

* **Backend (Este repositorio):** API REST desarrollada en **Laravel 11**. Se encarga de la autenticación (Sanctum), el almacenamiento de archivos y la orquestación de Jobs asíncronos para la IA.
* **Frontend:** Aplicación SPA desarrollada en **Angular**, la cual se sirve desde el directorio `public/` en entornos de producción.
    > 🔗 **Repositorio del Frontend:** [TestMind Angular](https://github.com/AdrianVG27/TestMind_front)



---

## 🛠️ Stack Tecnológico

* **Framework:** Laravel 11 (PHP 8.2+)
* **Base de Datos:** **MySQL 8.0** con implementación de columnas tipo **JSON** para una gestión flexible de las preguntas.
* **IA:** Google Gemini API (Modelo 3.1 Flash Lite).
* **Gestión de Colas:** Laravel Queue Workers para el procesamiento de PDFs en segundo plano.
* **Seguridad:** Autenticación basada en tokens con Laravel Sanctum.

---

## 📂 El uso de JSON en MySQL

Para este TFC, se ha optado por almacenar los tests en formato **JSON** dentro de MySQL. Esta decisión técnica permite:
1.  **Flexibilidad:** Adaptar el formato de las preguntas (opción múltiple, verdadero/falso) sin alterar el esquema de la base de datos.
2.  **Eficiencia:** Reducir la complejidad de las consultas (Joins) al recuperar el examen completo en un solo objeto.
3.  **Integración Directa:** El output de la IA se guarda y se sirve al frontend casi sin transformación, mejorando la velocidad de respuesta.

---

## 🚀 Instalación y Configuración

### 1. Configurar el Backend
```bash
# Clonar el repositorio
git clone [https://github.com/tu-usuario/testmind-laravel.git](https://github.com/tu-usuario/testmind-laravel.git)
cd testmind-laravel

# Instalar dependencias de PHP
composer install

# Configurar el entorno (Asegúrate de poner tu GEMINI_API_KEY)
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones y seeders
php artisan migrate --seed
```

### 2. Despliegue del Frontend (Angular)
Para producción, el frontend se integra directamente en este repositorio:
1. Dirígete al repositorio de [TestMind Angular](https://github.com/AdrianVG27/TestMind_front).
2. Genera el build de producción: `ng build --configuration production`.
3. Copia el contenido de la carpeta `dist/test-mind/browser/` (o la ruta de salida de tu Angular) dentro de la carpeta `public/` de este proyecto Laravel.
4. Laravel servirá automáticamente la SPA de Angular.

### 3. Ejecución del Procesamiento de IA
Dado que la generación de tests mediante Gemini es una tarea pesada, se realiza de forma asíncrona mediante **Laravel Queues**. Para procesar los archivos subidos, debes ejecutar el worker:

```bash
# En desarrollo
php artisan queue:work

# En producción (recomendado usar Supervisor)
php artisan queue:restart
```

---

## ⚙️ Configuración de la API de Gemini

Para que el motor de IA funcione, es imprescindible configurar la clave de API en tu archivo `.env`:

```env
GEMINI_API_KEY=tu_api_key_aqui
```
El sistema utiliza el modelo **Gemini 3.1 Flash Lite** optimizado mediante un *System Prompt* específico para garantizar que la respuesta sea siempre un JSON válido y compatible con nuestra estructura de base de datos.

---

## 🔄 Flujo de Trabajo (Workflow)



1. **Subida (Upload):** El cliente envía un PDF a través de la API de Angular.
2. **Despacho de Tarea (Job Dispatch):** Laravel almacena el archivo y pone en cola un `GenerarTestJob`.
3. **Procesamiento IA:** El worker extrae el contenido, se comunica con la API de Google Gemini y recibe la estructura del cuestionario.
4. **Almacenamiento JSON:** El resultado se guarda directamente en la columna de tipo `JSON` de la tabla `tests`.
5. **Notificación:** El test cambia su estado a `completado` y queda disponible para que el alumno lo realice.

---

## 🚧 Roadmap del Proyecto

El desarrollo de **TestMind** se divide en las siguientes áreas estratégicas:

### 🔐 Autenticación y Seguridad
- [x] **Sistema Multitabla**: Autenticación independiente para usuarios (`user`) y administradores (`admin`) mediante Laravel Sanctum.
- [x] **Registro de Usuarios**: Endpoint de registro con validación de datos y asignación automática de tokens.
- [x] **Control de Ámbito (Abilities)**: Diferenciación de permisos entre estudiantes y administradores en los tokens de API.
- [x] **Seguridad en Exámenes**: Endpoint `/test/{id}/realizar` que filtra y oculta las respuestas correctas para evitar trampas en el frontend.

### 🤖 Core de Inteligencia Artificial
- [x] **Integración con Gemini 3.1 Flash Lite**: Servicio especializado para la comunicación con Google AI.
- [x] **Ingeniería de Prompts**: Prompts optimizados para extraer contenido educativo de PDFs técnicos.
- [x] **Estructura JSON Estricta**: Generación de 3 tipos de preguntas: `unica_seleccion`, `multi_respuesta` y `completar_escribir`.
- [ ] **Soporte Multilingüe**: Capacidad de procesar apuntes y generar preguntas en Gallego e Inglés.

### ⚙️ Infraestructura y Procesamiento
- [x] **Procesamiento Asíncrono**: Implementación de Jobs para la generación de tests pesados sin bloquear la API.
- [x] **Sistema de Colas (Queues)**: Gestión de tareas mediante base de datos.
- [x] **Paralelismo con Supervisor**: Configuración de múltiples workers para procesar ráfagas de tests simultáneos.
- [x] **Resiliencia**: Lógica de reintentos (`tries`) y espera progresiva (`backoff`) para manejar límites de cuota (Rate Limit) de la IA.

### 📂 Gestión de Documentos y Mantenimiento
- [x] **Subida de Archivos**: Endpoint para carga de PDFs con almacenamiento local organizado por ID de usuario.
- [x] **Validación Técnica**: Control de tipos MIME y tamaño máximo de archivos.
- [ ] **Mantenimiento**: Comando para limpieza de archivos PDF temporales o tests huérfanos.

### 📝 Evaluación y Resultados
- [x] **Motor de Corrección**: Endpoint `/corregir` que valida las respuestas del usuario contra la base de datos y calcula la nota automáticamente.
- [x] **Historial de Intentos**: Tabla de intentos para persistir los resultados, aciertos y duración de cada test realizado por el usuario.
- [ ] **Optimización de Salida**: Implementación de *API Resources* para estandarizar todas las respuestas del servidor.

### 📊 Informes y Exportación (Admin)
- [ ] **Sistema de Informes (Blade)**: Creación de plantillas Blade optimizadas para la generación de informes en PDF o formato impresión.
- [ ] **Lógica de Exportación**: Integración de librería (ej. Snappy o DomPDF) para convertir los resultados en archivos descargables.
- [ ] **Endpoints para Analítica**: Rutas específicas para servir datos estadísticos (medias, fallos comunes) para su representación gráfica.
- [ ] **Módulo de Exportación GIFT**: Implementación de un motor de transformación de JSON a formato GIFT (estándar Moodle).

---

## ⚖️ Licencia
Este proyecto es de código abierto bajo la licencia [MIT](https://opensource.org/licenses/MIT). Siéntete libre de clonarlo, modificarlo y usarlo para fines educativos.

---

## 👨‍💻 Autor
**[@AdrianVG27](https://github.com/AdrianVG27)** - Estudiante de Desarrollo de Aplicaciones Web.
*Este proyecto es el resultado de mi Trabajo de Fin de Ciclo (TFC) - 2026.*

---

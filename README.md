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

## 🚧 En Proceso (Roadmap)

Actualmente, el proyecto se encuentra en fase de mejora continua con las siguientes funcionalidades en desarrollo:
- [ ] **Módulo de Exportación GIFT**: Implementación de un motor de transformación de JSON a formato GIFT (estándar Moodle) para la importación directa de cuestionarios de PHP.
- [ ] **Dashboard de Analíticas:** Visualización de progreso y notas mediante gráficas (Chart.js).
- [ ] **Corrección Automática:** Evaluación en tiempo real de las respuestas enviadas por el alumno.
- [ ] **Soporte Multilingüe:** Capacidad de procesar apuntes y generar preguntas en Gallego e Inglés.

---

## ⚖️ Licencia
Este proyecto es de código abierto bajo la licencia [MIT](https://opensource.org/licenses/MIT). Siéntete libre de clonarlo, modificarlo y usarlo para fines educativos.

---

## 👨‍💻 Autor
**[@AdrianVG27](https://github.com/AdrianVG27)** - Estudiante de Desarrollo de Aplicaciones Web.
*Este proyecto es el resultado de mi Trabajo de Fin de Ciclo (TFC) - 2026.*

---

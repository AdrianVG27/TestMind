<p align="center">
  <img src="public/assets/logos/logo_horizontal_sinFondo.png" alt="TestMind Logo" width="500">
</p>

# 🧠 TestMind - Backend (Laravel + Gemini AI)

<p align="center">
  <strong>IDIOMA</strong><br>
  <a href="README.md"><strong>ES</strong></a> &nbsp;|&nbsp; 
  <strong>GL</strong> &nbsp;|&nbsp; 
  <a href="README.en.md"><strong>EN</strong></a>
</p>

---

**TestMind** é o núcleo dunha plataforma intelixente deseñada para a xeración automática de avaliacións académicas. Mediante o uso da API de **Google Gemini**, o sistema procesa documentos PDF e transfórmaos en cuestionarios estruturados, optimizando o tempo de creación de contido educativo.

Este repositorio contén a lóxica do servidor, a xestión da base de datos e a integración coa IA.

---

## 🏗️ Arquitectura do Sistema

O proxecto baséase nunha arquitectura de **Frontend e Backend desacoplados**:

* **Backend (Este repositorio):** API REST desenvolvida en **Laravel 11**. Encárgase da autenticación (Sanctum), o almacenamento de ficheiros e a orquestración de Jobs asíncronos para a IA.
* **Frontend:** Aplicación SPA desenvolvida en **Angular**, a cal se serve dende o directorio `public/` en contornas de produción.
    > 🔗 **Repositorio do Frontend:** [TestMind Angular](https://github.com/AdrianVG27/TestMind_front)

---

## 🛠️ Stack Tecnolóxico

* **Framework:** Laravel 11 (PHP 8.2+)
* **Base de Datos:** **MySQL 8.0** con implementación de columnas tipo **JSON** para unha xestión flexible das preguntas.
* **IA:** Google Gemini API (Modelo 3.1 Flash Lite).
* **Xestión de Colas:** Laravel Queue Workers para o procesamento de PDFs en segundo plano.
* **Seguridade:** Autenticación baseada en tokens con Laravel Sanctum.

---

## 📂 O uso de JSON en MySQL

Para este TFC, optouse por almacear os tests en formato **JSON** dentro de MySQL. Esta decisión técnica permite:
1. **Flexibilidade:** Adaptar o formato das preguntas (opción múltiple, verdadeiro/falso) sen alterar o esquema da base de datos.
2. **Eficiencia:** Reducir a complexidade das consultas (Joins) ao recuperar o exame completo nun único obxecto.
3. **Integración Directa:** O output da IA gárdase e sérvese ao frontend case sen transformación, mellorando a velocidade de resposta.

---

## 🚀 Instalación e Configuración

### 1. Configurar o Backend
```bash
# Clonar o repositorio
git clone [https://github.com/tu-usuario/testmind-laravel.git](https://github.com/tu-usuario/testmind-laravel.git)
cd testmind-laravel

# Instalar dependencias de PHP
composer install

# Configurar a contorna (Asegúrate de poñer a túa GEMINI_API_KEY)
cp .env.example .env
php artisan key:generate

# Executar migracións e seeders
php artisan migrate --seed
```

### 2. Despregamento do Frontend (Angular)
Para produción, o frontend intégrase directamente neste repositorio:
1. Diríxete ao repositorio de [TestMind Angular](https://github.com/AdrianVG27/TestMind_front).
2. Xera o build de produción: `ng build --configuration production`.
3. Copia o contido da carpeta `dist/test-mind/browser/` (ou a ruta de saída do teu Angular) dentro da carpeta `public/` deste proxecto Laravel.
4. Laravel servirá automaticamente a SPA de Angular.

### 3. Execución do Procesamento de IA
Dado que a xeración de tests mediante Gemini é unha tarefa pesada, realízase de forma asíncrona mediante **Laravel Queues**. Para procesar os ficheiros subidos, debes executar o worker:

```bash
# En desenvolvemento
php artisan queue:work

# En produción (recomendado usar Supervisor)
php artisan queue:restart
```

---

## ⚙️ Configuración da API de Gemini

Para que o motor de IA funcione, é imprescindible configurar a clave de API no teu ficheiro `.env`:

```env
GEMINI_API_KEY=tu_api_key_aqui
```
O sistema utiliza o modelo **Gemini 3.1 Flash Lite** optimizado mediante un *System Prompt* específico para garantir que a resposta sexa sempre un JSON válido e compatible coa nosa estrutura de base de datos.

---

## 🔄 Fluxo de Traballo (Workflow)

1. **Subida (Upload):** O cliente envía un PDF a través da API de Angular.
2. **Despacho de Tarefa (Job Dispatch):** Laravel almacena o ficheiro e pon en cola un `GenerarTestJob`.
3. **Procesamento IA:** O worker extrae o contido, comunícase coa API de Google Gemini e recibe a estrutura do cuestionario.
4. **Almacenamento JSON:** O resultado gárdase directamente na columna de tipo `JSON` da táboa `tests`.
5. **Notificación:** O test cambia o seu estado a `completado` e queda dispoñible para que o alumno o realice.

---

## 🚧 Roadmap do Proxecto

O desenvolvemento de **TestMind** divídese nas seguintes áreas estratéxicas:

### 🔐 Autenticación e Seguridade
- [x] **Sistema Multitáboa**: Autenticación independente para usuarios (`user`) e administradores (`admin`) mediante Laravel Sanctum.
- [x] **Rexistro de Usuarios**: Endpoint de rexistro con validación de datos e asignación automática de tokens.
- [x] **Control de Ámbito (Abilities)**: Diferenciación de permisos entre estudantes e administradores nos tokens de API.
- [x] **Seguridade en Exames**: Endpoint `/test/{id}/realizar` que filtra e oculta as respostas correctas para evitar trampas no frontend.

### 🤖 Core de Intelixencia Artificial
- [x] **Integración con Gemini 3.1 Flash Lite**: Servizo especializado para a comunicación con Google AI.
- [x] **Enxeñaría de Prompts**: Prompts optimizados para extraer contido educativo de PDFs técnicos.
- [x] **Estrutura JSON Estrita**: Xeración de 3 tipos de preguntas: `unica_seleccion`, `multi_respuesta` e `completar_escribir`.
- [ ] **Soporte Multilingüe**: Capacidade de procesar apuntamentos e xerar preguntas en Galego e Inglés.

### ⚙️ Infraestrutura e Procesamento
- [x] **Procesamento Asíncrono**: Implementación de Jobs para a xeración de tests pesados sen bloquear a API.
- [x] **Sistema de Colas (Queues)**: Xestión de tarefas mediante base de datos.
- [x] **Paralelismo con Supervisor**: Configuración de múltiples workers para procesar ráfagas de tests simultáneos.
- [x] **Resiliencia**: Lóxica de reintentos (`tries`) e espera progresiva (`backoff`) para manexar límites de cota (Rate Limit) da IA.

### 📂 Xestión de Documentos e Mantemento
- [x] **Subida de Ficheiros**: Endpoint para carga de PDFs con almacenamento local organizado polo ID de usuario.
- [x] **Validación Técnica**: Control de tipos MIME e tamaño máximo de ficheiros.
- [ ] **Mantemento**: Comando para limpeza de ficheiros PDF temporais ou tests orfos.

### 📝 Avaliación e Resultados
- [x] **Motor de Corrección**: Endpoint `/corregir` que valida as respostas do usuario contra a base de datos e calcula a nota automaticamente.
- [x] **Historial de Intentos**: Táboa de intentos para persistir os resultados, acertos e duración de cada test realizado polo usuario.
- [ ] **Optimización de Saída**: Implementación de *API Resources* para estandarizar todas as respostas do servidor.

### 📊 Informes e Exportación (Admin)
- [ ] **Sistema de Informes (Blade)**: Creación de modelos Blade optimizados para a xeración de informes en PDF ou formato impresión.
- [ ] **Lóxica de Exportación**: Integración de libraría (ex. Snappy ou DomPDF) para converter os resultados en ficheiros descargables.
- [ ] **Endpoints para Analítica**: Rutas específicas para servir datos estatísticos (medias, fallos comúns) para a súa representación gráfica.
- [ ] **Módulo de Exportación GIFT**: Implementación dun motor de transformación de JSON a formato GIFT (estándar Moodle).

---

## ⚖️ Licenza
Este proxecto é de código aberto baixo a licenza [MIT](https://opensource.org/licenses/MIT). Sente a liberdade de clonalo, modificalo e usalo para fins educativos.

---

## 👨‍💻 Autor
**[@AdrianVG27](https://github.com/AdrianVG27)** - Estudante de Desenvolvemento de Aplicacións Web.
*Este proxecto é o resultado do meu Traballo de Fin de Ciclo (TFC) - 2026.*

---

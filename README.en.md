<p align="center">
  <img src="logos/logo_horizontal_sinFondo.png" alt="TestMind Logo" width="500">
</p>

# 🧠 TestMind - Backend (Laravel + Gemini AI)

<p align="center">
  <strong>LANGUAGE</strong><br>
  <a href="README.md"><strong>ES</strong></a> &nbsp;|&nbsp; 
  <a href="README.gl.md"><strong>GL</strong></a> &nbsp;|&nbsp; 
  <strong>EN</strong>
</p>

---

**TestMind** is the core of an intelligent platform designed for the automatic generation of academic assessments. By utilizing the **Google Gemini API**, the system processes PDF documents and transforms them into structured quizzes, optimizing the time required for educational content creation.

This repository contains the server-side logic, database management, and AI integration.

---

## 🏗️ System Architecture

The project is based on a **Decoupled Frontend and Backend** architecture:

* **Backend (This repository):** REST API developed with **Laravel 11**. It handles authentication (Sanctum), file storage, and asynchronous Job orchestration for the AI.
* **Frontend:** SPA application developed in **Angular**, served from the `public/` directory in production environments.
    > 🔗 **Frontend Repository:** [TestMind Angular](https://github.com/AdrianVG27/TestMind_front)

---

## 🛠️ Technology Stack

* **Framework:** Laravel 11 (PHP 8.2+)
* **Database:** **MySQL 8.0** with **JSON** column implementation for flexible question management.
* **AI:** Google Gemini API (3.1 Flash Lite Model).
* **Queue Management:** Laravel Queue Workers for background PDF processing.
* **Security:** Token-based authentication with Laravel Sanctum.

---

## 📂 JSON Usage in MySQL

For this Final Project, tests are stored in **JSON** format within MySQL. This technical decision allows for:
1. **Flexibility:** Adapting question formats (multiple choice, true/false) without altering the database schema.
2. **Efficiency:** Reducing query complexity (Joins) by retrieving the entire quiz in a single object.
3. **Direct Integration:** AI output is stored and served to the frontend with almost no transformation, improving response speed.

---

## 🚀 Installation & Setup

### 1. Configure the Backend
```bash
# Clone the repository
git clone https://github.com/AdrianVG27/testmind-laravel.git
cd testmind-laravel

# Install PHP dependencies
composer install

# Configure the environment (Make sure to add your GEMINI_API_KEY)
cp .env.example .env
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed
```

### 2. Frontend Deployment (Angular)
For production, the frontend is integrated directly into this repository:
1. Go to the [TestMind Angular](https://github.com/AdrianVG27/TestMind_front) repository.
2. Generate the production build: `ng build --configuration production`.
3. Copy the contents of the `dist/test-mind/browser/` folder (or your Angular output path) into the `public/` directory of this Laravel project.
4. Laravel will automatically serve the Angular SPA.

### 3. AI Processing Execution
Since test generation via Gemini is a heavy task, it is performed asynchronously using **Laravel Queues**. To process uploaded files, you must run the worker:

```bash
# In development
php artisan queue:work

# In production (Supervisor recommended)
php artisan queue:restart
```

---

## ⚙️ Gemini API Configuration

To enable the AI engine, you must configure your API key in the `.env` file:

```env
GEMINI_API_KEY=your_api_key_here
```

The system uses the **Gemini 3.1 Flash Lite** model, optimized via a specific *System Prompt* to ensure the response is always a valid JSON compatible with our database structure.

---

## 🔄 Workflow

1. **Upload:** The client sends a PDF via the Angular API.
2. **Job Dispatch:** Laravel stores the file and enqueues a `GenerarTestJob`.
3. **AI Processing:** The worker extracts content, communicates with the Google Gemini API, and receives the quiz structure.
4. **JSON Storage:** The result is saved directly into the `JSON` column of the `tests` table.
5. **Notification:** The test status changes to `completed` and becomes available for the student.

---

## 🚧 In Progress (Roadmap)

Currently, the project is in a continuous improvement phase with the following features under development:
- [ ] **GIFT Export Module**: Implementation of a transformation engine from JSON to GIFT format (Moodle standard) for direct import of quizzes.
- [ ] **Analytics Dashboard**: Progress and grade visualization using graphs (Chart.js).
- [ ] **Auto-Grading**: Real-time evaluation of student answers.
- [ ] **Multilingual Support**: Ability to process notes and generate questions in Galician and English.

---

## ⚖️ License
This project is open-source under the [MIT License](https://opensource.org/licenses/MIT). Feel free to clone, modify, and use it for educational purposes.

---

## 👨‍💻 Author
**[@AdrianVG27](https://github.com/AdrianVG27)** - Web Application Development Student.
*This project is the result of my Final Project (TFC) - 2026.*

---

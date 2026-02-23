# Hackathon Learning App

An AI-powered learning platform built with PHP, jQuery, AJAX, and MySQL. Features an AI tutor, notes generator, quiz maker, test center, performance analysis, and personalized study plans.

## Features

- **AI Tutor** – Ask any topic and get an AI-generated explanation
- **Notes Generator** – Generate structured study notes on any subject
- **Quiz Generator** – Create AI-powered quizzes to test your knowledge
- **Test Center** – Take timed MCQ tests with auto-scoring
- **Performance Analysis** – AI analyzes your test results and weak areas
- **Study Plan** – Get a personalized study plan based on your performance
- **Dashboard** – Track your activity stats at a glance
- **History** – View all past activities and test attempts
- **Authentication** – Email/password signup + Google OAuth login

## Tech Stack

| Layer     | Technology                        |
|-----------|-----------------------------------|
| Frontend  | HTML, CSS, jQuery 3.7.1, AJAX     |
| Backend   | PHP (no frameworks)               |
| Database  | MySQL (via PDO)                   |
| AI        | Groq API (Llama 3.3 70B)         |
| Auth      | Session-based + Google OAuth 2.0  |
| Server    | XAMPP (Apache + MySQL)            |

## Project Structure

```
hackathon/
├── index.php                  # Entry point – redirects to login
├── .env                       # Secrets (not tracked)
├── .env.example               # Template for .env
├── public/                    # Frontend pages
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── tutor.php
│   ├── notes.php
│   ├── quiz.php
│   ├── test.php
│   ├── result.php
│   ├── history.php
│   ├── logout.php
│   └── assets/
│       ├── css/style.css
│       └── js/app.js
├── api/                       # API endpoints
│   ├── auth/
│   ├── module/
│   ├── test/
│   ├── analysis/
│   ├── plan/
│   ├── dashboard/
│   └── history/
├── app/                       # Backend logic
│   ├── core/                  # Config, Database, Session, Helpers
│   ├── controllers/           # Request handlers
│   ├── services/              # Business logic & AI calls
│   └── repositories/          # Database queries
└── storage/
    └── schema.sql             # Database schema
```

## Setup

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL)
- [MySQL Workbench](https://dev.mysql.com/downloads/workbench/) (optional)
- A free [Groq API key](https://console.groq.com/)
- Google OAuth credentials (optional, for Google login)

### Installation

1. **Clone the repo** into XAMPP's htdocs:
   ```bash
   git clone https://github.com/Bishesh15/hackathon.git
   cd hackathon
   ```
   Or create a junction link:
   ```powershell
   New-Item -ItemType Junction -Path "C:\xampp\htdocs\hackathon" -Target "D:\clz\hackathon"
   ```

2. **Create the database** – Open MySQL Workbench and run `storage/schema.sql`

3. **Configure environment** – Copy `.env.example` to `.env` and fill in your keys:
   ```bash
   cp .env.example .env
   ```
   Edit `.env`:
   ```
   DB_PASS=your_mysql_password
   AI_API_KEY=your_groq_api_key
   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   ```

4. **Start XAMPP** – Launch Apache and MySQL from the XAMPP Control Panel

5. **Visit** `http://localhost/hackathon/`

## API Endpoints

| Method | Endpoint                        | Description              |
|--------|---------------------------------|--------------------------|
| POST   | `/api/auth/register.php`        | Register new user        |
| POST   | `/api/auth/login.php`           | Login                    |
| POST   | `/api/auth/logout.php`          | Logout                   |
| GET    | `/api/auth/google.php`          | Google OAuth redirect    |
| GET    | `/api/auth/google-callback.php` | Google OAuth callback    |
| POST   | `/api/module/run.php`           | Run tutor/notes/quiz     |
| POST   | `/api/test/create.php`          | Generate MCQ test        |
| POST   | `/api/test/submit.php`          | Submit test answers      |
| POST   | `/api/analysis/get.php`         | Get AI analysis          |
| POST   | `/api/plan/get.php`             | Get AI study plan        |
| GET    | `/api/dashboard/summary.php`    | Dashboard stats          |
| GET    | `/api/history/list.php`         | Activity & test history  |

## License

MIT

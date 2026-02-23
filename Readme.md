# LearnAI – AI-Powered Learning Platform

An AI-powered learning platform built for school and high-school students. Features a conversational AI tutor, notes generator, MCQ quizzes, long-answer tests with AI grading, performance analysis, and personalized study plans.

Built with PHP, jQuery, MySQL, and the Groq API (Llama 3.3 70B).

## Features

- **AI Tutor** – Real-time chat with an AI tutor on any academic topic, with full conversation history
- **Notes Generator** – Generate structured, formatted study notes on any subject
- **Quiz Generator** – AI-generated MCQ quizzes with instant scoring and timer
- **Test Center** – Long-answer tests with AI-powered grading and detailed feedback
- **Performance Analysis** – AI analyzes your results and identifies weak areas
- **Study Plans** – Personalized study plans generated from your performance data
- **Dashboard** – Activity stats at a glance (sessions, scores, streaks)
- **History** – Browse and delete past conversations and quiz/test attempts
- **Authentication** – Email/password signup + Google OAuth 2.0 login

## Tech Stack

| Layer     | Technology                                    |
|-----------|-----------------------------------------------|
| Frontend  | HTML, CSS, jQuery 3.7.1, AJAX                 |
| Icons     | Google Material Icons                         |
| Fonts     | Google Fonts (Poppins)                        |
| Backend   | PHP 8+ (no frameworks)                        |
| Database  | MySQL (via PDO)                               |
| AI        | Groq API – Llama 3.3 70B Versatile            |
| Auth      | Session-based + Google OAuth 2.0              |
| Server    | XAMPP (Apache + MySQL)                        |

## Project Structure

```
hackathon/
├── index.php                    # Entry point – redirects to login
├── .env                         # Secrets (not tracked)
├── .env.example                 # Template for .env
│
├── public/                      # Frontend pages
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
│   ├── includes/
│   │   └── sidebar.php          # Shared sidebar layout
│   └── assets/
│       ├── css/style.css        # All styles (Material Icons, Poppins)
│       └── js/app.js            # All client-side logic (jQuery)
│
├── api/                         # REST API endpoints
│   ├── auth/                    # Register, login, logout, Google OAuth
│   ├── chat/                    # send.php, messages.php, list.php
│   ├── exam/                    # create.php, submit.php
│   ├── module/                  # run.php (tutor/notes)
│   ├── test/                    # create.php, submit.php
│   ├── analysis/                # get.php
│   ├── plan/                    # get.php
│   ├── dashboard/               # summary.php
│   └── history/                 # list.php, delete.php
│
├── app/                         # Backend logic
│   ├── core/
│   │   ├── Config.php           # .env parser
│   │   ├── Database.php         # PDO singleton
│   │   ├── Session.php          # Auth session management
│   │   └── Helpers.php          # JSON response helpers
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── ChatController.php
│   │   ├── DashboardController.php
│   │   ├── ExamController.php
│   │   ├── HistoryController.php
│   │   ├── ModuleController.php
│   │   ├── AnalysisController.php
│   │   ├── PlanController.php
│   │   └── TestController.php
│   ├── services/
│   │   ├── AiService.php        # Groq API client
│   │   ├── PromptFactory.php    # AI prompt templates
│   │   ├── TopicService.php
│   │   ├── TestService.php
│   │   ├── AnalysisService.php
│   │   ├── PlanService.php
│   │   └── ValidationService.php
│   └── repositories/
│       ├── UserRepository.php
│       ├── ConversationRepository.php
│       ├── ExamRepository.php
│       ├── AttemptRepository.php
│       ├── ActivityRepository.php
│       └── TestRepository.php
│
└── storage/
    ├── schema.sql               # Database schema (v1)
    └── migrate_v2.sql           # Migration for conversations & exams
```

## Database Schema

**7 tables:** `users`, `activity_history`, `conversations`, `messages`, `tests`, `attempts`, `exams`

Key relationships:
- `conversations` → `messages` (one-to-many, AI tutor chat history)
- `exams` → `attempts` (one-to-many, quiz/test scoring)
- All user-specific data linked via `user_id` foreign keys

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
   Or create a junction link (Windows):
   ```powershell
   New-Item -ItemType Junction -Path "C:\xampp\htdocs\hackathon" -Target "D:\path\to\hackathon"
   ```

2. **Create the database** – Open MySQL Workbench and run:
   ```
   storage/schema.sql       # Base tables
   storage/migrate_v2.sql   # Conversations, messages, exams tables
   ```

3. **Configure environment** – Copy `.env.example` to `.env` and fill in your keys:
   ```bash
   cp .env.example .env
   ```
   Edit `.env`:
   ```
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=hackathon_db
   DB_USER=root
   DB_PASS=your_mysql_password

   AI_API_KEY=your_groq_api_key

   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   ```

4. **Start XAMPP** – Launch Apache and MySQL from the XAMPP Control Panel

5. **Visit** `http://localhost/hackathon/`

## API Endpoints

| Method | Endpoint                        | Description                      |
|--------|---------------------------------|----------------------------------|
| POST   | `/api/auth/register.php`        | Register new user                |
| POST   | `/api/auth/login.php`           | Login                            |
| POST   | `/api/auth/logout.php`          | Logout                           |
| GET    | `/api/auth/google.php`          | Google OAuth redirect            |
| GET    | `/api/auth/google-callback.php` | Google OAuth callback            |
| POST   | `/api/chat/send.php`            | Send message to AI tutor         |
| GET    | `/api/chat/messages.php?id=`    | Get messages for a conversation  |
| GET    | `/api/chat/list.php`            | List user's conversations        |
| POST   | `/api/exam/create.php`          | Generate quiz/test questions     |
| POST   | `/api/exam/submit.php`          | Submit quiz/test answers         |
| POST   | `/api/module/run.php`           | Run notes generator              |
| POST   | `/api/analysis/get.php`         | Get AI performance analysis      |
| POST   | `/api/plan/get.php`             | Get AI study plan                |
| GET    | `/api/dashboard/summary.php`    | Dashboard stats                  |
| GET    | `/api/history/list.php`         | Conversations & attempt history  |
| POST   | `/api/history/delete.php`       | Delete a conversation or attempt |

## License

MIT

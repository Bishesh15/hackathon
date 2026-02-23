<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/core/Session.php';
Session::start();
Session::requireAuth();
$user = Session::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home – LearnAI</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <!-- Search Hero -->
        <div class="home-hero">
            <h1>What would you like to learn?</h1>
            <p>Ask any school or high-school topic and get instant AI-powered explanations</p>
            <div class="search-box">
                <span class="search-icon material-icons">search</span>
                <input type="text" id="hero-search" placeholder="e.g. Photosynthesis, Quadratic equations, Newton's laws…">
                <button class="search-btn" id="hero-search-btn">Ask Tutor</button>
            </div>
            <div class="quick-topics">
                <span class="quick-topic" data-topic="Photosynthesis">Photosynthesis</span>
                <span class="quick-topic" data-topic="Pythagorean Theorem">Pythagorean Theorem</span>
                <span class="quick-topic" data-topic="World War II">World War II</span>
                <span class="quick-topic" data-topic="Newton's Laws">Newton's Laws</span>
                <span class="quick-topic" data-topic="Chemical Bonding">Chemical Bonding</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" id="stats-grid">
            <div class="card stat-card">
                <div class="card-icon purple"><span class="material-icons">menu_book</span></div>
                <div><div class="stat-value" id="s-activities">-</div><div class="stat-label">Total Activities</div></div>
            </div>
            <div class="card stat-card">
                <div class="card-icon blue"><span class="material-icons">smart_toy</span></div>
                <div><div class="stat-value" id="s-tutor">-</div><div class="stat-label">Tutor Sessions</div></div>
            </div>
            <div class="card stat-card">
                <div class="card-icon green"><span class="material-icons">description</span></div>
                <div><div class="stat-value" id="s-notes">-</div><div class="stat-label">Notes Generated</div></div>
            </div>
            <div class="card stat-card">
                <div class="card-icon orange"><span class="material-icons">quiz</span></div>
                <div><div class="stat-value" id="s-quiz">-</div><div class="stat-label">Quizzes Taken</div></div>
            </div>
            <div class="card stat-card">
                <div class="card-icon pink"><span class="material-icons">assignment</span></div>
                <div><div class="stat-value" id="s-tests">-</div><div class="stat-label">Tests Done</div></div>
            </div>
            <div class="card stat-card">
                <div class="card-icon red"><span class="material-icons">track_changes</span></div>
                <div><div class="stat-value" id="s-avg">-</div><div class="stat-label">Avg Score</div></div>
            </div>
        </div>

        <!-- Module Cards -->
        <div class="page-header"><h1>Modules</h1></div>
        <div class="modules-grid">
            <a href="tutor.php" class="card module-card purple">
                <div class="card-header">
                    <div class="card-icon purple"><span class="material-icons">smart_toy</span></div>
                </div>
                <div class="card-title">AI Tutor</div>
                <div class="module-desc">Have a conversation with your AI tutor about any topic</div>
                <span class="module-arrow material-icons">arrow_forward</span>
            </a>
            <a href="notes.php" class="card module-card green">
                <div class="card-header">
                    <div class="card-icon green"><span class="material-icons">description</span></div>
                </div>
                <div class="card-title">Notes Generator</div>
                <div class="module-desc">Generate structured study notes instantly</div>
                <span class="module-arrow material-icons">arrow_forward</span>
            </a>
            <a href="quiz.php" class="card module-card orange">
                <div class="card-header">
                    <div class="card-icon orange"><span class="material-icons">quiz</span></div>
                </div>
                <div class="card-title">Quiz</div>
                <div class="module-desc">Test yourself with MCQ quizzes and get scored</div>
                <span class="module-arrow material-icons">arrow_forward</span>
            </a>
            <a href="test.php" class="card module-card blue">
                <div class="card-header">
                    <div class="card-icon blue"><span class="material-icons">assignment</span></div>
                </div>
                <div class="card-title">Test Center</div>
                <div class="module-desc">Long-answer tests with image analysis support</div>
                <span class="module-arrow material-icons">arrow_forward</span>
            </a>
            <a href="history.php" class="card module-card pink">
                <div class="card-header">
                    <div class="card-icon pink"><span class="material-icons">history</span></div>
                </div>
                <div class="card-title">History</div>
                <div class="module-desc">Review your past conversations and quiz attempts</div>
                <span class="module-arrow material-icons">arrow_forward</span>
            </a>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
<script>App.loadDashboard();</script>
</body>
</html>

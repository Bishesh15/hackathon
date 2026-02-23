<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/core/Session.php';
Session::requireAuth();
$user = Session::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <span class="nav-brand">LearnApp</span>
        <span>Hi, <?= htmlspecialchars($user['name']) ?></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </nav>
    <div class="container">
        <h2>Dashboard</h2>
        <div id="stats-grid" class="stats-grid">
            <div class="stat-card"><span class="stat-num" id="s-activities">-</span><span>Activities</span></div>
            <div class="stat-card"><span class="stat-num" id="s-tutor">-</span><span>Tutor</span></div>
            <div class="stat-card"><span class="stat-num" id="s-notes">-</span><span>Notes</span></div>
            <div class="stat-card"><span class="stat-num" id="s-quiz">-</span><span>Quizzes</span></div>
            <div class="stat-card"><span class="stat-num" id="s-tests">-</span><span>Tests</span></div>
            <div class="stat-card"><span class="stat-num" id="s-avg">-</span><span>Avg Score</span></div>
        </div>

        <h2>Modules</h2>
        <div class="module-grid">
            <a href="tutor.php" class="module-card">AI Tutor</a>
            <a href="notes.php" class="module-card">Notes</a>
            <a href="quiz.php" class="module-card">Quiz</a>
            <a href="test.php" class="module-card">Test Center</a>
            <a href="history.php" class="module-card">History</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>App.loadDashboard();</script>
</body>
</html>

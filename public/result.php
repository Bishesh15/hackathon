<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/core/Session.php';
Session::requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Result</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">← Dashboard</a>
        <span>Test Result</span>
    </nav>
    <div class="container">
        <div id="result-score" class="card text-center">
            <h2>Your Score</h2>
            <p class="big-score" id="score-display">-</p>
            <div class="actions">
                <button id="btn-analysis" class="btn btn-primary">Get Analysis</button>
                <button id="btn-plan" class="btn btn-secondary">Get Study Plan</button>
                <a href="test.php" class="btn btn-secondary">New Test</a>
            </div>
        </div>

        <div id="result-details" class="card" style="display:none;">
            <h3>Question Details</h3>
            <div id="details-list"></div>
        </div>

        <div id="analysis-box" class="card" style="display:none;">
            <h3>Analysis</h3>
            <div id="analysis-content"></div>
        </div>

        <div id="plan-box" class="card" style="display:none;">
            <h3>Study Plan</h3>
            <div id="plan-content"></div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>App.loadResult();</script>
</body>
</html>

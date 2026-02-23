<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/core/Session.php';
Session::start();
Session::requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result – LearnAI</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><span class="material-icons" style="vertical-align:middle">track_changes</span> Quiz Result</h1>
        </div>

        <div class="card">
            <div class="result-hero">
                <div class="result-score" id="score-display">-</div>
                <div class="result-label" id="score-label"></div>
            </div>
            <div class="result-actions">
                <button id="btn-analysis" class="btn btn-primary"><span class="material-icons" style="vertical-align:middle;font-size:1.1rem">bar_chart</span> Get Analysis</button>
                <button id="btn-plan" class="btn btn-secondary"><span class="material-icons" style="vertical-align:middle;font-size:1.1rem">menu_book</span> Study Plan</button>
                <a href="quiz.php" class="btn btn-secondary"><span class="material-icons" style="vertical-align:middle;font-size:1.1rem">refresh</span> New Quiz</a>
            </div>
        </div>

        <div id="result-details" class="card mt-2" style="display:none;">
            <div class="card-title mb-2">Question Details</div>
            <div id="details-list"></div>
        </div>

        <div id="analysis-box" class="card mt-2" style="display:none;">
            <div class="card-title mb-2">Analysis</div>
            <div id="analysis-content" class="notes-output"></div>
        </div>

        <div id="plan-box" class="card mt-2" style="display:none;">
            <div class="card-title mb-2">Study Plan</div>
            <div id="plan-content" class="notes-output"></div>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
<script>App.loadResult();</script>
</body>
</html>

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
    <title>Quiz – LearnAI</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><span class="material-icons" style="vertical-align:middle">quiz</span> Quiz</h1>
            <p>Generate MCQ quizzes and test your knowledge</p>
        </div>

        <!-- Step 1: Setup -->
        <div id="quiz-setup" class="card quiz-setup">
            <div class="card-title mb-2">Create a Quiz</div>
            <form id="quiz-form">
                <div class="form-group">
                    <label for="quiz-topic">Topic</label>
                    <input type="text" id="quiz-topic" name="topic" placeholder="e.g. Photosynthesis, Algebra…" required>
                </div>
                <div class="form-group">
                    <label for="quiz-count">Number of Questions</label>
                    <select id="quiz-count" name="count">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Generate Quiz</button>
                <div id="quiz-msg" class="mt-2"></div>
            </form>
        </div>

        <!-- Step 2: Take quiz -->
        <div id="quiz-paper" class="quiz-paper" style="display:none;">
            <div class="card">
                <div class="d-flex justify-between align-center mb-2">
                    <div class="card-title" id="quiz-title">Quiz</div>
                    <span id="quiz-timer" style="font-weight:600; color:#667eea;"></span>
                </div>
                <div id="quiz-questions"></div>
                <button class="btn btn-primary mt-2" id="quiz-submit-btn">Submit Quiz</button>
            </div>
        </div>

        <!-- Step 3: Result -->
        <div id="quiz-result" style="display:none;">
            <div class="card">
                <div class="result-hero">
                    <div class="result-score" id="quiz-score">0%</div>
                    <div class="result-label" id="quiz-score-label">0 / 0 correct</div>
                </div>
                <div class="result-actions">
                    <button class="btn btn-primary" id="quiz-analysis-btn"><span class="material-icons" style="vertical-align:middle;font-size:1.1rem">bar_chart</span> Get Analysis</button>
                    <button class="btn btn-secondary" id="quiz-plan-btn"><span class="material-icons" style="vertical-align:middle;font-size:1.1rem">menu_book</span> Study Plan</button>
                    <button class="btn btn-secondary" id="quiz-retry-btn"><span class="material-icons" style="vertical-align:middle;font-size:1.1rem">refresh</span> Try Again</button>
                </div>
                <div id="quiz-analysis" class="notes-output mt-2" style="display:none;"></div>
            </div>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
</body>
</html>

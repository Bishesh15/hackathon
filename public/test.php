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
    <title>Test Center – LearnAI</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><span class="material-icons" style="vertical-align:middle">assignment</span> Test Center</h1>
            <p>Long-answer questions — write paragraphs or upload images for analysis</p>
        </div>

        <!-- Step 1: Setup -->
        <div id="test-setup" class="card" style="max-width:600px;">
            <div class="card-title mb-2">Create a Test</div>
            <form id="test-create-form">
                <div class="form-group">
                    <label for="test-topic">Topic</label>
                    <input type="text" id="test-topic" name="topic" placeholder="e.g. Causes of World War I, Cell Division…" required>
                </div>
                <div class="form-group">
                    <label for="test-count">Number of Questions</label>
                    <select id="test-count" name="count">
                        <option value="3" selected>3</option>
                        <option value="5">5</option>
                        <option value="7">7</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Generate Test</button>
                <div id="test-msg" class="mt-2"></div>
            </form>
        </div>

        <!-- Step 2: Answer questions -->
        <div id="test-paper" style="display:none;">
            <div class="card">
                <div class="card-title mb-2" id="test-title">Test</div>
                <div id="test-questions"></div>
                <button class="btn btn-primary mt-2" id="test-submit-btn">Submit Test</button>
            </div>
        </div>

        <!-- Step 3: Feedback -->
        <div id="test-feedback" style="display:none;">
            <div class="card">
                <div class="result-hero">
                    <div class="result-score" id="test-score">0%</div>
                    <div class="result-label">AI-graded score</div>
                </div>
                <div id="test-feedback-content" class="notes-output mt-2"></div>
                <div class="result-actions mt-2">
                    <button class="btn btn-secondary" id="test-retry-btn"><span class="material-icons" style="vertical-align:middle;font-size:1.1rem">refresh</span> Try Again</button>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
</body>
</html>

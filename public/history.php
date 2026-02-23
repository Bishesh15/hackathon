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
    <title>History – LearnAI</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><span class="material-icons" style="vertical-align:middle">history</span> History</h1>
            <p>Your past conversations and quiz attempts</p>
        </div>

        <!-- Conversations list -->
        <div class="card mb-2">
            <div class="card-title mb-2">Conversations</div>
            <div id="conv-list" class="history-list"><p style="color:#9ca3af;">Loading…</p></div>
        </div>

        <!-- Quiz attempts list -->
        <div class="card">
            <div class="card-title mb-2">Quiz Attempts</div>
            <div id="attempt-list" class="history-list"><p style="color:#9ca3af;">Loading…</p></div>
        </div>

        <!-- Conversation detail (chat replay) – hidden initially -->
        <div id="conv-detail" style="display:none;">
            <div class="d-flex align-center gap-1 mb-2">
                <button class="btn btn-secondary" id="conv-back"><span class="material-icons" style="vertical-align:middle;font-size:1.1rem">arrow_back</span> Back</button>
                <h2 id="conv-detail-title" style="font-size:1.2rem; font-weight:600;"></h2>
            </div>
            <div class="card">
                <div id="conv-messages" class="chat-messages" style="max-height:70vh;"></div>
            </div>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
<script>App.loadHistory();</script>
</body>
</html>

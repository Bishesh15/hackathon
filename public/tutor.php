<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/core/Session.php';
Session::start();
Session::requireAuth();
$conversationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Tutor – LearnAI</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content" style="padding: 0 2rem;">
        <div class="chat-container">
            <!-- Chat messages area -->
            <div class="chat-messages" id="chat-messages">
                <!-- Welcome message shown when no conversation -->
                <div id="chat-welcome" class="home-hero" style="padding-top:3rem;">
                    <h1><span class="material-icons" style="vertical-align:middle">smart_toy</span> AI Tutor</h1>
                    <p>Ask me anything about your school or high-school subjects. I'll explain it step by step!</p>
                </div>
            </div>

            <!-- Chat input area -->
            <div class="chat-input-area">
                <div class="chat-input-wrapper">
                    <textarea id="chat-input" placeholder="Type your question here…" rows="1"></textarea>
                    <button class="chat-send-btn" id="chat-send" title="Send"><span class="material-icons">send</span></button>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
<script>App.initChat(<?= $conversationId ?>);</script>
</body>
</html>

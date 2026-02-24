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
    <title>Notes – LearnAI</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content notes-split-layout">
        <!-- Left: Notes Generator -->
        <div class="notes-left-panel">
            <div class="page-header">
                <h1><span class="material-icons" style="vertical-align:middle">description</span> Notes Generator</h1>
                <p>Generate structured study notes on any school topic</p>
            </div>

            <div class="card" style="max-width:600px;">
                <form id="module-form" data-module="notes">
                    <div class="form-group">
                        <label for="topic">Topic</label>
                        <input type="text" id="topic" name="topic" placeholder="e.g. World War II, Organic Chemistry…" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Notes</button>
                    <div id="form-msg" class="mt-2"></div>
                </form>
            </div>

            <div id="module-result" class="notes-output mt-2" style="display:none;"></div>
        </div>

        <!-- Right: Saved Tutor Notes -->
        <div class="notes-right-panel">
            <div class="page-header" style="margin-bottom:1rem;">
                <h2><span class="material-icons" style="vertical-align:middle;font-size:1.3rem">edit_note</span> My Tutor Notes</h2>
                <p style="font-size:.85rem;">Notes saved from the AI Tutor notepad</p>
            </div>
            <div id="saved-notes-list">
                <div class="text-center mt-2" style="color:#9ca3af;">Loading…</div>
            </div>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
<script>App.loadSavedNotes();</script>
</body>
</html>

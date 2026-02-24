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
    <title>My Plan – LearnAI</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><span class="material-icons" style="vertical-align:middle">bookmark</span> My Plan</h1>
            <p>Your saved analyses and study plans from quizzes &amp; tests</p>
        </div>

        <!-- Filter tabs -->
        <div class="plan-tabs mb-2">
            <button class="plan-tab active" data-filter="all">All</button>
            <button class="plan-tab" data-filter="analysis">Analyses</button>
            <button class="plan-tab" data-filter="study_plan">Study Plans</button>
        </div>

        <div id="plans-list">
            <div class="text-center mt-2" style="color:#9ca3af;">Loading…</div>
        </div>

        <div id="no-plans" class="card text-center mt-2" style="display:none; padding:3rem;">
            <span class="material-icons" style="font-size:3rem; color:#d1d5db;">bookmark_border</span>
            <p style="color:#9ca3af; margin-top:1rem;">No saved plans yet. Complete a quiz or test and click "Get Analysis" or "Study Plan" to save them here.</p>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
<script>App.loadMyPlans();</script>
</body>
</html>

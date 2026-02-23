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
    <title>History</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">← Dashboard</a>
        <span>History</span>
    </nav>
    <div class="container">
        <h2>Activity History</h2>
        <div id="activity-list" class="history-list"><p>Loading...</p></div>

        <h2>Test Attempts</h2>
        <div id="attempt-list" class="history-list"><p>Loading...</p></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>App.loadHistory();</script>
</body>
</html>

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
    <title>Notes Generator</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">← Dashboard</a>
        <span>Notes Generator</span>
    </nav>
    <div class="container">
        <div class="card">
            <form id="module-form" class="form-stack" data-module="notes">
                <label>Topic <input type="text" name="topic" placeholder="e.g. World War II" required></label>
                <button type="submit" class="btn btn-primary">Generate Notes</button>
                <p id="form-msg" class="msg"></p>
            </form>
        </div>
        <div id="module-result" class="result-box" style="display:none;"></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

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
    <title>Test Center</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">← Dashboard</a>
        <span>Test Center</span>
    </nav>
    <div class="container">
        <!-- Step 1: Generate test -->
        <div id="test-setup" class="card">
            <h2>Create a Test</h2>
            <form id="test-create-form" class="form-stack">
                <label>Topic <input type="text" name="topic" placeholder="e.g. Machine Learning" required></label>
                <label>Number of Questions
                    <select name="count">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                </label>
                <button type="submit" class="btn btn-primary">Generate Test</button>
                <p id="form-msg" class="msg"></p>
            </form>
        </div>

        <!-- Step 2: Take test (rendered by JS) -->
        <div id="test-paper" style="display:none;"></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

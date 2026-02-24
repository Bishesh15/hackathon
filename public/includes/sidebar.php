<?php
/**
 * Shared sidebar layout – included by all authenticated pages.
 * Requirements: Session must be started and user must be logged in.
 */
require_once __DIR__ . '/../../app/core/Session.php';
Session::start();
Session::requireAuth();

$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$userName  = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$userInitial = strtoupper(substr($userName, 0, 1));
?>
<!-- Mobile header -->
<div class="mobile-header">
    <button class="hamburger" onclick="document.querySelector('.sidebar').classList.toggle('open');document.querySelector('.sidebar-overlay').classList.toggle('open');"><span class="material-icons">menu</span></button>
    <span class="app-name">LearnAI</span>
</div>
<div class="sidebar-overlay" onclick="document.querySelector('.sidebar').classList.remove('open');this.classList.remove('open');"></div>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo"><span class="material-icons">school</span></div>
        <h2>LearnAI</h2>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="/hackathon/public/dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <span class="icon material-icons">home</span> Home
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Learn</div>
            <a href="/hackathon/public/tutor.php" class="nav-item <?= $currentPage === 'tutor' ? 'active' : '' ?>">
                <span class="icon material-icons">smart_toy</span> AI Tutor
            </a>
            <a href="/hackathon/public/notes.php" class="nav-item <?= $currentPage === 'notes' ? 'active' : '' ?>">
                <span class="icon material-icons">description</span> Notes
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Assess</div>
            <a href="/hackathon/public/quiz.php" class="nav-item <?= $currentPage === 'quiz' ? 'active' : '' ?>">
                <span class="icon material-icons">quiz</span> Quiz
            </a>
            <a href="/hackathon/public/test.php" class="nav-item <?= $currentPage === 'test' ? 'active' : '' ?>">
                <span class="icon material-icons">assignment</span> Test
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Review</div>
            <a href="/hackathon/public/plan.php" class="nav-item <?= $currentPage === 'plan' ? 'active' : '' ?>">
                <span class="icon material-icons">bookmark</span> My Plan
            </a>
            <a href="/hackathon/public/history.php" class="nav-item <?= $currentPage === 'history' ? 'active' : '' ?>">
                <span class="icon material-icons">history</span> History
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= htmlspecialchars($userInitial) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-email"><?= htmlspecialchars($userEmail) ?></div>
            </div>
            <a href="/hackathon/public/logout.php" class="btn-logout" title="Sign out"><span class="material-icons">logout</span></a>
        </div>
    </div>
</aside>

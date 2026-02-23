<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/core/Session.php';
Session::logout();
header('Location: login.php');
exit;

<?php
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth($conn);

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../auth/login.php');
    exit;
} 
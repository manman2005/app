<?php
require_once '../includes/Auth.php';
require_once '../config/database.php';

$auth = new Auth($conn);
$auth->logout();

header('Location: ../auth/login.php');
exit; 
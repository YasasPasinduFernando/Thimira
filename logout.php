<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

unset($_SESSION['user_id'], $_SESSION['user_username']);
header('Location: ' . url('index.php'));
exit;

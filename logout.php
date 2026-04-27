<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

logout_frontend_user();
header('Location: ' . url('index.php'));
exit;

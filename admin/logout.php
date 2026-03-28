<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

session_unset();
session_destroy();

header('Location: ' . url('admin/login.php'));
exit;

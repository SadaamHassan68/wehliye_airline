<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

User::logout();
header('Location: ' . base_url() . '/index.php');
exit;

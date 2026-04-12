<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

$base = base_url();
$qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: ' . $base . '/admin/schedules.php' . $qs, true, 302);
exit;

<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

header('Location: ' . base_url() . '/admin/flights.php', true, 301);
exit;

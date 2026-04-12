<?php

declare(strict_types=1);

/** @var string $base */
/** @var array|null $user */
/** @var array $upcomingFlights */

$upcomingFlights = $upcomingFlights ?? [];

require __DIR__ . '/home_hero.php';
require __DIR__ . '/home_available_flights.php';
require __DIR__ . '/home_features.php';

<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

require_login();
$user = User::current();
$base = base_url();

if ($user['role'] === 'admin') {
    header('Location: ' . $base . '/admin/dashboard.php');
    exit;
}

$stats = Booking::dashboardStats();
$reports = Booking::routeIncomeReport();
$loadFactors = Booking::loadFactorReport();

$pageTitle = 'Dashboard — Wehliye Airline';
$adminSidebar = false;

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials/dashboard_stats.php';
require __DIR__ . '/includes/footer.php';

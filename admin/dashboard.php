<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

require_roles(['admin']);

$base = base_url();
$user = User::current();
$stats = Booking::dashboardStats();
$reports = Booking::routeIncomeReport();
$loadFactors = Booking::loadFactorReport();

// Fetch chart data for the dashboard (last 14 days)
$paidSeries = Booking::paidRevenueLastDays(14);
$newSeries = Booking::newBookingsLastDays(14);

$chartPayload = [
    'combo' => [
        'labels' => $paidSeries['labels'],
        'revenue' => $paidSeries['values'],
        'bookings' => $newSeries['values'],
    ]
];

$chartJson = json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$pageTitle = 'Admin dashboard — Wehliye Airline';
$adminSidebar = true;
$activeNav = 'dashboard';

// Pass Chart.js assets
$pageScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>'
    . '<script type="application/json" id="ofbms-report-data">' . ($chartJson ?: '{}') . '</script>'
    . '<script src="' . htmlspecialchars($base) . '/assets/js/admin-reports.js"></script>';

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/partials/dashboard_stats.php';
require dirname(__DIR__) . '/includes/footer.php';

<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

require_roles(['admin']);

$base = base_url();
$user = User::current();

$stats = Booking::dashboardStats();
$lifetimePaid = Booking::totalPaidRevenue();
$paidSeries = Booking::paidRevenueLastDays(14);
$newSeries = Booking::newBookingsLastDays(14);
$sum14Paid = array_sum($paidSeries['values']);
$sum14New = array_sum($newSeries['values']);

$byStatus = Booking::bookingCountsByStatus();
$byPayment = Booking::bookingCountsByPayment();
$routes = Booking::routeIncomeReport();
$routes = array_slice($routes, 0, 10);
$loadFactors = Booking::loadFactorReport();
$loadFactors = array_slice($loadFactors, 0, 12);

$chartPayload = [
    'combo' => [
        'labels' => $paidSeries['labels'],
        'revenue' => $paidSeries['values'],
        'bookings' => $newSeries['values'],
    ],
    'status' => [
        'labels' => array_map(static fn (array $r): string => (string) $r['status'], $byStatus),
        'values' => array_map(static fn (array $r): int => (int) $r['cnt'], $byStatus),
    ],
    'payment' => [
        'labels' => array_map(static fn (array $r): string => (string) $r['payment_status'], $byPayment),
        'values' => array_map(static fn (array $r): int => (int) $r['cnt'], $byPayment),
    ],
    'routes' => [
        'labels' => array_map(static fn (array $r): string => (string) $r['route'], $routes),
        'values' => array_map(static fn (array $r): float => (float) $r['income'], $routes),
    ],
    'load' => [
        'labels' => array_map(static fn (array $r): string => (string) $r['flight_no'], $loadFactors),
        'values' => array_map(static fn (array $r): float => (float) $r['load_factor'], $loadFactors),
    ],
];

$chartJson = json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($chartJson === false) {
    $chartJson = '{}';
}

$pageTitle = 'Reports — Wehliye Admin';
$adminSidebar = true;
$activeNav = 'reports';

$pageScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>'
    . '<script type="application/json" id="ofbms-report-data">' . $chartJson . '</script>'
    . '<script src="' . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '/assets/js/admin-reports.js"></script>';

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="ofbms-report-hero rounded-4 text-white mb-4">
    <div class="ofbms-report-hero-inner">
        <span class="ofbms-report-hero-badge"><i class="bi bi-graph-up-arrow me-1"></i> Analytics</span>
        <h1 class="ofbms-page-title h3 text-white mb-2">Reports &amp; insights</h1>
        <p class="ofbms-report-hero-lead mb-0">Visual trends from paid revenue, booking volume, routes, and cabin load. Figures use the same rules as the dashboard (paid revenue = accepted payments only).</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card ofbms-report-kpi h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="ofbms-report-kpi-label">Lifetime paid revenue</div>
                <div class="ofbms-report-kpi-value">$<?= number_format($lifetimePaid, 2) ?></div>
                <div class="ofbms-report-kpi-hint">All time · Paid bookings</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card ofbms-report-kpi h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="ofbms-report-kpi-label">Last 14 days (paid)</div>
                <div class="ofbms-report-kpi-value">$<?= number_format($sum14Paid, 2) ?></div>
                <div class="ofbms-report-kpi-hint">Recorded by booking date</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card ofbms-report-kpi h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="ofbms-report-kpi-label">New bookings (14d)</div>
                <div class="ofbms-report-kpi-value"><?= (int) $sum14New ?></div>
                <div class="ofbms-report-kpi-hint">Created in period</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card ofbms-report-kpi h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="ofbms-report-kpi-label">Awaiting approval</div>
                <div class="ofbms-report-kpi-value"><?= (int) ($stats['pending_approval'] ?? 0) ?></div>
                <div class="ofbms-report-kpi-hint">Pending payment</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card ofbms-report-chart-card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h2 class="h6 ofbms-page-title mb-1">Activity — last 14 days</h2>
                        <p class="text-muted small mb-0">Paid revenue (line, USD) and new bookings (bars, count).</p>
                    </div>
                </div>
                <div class="ofbms-chart-wrap" style="height: 320px;">
                    <canvas id="ofbmsChartCombo" aria-label="Revenue and bookings chart" role="img"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card ofbms-report-chart-card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <h2 class="h6 ofbms-page-title mb-1">Booking status</h2>
                <p class="text-muted small mb-3">Share of records by confirmation state.</p>
                <div class="ofbms-chart-wrap ofbms-chart-wrap-doughnut mx-auto" style="max-width: 320px; height: 280px;">
                    <canvas id="ofbmsChartStatus" aria-label="Booking status chart" role="img"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card ofbms-report-chart-card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <h2 class="h6 ofbms-page-title mb-1">Payment status</h2>
                <p class="text-muted small mb-3">Pipeline view across all bookings.</p>
                <div class="ofbms-chart-wrap ofbms-chart-wrap-doughnut mx-auto" style="max-width: 320px; height: 280px;">
                    <canvas id="ofbmsChartPayment" aria-label="Payment status chart" role="img"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card ofbms-report-chart-card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <h2 class="h6 ofbms-page-title mb-1">Route income (paid)</h2>
                <p class="text-muted small mb-3">Top routes by total paid amount.</p>
                <div class="ofbms-chart-wrap" style="height: 300px;">
                    <canvas id="ofbmsChartRoutes" aria-label="Route income chart" role="img"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card ofbms-report-chart-card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <h2 class="h6 ofbms-page-title mb-1">Load factor</h2>
                <p class="text-muted small mb-3">Non-cancelled seats vs capacity (top flights).</p>
                <div class="ofbms-chart-wrap" style="height: 300px;">
                    <canvas id="ofbmsChartLoad" aria-label="Load factor chart" role="img"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <a href="<?= htmlspecialchars($base) ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">← Dashboard</a>
    <a href="<?= htmlspecialchars($base) ?>/admin/bookings.php" class="btn btn-primary ofbms-btn-primary btn-sm rounded-pill px-3">Open bookings</a>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

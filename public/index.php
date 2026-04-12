 <?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/services.php';

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
if (str_contains($scriptName, '/public/index.php')) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    $parent = dirname(dirname($scriptName));
    $parent = $parent === '/' ? '' : $parent;
    header('Location: ' . $parent . '/index.php' . ($qs !== '' ? '?' . $qs : ''));
    exit;
}

$base = rtrim(dirname($scriptName), '/');
if ($base === '\\' || $base === '.') {
    $base = '';
}

$page = $_GET['page'] ?? 'home';
$message = '';
$error = '';

$protectedPages = ['dashboard', 'flights', 'bookings'];
if (!currentUser() && in_array($page, $protectedPages, true)) {
    header('Location: ' . $base . '/index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        if (attemptLogin(trim($_POST['email']), $_POST['password'])) {
            header('Location: ' . $base . '/index.php?page=dashboard');
            exit;
        }
        $error = 'Invalid credentials.';
    }

    if (isset($_POST['logout'])) {
        logout();
        header('Location: ' . $base . '/index.php?page=home');
        exit;
    }

    if (isset($_POST['create_flight'])) {
        requireRole(['admin']);
        $ok = createFlight([
            'flight_no' => trim($_POST['flight_no']),
            'origin' => trim($_POST['origin']),
            'destination' => trim($_POST['destination']),
            'departure_time' => $_POST['departure_time'],
            'arrival_time' => $_POST['arrival_time'],
            'aircraft' => trim($_POST['aircraft']),
            'capacity' => (int) $_POST['capacity'],
            'base_price' => (float) $_POST['base_price'],
            'status' => $_POST['status'],
        ]);
        $message = $ok ? 'Flight created.' : 'Failed to create flight.';
    }

    if (isset($_POST['update_status'])) {
        requireRole(['admin', 'agent']);
        $ok = updateFlightStatus((int) $_POST['flight_id'], $_POST['status']);
        $message = $ok ? 'Flight status updated.' : 'Failed to update status.';
    }

    if (isset($_POST['book'])) {
        requireRole(['passenger']);
        $user = currentUser();
        $pnr = createBooking((int) $user['id'], (int) $_POST['flight_id'], (int) $_POST['seats'], $_POST['payment_method']);
        if ($pnr) {
            $message = 'Booking confirmed. PNR: ' . $pnr;
        } else {
            $error = 'Booking failed: insufficient seats or invalid request.';
        }
    }

    if (isset($_POST['cancel_booking'])) {
        requireRole(['passenger', 'agent']);
        $ok = cancelBooking((int) $_POST['booking_id']);
        $message = $ok ? 'Cancellation request submitted.' : 'Cancellation failed.';
    }
}

$user = currentUser();
if ($user && $page === 'login') {
    header('Location: ' . $base . '/index.php?page=dashboard');
    exit;
}

$navHome = $base . '/index.php';
$link = static function (string $p) use ($base): string {
    return $base . '/index.php?page=' . rawurlencode($p);
};

$adminSidebar = $user && ($user['role'] === 'admin');

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wehliye Airline — Book flights</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/public/css/app.css" rel="stylesheet">
</head>
<body class="ofbms-body <?= $adminSidebar ? 'admin-mode' : 'ofbms-bg-pattern' ?>">
<?php if ($adminSidebar): ?>
<div class="admin-shell d-flex min-vh-100">
    <aside class="offcanvas offcanvas-lg offcanvas-start admin-sidebar text-white border-0 shadow-lg" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
        <div class="offcanvas-header border-bottom border-light border-opacity-10 px-3 py-3 d-lg-none">
            <h5 class="offcanvas-title text-white" id="adminSidebarLabel">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0 flex-grow-1">
            <div class="admin-sidebar-brand px-3 pt-lg-4 pb-3 px-lg-4">
                <a class="text-white text-decoration-none d-flex align-items-center gap-2 fw-bold" href="<?= htmlspecialchars($link('dashboard')) ?>" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar">
                    <span class="ofbms-brand-mark flex-shrink-0"><i class="bi bi-airplane-fill"></i></span>
                    <span class="lh-sm">Wehliye<br><span class="small fw-normal text-white-50">Admin</span></span>
                </a>
            </div>
            <nav class="admin-sidebar-nav flex-grow-1 px-2 px-lg-3">
                <a class="admin-nav-link <?= $page === 'dashboard' ? 'active' : '' ?>" href="<?= htmlspecialchars($link('dashboard')) ?>" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a class="admin-nav-link <?= $page === 'flights' ? 'active' : '' ?>" href="<?= htmlspecialchars($link('flights')) ?>" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar"><i class="bi bi-airplane"></i> Flights</a>
                <a class="admin-nav-link <?= $page === 'home' ? 'active' : '' ?>" href="<?= htmlspecialchars($navHome) ?>" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar"><i class="bi bi-house"></i> Public site</a>
            </nav>
            <div class="admin-sidebar-footer px-3 px-lg-4 py-3 mt-auto border-top border-light border-opacity-10">
                <div class="small text-white-50 text-truncate mb-2" title="<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['full_name']) ?></div>
                <form method="post" class="m-0">
                    <button class="btn btn-outline-light btn-sm w-100 rounded-pill" type="submit" name="logout">Log out</button>
                </form>
            </div>
        </div>
    </aside>
    <div class="admin-stage flex-grow-1 d-flex flex-column min-w-0 bg-light">
        <header class="admin-topbar d-flex align-items-center gap-2 d-lg-none sticky-top border-bottom bg-white shadow-sm px-3 py-2">
            <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open menu">
                <i class="bi bi-list fs-4"></i>
            </button>
            <span class="fw-bold text-dark">Wehliye Admin</span>
        </header>
        <main class="admin-main flex-grow-1 px-3 px-md-4 py-4">
<?php else: ?>
<nav class="navbar navbar-expand-lg navbar-dark ofbms-nav mb-0">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= htmlspecialchars($navHome) ?>">
            <span class="ofbms-brand-mark"><i class="bi bi-airplane-fill"></i></span>
            Wehliye Airline
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ofbmsNav" aria-controls="ofbmsNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ofbmsNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($link('dashboard')) ?>"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($link('flights')) ?>"><i class="bi bi-search me-1"></i> Flights</a></li>
                    <?php if (in_array($user['role'], ['passenger', 'agent'], true)): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($link('bookings')) ?>"><i class="bi bi-ticket-perforated me-1"></i> Bookings</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($navHome) ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base . '/signup.php') ?>">Sign up</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($link('login')) ?>">Sign in</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if ($user): ?>
                    <span class="badge rounded-pill ofbms-badge-role px-3 py-2"><?= htmlspecialchars($user['role']) ?></span>
                    <form method="post" class="m-0">
                        <button class="btn btn-sm btn-light text-primary fw-semibold" type="submit" name="logout">Log out</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-light" href="<?= htmlspecialchars($base . '/signup.php') ?>">Sign up</a>
                    <a class="btn btn-sm btn-light text-primary fw-semibold" href="<?= htmlspecialchars($link('login')) ?>">Sign in</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="ofbms-main container py-4 py-lg-5">
<?php endif; ?>
    <?php if ($message): ?><div class="alert alert-success border-0 shadow-sm rounded-3" role="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger border-0 shadow-sm rounded-3" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if ($page === 'login' && !$user): ?>
        <div class="row justify-content-center py-lg-4">
            <div class="col-md-5 col-lg-4">
                <div class="card ofbms-card ofbms-login-card shadow">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="ofbms-feature-icon mx-auto"><i class="bi bi-person-circle"></i></div>
                            <h5 class="card-title mb-1">Welcome back</h5>
                            <p class="text-muted small mb-0">Sign in to manage flights and bookings.</p>
                        </div>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Email</label>
                                <input class="form-control form-control-lg rounded-3" name="email" type="email" autocomplete="username" required placeholder="you@example.com">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Password</label>
                                <input class="form-control form-control-lg rounded-3" type="password" name="password" autocomplete="current-password" required>
                            </div>
                            <button class="btn btn-primary ofbms-btn-primary w-100 py-2 rounded-3" name="login" type="submit">Sign in</button>
                        </form>
                        <p class="text-center text-muted small mt-3 mb-0">New passenger? <a href="<?= htmlspecialchars($base . '/signup.php') ?>" class="text-decoration-none fw-semibold">Create an account</a></p>
                        <p class="text-center text-muted small mt-3 mb-0"><a href="<?= htmlspecialchars($navHome) ?>" class="text-decoration-none">← Back to home</a></p>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($page === 'dashboard'): ?>
        <?php
            requireLogin();
            $stats = dashboardStats();
            $reports = routeIncomeReport();
            $loadFactors = loadFactorReport();
        ?>
        <h1 class="ofbms-page-title h3 mb-4">Dashboard</h1>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card ofbms-card ofbms-stat-card stat-flights h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6>Active flights</h6>
                            <div class="stat-value"><?= (int) $stats['active_flights'] ?></div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-airplane"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card ofbms-card ofbms-stat-card stat-bookings h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6>Total bookings</h6>
                            <div class="stat-value"><?= (int) $stats['total_bookings'] ?></div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card ofbms-card ofbms-stat-card stat-revenue h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6>Today’s revenue</h6>
                            <div class="stat-value">$<?= number_format($stats['daily_revenue'], 2) ?></div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card ofbms-card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-bold text-uppercase text-muted mb-3">Route income</h2>
                        <div class="ofbms-table-wrap">
                            <table class="table table-hover align-middle">
                                <thead><tr><th>Route</th><th class="text-end">Income</th></tr></thead>
                                <tbody>
                                    <?php foreach ($reports as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['route']) ?></td>
                                            <td class="text-end fw-semibold">$<?= number_format((float) $r['income'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card ofbms-card h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-bold text-uppercase text-muted mb-3">Load factor</h2>
                        <div class="ofbms-table-wrap">
                            <table class="table table-hover align-middle">
                                <thead><tr><th>Flight</th><th>Sold / cap</th><th class="text-end">Load %</th></tr></thead>
                                <tbody>
                                    <?php foreach ($loadFactors as $lf): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($lf['flight_no']) ?></td>
                                            <td><?= (int) $lf['sold_seats'] ?> / <?= (int) $lf['capacity'] ?></td>
                                            <td class="text-end"><?= (float) $lf['load_factor'] ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($page === 'flights'): ?>
        <?php
            requireLogin();
            $origin = $_GET['origin'] ?? null;
            $destination = $_GET['destination'] ?? null;
            $date = $_GET['date'] ?? null;
            $flights = listFlights($origin ?: null, $destination ?: null, $date ?: null);
        ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="ofbms-page-title h3 mb-0">Flights</h1>
            <a href="<?= htmlspecialchars($link('dashboard')) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Dashboard</a>
        </div>
        <div class="ofbms-search-bar">
            <form class="row g-2 align-items-end" method="get">
                <input type="hidden" name="page" value="flights">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">From</label>
                    <input class="form-control rounded-3" name="origin" placeholder="Origin city" value="<?= htmlspecialchars((string) ($origin ?? '')) ?>">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">To</label>
                    <input class="form-control rounded-3" name="destination" placeholder="Destination" value="<?= htmlspecialchars((string) ($destination ?? '')) ?>">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Date</label>
                    <input class="form-control rounded-3" name="date" type="date" value="<?= htmlspecialchars((string) ($date ?? '')) ?>">
                </div>
                <div class="col-sm-6 col-md-3">
                    <button class="btn btn-primary ofbms-btn-primary w-100 rounded-3" type="submit">Search</button>
                </div>
            </form>
        </div>
        <div class="ofbms-table-wrap mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Flight</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Aircraft</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Seats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $f): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($f['flight_no']) ?></td>
                                <td><?= htmlspecialchars($f['origin']) ?> <i class="bi bi-arrow-right text-muted mx-1"></i> <?= htmlspecialchars($f['destination']) ?></td>
                                <td class="text-nowrap small"><?= htmlspecialchars($f['departure_time']) ?></td>
                                <td><?= htmlspecialchars($f['aircraft']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-dark ofbms-badge-status"><?= htmlspecialchars($f['status']) ?></span></td>
                                <td class="fw-semibold">$<?= number_format((float) $f['base_price'], 2) ?></td>
                                <td><?= availableSeats((int) $f['id']) ?></td>
                                <td style="min-width: 220px;">
                                    <?php if ($user['role'] === 'passenger'): ?>
                                        <form class="d-flex flex-wrap gap-1 align-items-center" method="post">
                                            <input type="hidden" name="flight_id" value="<?= (int) $f['id'] ?>">
                                            <input class="form-control form-control-sm rounded-2" name="seats" type="number" min="1" value="1" style="width:64px" title="Seats">
                                            <select class="form-select form-select-sm rounded-2" name="payment_method" style="width:auto;min-width:110px">
                                                <option>CreditCard</option>
                                                <option>PayPal</option>
                                                <option>MobileMoney</option>
                                            </select>
                                            <button class="btn btn-success btn-sm rounded-2" name="book" type="submit">Book</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (in_array($user['role'], ['admin', 'agent'], true)): ?>
                                        <form class="d-flex flex-wrap gap-1 align-items-center mt-1" method="post">
                                            <input type="hidden" name="flight_id" value="<?= (int) $f['id'] ?>">
                                            <select class="form-select form-select-sm rounded-2" name="status" style="width:auto;min-width:120px">
                                                <option>Scheduled</option>
                                                <option>Boarding</option>
                                                <option>Delayed</option>
                                                <option>Cancelled</option>
                                                <option>Completed</option>
                                            </select>
                                            <button class="btn btn-warning btn-sm rounded-2 text-dark" name="update_status" type="submit">Update</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($user['role'] === 'admin'): ?>
            <h2 class="h5 ofbms-page-title mb-3">Add flight</h2>
            <div class="card ofbms-card p-3 p-md-4">
                <form class="row g-3" method="post">
                    <div class="col-6 col-md-2"><label class="form-label small">Flight no.</label><input class="form-control rounded-3" name="flight_no" placeholder="OF999" required></div>
                    <div class="col-6 col-md-2"><label class="form-label small">Origin</label><input class="form-control rounded-3" name="origin" required></div>
                    <div class="col-6 col-md-2"><label class="form-label small">Destination</label><input class="form-control rounded-3" name="destination" required></div>
                    <div class="col-md-2"><label class="form-label small">Departure</label><input class="form-control rounded-3" type="datetime-local" name="departure_time" required></div>
                    <div class="col-md-2"><label class="form-label small">Arrival</label><input class="form-control rounded-3" type="datetime-local" name="arrival_time" required></div>
                    <div class="col-md-2"><label class="form-label small">Aircraft</label><input class="form-control rounded-3" name="aircraft" required></div>
                    <div class="col-6 col-md-2"><label class="form-label small">Capacity</label><input class="form-control rounded-3" type="number" name="capacity" required min="1"></div>
                    <div class="col-6 col-md-2"><label class="form-label small">Base price</label><input class="form-control rounded-3" type="number" step="0.01" name="base_price" required></div>
                    <div class="col-md-2"><label class="form-label small">Status</label><select class="form-select rounded-3" name="status"><option>Scheduled</option><option>Boarding</option><option>Delayed</option><option>Cancelled</option><option>Completed</option></select></div>
                    <div class="col-12 col-md-2 d-flex align-items-end"><button class="btn btn-primary ofbms-btn-primary w-100 rounded-3" name="create_flight" type="submit">Save</button></div>
                </form>
            </div>
        <?php endif; ?>
    <?php elseif ($page === 'bookings'): ?>
        <?php
            requireRole(['passenger', 'agent']);
            $history = bookingHistory((int) $user['id']);
        ?>
        <h1 class="ofbms-page-title h3 mb-4">Bookings &amp; e-tickets</h1>
        <div class="ofbms-table-wrap">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>PNR</th>
                            <th>Flight</th>
                            <th>Seats</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Refund</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $b): ?>
                            <tr>
                                <td class="font-monospace fw-semibold"><?= htmlspecialchars($b['pnr']) ?></td>
                                <td>
                                    <span class="d-block fw-medium"><?= htmlspecialchars($b['flight_no']) ?></span>
                                    <span class="small text-muted"><?= htmlspecialchars($b['origin']) ?> → <?= htmlspecialchars($b['destination']) ?></span>
                                </td>
                                <td><?= (int) $b['seats'] ?></td>
                                <td class="fw-semibold">$<?= number_format((float) $b['total_amount'], 2) ?></td>
                                <td><span class="badge bg-secondary-subtle text-dark ofbms-badge-status"><?= htmlspecialchars($b['status']) ?></span></td>
                                <td><?= htmlspecialchars($b['refund_status']) ?></td>
                                <td class="text-end">
                                    <?php if ($b['status'] !== 'Cancelled'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm rounded-pill" name="cancel_booking" type="submit">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="home-landing">
            <section class="home-hero-wrap mb-5">
                <div class="home-hero row g-4 g-xl-5 align-items-center">
                    <div class="col-lg-6 order-2 order-lg-1">
                        <p class="home-hero-eyebrow mb-2"><i class="bi bi-patch-check-fill me-1"></i> Premium regional service</p>
                        <h1 class="home-hero-title mb-3">The sky feels closer with <span class="home-gradient-text">Wehliye</span></h1>
                        <p class="home-hero-lead mb-4">Book in minutes, fly with confidence — transparent fares, real-time schedules, and care at every step.</p>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php if ($user): ?>
                                <a class="btn btn-light btn-lg rounded-pill px-4 fw-semibold shadow home-hero-cta-primary" href="<?= htmlspecialchars($link('flights')) ?>"><i class="bi bi-search me-2"></i>Search flights</a>
                                <a class="btn btn-outline-light btn-lg rounded-pill px-4 home-hero-cta-ghost" href="<?= htmlspecialchars($link('dashboard')) ?>">Dashboard</a>
                            <?php else: ?>
                                <a class="btn btn-light btn-lg rounded-pill px-4 fw-semibold shadow home-hero-cta-primary" href="<?= htmlspecialchars($link('login')) ?>"><i class="bi bi-box-arrow-in-right me-2"></i>Sign in to book</a>
                                <a class="btn btn-outline-light btn-lg rounded-pill px-4 home-hero-cta-ghost" href="<?= htmlspecialchars($base . '/signup.php') ?>"><i class="bi bi-person-plus me-2"></i>Create account</a>
                                <a class="btn btn-outline-light btn-lg rounded-pill px-4 home-hero-cta-ghost d-none d-sm-inline-flex" href="#home-features">Explore</a>
                            <?php endif; ?>
                        </div>
                        <div class="home-hero-metrics row g-3 text-center text-lg-start">
                            <div class="col-4">
                                <div class="home-metric-val">40+</div>
                                <div class="home-metric-label">Routes</div>
                            </div>
                            <div class="col-4">
                                <div class="home-metric-val">4.9</div>
                                <div class="home-metric-label">Guest rating</div>
                            </div>
                            <div class="col-4">
                                <div class="home-metric-val">24/7</div>
                                <div class="home-metric-label">Support</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 order-1 order-lg-2">
                        <div class="home-hero-visual">
                            <div class="home-orbit home-orbit-1"></div>
                            <div class="home-orbit home-orbit-2"></div>
                            <div class="home-ticket-card">
                                <div class="home-ticket-top">
                                    <span class="home-ticket-airline">Wehliye Airline</span>
                                    <span class="home-ticket-class">Economy</span>
                                </div>
                                <div class="home-ticket-route">
                                    <div>
                                        <span class="home-ticket-code">NBO</span>
                                        <span class="home-ticket-city">Nairobi</span>
                                    </div>
                                    <div class="home-ticket-plane"><i class="bi bi-airplane"></i></div>
                                    <div class="text-end">
                                        <span class="home-ticket-code">EBB</span>
                                        <span class="home-ticket-city">Kampala</span>
                                    </div>
                                </div>
                                <div class="home-ticket-meta">
                                    <div><span class="text-white-50 small">Flight</span><br><strong>WH · 101</strong></div>
                                    <div><span class="text-white-50 small">Departs</span><br><strong>08:00</strong></div>
                                    <div><span class="text-white-50 small">PNR</span><br><strong class="font-monospace">7K2Q9F</strong></div>
                                </div>
                            </div>
                            <div class="home-float-badge home-float-1"><i class="bi bi-lightning-charge-fill text-warning"></i> Fast check-in</div>
                            <div class="home-float-badge home-float-2"><i class="bi bi-shield-lock text-info"></i> Secure pay</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="home-trust-strip mb-5" aria-label="Highlights">
                <div class="row g-3 g-md-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="home-trust-item"><i class="bi bi-wifi"></i><span>Inflight-ready booking</span></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="home-trust-item"><i class="bi bi-clock-history"></i><span>Live schedule updates</span></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="home-trust-item"><i class="bi bi-credit-card-2-front"></i><span>Cards, PayPal &amp; mobile money</span></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="home-trust-item"><i class="bi bi-recycle"></i><span>Easy changes &amp; refunds</span></div>
                    </div>
                </div>
            </section>

            <section id="home-features" class="mb-5">
                <div class="text-center mb-4 mb-md-5">
                    <h2 class="home-section-title">Everything you need in one place</h2>
                    <p class="home-section-sub text-muted mx-auto mb-0">Passengers, agents, and admins each get tools that stay fast and clear.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-xl-4">
                        <div class="home-feature-card h-100">
                            <div class="home-feature-icon home-fi-1"><i class="bi bi-globe2"></i></div>
                            <h3 class="home-feature-title">Routes &amp; aircraft</h3>
                            <p class="home-feature-text">Search by city and date, compare cabin options, and see aircraft capacity at a glance.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="home-feature-card h-100">
                            <div class="home-feature-icon home-fi-2"><i class="bi bi-ticket-perforated"></i></div>
                            <h3 class="home-feature-title">PNR &amp; e-tickets</h3>
                            <p class="home-feature-text">Unique booking records, digital tickets, and a clear history for every trip.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="home-feature-card h-100">
                            <div class="home-feature-icon home-fi-3"><i class="bi bi-shield-check"></i></div>
                            <h3 class="home-feature-title">Trusted payments</h3>
                            <p class="home-feature-text">Multiple payment rails with status tracking and structured refund handling.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="home-feature-card h-100">
                            <div class="home-feature-icon home-fi-4"><i class="bi bi-people"></i></div>
                            <h3 class="home-feature-title">Agents &amp; manifests</h3>
                            <p class="home-feature-text">Operations staff update flight status and support passengers in real time.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="home-feature-card h-100">
                            <div class="home-feature-icon home-fi-5"><i class="bi bi-graph-up-arrow"></i></div>
                            <h3 class="home-feature-title">Revenue &amp; load factor</h3>
                            <p class="home-feature-text">Dashboards for income by route and seat utilization to guide pricing.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="home-feature-card h-100">
                            <div class="home-feature-icon home-fi-6"><i class="bi bi-bell"></i></div>
                            <h3 class="home-feature-title">Stay informed</h3>
                            <p class="home-feature-text">Built for reminders and alerts as you connect email or SMS in production.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="home-steps mb-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-4">
                        <h2 class="home-section-title mb-2">How it works</h2>
                        <p class="text-muted mb-0">Three simple steps from search to boarding pass.</p>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="home-step">
                                    <span class="home-step-num">1</span>
                                    <h4 class="home-step-title">Search &amp; pick</h4>
                                    <p class="home-step-text">Enter origin, destination, and travel date.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="home-step">
                                    <span class="home-step-num">2</span>
                                    <h4 class="home-step-title">Pay securely</h4>
                                    <p class="home-step-text">Choose seats and complete payment in one flow.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="home-step">
                                    <span class="home-step-num">3</span>
                                    <h4 class="home-step-title">Fly Wehliye</h4>
                                    <p class="home-step-text">Get your PNR, manage trips, and check updates anytime.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="home-cta-band text-center">
                <div class="home-cta-inner">
                    <h2 class="home-cta-title mb-2">Ready for your next journey?</h2>
                    <p class="home-cta-sub mb-4">Sign in to search live flights or open your dashboard if you already have an account.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <?php if ($user): ?>
                            <a class="btn btn-light btn-lg rounded-pill px-4 fw-semibold" href="<?= htmlspecialchars($link('flights')) ?>">Go to flights</a>
                        <?php else: ?>
                            <a class="btn btn-light btn-lg rounded-pill px-4 fw-semibold" href="<?= htmlspecialchars($link('login')) ?>">Sign in now</a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    <?php endif; ?>
<?php if ($adminSidebar): ?>
        </main>
    </div>
</div>
<?php else: ?>
</main>

<footer class="ofbms-footer">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><strong class="text-dark">Wehliye Airline</strong> — Flight booking &amp; operations</span>
        <span><?= date('Y') ?> · Demo project</span>
    </div>
</footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

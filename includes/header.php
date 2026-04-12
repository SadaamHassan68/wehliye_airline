<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $base */
/** @var array|null $user */
/** @var bool $adminSidebar */
/** @var string $activeNav */

$user = $user ?? User::current();
$adminSidebar = $adminSidebar ?? false;
$activeNav = $activeNav ?? '';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/app.css?v=1.9" rel="stylesheet">
</head>
<?php
$isHome = str_contains($pageTitle, 'Book flights');
?>
<body class="ofbms-body <?= $adminSidebar ? 'admin-mode' : ($isHome ? 'ofbms-bg-pattern ofbms-home' : 'ofbms-bg-pattern') ?>">
<?php if ($adminSidebar): ?>
<div class="admin-shell d-flex min-vh-100 overflow-hidden">
    <!-- Desktop Sidebar (Pinned) -->
    <aside class="admin-aside d-none d-lg-flex flex-column flex-shrink-0">
        <?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
    </aside>
    
    <!-- Mobile Sidebar (Offcanvas) -->
    <div class="offcanvas offcanvas-start d-lg-none border-0" tabindex="-1" id="adminSidebarMobile" style="background: #0f172a; width: 280px;">
        <div class="offcanvas-body p-0">
             <?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
        </div>
    </div>
    
    <div class="admin-stage flex-grow-1 d-flex flex-column min-w-0" style="height: 100vh; overflow-y: auto;">
        <header class="admin-topbar d-flex align-items-center justify-content-between sticky-top px-3 py-2" style="height: 64px; z-index: 999;">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary border-0 p-1" type="button" id="adminSidebarToggle">
                    <i class="bi bi-layout-sidebar fs-4"></i>
                </button>
                <button class="btn btn-outline-secondary border-0 d-lg-none p-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarMobile">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb  mb-0 small fw-bold text-uppercase" style="letter-spacing: 1.5px; font-size: 0.65rem;">
                        <li class="breadcrumb-item"><a href="<?= $base ?>/admin/dashboard.php" class="text-decoration-none">Admin Console</a></li>
                        <?php 
                            $cleanTitle = str_replace([' — Wehliye Admin', ' — Wehliye Airline', 'Admin '], '', $pageTitle);
                        ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($cleanTitle) ?></li>
                    </ol>
                </nav>
            </div>
            <div class="admin-user-nav d-flex align-items-center gap-2 gap-md-4">
                <a href="<?= htmlspecialchars($base) ?>/index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold small d-none d-md-flex align-items-center gap-2" style="font-size: 0.75rem;">
                    <i class="bi bi-eye"></i> View Site
                </a>
                <div class="vr d-none d-md-block" style="height: 24px; opacity: 0.1;"></div>
                
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <div class="text-end d-none d-sm-block">
                            <div class="small fw-bold text-white lh-1"><?= htmlspecialchars($user['full_name']) ?></div>
                            <div class="text-white-50" style="font-size: 0.65rem; font-weight: 500;">System Administrator</div>
                        </div>
                        <div class="admin-pfp-mini shadow-sm" style="border: 2px solid #fff; box-shadow: 0 0 0 1px #e2e8f0;">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="background: #1e293b;">
                        <li>
                            <form action="<?= htmlspecialchars($base) ?>/logout.php" method="post" class="m-0 p-0">
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2" style="background: transparent;">
                                    <i class="bi bi-box-arrow-right"></i> <span class="fw-semibold">Logout</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <script>
        (function() {
            const shell = document.querySelector('.admin-shell');
            if (localStorage.getItem('adminSidebarCollapsed') === 'true') {
                shell.classList.add('sidebar-collapsed');
            }
        })();
        document.addEventListener('DOMContentLoaded', function() {
            const shell = document.querySelector('.admin-shell');
            const toggle = document.getElementById('adminSidebarToggle');
            if (!shell || !toggle) return;
            toggle.addEventListener('click', () => {
                shell.classList.toggle('sidebar-collapsed');
                localStorage.setItem('adminSidebarCollapsed', shell.classList.contains('sidebar-collapsed'));
            });
        });
        </script>

        <!-- Scrolling Body Content -->
        <main class="admin-main flex-grow-1 px-4 py-4" style="overflow-y: auto;">
<?php else: ?>
<nav class="navbar navbar-expand-lg navbar-dark ofbms-nav sticky-top mb-0">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= htmlspecialchars($base) ?>/index.php">
            <span class="ofbms-brand-mark"><i class="bi bi-airplane-fill"></i></span>
            Wehliye Airline
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ofbmsNav" aria-controls="ofbmsNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ofbmsNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/index.php"><i class="bi bi-house me-1"></i> Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Admin Dashboard</a></li>
                    <?php elseif ($user['role'] === 'agent'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/staff/manifest.php"><i class="bi bi-people me-1"></i> Manifest</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/index.php"><i class="bi bi-search me-1"></i> Flight search</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/index.php"><i class="bi bi-search me-1"></i> Flights</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/bookings.php"><i class="bi bi-ticket-perforated me-1"></i> Bookings</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/signup.php">Sign up</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($base) ?>/login.php">Sign in</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if ($user): ?>
                    <span class="badge rounded-pill ofbms-badge-role px-3 py-2"><?= htmlspecialchars($user['role']) ?></span>
                    <form method="post" action="<?= htmlspecialchars($base) ?>/logout.php" class="m-0">
                        <button class="btn btn-sm btn-light text-primary fw-semibold" type="submit">Log out</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-light" href="<?= htmlspecialchars($base) ?>/signup.php">Sign up</a>
                    <a class="btn btn-sm btn-light text-primary fw-semibold" href="<?= htmlspecialchars($base) ?>/login.php">Sign in</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="ofbms-main <?= $isHome ? '' : 'container py-4 py-lg-5' ?>">
<?php endif; ?>

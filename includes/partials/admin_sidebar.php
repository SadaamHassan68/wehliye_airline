<?php

declare(strict_types=1);

/** @var string $base */
/** @var array|null $user */
/** @var string $activeNav */

?>
<div class="admin-sidebar d-flex flex-column shadow-lg bg-dark">
    <div class="admin-sidebar-header">
        <a class="brand-link" href="<?= htmlspecialchars($base) ?>/admin/dashboard.php">
            <div class="brand-icon-box">
                <i class="bi bi-airplane-fill"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">Wehilyee</span>
                <span class="brand-tagline">Control Center</span>
            </div>
        </a>
    </div>

    <div class="sidebar-scroll flex-grow-1">
        <nav class="admin-sidebar-nav px-3">
            <div class="nav-section-label">Main Menu</div>
            <a class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/admin/dashboard.php">
                <i class="bi bi-grid-1x2-fill"></i> 
                <span>Dashboard</span>
            </a>
            <a class="nav-item <?= $activeNav === 'reports' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/admin/reports.php">
                <i class="bi bi-pie-chart-fill"></i>
                <span>Analytics</span>
            </a>
            
            <div class="nav-section-label">Operations</div>
            <a class="nav-item <?= $activeNav === 'airports' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/admin/airports.php">
                <i class="bi bi-geo-alt-fill"></i>
                <span>Airports</span>
            </a>
            <a class="nav-item <?= $activeNav === 'flights' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/admin/flights.php">
                <i class="bi bi-bezier2"></i>
                <span>Routes</span>
            </a>
            <a class="nav-item <?= $activeNav === 'schedules' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/admin/schedules.php">
                <i class="bi bi-calendar3"></i>
                <span>Schedules</span>
            </a>
            <a class="nav-item <?= $activeNav === 'bookings' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/admin/bookings.php">
                <i class="bi bi-ticket-perforated-fill"></i>
                <span>Bookings</span>
            </a>
            
            <div class="nav-section-label">Links</div>
            <a class="nav-item" href="<?= htmlspecialchars($base) ?>/index.php" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Visit Site</span>
            </a>
        </nav>
    </div>

    <div class="admin-sidebar-footer">
        <div class="user-card mb-3">
            <div class="admin-pfp-mini">
                <?= strtoupper(substr($user['full_name'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name small text-white"><?= htmlspecialchars($user['full_name'] ?? 'Admin') ?></div>
                <div class="user-role" style="font-size: 0.65rem; color: #94a3b8;">Administrator</div>
            </div>
        </div>
        <form action="<?= htmlspecialchars($base) ?>/logout.php" method="post">
            <button type="submit" class="btn btn-logout d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-left"></i> <span>Logout</span>
            </button>
        </form>
    </div>
</div>

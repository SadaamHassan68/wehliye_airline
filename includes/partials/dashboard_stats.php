<?php

declare(strict_types=1);

/** @var array $stats */
/** @var array $reports */
/** @var array $loadFactors */
/** @var bool $adminSidebar */
/** @var string $base */

?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="ofbms-page-title h3 mb-1">Administrative Overview</h1>
        <p class="text-muted small mb-0">Monitor airline performance, route load factors, and revenue trends.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= htmlspecialchars($base) ?>/admin/reports.php" class="btn btn-primary ofbms-btn-primary btn-sm rounded-pill px-3 shadow-sm">
            <i class="bi bi-bar-chart-fill me-1"></i> Full Analytics
        </a>
    </div>
</div>

<<div class="row g-3 mb-4">
    <!-- Card 1: Active Flights — Electric Blue -->
    <div class="col-6 col-md-3">
        <div class="card admin-card-pro h-100" style="border-top: 3px solid #3b82f6; background: linear-gradient(160deg, #eff6ff 0%, #ffffff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="admin-stat-icon-wrap" style="background: rgba(59,130,246,0.12); color: #2563eb;">
                        <i class="bi bi-airplane"></i>
                    </div>
                    <span class="small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.65rem; color: #3b82f6;">Active flights</span>
                </div>
                <div class="h3 fw-bold mb-0" style="color: #1e3a8a;"><?= (int) $stats['active_flights'] ?></div>
                <div class="small mt-1" style="color: #64748b;">Scheduled assets</div>
            </div>
        </div>
    </div>
    <!-- Card 2: Awaiting Approval — Amber -->
    <div class="col-6 col-md-3">
        <div class="card admin-card-pro h-100" style="border-top: 3px solid #f59e0b; background: linear-gradient(160deg, #fffbeb 0%, #ffffff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="admin-stat-icon-wrap" style="background: rgba(245,158,11,0.12); color: #d97706;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <span class="small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.65rem; color: #d97706;">Awaiting Approval</span>
                </div>
                <div class="h3 fw-bold mb-0" style="color: #92400e;"><?= (int) ($stats['pending_approval'] ?? 0) ?></div>
                <div class="small mt-1" style="color: #64748b;">Pending payments</div>
            </div>
        </div>
    </div>
    <!-- Card 3: Total Bookings — Emerald -->
    <div class="col-6 col-md-3">
        <div class="card admin-card-pro h-100" style="border-top: 3px solid #10b981; background: linear-gradient(160deg, #ecfdf5 0%, #ffffff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="admin-stat-icon-wrap" style="background: rgba(16,185,129,0.12); color: #059669;">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                    <span class="small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.65rem; color: #059669;">Total Bookings</span>
                </div>
                <div class="h3 fw-bold mb-0" style="color: #064e3b;"><?= (int) $stats['total_bookings'] ?></div>
                <div class="small mt-1" style="color: #64748b;">Lifetime volume</div>
            </div>
        </div>
    </div>
    <!-- Card 4: Today's Revenue — Violet -->
    <div class="col-6 col-md-3">
        <div class="card admin-card-pro h-100" style="border-top: 3px solid #7c3aed; background: linear-gradient(160deg, #f5f3ff 0%, #ffffff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="admin-stat-icon-wrap" style="background: rgba(124,58,237,0.12); color: #7c3aed;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <span class="small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.65rem; color: #7c3aed;">Today's Revenue</span>
                </div>
                <div class="h3 fw-bold mb-0" style="color: #4c1d95;">$<?= number_format($stats['daily_revenue'], 2) ?></div>
                <div class="small mt-1 fw-semibold" style="color: #059669;"><i class="bi bi-shield-check"></i> Paid bookings</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card admin-card-pro">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h6 fw-bold text-uppercase text-muted mb-1">Recent Activity</h2>
                        <p class="small text-muted mb-0">Revenue (line) vs Volume (bars) over 14 days</p>
                    </div>
                </div>
                <div class="ofbms-chart-wrap" style="height: 300px;">
                    <canvas id="ofbmsChartCombo"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card admin-card-pro h-100">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-3">Top Route Income</h2>
                <div class="ofbms-table-wrap">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Route</th><th class="text-end">Income</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($reports, 0, 5) as $r): ?>
                                <tr>
                                    <td class="small fw-semibold"><?= htmlspecialchars($r['route']) ?></td>
                                    <td class="text-end fw-bold text-primary" style="font-size: 0.85rem;">$<?= number_format((float) $r['income'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="<?= htmlspecialchars($base) ?>/admin/reports.php" class="btn btn-light w-100 btn-sm mt-3 fw-bold small rounded-pill">View all routes</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card admin-card-pro">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-3">Load Factor Awareness</h2>
                <div class="row g-3">
                    <?php foreach (array_slice($loadFactors, 0, 4) as $lf): ?>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 border bg-light bg-opacity-50">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold small"><?= htmlspecialchars($lf['flight_no']) ?></span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary"><?= (float) $lf['load_factor'] ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= (float) $lf['load_factor'] ?>%"></div>
                                </div>
                                <div class="mt-2" style="font-size: 0.7rem; color: #64748b;">
                                    <?= (int) $lf['sold_seats'] ?> of <?= (int) $lf['capacity'] ?> seats filled
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

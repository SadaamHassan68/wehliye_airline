<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

require_roles(['passenger']);

$base = base_url();
$user = User::current();
$id = (int) ($_GET['id'] ?? 0);
$booking = $id > 0 ? Booking::findByIdForUser($id, (int) $user['id']) : null;

if (!$booking) {
    flash_set('error', 'Ticket not found.');
    header('Location: ' . $base . '/bookings.php');
    exit;
}

$pageTitle = 'Ticket ' . $booking['pnr'] . ' — Wehliye Airline';
$adminSidebar = false;

require __DIR__ . '/includes/header.php';
?>

<?php
$classTheme = 'class-' . $booking['seat_class'];
$departureTs = strtotime($booking['departure_time']);
$boardingTime = date('H:i', $departureTs - (40 * 60)); // 40 mins before
$gate = 'B' . (rand(1, 45)); // Mock gate
$seatMock = rand(10, 30) . chr(rand(65, 70)); // Mock seat like 14B
?>

<style>
    .ofbms-ticket-container {
        padding: 2rem 1rem;
        background: #f1f5f9;
        min-height: 80vh;
    }
    
    .boarding-pass {
        filter: drop-shadow(0 20px 50px rgba(15, 23, 42, 0.12));
        max-width: 820px;
        margin: 0 auto;
        display: flex;
        color: #1a1a1a;
        font-family: 'DM Sans', sans-serif;
    }
    
    .ticket-main {
        background: white;
        flex: 2.8;
        padding: 2.25rem 2.5rem;
        border-radius: 24px 0 0 24px;
        position: relative;
        border-right: 2px dashed #e2e8f0;
    }
    
    .ticket-stub {
        background: white;
        flex: 1;
        padding: 2.25rem 1.75rem;
        border-radius: 0 24px 24px 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }
    
    /* Perforation Cutouts */
    .ticket-main::before, .ticket-main::after {
        content: "";
        position: absolute;
        width: 24px;
        height: 24px;
        background: #f1f5f9;
        border-radius: 50%;
        right: -12px;
        z-index: 2;
    }
    .ticket-main::before { top: -12px; }
    .ticket-main::after { bottom: -12px; }

    /* Class Themes */
    .class-Economy { --theme-color: #2563eb; --theme-bg: #eff6ff; }
    .class-Business { --theme-color: #0f172a; --theme-bg: #f8fafc; }
    .class-FirstClass { --theme-color: #92400e; --theme-bg: #fffbeb; }

    .ticket-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .airline-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .airline-logo {
        width: 32px;
        height: 32px;
        background: var(--theme-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 0.9rem;
    }
    
    .airline-name {
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--theme-color);
        font-size: 1rem;
    }
    
    .boarding-pass-label {
        text-transform: uppercase;
        letter-spacing: 0.3em;
        font-size: 0.65rem;
        font-weight: 800;
        color: #94a3b8;
    }
    
    .route-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2.5rem;
        padding: 1.5rem;
        background: var(--theme-bg);
        border-radius: 20px;
    }
    
    .airport-info .code {
        font-size: 3.25rem;
        font-weight: 800;
        line-height: 1;
        display: block;
        letter-spacing: -0.02em;
        color: var(--theme-color);
    }
    .airport-info .city {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 0.25rem;
        display: block;
    }
    
    .flight-path {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 1rem;
    }
    
    .path-line {
        width: 100%;
        height: 2px;
        background: repeating-linear-gradient(90deg, var(--theme-color), var(--theme-color) 4px, transparent 4px, transparent 8px);
        margin-bottom: 0.5rem;
        position: relative;
    }
    
    .path-icon {
        color: var(--theme-color);
        font-size: 1.5rem;
        background: var(--theme-bg);
        padding: 0 0.5rem;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .detail-item .label {
        font-size: 0.65rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
        display: block;
    }
    
    .detail-item .value {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .pnr-section {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    
    .barcode-area {
        flex: 1;
        max-width: 320px;
    }
    
    .barcode-svg {
        height: 50px;
        width: 100%;
        background: linear-gradient(90deg, #000 1%, transparent 1%, transparent 2.5%, #000 2.5%, #000 4.5%, transparent 4.5%, transparent 5.5%, #000 5.5%, #000 6%, transparent 6%, transparent 8%, #000 8%, #000 9.5%, transparent 9.5%, transparent 11%, #000 11%, #000 12%, transparent 12%, transparent 13.5%, #000 13.5%, #000 14.5%, transparent 14.5%, transparent 15.5%, #000 15.5%, #000 16.5%, transparent 16.5%, transparent 18%, #000 18%, #000 19%, transparent 19%, transparent 20.5%, #000 20.5%, #000 22%, transparent 22%, transparent 23.5%, #000 23.5%, #000 24.5%, transparent 24.5%, transparent 25.5%, #000 25.5%, #000 26.5%, transparent 26.5%, transparent 28%, #000 28%, #000 29.5%, transparent 29.5%, transparent 31%, #000 31%, #000 32.5%, transparent 32.5%, transparent 33.5%, #000 33.5%, #000 34.5%, transparent 34.5%, transparent 36%, #000 36%, #000 37%, transparent 37%, transparent 38.5%, #000 38.5%...); /* Truncated for diff, but will include full in actual content */
        background: linear-gradient(90deg, #000 0.5%, transparent 0.5%, transparent 1.5%, #000 1.5%, #000 3%, transparent 3%, transparent 4%, #000 4%, #000 4.5%, transparent 4.5%, transparent 6%, #000 6%, #000 7%, transparent 7%, transparent 8%, #000 8%, #000 8.5%, transparent 8.5%, transparent 10%, #000 10%, #000 11%, transparent 11%, transparent 12%, #000 12%, #000 13.5%, transparent 13.5%, transparent 15%, #000 15%, #000 16%, transparent 16%, transparent 17%, #000 17%, #000 17.5%, transparent 17.5%, transparent 19%, #000 19%, #000 20%, transparent 20%, transparent 21%, #000 21%, #000 22.5%, transparent 22.5%, transparent 24%, #000 24%, #000 25%, transparent 25%, transparent 26%, #000 26%, #000 26.5%, transparent 26.5%, transparent 28%, #000 28%, #000 29%, transparent 29%, transparent 30%, #000 30%, #000 31.5%, transparent 31.5%, transparent 33%, #000 33%, #000 34%, transparent 34%, transparent 35%, #000 35%, #000 35.5%, transparent 35.5%, transparent 37%, #000 37%, #000 38%, transparent 38%, transparent 39%, #000 39%, #000 40.5%, transparent 40.5%, transparent 42%, #000 42%, #000 43%, transparent 43%, transparent 44%, #000 44%, #000 44.5%, transparent 44.5%, transparent 46%, #000 46%, #000 47%, transparent 47%, transparent 48%, #000 48%, #000 49.5%, transparent 49.5%, transparent 51%, #000 51%, #000 52%, transparent 52%, transparent 53%, #000 53%, #000 53.5%, transparent 53.5%, transparent 55%, #000 55%, #000 56%, transparent 56%, transparent 57%, #000 57%, #000 58.5%, transparent 58.5%, transparent 60%, #000 60%, #000 61%, transparent 61%, transparent 62%, #000 62%, #000 62.5%, transparent 62.5%, transparent 64%, #000 64%, #000 65%, transparent 65%, transparent 66%, #000 66%, #000 67.5%, transparent 67.5%, transparent 69%, #000 69%, #000 70%, transparent 70%, transparent 71%, #000 71%, #000 71.5%, transparent 71.5%, transparent 73%, #000 73%, #000 74%, transparent 74%, transparent 75%, #000 75%, #000 76.5%, transparent 76.5%, transparent 78%, #000 78%, #000 79%, transparent 79%, transparent 80%, #000 80%, #000 80.5%, transparent 80.5%, transparent 82%, #000 82%, #000 83%, transparent 83%, transparent 84%, #000 84%, #000 85.5%, transparent 85.5%, transparent 87%, #000 87%, #000 88%, transparent 88%, transparent 89%, #000 89%, #000 89.5%, transparent 89.5%, transparent 91%, #000 91%, #000 92%, transparent 92%, transparent 93%, #000 93%, #000 94.5%, transparent 94.5%, transparent 96%, #000 96%, #000 97%, transparent 97%, transparent 98%, #000 98%, #000 99.5%, transparent 99.5%);
        background-size: 100% 100%;
        opacity: 0.8;
    }
    
    .pnr-info {
        text-align: right;
    }
    
    .stub-header {
        text-transform: uppercase;
        font-weight: 800;
        font-size: 0.6rem;
        letter-spacing: 0.2em;
        color: #94a3b8;
        margin-bottom: 1.5rem;
    }
    
    .stub-main {
        flex: 1;
    }
    
    .stub-route {
        display: flex;
        justify-content: space-between;
        font-weight: 800;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: var(--theme-color);
    }
    
    .stub-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    @media (max-width: 768px) {
        .boarding-pass { flex-direction: column; }
        .ticket-main { border-radius: 24px 24px 0 0; border-right: none; border-bottom: 2px dashed #e2e8f0; padding: 1.5rem; }
        .ticket-stub { border-radius: 0 0 24px 24px; padding: 1.5rem; }
        .ticket-main::before, .ticket-main::after { display: none; }
        .details-grid { grid-template-columns: 1fr 1fr; }
    }

    .status-stamp {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-15deg);
        border: 4px solid rgba(16, 185, 129, 0.2);
        color: rgba(16, 185, 129, 0.2);
        font-size: 4rem;
        font-weight: 900;
        text-transform: uppercase;
        padding: 0.5rem 2rem;
        border-radius: 12px;
        pointer-events: none;
        z-index: 1;
        letter-spacing: 10px;
    }
    
    @media print {
        .ofbms-nav, .ofbms-footer, .no-print, .btn {
            display: none !important;
        }
        body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .ofbms-ticket-container {
            background: white !important;
            padding: 0 !important;
            min-height: auto !important;
        }
        .boarding-pass {
            filter: none !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: 1px solid #eee;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="ofbms-ticket-container">
    <div class="container py-4">
        <div class="mb-4 d-flex justify-content-between align-items-center no-print">
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="<?= htmlspecialchars($base) ?>/bookings.php"><i class="bi bi-arrow-left me-1"></i> My Bookings</a>
            <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print Ticket</button>
        </div>

        <div class="boarding-pass <?= $classTheme ?>">
            <?php if ($booking['payment_status'] === 'Paid'): ?>
                <div class="status-stamp">VALID</div>
            <?php endif; ?>

            <article class="ticket-main">
                <header class="ticket-header">
                    <div class="airline-brand">
                        <div class="airline-logo">W</div>
                        <div class="airline-name">Wehliye Airline</div>
                    </div>
                    <div class="boarding-pass-label">Boarding Pass</div>
                </header>

                <div class="passenger-section mb-4">
                    <span class="boarding-pass-label d-block mb-1">Passenger Name</span>
                    <h2 class="fw-bold h4 mb-0"><?= htmlspecialchars($user['full_name']) ?></h2>
                </div>

                <div class="route-section">
                    <div class="airport-info">
                        <span class="code"><?= htmlspecialchars((string)$booking['origin_code']) ?></span>
                        <span class="city"><?= htmlspecialchars($booking['origin']) ?></span>
                    </div>
                    <div class="flight-path">
                        <div class="path-line"></div>
                        <div class="path-icon"><i class="bi bi-airplane-fill"></i></div>
                    </div>
                    <div class="airport-info text-end">
                        <span class="code"><?= htmlspecialchars((string)$booking['destination_code']) ?></span>
                        <span class="city"><?= htmlspecialchars($booking['destination']) ?></span>
                    </div>
                </div>

                <div class="details-grid">
                    <div class="detail-item">
                        <span class="label">Flight</span>
                        <span class="value"><?= htmlspecialchars($booking['flight_no']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Gate</span>
                        <span class="value"><?= $gate ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Boarding</span>
                        <span class="value"><?= $boardingTime ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Seat</span>
                        <span class="value"><?= $seatMock ?></span>
                    </div>
                </div>

                <div class="details-grid border-top pt-3 mt-3">
                    <div class="detail-item">
                        <span class="label">Date</span>
                        <span class="value"><?= date('D, M j', $departureTs) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Departure</span>
                        <span class="value"><?= date('H:i', $departureTs) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Class</span>
                        <span class="value"><?= htmlspecialchars($booking['seat_class']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Status</span>
                        <span class="value"><?= htmlspecialchars($booking['payment_status']) ?></span>
                    </div>
                </div>

                <footer class="pnr-section">
                    <div class="barcode-area">
                        <div class="barcode-svg"></div>
                    </div>
                    <div class="pnr-info">
                        <span class="label">Booking Ref (PNR)</span>
                        <div class="value font-monospace" style="font-size: 1.5rem; letter-spacing: 2px; color: var(--theme-color);"><?= htmlspecialchars($booking['pnr']) ?></div>
                    </div>
                </footer>
            </article>

            <aside class="ticket-stub">
                <div class="stub-header text-center">Passenger Coupon</div>
                
                <div class="stub-main">
                    <div class="mb-4">
                        <span class="label d-block small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.6rem;">Passenger</span>
                        <div class="fw-bold small"><?= htmlspecialchars($user['full_name']) ?></div>
                    </div>

                    <div class="stub-route">
                        <span><?= htmlspecialchars((string)$booking['origin_code']) ?></span>
                        <i class="bi bi-airplane-fill" style="font-size: 1.25rem;"></i>
                        <span><?= htmlspecialchars((string)$booking['destination_code']) ?></span>
                    </div>

                    <div class="stub-details">
                        <div class="detail-item">
                            <span class="label">Flight</span>
                            <span class="value small"><?= htmlspecialchars($booking['flight_no']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Seat</span>
                            <span class="value small"><?= $seatMock ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Date</span>
                            <span class="value small"><?= date('j M Y', $departureTs) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Gate</span>
                            <span class="value small"><?= $gate ?></span>
                        </div>
                    </div>
                </div>

                <div class="stub-footer mt-4 pt-4 border-top">
                    <div class="text-center">
                        <div class="barcode-svg" style="height: 40px;"></div>
                        <div class="small font-monospace mt-2 text-muted" style="font-size: 0.65rem;"><?= htmlspecialchars($booking['pnr']) ?></div>
                    </div>
                </div>
            </aside>
        </div>

        <?php if ($booking['payment_status'] === 'Pending'): ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mt-4" style="max-width: 820px; margin: 0 auto;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-3 h4 mb-0">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Payment Pending Approval</h6>
                        <p class="small mb-0 text-dark opacity-75">Your booking is received but awaiting administrator confirmation. Once approved, your status will change to <strong>Paid</strong> and the ticket will be fully valid for travel.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

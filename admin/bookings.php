<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

require_roles(['admin']);

$base = base_url();
$user = User::current();

$redirectAfterPost = static function () use ($base): string {
    $params = array_filter([
        'pnr' => trim((string) ($_POST['ref_pnr'] ?? '')),
        'email' => trim((string) ($_POST['ref_email'] ?? '')),
        'flight_no' => trim((string) ($_POST['ref_flight_no'] ?? '')),
        'status' => trim((string) ($_POST['ref_status'] ?? '')),
        'payment' => trim((string) ($_POST['ref_payment'] ?? '')),
        'date_from' => trim((string) ($_POST['ref_date_from'] ?? '')),
        'date_to' => trim((string) ($_POST['ref_date_to'] ?? '')),
    ], static fn (string $v): bool => $v !== '');
    $url = $base . '/admin/bookings.php';
    if ($params !== []) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $id = (int) ($_POST['booking_id'] ?? 0);
    if ($id > 0) {
        $ok = Booking::cancel($id);
        flash_set($ok ? 'success' : 'error', $ok ? 'Booking cancelled.' : 'Could not cancel booking.');
    } else {
        flash_set('error', 'Invalid booking.');
    }
    header('Location: ' . $redirectAfterPost());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $id = (int) ($_POST['booking_id'] ?? 0);
    if ($id > 0) {
        $ok = Booking::delete($id);
        flash_set($ok ? 'success' : 'error', $ok ? 'Booking deleted permanently.' : 'Could not delete booking.');
    } else {
        flash_set('error', 'Invalid booking.');
    }
    header('Location: ' . $redirectAfterPost());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_booking'])) {
    $id = (int) ($_POST['booking_id'] ?? 0);
    if ($id > 0) {
        $ok = Booking::acceptBooking($id);
        flash_set($ok ? 'success' : 'error', $ok ? 'Booking accepted — payment recorded. Revenue updated.' : 'Could not accept this booking.');
    }
    header('Location: ' . $redirectAfterPost());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $id = (int) ($_POST['booking_id'] ?? 0);
    $ps = (string) ($_POST['payment_status'] ?? '');
    if ($id > 0 && $ps !== '') {
        $ok = Booking::setPaymentStatus($id, $ps);
        flash_set($ok ? 'success' : 'error', $ok ? 'Payment status updated.' : 'Could not update payment status.');
    } else {
        flash_set('error', 'Invalid request.');
    }
    header('Location: ' . $redirectAfterPost());
    exit;
}

$flash = flash_get();
$pnr = trim($_GET['pnr'] ?? '');
$email = trim($_GET['email'] ?? '');
$flightNo = trim($_GET['flight_no'] ?? '');
$status = trim($_GET['status'] ?? '');
$payment = trim($_GET['payment'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$bookings = Booking::adminList(
    $pnr ?: null,
    $email ?: null,
    $flightNo ?: null,
    $status ?: null,
    $payment ?: null,
    $dateFrom ?: null,
    $dateTo ?: null
);

$pageTitle = 'Bookings — Wehliye Admin';
$adminSidebar = true;
$activeNav = 'bookings';

require dirname(__DIR__) . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="ofbms-page-title h3 mb-1">Bookings</h1>
        <p class="text-muted small mb-0">Manage passenger reservations, payment approvals, and cancellations.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= htmlspecialchars($base) ?>/admin/reports.php" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="bi bi-bar-chart me-1"></i> Reports</a>
        <a href="<?= htmlspecialchars($base) ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Dashboard</a>
    </div>
</div>

<div class="card admin-card-pro p-4 mb-4">
    <form class="row g-3 align-items-end" method="get" action="<?= htmlspecialchars($base) ?>/admin/bookings.php">
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem;">Reference / Passenger</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-search small"></i></span>
                <input class="form-control border-start-0 rounded-end-3" name="pnr" placeholder="PNR or Email" value="<?= htmlspecialchars($pnr ?: $email) ?>" style="font-size: 0.9rem;">
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem;">Flight No.</label>
            <input class="form-control rounded-3" name="flight_no" placeholder="e.g. OF102" value="<?= htmlspecialchars($flightNo) ?>" style="font-size: 0.9rem;">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem;">Booking Status</label>
            <select class="form-select rounded-3" name="status" style="font-size: 0.9rem;">
                <option value="">All Statuses</option>
                <option value="Confirmed" <?= $status === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem;">Payment</label>
            <select class="form-select rounded-3" name="payment" style="font-size: 0.9rem;">
                <option value="">All Payments</option>
                <option value="Pending" <?= $payment === 'Pending' ? 'selected' : '' ?>>Pending Approval</option>
                <option value="Paid" <?= $payment === 'Paid' ? 'selected' : '' ?>>Paid</option>
                <option value="Failed" <?= $payment === 'Failed' ? 'selected' : '' ?>>Failed</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary ofbms-btn-primary flex-grow-1 rounded-3 py-2 fw-semibold" type="submit">Filter List</button>
            <a href="<?= htmlspecialchars($base) ?>/admin/bookings.php" class="btn btn-light rounded-3 py-2 border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<div class="ofbms-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>PNR & Passenger</th>
                    <th>Flight Details</th>
                    <th>Seats / Class</th>
                    <th>Financials</th>
                    <th style="width: 240px;">Payment Flow</th>
                    <th>Status</th>
                    <th class="text-end">Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <?php $bid = (int) $b['id']; ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary fw-bold rounded-3 px-2 py-1 font-monospace" style="font-size: 0.85rem;"><?= htmlspecialchars($b['pnr']) ?></div>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;"><?= htmlspecialchars($b['full_name']) ?></div>
                                    <div class="small text-muted text-truncate" style="font-size: 0.75rem;"><?= htmlspecialchars($b['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark mb-0" style="font-size: 0.9rem;"><?= htmlspecialchars($b['flight_no']) ?></div>
                            <div class="small text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($b['origin_code'] ?? '') ?> → <?= htmlspecialchars($b['destination_code'] ?? '') ?></div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= (int) $b['seats'] ?> <span class="fw-normal text-muted">Seats</span></div>
                            <span class="badge rounded-pill bg-light text-dark border px-2 py-1" style="font-size: 0.65rem;"><?= htmlspecialchars($b['seat_class']) ?></span>
                        </td>
                        <td>
                            <div class="fw-bold text-primary" style="font-size: 1rem;">$<?= number_format((float) $b['total_amount'], 2) ?></div>
                            <div class="small text-muted" style="font-size: 0.7rem;"><?= date('j M Y, H:i', strtotime($b['created_at'])) ?></div>
                        </td>
                        <td>
                            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/bookings.php" class="d-flex gap-1 align-items-center">
                                <input type="hidden" name="booking_id" value="<?= $bid ?>">
                                <input type="hidden" name="ref_pnr" value="<?= htmlspecialchars($pnr) ?>">
                                <input type="hidden" name="ref_email" value="<?= htmlspecialchars($email) ?>">
                                <input type="hidden" name="ref_flight_no" value="<?= htmlspecialchars($flightNo) ?>">
                                <input type="hidden" name="ref_status" value="<?= htmlspecialchars($status) ?>">
                                <input type="hidden" name="ref_payment" value="<?= htmlspecialchars($payment) ?>">
                                <select class="form-select form-select-sm rounded-3 py-1" name="payment_status" style="font-size: 0.75rem;">
                                    <option value="Pending" <?= $b['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Paid" <?= $b['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="Failed" <?= $b['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="Refunded" <?= $b['payment_status'] === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                                </select>
                                <button type="submit" name="update_payment" value="1" class="btn btn-sm btn-outline-secondary rounded-pill px-2" style="font-size: 0.7rem;">Set</button>
                            </form>
                            <?php if ($b['payment_status'] === 'Pending' && $b['status'] !== 'Cancelled'): ?>
                                <form method="post" action="<?= htmlspecialchars($base) ?>/admin/bookings.php" class="mt-2">
                                    <input type="hidden" name="booking_id" value="<?= $bid ?>">
                                    <input type="hidden" name="ref_pnr" value="<?= htmlspecialchars($pnr) ?>">
                                    <input type="hidden" name="ref_email" value="<?= htmlspecialchars($email) ?>">
                                    <input type="hidden" name="ref_flight_no" value="<?= htmlspecialchars($flightNo) ?>">
                                    <input type="hidden" name="ref_status" value="<?= htmlspecialchars($status) ?>">
                                    <input type="hidden" name="ref_payment" value="<?= htmlspecialchars($payment) ?>">
                                    <button type="submit" name="accept_booking" value="1" class="btn btn-sm btn-success rounded-3 w-100 py-1 fw-bold" style="font-size: 0.7rem;">Quick Approve</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $b['status'] === 'Confirmed' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?> ofbms-badge-status border-0 rounded-pill">
                                <?= htmlspecialchars($b['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="<?= htmlspecialchars($base) ?>/ticket.php?id=<?= $bid ?>" class="btn btn-sm btn-outline-primary rounded-pill" title="View Digital Ticket"><i class="bi bi-eye"></i></a>
                                <?php if ($b['status'] !== 'Cancelled'): ?>
                                    <form method="post" action="<?= htmlspecialchars($base) ?>/admin/bookings.php" class="d-inline" data-confirm="Cancel this booking?">
                                        <input type="hidden" name="booking_id" value="<?= $bid ?>">
                                        <input type="hidden" name="ref_pnr" value="<?= htmlspecialchars($pnr) ?>">
                                        <input type="hidden" name="ref_status" value="<?= htmlspecialchars($status) ?>">
                                        <button type="submit" name="cancel_booking" value="1" class="btn btn-sm btn-outline-warning rounded-pill" title="Cancel Booking"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?= htmlspecialchars($base) ?>/admin/bookings.php" class="d-inline" data-confirm="Permanently delete this booking? This is irreversible.">
                                    <input type="hidden" name="booking_id" value="<?= $bid ?>">
                                    <button type="submit" name="delete_booking" value="1" class="btn btn-sm btn-outline-danger rounded-pill" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($bookings === []): ?>
        <div class="text-center py-5">
            <div class="bg-light rounded-circle d-inline-flex p-4 mb-3"><i class="bi bi-calendar-x h1 text-muted mb-0"></i></div>
            <p class="text-muted fw-bold">No bookings match your current search.</p>
            <a href="<?= htmlspecialchars($base) ?>/admin/bookings.php" class="btn btn-link btn-sm text-decoration-none">Clear all filters</a>
        </div>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

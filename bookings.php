<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

require_roles(['passenger', 'agent']);

$base = base_url();
$user = User::current();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $ok = Booking::cancel((int) ($_POST['booking_id'] ?? 0));
    flash_set($ok ? 'success' : 'error', $ok ? 'Cancellation request submitted.' : 'Cancellation failed.');
    header('Location: ' . $base . '/bookings.php');
    exit;
}

$flash = flash_get();
$history = Booking::history((int) $user['id']);
$pageTitle = 'Bookings — Wehliye Airline';
$adminSidebar = false;

require __DIR__ . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<h1 class="ofbms-page-title h3 mb-4">Bookings &amp; e-tickets</h1>
<div class="ofbms-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>PNR</th>
                    <th>Flight</th>
                    <th>Seats</th>
                    <th>Class</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Refund</th>
                    <th class="text-end">Actions</th>
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
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($b['seat_class']) ?></span></td>
                        <td class="fw-semibold">$<?= number_format((float) $b['total_amount'], 2) ?></td>
                        <td>
                            <?php if ($b['payment_status'] === 'Paid'): ?>
                                <span class="badge rounded-pill text-bg-success">Paid</span>
                            <?php elseif ($b['payment_status'] === 'Pending'): ?>
                                <span class="badge rounded-pill text-bg-warning text-dark">Awaiting approval</span>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-secondary-subtle text-dark"><?= htmlspecialchars($b['payment_status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary-subtle text-dark ofbms-badge-status"><?= htmlspecialchars($b['status']) ?></span></td>
                        <td><?= htmlspecialchars($b['refund_status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-outline-primary btn-sm rounded-pill me-1" href="<?= htmlspecialchars($base) ?>/ticket.php?id=<?= (int) $b['id'] ?>">View ticket</a>
                            <?php if ($b['status'] !== 'Cancelled'): ?>
                                <form method="post" class="d-inline" data-confirm="Cancel this booking?">
                                    <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm rounded-pill" name="cancel_booking" type="submit" value="1">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

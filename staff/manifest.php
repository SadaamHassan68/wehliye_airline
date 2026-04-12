<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

require_roles(['agent']);

$base = base_url();
$user = User::current();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $fidPost = (int) ($_POST['flight_id'] ?? 0);
    $ok = Flight::updateStatus($fidPost, (string) ($_POST['status'] ?? 'Scheduled'));
    flash_set($ok ? 'success' : 'error', $ok ? 'Flight status updated.' : 'Update failed.');
    header('Location: ' . $base . '/staff/manifest.php' . ($fidPost > 0 ? '?flight_id=' . $fidPost : ''));
    exit;
}

$flash = flash_get();
$flights = Flight::search(null, null, null);
$selectedId = isset($_GET['flight_id']) ? (int) $_GET['flight_id'] : 0;
$manifest = $selectedId > 0 ? Booking::forFlight($selectedId) : [];
$selectedFlight = null;
if ($selectedId > 0) {
    foreach ($flights as $f) {
        if ((int) $f['id'] === $selectedId) {
            $selectedFlight = $f;
            break;
        }
    }
}

$pageTitle = 'Passenger manifest — Wehliye Airline';
$adminSidebar = false;

require dirname(__DIR__) . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<h1 class="ofbms-page-title h3 mb-4">Operations — flights &amp; manifest</h1>

<div class="ofbms-table-wrap mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Flight</th>
                    <th>Route</th>
                    <th>Departure</th>
                    <th>Status</th>
                    <th>Seats sold</th>
                    <th>Manifest</th>
                    <th>Update status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flights as $f): ?>
                    <?php
                    $fid = (int) $f['id'];
                    $cap = (int) $f['capacity'];
                    $sold = $cap - Flight::availableSeats($fid);
                    ?>
                    <tr class="<?= $selectedId === $fid ? 'table-primary' : '' ?>">
                        <td class="fw-semibold"><?= htmlspecialchars($f['flight_no']) ?></td>
                        <td><?= htmlspecialchars($f['origin']) ?> → <?= htmlspecialchars($f['destination']) ?></td>
                        <td class="small text-nowrap"><?= htmlspecialchars($f['departure_time']) ?></td>
                        <td><span class="badge bg-secondary-subtle text-dark ofbms-badge-status"><?= htmlspecialchars($f['status']) ?></span></td>
                        <td><?= $sold ?> / <?= $cap ?></td>
                        <td><a class="btn btn-sm btn-outline-primary rounded-pill" href="<?= htmlspecialchars($base) ?>/staff/manifest.php?flight_id=<?= $fid ?>">View</a></td>
                        <td>
                            <form class="d-flex gap-1 flex-wrap" method="post" action="<?= htmlspecialchars($base) ?>/staff/manifest.php">
                                <input type="hidden" name="flight_id" value="<?= $fid ?>">
                                <select class="form-select form-select-sm rounded-2" name="status" style="width:auto;min-width:120px">
                                    <option>Scheduled</option>
                                    <option>Boarding</option>
                                    <option>Delayed</option>
                                    <option>Cancelled</option>
                                    <option>Completed</option>
                                </select>
                                <button class="btn btn-warning btn-sm rounded-2 text-dark" type="submit" name="update_status" value="1">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($selectedFlight): ?>
    <h2 class="h5 ofbms-page-title mb-3">Manifest: <?= htmlspecialchars($selectedFlight['flight_no']) ?> — <?= htmlspecialchars($selectedFlight['origin']) ?> → <?= htmlspecialchars($selectedFlight['destination']) ?></h2>
    <?php if (count($manifest) === 0): ?>
        <p class="text-muted">No active bookings on this flight.</p>
    <?php else: ?>
        <div class="ofbms-table-wrap">
            <table class="table table-sm">
                <thead><tr><th>PNR</th><th>Passenger</th><th>Email</th><th>Seats</th></tr></thead>
                <tbody>
                    <?php foreach ($manifest as $row): ?>
                        <tr>
                            <td class="font-monospace"><?= htmlspecialchars($row['pnr']) ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td class="small"><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= (int) $row['seats'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

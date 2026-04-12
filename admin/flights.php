<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

require_roles(['admin']);

$base = base_url();
$user = User::current();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_route'])) {
    $oid = (int) ($_POST['origin_airport_id'] ?? 0);
    $did = (int) ($_POST['destination_airport_id'] ?? 0);
    $fn = trim((string) ($_POST['flight_no'] ?? ''));
    $al = trim((string) ($_POST['airline'] ?? ''));
    $bpRaw = trim((string) ($_POST['base_price'] ?? ''));
    $bp = $bpRaw !== '' ? (float) $bpRaw : null;
    $ok = AirRoute::create($oid, $did, $fn !== '' ? $fn : null, $al !== '' ? $al : null, $bp);
    flash_set($ok ? 'success' : 'error', $ok ? 'Flight route added.' : 'Could not add route (duplicate, same airport, or invalid data).');
    header('Location: ' . $base . '/admin/flights.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_route'])) {
    $id = (int) ($_POST['route_id'] ?? 0);
    $fn = trim((string) ($_POST['flight_no'] ?? ''));
    $al = trim((string) ($_POST['airline'] ?? ''));
    $bpRaw = trim((string) ($_POST['base_price'] ?? ''));
    $bp = $bpRaw !== '' ? (float) $bpRaw : null;
    if ($id > 0) {
        $ok = AirRoute::updateDefaults($id, $fn !== '' ? $fn : null, $al !== '' ? $al : null, $bp);
        flash_set($ok ? 'success' : 'error', $ok ? 'Route defaults saved.' : 'Could not update route.');
    }
    header('Location: ' . $base . '/admin/flights.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_route'])) {
    $id = (int) ($_POST['route_id'] ?? 0);
    if ($id > 0) {
        $ok = AirRoute::delete($id);
        flash_set($ok ? 'success' : 'error', $ok ? 'Route removed.' : 'Cannot delete: scheduled flights use this route.');
    }
    header('Location: ' . $base . '/admin/flights.php');
    exit;
}

$flash = flash_get();
$airports = Airport::all();
$routes = AirRoute::allWithLabels();

$pageTitle = 'Flight routes — Wehliye Admin';
$adminSidebar = true;
$activeNav = 'flights';

require dirname(__DIR__) . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card ofbms-card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <h2 class="h6 ofbms-page-title mb-2">How it works</h2>
        <ol class="small text-secondary mb-0 ps-3">
            <li class="mb-1"><a href="<?= htmlspecialchars($base) ?>/admin/airports.php">Airports</a> — add airport codes (e.g. JFK, DXB).</li>
            <li class="mb-1"><strong>Flight routes</strong> (this page) — pair airports and set default flight number, airline, and base price for scheduling.</li>
            <li class="mb-1"><a href="<?= htmlspecialchars($base) ?>/admin/schedules.php">Schedules</a> — pick a route, set departure and arrival times, capacity, and status. Only <strong>Scheduled</strong> flights appear on the public homepage.</li>
        </ol>
    </div>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="ofbms-page-title h3 mb-1">Flight Routes</h1>
        <p class="text-muted small mb-0">Define specific flight paths and their base pricing between airports.</p>
    </div>
    <a href="<?= htmlspecialchars($base) ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Dashboard</a>
</div>

<div class="card admin-card-pro p-4 mb-4">
    <h2 class="small fw-bold text-muted text-uppercase mb-3" style="letter-spacing: 0.5px;">Create new route</h2>
    <form class="row g-3 align-items-end" method="post" action="<?= htmlspecialchars($base) ?>/admin/flights.php">
        <div class="col-md-2">
            <label class="form-label small fw-semibold">Flight No.</label>
            <input class="form-control rounded-3" name="flight_no" placeholder="OF100" required style="font-size: 0.9rem;">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Origin</label>
            <select class="form-select rounded-3" name="origin_id" required style="font-size: 0.9rem;">
                <option value="">Select Origin</option>
                <?php foreach ($airports as $a): ?>
                    <option value="<?= (int) $a['id'] ?>"><?= htmlspecialchars($a['code']) ?> - <?= htmlspecialchars($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Destination</label>
            <select class="form-select rounded-3" name="destination_id" required style="font-size: 0.9rem;">
                <option value="">Select Destination</option>
                <?php foreach ($airports as $a): ?>
                    <option value="<?= (int) $a['id'] ?>"><?= htmlspecialchars($a['code']) ?> - <?= htmlspecialchars($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">Base Price ($)</label>
            <input class="form-control rounded-3" name="base_price" type="number" step="0.01" placeholder="99.00" required style="font-size: 0.9rem;">
        </div>
        <div class="col-md-2">
            <button type="submit" name="create_flight" value="1" class="btn btn-primary ofbms-btn-primary rounded-3 w-100 py-2 fw-semibold">Define Route</button>
        </div>
    </form>
</div>

<div class="ofbms-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 140px;">Flight No.</th>
                    <th>Full Flight Path</th>
                    <th>Base Pricing</th>
                    <th class="text-end">Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routes as $f): ?>
                    <tr>
                        <td>
                            <div class="bg-primary bg-opacity-10 text-primary fw-bold rounded-3 px-3 py-1 font-monospace text-center" style="font-size: 0.9rem;">
                                <?= htmlspecialchars((string) ($f['flight_no'] ?? 'N/A')) ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-dark bg-light rounded-pill px-3 py-1 border small fw-bold">
                                    <?= htmlspecialchars((string) ($f['origin_code'] ?? '')) ?>
                                </div>
                                <i class="bi bi-arrow-right text-muted"></i>
                                <div class="text-dark bg-light rounded-pill px-3 py-1 border small fw-bold">
                                    <?= htmlspecialchars((string) ($f['destination_code'] ?? '')) ?>
                                </div>
                                <div class="ms-2 text-muted small">
                                    <?= htmlspecialchars((string) ($f['origin_city'] ?? '')) ?> <i class="bi bi-arrow-right-short"></i> <?= htmlspecialchars((string) ($f['dest_city'] ?? '')) ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-success" style="font-size: 1.05rem;">
                                $<?= number_format((float) ($f['base_price'] ?? 0), 2) ?>
                            </div>
                            <div class="small text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars((string) ($f['airline'] ?? 'Wehliye Air')) ?></div>
                        </td>
                        <td class="text-end">
                            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/flights.php" class="d-inline" data-confirm="Delete this route?">
                                <input type="hidden" name="route_id" value="<?= (int) $f['id'] ?>">
                                <button type="submit" name="delete_route" value="1" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

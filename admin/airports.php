<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

require_roles(['admin']);

$base = base_url();
$user = User::current();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_airport'])) {
    $ok = Airport::create(
        (string) ($_POST['code'] ?? ''),
        (string) ($_POST['name'] ?? ''),
        (string) ($_POST['city'] ?? ''),
        (string) ($_POST['country'] ?? '')
    );
    flash_set($ok ? 'success' : 'error', $ok ? 'Airport added.' : 'Could not add airport (duplicate code or invalid data).');
    header('Location: ' . $base . '/admin/airports.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_airport'])) {
    $id = (int) ($_POST['airport_id'] ?? 0);
    if ($id > 0) {
        $ok = Airport::delete($id);
        flash_set($ok ? 'success' : 'error', $ok ? 'Airport removed.' : 'Cannot delete: airport is used on a route.');
    }
    header('Location: ' . $base . '/admin/airports.php');
    exit;
}

$flash = flash_get();
$airports = Airport::all();

$pageTitle = 'Airports — Wehliye Admin';
$adminSidebar = true;
$activeNav = 'airports';

require dirname(__DIR__) . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="ofbms-page-title h3 mb-1">Airports</h1>
        <p class="text-muted small mb-0">Manage global airport codes, names, and regional locations.</p>
    </div>
    <a href="<?= htmlspecialchars($base) ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Dashboard</a>
</div>

<div class="card admin-card-pro p-4 mb-4">
    <h2 class="small fw-bold text-muted text-uppercase mb-3" style="letter-spacing: 0.5px;">Register new airport</h2>
    <form class="row g-3 align-items-end" method="post" action="<?= htmlspecialchars($base) ?>/admin/airports.php">
        <div class="col-md-2">
            <label class="form-label small fw-semibold">IATA Code</label>
            <input class="form-control rounded-3" name="code" placeholder="e.g. NBO" maxlength="8" required style="font-size: 0.9rem;">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Airport name</label>
            <input class="form-control rounded-3" name="name" placeholder="Jomo Kenyatta International" required style="font-size: 0.9rem;">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">City</label>
            <input class="form-control rounded-3" name="city" placeholder="Nairobi" required style="font-size: 0.9rem;">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">Country</label>
            <input class="form-control rounded-3" name="country" placeholder="Kenya" required style="font-size: 0.9rem;">
        </div>
        <div class="col-md-2">
            <button type="submit" name="create_airport" value="1" class="btn btn-primary ofbms-btn-primary rounded-3 w-100 py-2 fw-semibold">Add Airport</button>
        </div>
    </form>
</div>

<div class="ofbms-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 120px;">Code</th>
                    <th>Full Airport Name</th>
                    <th>City/Region</th>
                    <th>Country</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($airports as $a): ?>
                    <tr>
                        <td>
                            <div class="bg-primary bg-opacity-10 text-primary fw-bold rounded-3 px-3 py-1 font-monospace text-center" style="font-size: 0.9rem;">
                                <?= htmlspecialchars($a['code']) ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($a['name']) ?></div>
                        </td>
                        <td>
                            <div class="text-secondary" style="font-size: 0.9rem;"><?= htmlspecialchars($a['city']) ?></div>
                        </td>
                        <td>
                            <div class="text-secondary" style="font-size: 0.9rem;"><?= htmlspecialchars($a['country']) ?></div>
                        </td>
                        <td class="text-end">
                            <form method="post" class="d-inline" data-confirm="Permanently delete this airport record?">
                                <input type="hidden" name="airport_id" value="<?= (int) $a['id'] ?>">
                                <button type="submit" name="delete_airport" value="1" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Delete Airport">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($airports === []): ?>
        <div class="text-center py-5">
            <i class="bi bi-geo-alt h1 text-muted mb-3 d-block"></i>
            <p class="text-muted">No airports registered yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

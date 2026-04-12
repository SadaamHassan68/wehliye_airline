<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

require_roles(['admin']);

$base = base_url();
$user = User::current();

$dtInputToMysql = static function (string $v): string {
    $v = str_replace('T', ' ', trim($v));
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v)) {
        return $v . ':00';
    }
    return $v;
};

$redirectAfterPost = static function () use ($base): string {
    $qo = trim((string) ($_POST['ref_origin'] ?? ''));
    $qd = trim((string) ($_POST['ref_destination'] ?? ''));
    $qt = trim((string) ($_POST['ref_date'] ?? ''));
    $url = $base . '/admin/schedules.php';
    $p = array_filter(['origin' => $qo, 'destination' => $qd, 'date' => $qt], static fn (string $v): bool => $v !== '');
    if ($p !== []) {
        $url .= '?' . http_build_query($p);
    }
    return $url;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_status'])) {
    $id = (int) ($_POST['flight_id'] ?? 0);
    $st = (string) ($_POST['flight_status'] ?? '');
    if ($id > 0 && $st !== '') {
        $ok = Flight::updateStatus($id, $st);
        flash_set($ok ? 'success' : 'error', $ok ? 'Status updated.' : 'Could not update status.');
    }
    header('Location: ' . $redirectAfterPost());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_flight'])) {
    $rid = (int) ($_POST['route_id'] ?? 0);
    $o = trim((string) ($_POST['origin'] ?? ''));
    $d = trim((string) ($_POST['destination'] ?? ''));
    $flightNo = trim((string) ($_POST['flight_no'] ?? ''));
    $aircraft = trim((string) ($_POST['aircraft'] ?? ''));
    $bpRaw = trim((string) ($_POST['base_price'] ?? ''));
    $basePrice = $bpRaw !== '' ? (float) $bpRaw : null;

    if ($rid > 0) {
        $rt = AirRoute::findById($rid);
        if ($rt) {
            if ($flightNo === '' && !empty($rt['flight_no'])) {
                $flightNo = (string) $rt['flight_no'];
            }
            if ($aircraft === '' && !empty($rt['airline'])) {
                $aircraft = (string) $rt['airline'];
            }
            if ($basePrice === null && isset($rt['base_price']) && $rt['base_price'] !== null) {
                $basePrice = (float) $rt['base_price'];
            }
            if ($o === '' && $d === '') {
                $rd = AirRoute::resolveOriginDestination($rid);
                if ($rd) {
                    $o = $rd['origin'];
                    $d = $rd['destination'];
                }
            }
        }
    }

    if ($rid <= 0 && ($o === '' || $d === '')) {
        flash_set('error', 'Select a saved route or enter origin and destination.');
        header('Location: ' . $redirectAfterPost());
        exit;
    }
    if ($flightNo === '' || $aircraft === '') {
        flash_set('error', 'Flight number and aircraft are required (or set defaults on the flight route).');
        header('Location: ' . $redirectAfterPost());
        exit;
    }
    if ($basePrice === null) {
        $basePrice = 0.0;
    }

    $ok = Flight::create([
        'flight_no' => $flightNo,
        'origin' => $o,
        'destination' => $d,
        'departure_time' => $dtInputToMysql((string) ($_POST['departure_time'] ?? '')),
        'arrival_time' => $dtInputToMysql((string) ($_POST['arrival_time'] ?? '')),
        'aircraft' => $aircraft,
        'capacity' => (int) ($_POST['capacity'] ?? 0),
        'base_price' => $basePrice,
        'status' => (string) ($_POST['status'] ?? 'Scheduled'),
        'route_id' => $rid,
    ]);
    flash_set($ok ? 'success' : 'error', $ok ? 'Flight scheduled.' : 'Failed to create flight.');
    header('Location: ' . $redirectAfterPost());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_flight'])) {
    $id = (int) ($_POST['flight_id'] ?? 0);
    $rid = (int) ($_POST['route_id'] ?? 0);
    $o = trim((string) ($_POST['origin'] ?? ''));
    $d = trim((string) ($_POST['destination'] ?? ''));
    if ($rid <= 0 && ($o === '' || $d === '')) {
        flash_set('error', 'Select a saved route or enter origin and destination.');
        header('Location: ' . $redirectAfterPost());
        exit;
    }
    $data = [
        'flight_no' => trim((string) ($_POST['flight_no'] ?? '')),
        'origin' => $o,
        'destination' => $d,
        'departure_time' => $dtInputToMysql((string) ($_POST['departure_time'] ?? '')),
        'arrival_time' => $dtInputToMysql((string) ($_POST['arrival_time'] ?? '')),
        'aircraft' => trim((string) ($_POST['aircraft'] ?? '')),
        'capacity' => (int) ($_POST['capacity'] ?? 0),
        'base_price' => (float) ($_POST['base_price'] ?? 0),
        'status' => (string) ($_POST['status'] ?? 'Scheduled'),
        'route_id' => $rid,
    ];
    if ($id > 0) {
        $ok = Flight::update($id, $data);
        if ($ok) {
            flash_set('success', 'Flight updated.');
        } else {
            $sold = Flight::bookedSeatsExcludingCancelled($id);
            if ($data['capacity'] < $sold) {
                flash_set('error', 'Capacity cannot be less than seats already booked (' . $sold . ').');
            } else {
                flash_set('error', 'Failed to update flight. The flight number may already be in use.');
            }
        }
    } else {
        flash_set('error', 'Invalid flight.');
    }
    header('Location: ' . $redirectAfterPost());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_flight'])) {
    $id = (int) ($_POST['flight_id'] ?? 0);
    if ($id > 0) {
        $ok = Flight::delete($id);
        flash_set($ok ? 'success' : 'error', $ok ? 'Flight deleted.' : 'Failed to delete flight.');
    } else {
        flash_set('error', 'Invalid flight.');
    }
    header('Location: ' . $redirectAfterPost());
    exit;
}

$flash = flash_get();
$origin = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$date = trim($_GET['date'] ?? '');
$flights = Flight::search($origin ?: null, $destination ?: null, $date ?: null);

try {
    $routesList = AirRoute::allWithLabels();
} catch (Throwable $e) {
    $routesList = [];
}

$statusOptions = Flight::allowedStatuses();

$flightDtLocal = static function (string $mysql): string {
    return str_replace(' ', 'T', substr($mysql, 0, 16));
};

$pageTitle = 'Schedules — Wehliye Airline';
$adminSidebar = true;
$activeNav = 'schedules';

require dirname(__DIR__) . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="ofbms-page-title h3 mb-0">Schedule flights</h1>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= htmlspecialchars($base) ?>/admin/flights.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Flight routes</a>
        <button class="btn btn-primary ofbms-btn-primary btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#addFlightFormCollapse" aria-expanded="false" aria-controls="addFlightFormCollapse">
            <i class="bi bi-plus-circle me-1"></i> Add schedule
        </button>
        <a href="<?= htmlspecialchars($base) ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Dashboard</a>
    </div>
</div>
<p class="text-secondary small mb-4">Set departure and arrival times, capacity, and status. Update status to <strong>Scheduled</strong>, <strong>Delayed</strong>, <strong>Cancelled</strong>, or <strong>Landed</strong> as needed. The public homepage lists only flights in <strong>Scheduled</strong> status with a future departure.</p>

<div class="ofbms-search-bar">
    <form class="row g-2 align-items-end" method="get" action="<?= htmlspecialchars($base) ?>/admin/schedules.php">
        <div class="col-sm-6 col-md-3">
            <label class="form-label small fw-semibold mb-1">From</label>
            <input class="form-control rounded-3" name="origin" placeholder="Origin" value="<?= htmlspecialchars($origin) ?>">
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label small fw-semibold mb-1">To</label>
            <input class="form-control rounded-3" name="destination" placeholder="Destination" value="<?= htmlspecialchars($destination) ?>">
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label small fw-semibold mb-1">Date</label>
            <input class="form-control rounded-3" name="date" type="date" value="<?= htmlspecialchars($date) ?>">
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
                    <th>Quick status</th>
                    <th>Price</th>
                    <th>Seats left</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flights as $f): ?>
                    <?php
                    $fid = (int) $f['id'];
                    $depL = $flightDtLocal((string) $f['departure_time']);
                    $arrL = $flightDtLocal((string) $f['arrival_time']);
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($f['flight_no']) ?></td>
                        <td><?= htmlspecialchars($f['origin']) ?> <i class="bi bi-arrow-right text-muted mx-1"></i> <?= htmlspecialchars($f['destination']) ?></td>
                        <td class="text-nowrap small"><?= htmlspecialchars($f['departure_time']) ?></td>
                        <td><?= htmlspecialchars($f['aircraft']) ?></td>
                        <td><span class="badge bg-secondary-subtle text-dark ofbms-badge-status"><?= htmlspecialchars($f['status']) ?></span></td>
                        <td>
                            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/schedules.php" class="d-flex flex-wrap gap-1 align-items-center">
                                <input type="hidden" name="flight_id" value="<?= $fid ?>">
                                <input type="hidden" name="ref_origin" value="<?= htmlspecialchars($origin) ?>">
                                <input type="hidden" name="ref_destination" value="<?= htmlspecialchars($destination) ?>">
                                <input type="hidden" name="ref_date" value="<?= htmlspecialchars($date) ?>">
                                <select class="form-select form-select-sm rounded-3" name="flight_status" style="min-width: 6.5rem;" aria-label="Flight status">
                                    <?php foreach ($statusOptions as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt) ?>" <?= $f['status'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="quick_status" value="1" class="btn btn-sm btn-outline-secondary rounded-pill">Set</button>
                            </form>
                        </td>
                        <td class="fw-semibold">$<?= number_format((float) $f['base_price'], 2) ?></td>
                        <td><?= Flight::availableSeats($fid) ?></td>
                        <td class="text-end text-nowrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary rounded-pill me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#editFlightModal"
                                data-flight-id="<?= $fid ?>"
                                data-route-id="<?= (int) ($f['route_id'] ?? 0) ?>"
                                data-flight-no="<?= htmlspecialchars($f['flight_no'], ENT_QUOTES, 'UTF-8') ?>"
                                data-origin="<?= htmlspecialchars($f['origin'], ENT_QUOTES, 'UTF-8') ?>"
                                data-destination="<?= htmlspecialchars($f['destination'], ENT_QUOTES, 'UTF-8') ?>"
                                data-departure="<?= htmlspecialchars($depL, ENT_QUOTES, 'UTF-8') ?>"
                                data-arrival="<?= htmlspecialchars($arrL, ENT_QUOTES, 'UTF-8') ?>"
                                data-aircraft="<?= htmlspecialchars($f['aircraft'], ENT_QUOTES, 'UTF-8') ?>"
                                data-capacity="<?= (int) $f['capacity'] ?>"
                                data-base-price="<?= htmlspecialchars((string) $f['base_price'], ENT_QUOTES, 'UTF-8') ?>"
                                data-status="<?= htmlspecialchars($f['status'], ENT_QUOTES, 'UTF-8') ?>"
                            ><i class="bi bi-pencil"></i> Edit</button>
                            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/schedules.php" class="d-inline" data-confirm="Delete this flight and all its bookings? This cannot be undone.">
                                <input type="hidden" name="flight_id" value="<?= $fid ?>">
                                <input type="hidden" name="ref_origin" value="<?= htmlspecialchars($origin) ?>">
                                <input type="hidden" name="ref_destination" value="<?= htmlspecialchars($destination) ?>">
                                <input type="hidden" name="ref_date" value="<?= htmlspecialchars($date) ?>">
                                <button type="submit" name="delete_flight" value="1" class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editFlightModal" tabindex="-1" aria-labelledby="editFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 ofbms-page-title" id="editFlightModalLabel">Edit schedule</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/schedules.php">
                <input type="hidden" name="flight_id" id="edit_flight_id" value="">
                <input type="hidden" name="ref_origin" value="<?= htmlspecialchars($origin) ?>">
                <input type="hidden" name="ref_destination" value="<?= htmlspecialchars($destination) ?>">
                <input type="hidden" name="ref_date" value="<?= htmlspecialchars($date) ?>">
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small" for="edit_route_id">Route (optional)</label>
                            <select class="form-select rounded-3" id="edit_route_id" name="route_id">
                                <option value="0">— Manual origin / destination —</option>
                                <?php foreach ($routesList as $r): ?>
                                    <option value="<?= (int) $r['id'] ?>"><?= htmlspecialchars($r['origin_code'] . ' → ' . $r['dest_code'] . ' (' . $r['origin_city'] . ' → ' . $r['dest_city'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small" for="edit_flight_no">Flight no.</label>
                            <input class="form-control rounded-3" id="edit_flight_no" name="flight_no" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small" for="edit_origin">Origin</label>
                            <input class="form-control rounded-3" id="edit_origin" name="origin">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small" for="edit_destination">Destination</label>
                            <input class="form-control rounded-3" id="edit_destination" name="destination">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="edit_departure_time">Departure</label>
                            <input class="form-control rounded-3" type="datetime-local" id="edit_departure_time" name="departure_time" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="edit_arrival_time">Arrival</label>
                            <input class="form-control rounded-3" type="datetime-local" id="edit_arrival_time" name="arrival_time" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="edit_aircraft">Aircraft</label>
                            <input class="form-control rounded-3" id="edit_aircraft" name="aircraft" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small" for="edit_capacity">Capacity</label>
                            <input class="form-control rounded-3" type="number" id="edit_capacity" name="capacity" required min="1">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small" for="edit_base_price">Base price</label>
                            <input class="form-control rounded-3" type="number" step="0.01" id="edit_base_price" name="base_price" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="edit_status">Status</label>
                            <select class="form-select rounded-3" id="edit_status" name="status">
                                <?php foreach ($statusOptions as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_flight" value="1" class="btn btn-primary ofbms-btn-primary rounded-pill px-4">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="collapse" id="addFlightFormCollapse">
    <h2 class="h5 ofbms-page-title mb-3">Add schedule</h2>
    <div class="card ofbms-card p-3 p-md-4">
        <form class="row g-3" method="post" action="<?= htmlspecialchars($base) ?>/admin/schedules.php">
            <input type="hidden" name="ref_origin" value="<?= htmlspecialchars($origin) ?>">
            <input type="hidden" name="ref_destination" value="<?= htmlspecialchars($destination) ?>">
            <input type="hidden" name="ref_date" value="<?= htmlspecialchars($date) ?>">
            <div class="col-12 col-md-4">
                <label class="form-label small">Route</label>
                <select class="form-select rounded-3" name="route_id" id="schedule_route_id">
                    <option value="0">— Manual origin / destination —</option>
                    <?php foreach ($routesList as $r): ?>
                        <option
                            value="<?= (int) $r['id'] ?>"
                            data-flight-no="<?= htmlspecialchars((string) ($r['flight_no'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            data-airline="<?= htmlspecialchars((string) ($r['airline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            data-base-price="<?= htmlspecialchars($r['base_price'] !== null && $r['base_price'] !== '' ? (string) $r['base_price'] : '', ENT_QUOTES, 'UTF-8') ?>"
                        ><?= htmlspecialchars($r['origin_code'] . ' → ' . $r['dest_code']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2"><label class="form-label small">Flight no.</label><input class="form-control rounded-3" name="flight_no" placeholder="WH101"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Origin</label><input class="form-control rounded-3" name="origin"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Destination</label><input class="form-control rounded-3" name="destination"></div>
            <div class="col-md-2"><label class="form-label small">Departure</label><input class="form-control rounded-3" type="datetime-local" name="departure_time" required></div>
            <div class="col-md-2"><label class="form-label small">Arrival</label><input class="form-control rounded-3" type="datetime-local" name="arrival_time" required></div>
            <div class="col-md-2"><label class="form-label small">Aircraft</label><input class="form-control rounded-3" name="aircraft" placeholder="From route airline"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Capacity</label><input class="form-control rounded-3" type="number" name="capacity" required min="1"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Base price</label><input class="form-control rounded-3" type="number" step="0.01" name="base_price" placeholder="0.00"></div>
            <div class="col-md-2"><label class="form-label small">Status</label><select class="form-select rounded-3" name="status"><?php foreach ($statusOptions as $opt): ?><option value="<?= htmlspecialchars($opt) ?>" <?= $opt === 'Scheduled' ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-md-2 d-flex align-items-end"><button class="btn btn-primary ofbms-btn-primary w-100 rounded-3" name="create_flight" type="submit" value="1">Save</button></div>
        </form>
    </div>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

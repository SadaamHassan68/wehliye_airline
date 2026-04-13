<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$user = User::current();
$base = base_url();
$flash = flash_get();
$pageTitle = 'Wehliye Airline — Book flights';
$adminSidebar = false;

$origin = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$date = trim($_GET['date'] ?? '');
$hasSearch = $origin !== '' || $destination !== '' || $date !== '';
$flights = Flight::search($origin ?: null, $destination ?: null, $date ?: null);

$upcomingFlights = [];
try {
    $upcomingFlights = Flight::upcomingScheduled(12);
} catch (Throwable $e) {
    $upcomingFlights = [];
}

require __DIR__ . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="container mt-4">
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3 ofbms-home-alert" role="alert"><?= htmlspecialchars($flash['message']) ?></div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/partials/home_hero.php'; ?>

<div class="container">
    <?php require __DIR__ . '/includes/partials/home_available_flights.php'; ?>
    
    <section id="flight-search" class="ofbms-home-search-section mb-5">
        <div class="ofbms-search-console p-4 p-md-5 rounded-4 shadow-lg position-relative" style="background: rgba(22, 15, 42, 0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); margin-top: -3rem; z-index: 10;">
            <div class="ofbms-home-search-head mb-4 text-center text-md-start">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-2 rounded-pill"><i class="bi bi-airplane-engines me-1"></i> Flight Search</span>
                <h2 class="fw-bold mb-1" style="color: var(--ofbms-navy);">Where to next?</h2>
                <p class="text-muted mb-0">Search by route and date. Accurate real-time schedules.</p>
            </div>
            <form class="row g-3 align-items-end" method="get" action="<?= htmlspecialchars($base) ?>/index.php">
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase mb-1"><i class="bi bi-geo-alt me-1 text-primary"></i> Origin</label>
                    <div class="input-group input-group-lg shadow-sm rounded-3">
                        <span class="input-group-text border-end-0" style="background: rgba(255,255,255,0.05); color: #94a3b8; border-color: rgba(255,255,255,0.1);"><i class="bi bi-geo-alt"></i></span>
                        <input class="form-control border-start-0 ps-0" style="font-weight: 500; background: rgba(255,255,255,0.07); color: #e2e8f0; border-color: rgba(255,255,255,0.1);" name="origin" placeholder="City or Airport" value="<?= htmlspecialchars($origin) ?>" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase mb-1"><i class="bi bi-geo me-1 text-primary"></i> Destination</label>
                    <div class="input-group input-group-lg shadow-sm rounded-3">
                        <span class="input-group-text border-end-0" style="background: rgba(255,255,255,0.05); color: #94a3b8; border-color: rgba(255,255,255,0.1);"><i class="bi bi-geo"></i></span>
                        <input class="form-control border-start-0 ps-0" style="font-weight: 500; background: rgba(255,255,255,0.07); color: #e2e8f0; border-color: rgba(255,255,255,0.1);" name="destination" placeholder="City or Airport" value="<?= htmlspecialchars($destination) ?>" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase mb-1"><i class="bi bi-calendar3 me-1 text-primary"></i> Travel date</label>
                    <div class="input-group input-group-lg shadow-sm rounded-3">
                        <span class="input-group-text border-end-0" style="background: rgba(255,255,255,0.05); color: #94a3b8; border-color: rgba(255,255,255,0.1);"><i class="bi bi-calendar-event"></i></span>
                        <input class="form-control border-start-0 ps-0" style="font-weight: 500; background: rgba(255,255,255,0.07); color: #e2e8f0; border-color: rgba(255,255,255,0.1); color-scheme: dark;" name="date" type="date" value="<?= htmlspecialchars($date) ?>">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <button class="btn btn-primary w-100 shadow-sm" style="height: calc(3.5rem + 2px); border-radius: 0.5rem; background: linear-gradient(135deg, var(--ofbms-sky) 0%, var(--ofbms-sky-deep) 100%); font-weight: 700; font-size: 1.1rem; border: none;" type="submit">
                        <i class="bi bi-search me-2"></i> Find Flights
                    </button>
                </div>
            </form>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4 mt-5">
            <div>
                <h3 class="fw-bold mb-1" style="color: #ffffff;"><i class="bi bi-list-check me-2" style="color: #a78bfa;"></i><?= $hasSearch ? 'Search results' : 'All flights' ?></h3>
                <p class="small mb-0" style="color: #94a3b8;"><?= $hasSearch ? 'Filtered by your exact criteria.' : 'Complete schedule — refine above.' ?></p>
            </div>
        </div>
        
        <?php if (count($flights) === 0): ?>
            <div class="card border-0 rounded-4 text-center p-5 mb-5" style="background: rgba(22, 15, 42, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="bi bi-cloud-slash" style="font-size: 4rem; color: #7c3aed; opacity: 0.6;"></i>
                    </div>
                    <h4 class="fw-bold mb-2" style="color: #fff;">No Flights Found</h4>
                    <p class="mb-4" style="color: #94a3b8;"><?= $hasSearch ? 'Try adjusting your origin, destination, or date to find more options.' : 'There are currently no flights available in the system.' ?></p>
                    <?php if ($hasSearch): ?>
                        <a href="<?= htmlspecialchars($base) ?>/index.php#flight-search" class="btn rounded-pill fw-semibold px-4" style="border: 1px solid rgba(196,181,253,0.4); color: #c4b5fd; background: rgba(124,58,237,0.1);"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset Search</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3 g-lg-4 mb-5">
                <?php foreach ($flights as $f): ?>
                    <?php
                    $fid = (int) $f['id'];
                    $seatsLeft = Flight::availableSeats($fid);
                    $canBook = Flight::isBookable($fid) && $seatsLeft > 0;
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="ofbms-flight-card-pro h-100 d-flex flex-column hover-elevate" style="background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 12px 36px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);">
                            <!-- Image Header -->
                            <div style="height: 180px; width: 100%; position: relative; overflow: hidden;" class="flight-img-wrap">
                                <div class="flight-img" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url('<?= htmlspecialchars($base) ?>/assets/img/flight_header.png') center/cover no-repeat; transition: transform 0.5s ease;"></div>
                                <!-- Gradient overlay -->
                                <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.8) 100%);"></div>
                                
                                <!-- Status -->
                                <div style="position: absolute; top: 16px; right: 16px;">
                                    <span class="badge rounded-pill <?= $f['status'] === 'Scheduled' ? 'bg-success' : 'bg-warning text-dark' ?> px-3 py-2 fw-semibold" style="box-shadow: 0 4px 12px rgba(0,0,0,0.15); letter-spacing: 0.5px;"><i class="bi bi-record-circle-fill me-1 small"></i> <?= htmlspecialchars($f['status']) ?></span>
                                </div>
                                
                                <!-- Airline / Flight Info -->
                                <div style="position: absolute; bottom: 16px; left: 20px; right: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
                                    <div>
                                        <span class="badge bg-white text-dark mb-2 px-2 py-1 rounded" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 1px;">Wehliye Air</span>
                                        <h4 class="text-white fw-bold mb-0" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-size: 1.3rem;"><i class="bi bi-airplane-engines me-2"></i>Flight <?= htmlspecialchars($f['flight_no']) ?></h4>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-white-50 d-block small fw-semibold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">Starting at</span>
                                        <span class="text-white fw-bold fs-4 lh-1" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">$<?= number_format((float) $f['base_price'], 0) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex flex-column flex-grow-1 p-4 pb-4">
                                <!-- Route visualization -->
                                <div class="d-flex align-items-center justify-content-between mb-4 position-relative">
                                    <!-- Dashed line connecting -->
                                    <div style="position: absolute; top: 50%; left: 30px; right: 30px; height: 2px; border-top: 2px dashed #cbd5e1; z-index: 1;"></div>
                                    
                                    <div class="text-center position-relative z-bg" style="background: white; padding-right: 10px; z-index: 2; width: 40%;">
                                        <h2 class="fw-bold mb-0 text-dark" style="font-size: 2rem; letter-spacing: -1px;"><?= htmlspecialchars(strlen($f['origin']) > 3 ? substr(strtoupper($f['origin']), 0, 3) : strtoupper($f['origin'])) ?></h2>
                                        <span class="small text-muted fw-semibold d-block text-truncate"><?= htmlspecialchars($f['origin']) ?></span>
                                    </div>
                                    
                                    <div class="position-relative" style="z-index: 3; background: white; padding: 0 8px;">
                                        <div class="btn btn-sm btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--ofbms-sky) 0%, var(--ofbms-sky-deep) 100%); border: none;">
                                            <i class="bi bi-airplane-fill text-white"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center position-relative z-bg" style="background: white; padding-left: 10px; z-index: 2; width: 40%;">
                                        <h2 class="fw-bold mb-0 text-dark" style="font-size: 2rem; letter-spacing: -1px;"><?= htmlspecialchars(strlen($f['destination']) > 3 ? substr(strtoupper($f['destination']), 0, 3) : strtoupper($f['destination'])) ?></h2>
                                        <span class="small text-muted fw-semibold d-block text-truncate"><?= htmlspecialchars($f['destination']) ?></span>
                                    </div>
                                </div>

                                <!-- Info Grid -->
                                <div class="row g-2 mb-4 rounded-3 p-3" style="background: #f8fafc;">
                                    <div class="col-6 border-end border-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded bg-white shadow-sm p-2 text-primary border border-light"><i class="bi bi-calendar-event"></i></div>
                                            <div>
                                                <span class="d-block small text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Departure</span>
                                                <span class="fw-semibold text-dark small"><?= date('M j, Y', strtotime($f['departure_time'])) ?></span>
                                                <span class="d-block text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($f['departure_time'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 ps-3">
                                         <div class="d-flex align-items-center gap-2">
                                            <div class="rounded bg-white shadow-sm p-2 text-info border border-light"><i class="bi bi-bezier2"></i></div>
                                            <div style="min-width: 0;">
                                                <span class="d-block small text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Aircraft</span>
                                                <span class="fw-semibold text-dark small text-truncate d-block" style="max-width: 100%;"><?= htmlspecialchars($f['aircraft']) ?></span>
                                                <span class="d-block <?= $seatsLeft < 10 ? 'text-danger' : 'text-success' ?> fw-semibold" style="font-size: 0.7rem;"><?= $seatsLeft ?> seats left</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Booking Area -->
                                <div class="mt-auto pt-3 border-top border-light">
                                    <?php if ($user && $user['role'] === 'passenger' && $canBook): ?>
                                        <form class="row g-2 align-items-center w-100" method="post" action="<?= htmlspecialchars($base) ?>/booking_process.php">
                                            <input type="hidden" name="redirect_qs" value="">
                                            <input type="hidden" name="flight_id" value="<?= $fid ?>">
                                            <input type="hidden" name="payment_method" value="CreditCard">
                                            
                                            <div class="col-7">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-0"><i class="bi bi-star-fill text-warning"></i></span>
                                                    <select class="form-select border-0 bg-light fw-semibold" name="seat_class" required>
                                                        <option value="Economy">Economy</option>
                                                        <option value="Business">Business (1.5x)</option>
                                                        <option value="FirstClass">First Class (2.5x)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-5">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-0"><i class="bi bi-people-fill text-muted"></i></span>
                                                    <input type="number" class="form-control border-0 bg-light text-center fw-semibold" name="seats" value="1" min="1" max="<?= $seatsLeft ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <button class="btn btn-primary btn-lg rounded-pill fw-bold w-100 shadow-sm" style="background: linear-gradient(135deg, var(--ofbms-sky) 0%, var(--ofbms-sky-deep) 100%); border: none;" name="book" type="submit" value="1"><i class="bi bi-ticket-perforated me-2"></i> Book Flight</button>
                                            </div>
                                        </form>
                                    <?php elseif (!$user && $canBook): ?>
                                        <a class="btn btn-outline-primary btn-lg rounded-pill fw-bold w-100" href="<?= htmlspecialchars($base) ?>/login.php"><i class="bi bi-box-arrow-in-right me-2"></i> Sign in to book</a>
                                    <?php elseif (!$canBook): ?>
                                        <button class="btn btn-secondary btn-lg rounded-pill fw-bold w-100 disabled" disabled><i class="bi bi-slash-circle me-2"></i> Not available</button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-lg rounded-pill fw-bold w-100 disabled" disabled><i class="bi bi-info-circle me-2"></i> Passenger account required</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php require __DIR__ . '/includes/partials/home_features.php'; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

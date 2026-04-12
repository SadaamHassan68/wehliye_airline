<?php

declare(strict_types=1);

/** @var string $base */
/** @var array|null $user */
/** @var array $upcomingFlights */

?>
    <section id="available-flights" class="ofbms-home-available mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
            <div>
                <span class="ofbms-home-section-eyebrow">Live schedule</span>
                <h2 class="ofbms-home-section-title mb-1">Available flights</h2>
                <p class="ofbms-home-section-lead small mb-0">Upcoming departures — fares and seats update in real time.</p>
            </div>
            <a class="btn btn-sm ofbms-home-link-btn rounded-pill px-3" href="#flight-search">Full search</a>
        </div>
        <?php if ($upcomingFlights === []): ?>
            <div class="ofbms-home-empty p-4 text-center">No upcoming flights right now. Check back soon or use search below.</div>
        <?php else: ?>
            <div class="row g-3 g-lg-4">
                <?php foreach ($upcomingFlights as $f): ?>
                    <?php
                    $fid = (int) $f['id'];
                    $seats = Flight::availableSeats($fid);
                    $bookable = Flight::isBookable($fid) && $seats > 0;
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="ofbms-flight-card-pro h-100 d-flex flex-column hover-elevate" style="background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); border: 1px solid rgba(226, 232, 240, 0.8); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                            <div style="height: 140px; width: 100%; background: url('<?= htmlspecialchars($base) ?>/assets/img/flight_header.png') center/cover no-repeat; position: relative;">
                                <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 60%; background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);"></div>
                                <div style="position: absolute; top: 15px; right: 15px;">
                                    <span class="badge rounded-pill <?= $f['status'] === 'Scheduled' ? 'bg-success' : 'bg-warning text-dark' ?> shadow-sm"><span style="opacity: 0.95;"><?= htmlspecialchars($f['status']) ?></span></span>
                                </div>
                                <div style="position: absolute; bottom: 12px; left: 16px;">
                                    <span class="badge bg-white text-primary border border-primary border-opacity-10 d-inline-flex align-items-center px-2 py-1 shadow-sm fs-6"><i class="bi bi-airplane-fill me-1"></i> Flight <?= htmlspecialchars($f['flight_no']) ?></span>
                                </div>
                            </div>
                            
                            <div class="d-flex flex-column flex-grow-1 p-3 p-md-4">
                                <div class="d-flex justify-content-end mb-3">
                                    <div class="text-end mt-n2">
                                        <div class="text-primary fw-bold" style="font-size: 1.6rem; letter-spacing: -0.02em;">$<?= number_format((float) $f['base_price'], 2) ?></div>
                                        <div class="small fw-semibold text-muted text-uppercase" style="letter-spacing: 0.05em; font-size: 0.7rem;">per passenger</div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="text-center w-100">
                                        <div class="fw-bold text-dark fs-5 mb-0" style="letter-spacing: -1px;"><?= htmlspecialchars($f['origin']) ?></div>
                                        <div class="small text-muted fw-semibold">Origin</div>
                                    </div>
                                    <div class="px-3 text-center w-100" style="color: var(--ofbms-sky);">
                                        <i class="bi bi-airplane" style="font-size: 1.4rem;"></i>
                                        <div class="d-block w-100 border-bottom border-primary border-2 opacity-25 mt-1" style="height: 1px;"></div>
                                    </div>
                                    <div class="text-center w-100">
                                        <div class="fw-bold text-dark fs-5 mb-0" style="letter-spacing: -1px;"><?= htmlspecialchars($f['destination']) ?></div>
                                        <div class="small text-muted fw-semibold">Destination</div>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mb-4 mt-auto border-start border-4 border-primary ps-3 bg-light bg-opacity-50 py-2 rounded-end">
                                    <div class="small w-100">
                                        <span class="text-muted d-block" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Departure</span>
                                        <strong class="text-dark"><i class="bi bi-calendar-event me-2 text-primary"></i><?= htmlspecialchars($f['departure_time']) ?></strong>
                                    </div>
                                    <div class="small w-100 mt-2">
                                        <span class="text-muted d-block" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Aircraft & Seats</span>
                                        <strong class="text-dark"><i class="bi bi-bezier2 me-2 text-info"></i><?= htmlspecialchars($f['aircraft']) ?> <span class="mx-1 text-muted">•</span> <i class="bi bi-people me-1 <?= $seats < 10 ? 'text-danger' : 'text-success' ?>"></i><?= $seats ?> remaining</strong>
                                    </div>
                                </div>
                                
                                <div class="mt-auto pt-3 border-top border-light">
                                    <?php if ($user && $user['role'] === 'passenger' && $bookable): ?>
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
                                                    <input type="number" class="form-control border-0 bg-light text-center" name="seats" value="1" min="1" max="<?= $seats ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <button class="btn btn-primary btn-lg rounded-pill fw-bold w-100 shadow-sm" style="background: linear-gradient(135deg, var(--ofbms-sky) 0%, var(--ofbms-sky-deep) 100%); border: none;" name="book" type="submit" value="1"><i class="bi bi-ticket-perforated me-2"></i> Book Flight</button>
                                            </div>
                                        </form>
                                    <?php elseif (!$user && $bookable): ?>
                                        <a class="btn btn-outline-primary btn-lg rounded-pill fw-bold w-100" href="<?= htmlspecialchars($base) ?>/login.php"><i class="bi bi-box-arrow-in-right me-2"></i> Sign in to book</a>
                                    <?php elseif (!$bookable): ?>
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

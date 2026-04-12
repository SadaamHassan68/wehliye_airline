<?php

declare(strict_types=1);

/** @var string $base */
/** @var array|null $user */

?>
<div class="home-landing">
    <section class="home-hero-wrap mb-5">
        <div class="container">
            <div class="home-hero row g-4 g-xl-5 align-items-center">
                <div class="col-lg-6 order-2 order-lg-1">
                    <p class="home-hero-eyebrow mb-2"><i class="bi bi-patch-check-fill me-1"></i> Premium regional service</p>
                    <h1 class="home-hero-title mb-3">The sky feels closer with <span class="home-gradient-text">Wehliye</span></h1>
                    <p class="home-hero-lead mb-4">Book in minutes, fly with confidence — transparent fares, real-time schedules, and care at every step.</p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php if ($user): ?>
                            <a class="btn btn-light btn-lg rounded-pill px-4 fw-semibold shadow home-hero-cta-primary" href="#flight-search"><i class="bi bi-search me-2"></i>Search flights</a>
                            <a class="btn btn-outline-light btn-lg rounded-pill px-4 home-hero-cta-ghost" href="<?= htmlspecialchars($base) ?>/dashboard.php">Dashboard</a>
                        <?php else: ?>
                            <a class="btn btn-light btn-lg rounded-pill px-4 fw-semibold shadow home-hero-cta-primary" href="<?= htmlspecialchars($base) ?>/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Sign in to book</a>
                            <a class="btn btn-outline-light btn-lg rounded-pill px-4 home-hero-cta-ghost" href="<?= htmlspecialchars($base) ?>/signup.php"><i class="bi bi-person-plus me-2"></i>Create account</a>
                            <a class="btn btn-outline-light btn-lg rounded-pill px-4 home-hero-cta-ghost d-none d-sm-inline-flex" href="#available-flights">Explore</a>
                        <?php endif; ?>
                    </div>
                    <div class="home-hero-metrics row g-3 text-center text-lg-start">
                        <div class="col-4">
                            <div class="home-metric-val">40+</div>
                            <div class="home-metric-label">Routes</div>
                        </div>
                        <div class="col-4">
                            <div class="home-metric-val">4.9</div>
                            <div class="home-metric-label">Guest rating</div>
                        </div>
                        <div class="col-4">
                            <div class="home-metric-val">24/7</div>
                            <div class="home-metric-label">Support</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="home-hero-visual">
                        <div class="home-orbit home-orbit-1"></div>
                        <div class="home-orbit home-orbit-2"></div>
                        <div class="home-ticket-card ofbms-glass-ticket">
                            <div class="home-ticket-top">
                                <span class="home-ticket-airline">Wehliye Airline</span>
                                <span class="home-ticket-class">Economy</span>
                            </div>
                            <div class="home-ticket-route">
                                <div>
                                    <span class="home-ticket-code">NBO</span>
                                    <span class="home-ticket-city">Nairobi</span>
                                </div>
                                <div class="home-ticket-plane"><i class="bi bi-airplane"></i></div>
                                <div class="text-end">
                                    <span class="home-ticket-code">EBB</span>
                                    <span class="home-ticket-city">Kampala</span>
                                </div>
                            </div>
                            <div class="home-ticket-meta">
                                <div><span class="text-white-50 small">Flight</span><br><strong>WH · 101</strong></div>
                                <div><span class="text-white-50 small">Departs</span><br><strong>08:00</strong></div>
                                <div><span class="text-white-50 small">PNR</span><br><strong class="font-monospace">7K2Q9F</strong></div>
                            </div>
                        </div>
                        <div class="home-float-badge home-float-1"><i class="bi bi-lightning-charge-fill text-warning"></i> Fast check-in</div>
                        <div class="home-float-badge home-float-2"><i class="bi bi-shield-lock text-info"></i> Secure pay</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-trust-strip mb-5" aria-label="Highlights">
        <div class="container">
            <div class="row g-3 g-md-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="home-trust-item ofbms-glass-pill"><i class="bi bi-wifi"></i><span>Inflight-ready booking</span></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="home-trust-item ofbms-glass-pill"><i class="bi bi-clock-history"></i><span>Live schedule updates</span></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="home-trust-item ofbms-glass-pill"><i class="bi bi-credit-card-2-front"></i><span>Cards, PayPal &amp; mobile money</span></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="home-trust-item ofbms-glass-pill"><i class="bi bi-recycle"></i><span>Easy changes &amp; refunds</span></div>
                </div>
            </div>
        </div>
    </section>
</div>

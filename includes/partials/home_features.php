<?php

declare(strict_types=1);

/** @var string $base */
/** @var array|null $user */

?>
    <section id="home-features" class="mb-5">
        <div class="text-center mb-4 mb-md-5">
            <h2 class="home-section-title">Everything you need in one place</h2>
            <p class="home-section-sub text-muted mx-auto mb-0">Passengers, agents, and admins each get tools that stay fast and clear.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-xl-4">
                <div class="home-feature-card ofbms-glass-feature h-100">
                    <div class="home-feature-icon home-fi-1"><i class="bi bi-globe2"></i></div>
                    <h3 class="home-feature-title">Routes &amp; aircraft</h3>
                    <p class="home-feature-text">Search by city and date, compare cabin options, and see aircraft capacity at a glance.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="home-feature-card ofbms-glass-feature h-100">
                    <div class="home-feature-icon home-fi-2"><i class="bi bi-ticket-perforated"></i></div>
                    <h3 class="home-feature-title">PNR &amp; e-tickets</h3>
                    <p class="home-feature-text">Unique booking records, digital tickets, and a clear history for every trip.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="home-feature-card ofbms-glass-feature h-100">
                    <div class="home-feature-icon home-fi-3"><i class="bi bi-shield-check"></i></div>
                    <h3 class="home-feature-title">Trusted payments</h3>
                    <p class="home-feature-text">Multiple payment rails with status tracking and structured refund handling.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="home-feature-card ofbms-glass-feature h-100">
                    <div class="home-feature-icon home-fi-4"><i class="bi bi-people"></i></div>
                    <h3 class="home-feature-title">Agents &amp; manifests</h3>
                    <p class="home-feature-text">Operations staff update flight status and support passengers in real time.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="home-feature-card ofbms-glass-feature h-100">
                    <div class="home-feature-icon home-fi-5"><i class="bi bi-graph-up-arrow"></i></div>
                    <h3 class="home-feature-title">Revenue &amp; load factor</h3>
                    <p class="home-feature-text">Dashboards for income by route and seat utilization to guide pricing.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="home-feature-card ofbms-glass-feature h-100">
                    <div class="home-feature-icon home-fi-6"><i class="bi bi-bell"></i></div>
                    <h3 class="home-feature-title">Stay informed</h3>
                    <p class="home-feature-text">Built for reminders and alerts as you connect email or SMS in production.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="home-steps mb-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-4">
                <h2 class="home-section-title mb-2">How it works</h2>
                <p class="text-muted mb-0">Three simple steps from search to boarding pass.</p>
            </div>
            <div class="col-lg-8">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="home-step ofbms-glass-step">
                            <span class="home-step-num">1</span>
                            <h4 class="home-step-title">Search &amp; pick</h4>
                            <p class="home-step-text">Enter origin, destination, and travel date.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="home-step ofbms-glass-step">
                            <span class="home-step-num">2</span>
                            <h4 class="home-step-title">Pay securely</h4>
                            <p class="home-step-text">Choose seats and complete payment in one flow.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="home-step ofbms-glass-step">
                            <span class="home-step-num">3</span>
                            <h4 class="home-step-title">Fly Wehliye</h4>
                            <p class="home-step-text">Get your PNR, manage trips, and check updates anytime.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-cta-band text-center ofbms-glass-cta">
        <div class="home-cta-inner">
            <h2 class="home-cta-title mb-2">Ready for your next journey?</h2>
            <p class="home-cta-sub mb-4">Sign in to search live flights or open your dashboard if you already have an account.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php if ($user): ?>
                    <a class="btn btn-light btn-lg rounded-pill px-4 fw-semibold" href="#flight-search">Search flights</a>
                <?php else: ?>
                    <a class="btn btn-light btn-lg rounded-pill px-4 fw-semibold" href="<?= htmlspecialchars($base) ?>/login.php">Sign in now</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

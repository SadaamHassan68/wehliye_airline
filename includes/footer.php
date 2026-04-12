<?php

declare(strict_types=1);

/** @var bool $adminSidebar */
/** @var string $pageScripts Extra scripts HTML before app.js (e.g. Chart.js + init). */

$adminSidebar = $adminSidebar ?? false;
$pageScripts = $pageScripts ?? '';

?>
<?php if ($adminSidebar): ?>
        </main>
    </div>
</div>
<?php else: ?>
</main>

<footer class="ofbms-footer">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><strong class="text-dark">Wehliye Airline</strong> — Flight booking &amp; operations</span>
        <span><?= date('Y') ?> · Demo project</span>
    </div>
</footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($pageScripts)): ?>
<?= $pageScripts ?>
<?php endif; ?>
<script src="<?= htmlspecialchars(base_url()) ?>/assets/js/app.js"></script>
</body>
</html>

<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$base = base_url();
$user = User::current();

if ($user) {
    if ($user['role'] === 'admin') {
        header('Location: ' . $base . '/admin/dashboard.php');
    } elseif ($user['role'] === 'agent') {
        header('Location: ' . $base . '/staff/manifest.php');
    } else {
        header('Location: ' . $base . '/index.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (User::login(trim((string) ($_POST['email'] ?? '')), (string) ($_POST['password'] ?? ''))) {
        $u = User::current();
        if ($u['role'] === 'admin') {
            header('Location: ' . $base . '/admin/dashboard.php');
        } elseif ($u['role'] === 'agent') {
            header('Location: ' . $base . '/staff/manifest.php');
        } else {
            header('Location: ' . $base . '/index.php');
        }
        exit;
    }
    $error = 'Invalid credentials.';
}

$pageTitle = 'Sign in — Wehliye Airline';
$adminSidebar = false;

require __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-3"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row justify-content-center py-lg-4">
    <div class="col-md-5 col-lg-4">
        <div class="card ofbms-card ofbms-login-card shadow">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="ofbms-feature-icon mx-auto"><i class="bi bi-person-circle"></i></div>
                    <h5 class="card-title mb-1">Welcome back</h5>
                    <p class="text-muted small mb-0">Sign in to manage flights and bookings.</p>
                </div>
                <form method="post" action="<?= htmlspecialchars($base) ?>/login.php">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input class="form-control form-control-lg rounded-3" name="email" type="email" autocomplete="username" required placeholder="you@example.com">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Password</label>
                        <input class="form-control form-control-lg rounded-3" type="password" name="password" autocomplete="current-password" required>
                    </div>
                    <button class="btn btn-primary ofbms-btn-primary w-100 py-2 rounded-3" name="login" type="submit" value="1">Sign in</button>
                </form>
                <p class="text-center text-muted small mt-3 mb-0">New passenger? <a href="<?= htmlspecialchars($base) ?>/signup.php" class="text-decoration-none fw-semibold">Create an account</a></p>
                <p class="text-center text-muted small mt-3 mb-0"><a href="<?= htmlspecialchars($base) ?>/index.php" class="text-decoration-none">← Back to home</a></p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

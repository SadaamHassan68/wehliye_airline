<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$base = base_url();

if (User::current()) {
    header('Location: ' . $base . '/index.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';
    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = User::registerPassenger(
            (string) ($_POST['full_name'] ?? ''),
            (string) ($_POST['email'] ?? ''),
            $password
        );
        if ($result['ok']) {
            $success = true;
        } else {
            $error = $result['error'] ?? 'Registration failed.';
        }
    }
}

$pageTitle = 'Create account — Wehliye Airline';
$adminSidebar = false;
$user = null;

require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-lg-3">
    <div class="col-md-6 col-lg-5">
        <div class="card ofbms-card ofbms-login-card shadow">
            <div class="card-body p-4 p-md-5">
                <?php if ($success): ?>
                    <div class="text-center mb-4">
                        <div class="ofbms-feature-icon mx-auto bg-success-subtle text-success"><i class="bi bi-check-lg"></i></div>
                        <h5 class="card-title mb-1">Account created</h5>
                        <p class="text-muted small mb-0">You can sign in with your email and password.</p>
                    </div>
                    <a class="btn btn-primary ofbms-btn-primary w-100 py-2 rounded-3" href="<?= htmlspecialchars($base) ?>/login.php">Continue to sign in</a>
                <?php else: ?>
                    <div class="text-center mb-4">
                        <div class="ofbms-feature-icon mx-auto"><i class="bi bi-person-plus"></i></div>
                        <h5 class="card-title mb-1">Passenger sign up</h5>
                        <p class="text-muted small mb-0">Create an account to search and book flights.</p>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 rounded-3 small mb-3"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= htmlspecialchars($base) ?>/signup.php">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Full name</label>
                            <input class="form-control form-control-lg rounded-3" name="full_name" required maxlength="120" placeholder="Your name" value="<?= htmlspecialchars((string) ($_POST['full_name'] ?? '')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email</label>
                            <input class="form-control form-control-lg rounded-3" name="email" type="email" autocomplete="email" required maxlength="120" placeholder="you@example.com" value="<?= htmlspecialchars((string) ($_POST['email'] ?? '')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password</label>
                            <input class="form-control form-control-lg rounded-3" type="password" name="password" autocomplete="new-password" required minlength="8" placeholder="At least 8 characters">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Confirm password</label>
                            <input class="form-control form-control-lg rounded-3" type="password" name="password_confirm" autocomplete="new-password" required minlength="8" placeholder="Repeat password">
                        </div>
                        <button class="btn btn-primary ofbms-btn-primary w-100 py-2 rounded-3" name="signup" type="submit" value="1">Create account</button>
                    </form>
                    <p class="text-center text-muted small mt-4 mb-0">
                        Already have an account? <a href="<?= htmlspecialchars($base) ?>/login.php" class="text-decoration-none fw-semibold">Sign in</a>
                    </p>
                <?php endif; ?>
                <p class="text-center text-muted small mt-3 mb-0"><a href="<?= htmlspecialchars($base) ?>/index.php" class="text-decoration-none">← Back to home</a></p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

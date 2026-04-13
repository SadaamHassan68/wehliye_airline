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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Wehliye Airline</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/app.css?v=1.9" rel="stylesheet">
    <style>
        body { background: #fff; min-height: 100vh; display: flex; flex-direction: column; font-family: "DM Sans", sans-serif; margin: 0; padding: 0; overflow-x: hidden; }
        .auth-split { display: flex; min-height: 100vh; }
        .auth-brand { position: absolute; top: 2.5rem; left: 3rem; z-index: 10; display: flex; align-items: center; text-decoration: none; color: #fff; font-weight: 700; font-size: 1.25rem; letter-spacing: -0.02em; }
        .auth-brand .mark { background: linear-gradient(135deg, #7c3aed 0%, #38bdf8 100%); color: #fff; width: 36px; height: 36px; display: inline-flex; justify-content: center; align-items: center; border-radius: 10px; margin-right: 0.75rem; box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4); }
        .auth-panel-img { flex: 1.2; position: relative; background: url('<?= htmlspecialchars($base) ?>/assets/img/hero_bg.png') center/cover no-repeat; display: none; }
        @media (min-width: 992px) { .auth-panel-img { display: block; } }
        .auth-panel-img::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(22, 15, 42, 0.8) 0%, rgba(124, 58, 237, 0.7) 100%); mix-blend-mode: multiply; }
        .auth-panel-img-content { position: absolute; bottom: 4rem; left: 3rem; right: 3rem; z-index: 2; color: #fff; }
        .auth-panel-form { flex: 1; display: flex; flex-direction: column; justify-content: center; position: relative; background: #ffffff; padding: 3rem 2rem; }
        @media (min-width: 992px) { .auth-panel-form { padding: 4rem 5rem; max-width: 650px; } .auth-brand { color: #160f2a; } .auth-brand .mark { box-shadow: 0 4px 15px rgba(124, 58, 237, 0.25); } }
        .form-wrap { max-width: 420px; width: 100%; margin: 0 auto; }
        .auth-title { font-weight: 800; font-size: 2.25rem; letter-spacing: -0.03em; color: #0f172a; margin-bottom: 0.5rem; }
        .auth-subtitle { color: #64748b; font-size: 1rem; margin-bottom: 2.5rem; }
        .pro-input { background: #f8fafc; border: 2px solid transparent; border-radius: 12px; padding: 0.9rem 1.25rem; font-weight: 500; font-size: 1rem; color: #0f172a; transition: all 0.2s ease; }
        .pro-input:focus { background: #ffffff; border-color: #7c3aed; box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1); outline: none; }
        .pro-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; display: block; }
        .pro-btn { background: #1e1540; color: #ffffff; border: none; border-radius: 12px; padding: 1rem; font-weight: 700; font-size: 1rem; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(30, 21, 64, 0.15); display: flex; justify-content: center; align-items: center; width: 100%; }
        .pro-btn:hover { background: #7c3aed; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(124, 58, 237, 0.25); color: #fff; }
        .auth-alert { background: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; border-radius: 12px; padding: 1rem; margin-bottom: 2rem; display: flex; align-items: center; font-weight: 500; }
        .back-link { display: inline-flex; align-items: center; color: #64748b; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: color 0.2s; margin-top: 2rem; }
        .back-link:hover { color: #0f172a; }
    </style>
</head>
<body>

<a href="<?= htmlspecialchars($base) ?>/index.php" class="auth-brand">
    <span class="mark"><i class="bi bi-airplane-fill"></i></span> Wehliye
</a>

<div class="auth-split">
    <div class="auth-panel-img">
        <div class="auth-panel-img-content">
            <h2 class="display-5 fw-bold mb-3" style="text-shadow: 0 2px 10px rgba(0,0,0,0.3);">Where to next?</h2>
            <p class="fs-5 text-white-50 mb-0" style="max-width: 400px; text-shadow: 0 2px 10px rgba(0,0,0,0.3);">Access your bookings, manage your flights, and discover the world with ease.</p>
        </div>
    </div>
    <div class="auth-panel-form">
        <div class="form-wrap">
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Sign in to your account to continue.</p>

            <?php if ($error): ?>
                <div class="auth-alert">
                    <i class="bi bi-exclamation-octagon-fill fs-5 me-3"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($base) ?>/login.php">
                <div class="mb-4">
                    <label class="pro-label">Email address</label>
                    <input type="email" class="form-control pro-input" name="email" placeholder="name@example.com" required autocomplete="username">
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="pro-label mb-0">Password</label>
                        <a href="#" class="text-decoration-none small fw-bold" style="color: #7c3aed;">Forgot?</a>
                    </div>
                    <input type="password" class="form-control pro-input" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <div class="form-check mb-4 mt-2">
                    <input class="form-check-input" type="checkbox" id="rememberMe" style="border-color: #cbd5e1; border-radius: 4px;">
                    <label class="form-check-label text-muted fw-medium small" for="rememberMe">Remember me for 30 days</label>
                </div>
                <button type="submit" name="login" value="1" class="pro-btn">
                    Sign In <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="mt-5 text-center">
                <p class="text-muted fw-medium">Don't have an account? <a href="<?= htmlspecialchars($base) ?>/signup.php" class="fw-bold text-decoration-none" style="color: #7c3aed;">Create account</a></p>
                <a href="<?= htmlspecialchars($base) ?>/index.php" class="back-link"><i class="bi bi-arrow-left me-2"></i> Return to home</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
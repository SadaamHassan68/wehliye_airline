<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

require_roles(['passenger']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['book'])) {
    header('Location: ' . base_url() . '/index.php');
    exit;
}

$user = User::current();
$flightId = (int) ($_POST['flight_id'] ?? 0);
$seats = (int) ($_POST['seats'] ?? 1);
$seatClass = (string) ($_POST['seat_class'] ?? 'Economy');
$paymentMethod = (string) ($_POST['payment_method'] ?? 'CreditCard');
$redirectQs = trim((string) ($_POST['redirect_qs'] ?? ''));

$pnr = Booking::create((int) $user['id'], $flightId, max(1, $seats), $paymentMethod, $seatClass);
if ($pnr) {
    flash_set(
        'success',
        'Booking received. PNR: ' . $pnr . ' — awaiting administrator approval. Revenue is recorded only after approval (payment shows Paid).'
    );
} else {
    flash_set('error', 'Booking failed: insufficient seats or invalid request.');
}

$loc = base_url() . '/index.php';
if ($redirectQs !== '') {
    $loc .= '?' . $redirectQs;
}
header('Location: ' . $loc);
exit;

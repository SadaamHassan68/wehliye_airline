<?php

declare(strict_types=1);

class Booking
{
    public static function generatePnr(): string
    {
        return strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    public static function create(int $userId, int $flightId, int $seats, string $paymentMethod, string $seatClass = 'Economy'): ?string
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $seatsLeft = Flight::availableSeats($flightId);
            if ($seatsLeft < $seats) {
                $pdo->rollBack();
                return null;
            }

            if (!Flight::isBookable($flightId)) {
                $pdo->rollBack();
                return null;
            }

            $priceStmt = $pdo->prepare('SELECT base_price FROM flights WHERE id = :id FOR UPDATE');
            $priceStmt->execute(['id' => $flightId]);
            $basePrice = (float) $priceStmt->fetchColumn();
            
            // Optional: Pricing logic based on seat class could be added here
            $multiplier = 1.0;
            if ($seatClass === 'Business') $multiplier = 1.5;
            if ($seatClass === 'FirstClass') $multiplier = 2.5;
            
            $total = ($basePrice * $multiplier) * $seats;
            $pnr = self::generatePnr();

            /* Revenue counts only after admin sets payment to Paid (see dashboard / route reports). */
            $sql = 'INSERT INTO bookings (pnr, user_id, flight_id, seat_class, seats, total_amount, payment_method, payment_status, status)
                    VALUES (:pnr, :user_id, :flight_id, :seat_class, :seats, :total, :payment_method, "Pending", "Confirmed")';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'pnr' => $pnr,
                'user_id' => $userId,
                'flight_id' => $flightId,
                'seat_class' => $seatClass,
                'seats' => $seats,
                'total' => $total,
                'payment_method' => $paymentMethod,
            ]);

            $pdo->commit();
            return $pnr;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return null;
        }
    }

    public static function history(int $userId): array
    {
        $sql = 'SELECT b.*, f.flight_no, f.origin, f.destination, f.departure_time
                FROM bookings b
                JOIN flights f ON f.id = b.flight_id
                WHERE b.user_id = :uid
                ORDER BY b.created_at DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function findByIdForUser(int $bookingId, int $userId): ?array
    {
        $sql = 'SELECT b.*, f.flight_no, f.origin, f.destination, f.departure_time, f.arrival_time, f.aircraft, f.status AS flight_status,
                       a1.code AS origin_code, a2.code AS destination_code
                FROM bookings b
                JOIN flights f ON f.id = b.flight_id
                JOIN routes r ON r.id = f.route_id
                JOIN airports a1 ON a1.id = r.origin_airport_id
                JOIN airports a2 ON a2.id = r.destination_airport_id
                WHERE b.id = :bid AND b.user_id = :uid
                LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute(['bid' => $bookingId, 'uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function cancel(int $bookingId): bool
    {
        $stmt = db()->prepare('UPDATE bookings SET status = "Cancelled", refund_status = "Pending" WHERE id = :id');
        return $stmt->execute(['id' => $bookingId]);
    }

    /** Permanently remove a booking row (admin only). */
    public static function delete(int $bookingId): bool
    {
        $stmt = db()->prepare('DELETE FROM bookings WHERE id = :id');
        $stmt->execute(['id' => $bookingId]);
        return $stmt->rowCount() > 0;
    }

    /** @param 'Pending'|'Paid'|'Failed'|'Refunded' $paymentStatus */
    public static function setPaymentStatus(int $bookingId, string $paymentStatus): bool
    {
        $allowed = ['Pending', 'Paid', 'Failed', 'Refunded'];
        if (!in_array($paymentStatus, $allowed, true)) {
            return false;
        }
        $stmt = db()->prepare('UPDATE bookings SET payment_status = :ps WHERE id = :id');
        return $stmt->execute(['ps' => $paymentStatus, 'id' => $bookingId]);
    }

    /** Mark payment as received / booking accepted — required before revenue counts as Paid. */
    public static function acceptBooking(int $bookingId): bool
    {
        $stmt = db()->prepare(
            'UPDATE bookings SET payment_status = "Paid" WHERE id = :id AND payment_status = "Pending" AND status != "Cancelled"'
        );
        if (!$stmt->execute(['id' => $bookingId])) {
            return false;
        }
        return $stmt->rowCount() > 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminList(
        ?string $pnr = null,
        ?string $email = null,
        ?string $flightNo = null,
        ?string $status = null,
        ?string $paymentStatus = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $sql = 'SELECT b.*, u.full_name, u.email, f.flight_no, f.origin, f.destination, f.departure_time
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN flights f ON f.id = b.flight_id
                WHERE 1=1';
        $params = [];
        if ($pnr !== null && $pnr !== '') {
            $sql .= ' AND b.pnr LIKE :pnr';
            $params['pnr'] = '%' . $pnr . '%';
        }
        if ($email !== null && $email !== '') {
            $sql .= ' AND u.email LIKE :email';
            $params['email'] = '%' . $email . '%';
        }
        if ($flightNo !== null && $flightNo !== '') {
            $sql .= ' AND f.flight_no LIKE :flight_no';
            $params['flight_no'] = '%' . $flightNo . '%';
        }
        if ($status !== null && $status !== '') {
            $sql .= ' AND b.status = :bstatus';
            $params['bstatus'] = $status;
        }
        if ($paymentStatus !== null && $paymentStatus !== '') {
            $sql .= ' AND b.payment_status = :pstatus';
            $params['pstatus'] = $paymentStatus;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $sql .= ' AND DATE(b.created_at) >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $sql .= ' AND DATE(b.created_at) <= :dt';
            $params['dt'] = $dateTo;
        }
        $sql .= ' ORDER BY b.created_at DESC LIMIT 500';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Passengers on a flight (manifest) */
    public static function forFlight(int $flightId): array
    {
        $sql = 'SELECT b.pnr, b.seats, b.status AS booking_status, b.payment_status, u.full_name, u.email
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                WHERE b.flight_id = :fid AND b.status != "Cancelled"
                ORDER BY u.full_name ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute(['fid' => $flightId]);
        return $stmt->fetchAll();
    }

    public static function totalPaidRevenue(): float
    {
        return (float) db()->query(
            'SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE payment_status = "Paid"'
        )->fetchColumn();
    }

    public static function dashboardStats(): array
    {
        $activeFlights = (int) db()->query("SELECT COUNT(*) FROM flights WHERE status IN ('Scheduled','Boarding','Delayed')")->fetchColumn();
        $totalBookings = (int) db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
        $pendingApproval = (int) db()->query(
            "SELECT COUNT(*) FROM bookings WHERE payment_status = 'Pending' AND status != 'Cancelled'"
        )->fetchColumn();
        $dailyRevenue = (float) db()->query(
            "SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE DATE(created_at) = CURDATE() AND payment_status = 'Paid'"
        )->fetchColumn();

        return [
            'active_flights' => $activeFlights,
            'total_bookings' => $totalBookings,
            'pending_approval' => $pendingApproval,
            'daily_revenue' => $dailyRevenue,
        ];
    }

    public static function routeIncomeReport(): array
    {
        $sql = 'SELECT CONCAT(f.origin, " -> ", f.destination) AS route, COALESCE(SUM(b.total_amount),0) AS income
                FROM flights f
                LEFT JOIN bookings b ON b.flight_id = f.id AND b.payment_status = "Paid"
                GROUP BY f.origin, f.destination
                ORDER BY income DESC';
        return db()->query($sql)->fetchAll();
    }

    public static function loadFactorReport(): array
    {
        $sql = 'SELECT f.flight_no,
                       f.capacity,
                       COALESCE(SUM(CASE WHEN b.status != "Cancelled" THEN b.seats ELSE 0 END), 0) AS sold_seats,
                       ROUND((COALESCE(SUM(CASE WHEN b.status != "Cancelled" THEN b.seats ELSE 0 END),0) / f.capacity) * 100, 2) AS load_factor
                FROM flights f
                LEFT JOIN bookings b ON b.flight_id = f.id
                GROUP BY f.id
                ORDER BY load_factor DESC';
        return db()->query($sql)->fetchAll();
    }

    /**
     * Paid revenue per calendar day for charting (fills missing days with 0).
     *
     * @return array{labels: list<string>, values: list<float>}
     */
    public static function paidRevenueLastDays(int $days): array
    {
        $days = max(1, min(90, $days));
        $interval = $days - 1;
        $stmt = db()->prepare(
            'SELECT DATE(created_at) AS day, COALESCE(SUM(total_amount), 0) AS revenue
             FROM bookings
             WHERE payment_status = "Paid" AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL :iv DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC'
        );
        $stmt->execute(['iv' => $interval]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r['day']] = (float) $r['revenue'];
        }
        $labels = [];
        $values = [];
        $start = strtotime('-' . $interval . ' days', strtotime('today'));
        for ($i = 0; $i < $days; $i++) {
            $ts = strtotime('+' . $i . ' days', $start);
            $key = date('Y-m-d', $ts);
            $labels[] = date('M j', $ts);
            $values[] = $map[$key] ?? 0.0;
        }
        return ['labels' => $labels, 'values' => $values];
    }

    /** @return list<array{status: string, cnt: int|string}> */
    public static function bookingCountsByStatus(): array
    {
        $sql = 'SELECT status, COUNT(*) AS cnt FROM bookings GROUP BY status ORDER BY cnt DESC';
        return db()->query($sql)->fetchAll();
    }

    /** @return list<array{payment_status: string, cnt: int|string}> */
    public static function bookingCountsByPayment(): array
    {
        $sql = 'SELECT payment_status, COUNT(*) AS cnt FROM bookings GROUP BY payment_status ORDER BY cnt DESC';
        return db()->query($sql)->fetchAll();
    }

    /** New bookings per day (any payment), for trend line. @return array{labels: list<string>, values: list<int>} */
    public static function newBookingsLastDays(int $days): array
    {
        $days = max(1, min(90, $days));
        $interval = $days - 1;
        $stmt = db()->prepare(
            'SELECT DATE(created_at) AS day, COUNT(*) AS n
             FROM bookings
             WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL :iv DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC'
        );
        $stmt->execute(['iv' => $interval]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r['day']] = (int) $r['n'];
        }
        $labels = [];
        $values = [];
        $start = strtotime('-' . $interval . ' days', strtotime('today'));
        for ($i = 0; $i < $days; $i++) {
            $ts = strtotime('+' . $i . ' days', $start);
            $key = date('Y-m-d', $ts);
            $labels[] = date('M j', $ts);
            $values[] = $map[$key] ?? 0;
        }
        return ['labels' => $labels, 'values' => $values];
    }
}

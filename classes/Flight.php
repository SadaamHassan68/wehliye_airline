<?php

declare(strict_types=1);

class Flight
{
    /** @return list<string> */
    public static function allowedStatuses(): array
    {
        return ['Scheduled', 'Boarding', 'Delayed', 'Cancelled', 'Completed', 'Landed'];
    }

    public static function search(?string $origin = null, ?string $destination = null, ?string $date = null): array
    {
        $sql = 'SELECT * FROM flights WHERE 1=1';
        $params = [];

        if ($origin) {
            $sql .= ' AND origin LIKE :origin';
            $params['origin'] = '%' . $origin . '%';
        }
        if ($destination) {
            $sql .= ' AND destination LIKE :destination';
            $params['destination'] = '%' . $destination . '%';
        }
        if ($date) {
            $sql .= ' AND DATE(departure_time) = :dep_date';
            $params['dep_date'] = $date;
        }

        $sql .= ' ORDER BY departure_time ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Upcoming flights that passengers can still book (for homepage). */
    public static function upcomingScheduled(int $limit = 12): array
    {
        $limit = max(1, min(50, $limit));
        $sql = "SELECT * FROM flights WHERE status = 'Scheduled' AND departure_time >= NOW() ORDER BY departure_time ASC LIMIT " . $limit;
        return db()->query($sql)->fetchAll();
    }

    public static function isBookable(int $flightId): bool
    {
        $f = self::findById($flightId);
        if (!$f) {
            return false;
        }
        return in_array($f['status'], ['Scheduled', 'Boarding', 'Delayed'], true);
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): bool
    {
        $rid = isset($data['route_id']) ? (int) $data['route_id'] : 0;
        if ($rid > 0) {
            $rd = AirRoute::resolveOriginDestination($rid);
            if ($rd) {
                $data['origin'] = $rd['origin'];
                $data['destination'] = $rd['destination'];
            }
        }
        unset($data['route_id']);

        $sql = 'INSERT INTO flights (flight_no, origin, destination, departure_time, arrival_time, aircraft, capacity, base_price, status, route_id)
                VALUES (:flight_no, :origin, :destination, :departure_time, :arrival_time, :aircraft, :capacity, :base_price, :status, :route_id)';
        $data['route_id'] = $rid > 0 ? $rid : null;
        $stmt = db()->prepare($sql);
        try {
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM flights WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function bookedSeatsExcludingCancelled(int $flightId): int
    {
        $stmt = db()->prepare('SELECT COALESCE(SUM(seats), 0) FROM bookings WHERE flight_id = :id AND status != :st');
        $stmt->execute(['id' => $flightId, 'st' => 'Cancelled']);
        return (int) $stmt->fetchColumn();
    }

    /** @param array<string, mixed> $data */
    public static function update(int $id, array $data): bool
    {
        if ((int) $data['capacity'] < self::bookedSeatsExcludingCancelled($id)) {
            return false;
        }

        $rid = isset($data['route_id']) ? (int) $data['route_id'] : 0;
        if ($rid > 0) {
            $rd = AirRoute::resolveOriginDestination($rid);
            if ($rd) {
                $data['origin'] = $rd['origin'];
                $data['destination'] = $rd['destination'];
            }
        }
        unset($data['route_id']);
        $data['route_id'] = $rid > 0 ? $rid : null;
        $data['id'] = $id;

        $sql = 'UPDATE flights SET flight_no = :flight_no, origin = :origin, destination = :destination, route_id = :route_id,
                departure_time = :departure_time, arrival_time = :arrival_time, aircraft = :aircraft,
                capacity = :capacity, base_price = :base_price, status = :status WHERE id = :id';
        try {
            $stmt = db()->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function delete(int $id): bool
    {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM bookings WHERE flight_id = :id')->execute(['id' => $id]);
            $pdo->prepare('DELETE FROM flights WHERE id = :id')->execute(['id' => $id]);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public static function updateStatus(int $flightId, string $status): bool
    {
        if (!in_array($status, self::allowedStatuses(), true)) {
            return false;
        }
        $stmt = db()->prepare('UPDATE flights SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $flightId]);
    }

    public static function availableSeats(int $flightId): int
    {
        $stmt = db()->prepare('SELECT capacity - COALESCE((SELECT SUM(seats) FROM bookings WHERE flight_id = :id AND status != "Cancelled"),0) AS seats_left FROM flights WHERE id = :id');
        $stmt->execute(['id' => $flightId]);
        return (int) ($stmt->fetchColumn() ?? 0);
    }
}

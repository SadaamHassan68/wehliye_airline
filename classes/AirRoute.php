<?php

declare(strict_types=1);

class AirRoute
{
    /** @return array<int, array<string, mixed>> */
    public static function allWithLabels(): array
    {
        $sql = 'SELECT r.id, r.origin_airport_id, r.destination_airport_id,
                r.flight_no, r.airline, r.base_price,
                o.code AS origin_code, o.city AS origin_city, o.name AS origin_name,
                d.code AS dest_code, d.city AS dest_city, d.name AS dest_name
                FROM routes r
                JOIN airports o ON o.id = r.origin_airport_id
                JOIN airports d ON d.id = r.destination_airport_id
                ORDER BY o.city ASC, d.city ASC';
        return db()->query($sql)->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function findById(int $id): ?array
    {
        $sql = 'SELECT r.id, r.origin_airport_id, r.destination_airport_id,
                r.flight_no, r.airline, r.base_price,
                o.code AS origin_code, o.city AS origin_city, o.name AS origin_name,
                d.code AS dest_code, d.city AS dest_city, d.name AS dest_name
                FROM routes r
                JOIN airports o ON o.id = r.origin_airport_id
                JOIN airports d ON d.id = r.destination_airport_id
                WHERE r.id = :id';
        $stmt = db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $originAirportId, int $destinationAirportId, ?string $flightNo = null, ?string $airline = null, ?float $basePrice = null): bool
    {
        if ($originAirportId === $destinationAirportId) {
            return false;
        }
        $fn = $flightNo !== null && trim($flightNo) !== '' ? trim($flightNo) : null;
        $al = $airline !== null && trim($airline) !== '' ? trim($airline) : null;
        $bp = $basePrice !== null && $basePrice > 0 ? $basePrice : null;
        try {
            $stmt = db()->prepare(
                'INSERT INTO routes (origin_airport_id, destination_airport_id, flight_no, airline, base_price) VALUES (:o, :d, :fn, :al, :bp)'
            );
            return $stmt->execute([
                'o' => $originAirportId,
                'd' => $destinationAirportId,
                'fn' => $fn,
                'al' => $al,
                'bp' => $bp,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function updateDefaults(int $id, ?string $flightNo, ?string $airline, ?float $basePrice): bool
    {
        $fn = $flightNo !== null && trim($flightNo) !== '' ? trim($flightNo) : null;
        $al = $airline !== null && trim($airline) !== '' ? trim($airline) : null;
        $bp = $basePrice !== null && $basePrice > 0 ? $basePrice : null;
        $stmt = db()->prepare('UPDATE routes SET flight_no = :fn, airline = :al, base_price = :bp WHERE id = :id');
        return $stmt->execute(['id' => $id, 'fn' => $fn, 'al' => $al, 'bp' => $bp]);
    }

    public static function delete(int $id): bool
    {
        $pdo = db();
        $c = $pdo->prepare('SELECT COUNT(*) FROM flights WHERE route_id = :id');
        $c->execute(['id' => $id]);
        if ((int) $c->fetchColumn() > 0) {
            return false;
        }
        $stmt = $pdo->prepare('DELETE FROM routes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /** @return array{origin: string, destination: string}|null */
    public static function resolveOriginDestination(int $routeId): ?array
    {
        $sql = 'SELECT o.city AS origin, d.city AS destination
                FROM routes r
                JOIN airports o ON o.id = r.origin_airport_id
                JOIN airports d ON d.id = r.destination_airport_id
                WHERE r.id = :id';
        $stmt = db()->prepare($sql);
        $stmt->execute(['id' => $routeId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

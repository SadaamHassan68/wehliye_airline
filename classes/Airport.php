<?php

declare(strict_types=1);

class Airport
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        $stmt = db()->query('SELECT * FROM airports ORDER BY country ASC, city ASC, code ASC');
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM airports WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $code, string $name, string $city, string $country): bool
    {
        $stmt = db()->prepare('INSERT INTO airports (code, name, city, country) VALUES (:code, :name, :city, :country)');
        try {
            return $stmt->execute([
                'code' => strtoupper(trim($code)),
                'name' => trim($name),
                'city' => trim($city),
                'country' => trim($country),
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function delete(int $id): bool
    {
        $pdo = db();
        $c = $pdo->prepare('SELECT COUNT(*) FROM routes WHERE origin_airport_id = :id OR destination_airport_id = :id');
        $c->execute(['id' => $id]);
        if ((int) $c->fetchColumn() > 0) {
            return false;
        }
        $stmt = $pdo->prepare('DELETE FROM airports WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}

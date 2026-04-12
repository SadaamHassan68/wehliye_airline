<?php

declare(strict_types=1);

class User
{
    public static function current(): ?array
    {
        if (!isset($_SESSION['user'])) {
            return null;
        }
        return $_SESSION['user'];
    }

    public static function login(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        $sql = 'SELECT id, full_name, email, password_hash, role FROM users WHERE LOWER(email) = :email LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /** @return array{ok: bool, error: ?string} */
    public static function registerPassenger(string $fullName, string $email, string $password): array
    {
        $fullName = trim($fullName);
        $email = strtolower(trim($email));

        $nameLen = function_exists('mb_strlen') ? mb_strlen($fullName) : strlen($fullName);
        if ($nameLen < 2) {
            return ['ok' => false, 'error' => 'Please enter your full name.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Please enter a valid email address.'];
        }
        if (strlen($password) < 8) {
            return ['ok' => false, 'error' => 'Password must be at least 8 characters.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO users (full_name, email, password_hash, role) VALUES (:name, :email, :hash, "passenger")';

        try {
            $stmt = db()->prepare($sql);
            $stmt->execute(['name' => $fullName, 'email' => $email, 'hash' => $hash]);
        } catch (PDOException $e) {
            $code = $e->getCode();
            if ($code === '23000' || $code === 23000 || str_contains($e->getMessage(), 'Duplicate')) {
                return ['ok' => false, 'error' => 'An account with this email already exists.'];
            }
            return ['ok' => false, 'error' => 'Registration failed. Please try again.'];
        }

        return ['ok' => true, 'error' => null];
    }

    /** @return array<int, array<string, mixed>> */
    public static function adminList(): array
    {
        return db()->query('SELECT id, full_name, email, role, loyalty_points, created_at FROM users ORDER BY id ASC')->fetchAll();
    }

    public static function adminSetRole(int $id, string $role): bool
    {
        if (!in_array($role, ['admin', 'passenger', 'agent'], true)) {
            return false;
        }
        $stmt = db()->prepare('UPDATE users SET role = :r WHERE id = :id');
        return $stmt->execute(['r' => $role, 'id' => $id]);
    }

    public static function adminDelete(int $id): bool
    {
        $current = self::current();
        if ($current && (int) $current['id'] === $id) {
            return false;
        }
        $pdo = db();
        $c = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = :id');
        $c->execute(['id' => $id]);
        if ((int) $c->fetchColumn() > 0) {
            return false;
        }
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}

<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';

spl_autoload_register(static function (string $class): void {
    $path = dirname(__DIR__) . '/classes/' . $class . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

function base_url(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $projectRoot = realpath(dirname(__DIR__));
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($projectRoot && $docRoot && str_starts_with($projectRoot, $docRoot)) {
        $rel = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
        $cached = $rel === '' ? '' : rtrim($rel, '/');
        return $cached;
    }
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $cached = rtrim(dirname($script), '/');
    if ($cached === '\\' || $cached === '.') {
        $cached = '';
    }
    return $cached;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** @return array{type: string, message: string}|null */
function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function require_login(): void
{
    if (!User::current()) {
        header('Location: ' . base_url() . '/login.php');
        exit;
    }
}

function require_roles(array $roles): void
{
    require_login();
    $user = User::current();
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Forbidden: insufficient permission.');
    }
}

<?php

declare(strict_types=1);

use App\Core\Session;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed
{
    static $old = null;
    $old ??= Session::pullFlash('old', []);
    return $old[$key] ?? $default;
}

function error(string $key): ?string
{
    static $errors = null;
    $errors ??= Session::pullFlash('errors', []);
    return $errors[$key] ?? null;
}

function flash(string $key): mixed
{
    return Session::pullFlash($key);
}

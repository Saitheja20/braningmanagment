<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public static function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = self::input($key);
        }

        return $data;
    }

    public static function ip(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public static function userAgent(): ?string
    {
        return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255) ?: null;
    }
}

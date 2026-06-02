<?php

declare(strict_types=1);

namespace App\Core;

final class Cache
{
    public static function remember(string $key, int $seconds, callable $callback): mixed
    {
        $path = self::path($key);

        if (is_file($path) && filemtime($path) + $seconds > time()) {
            return unserialize((string) file_get_contents($path));
        }

        $value = $callback();
        file_put_contents($path, serialize($value), LOCK_EX);
        return $value;
    }

    public static function forget(string $key): void
    {
        $path = self::path($key);
        if (is_file($path)) {
            unlink($path);
        }
    }

    private static function path(string $key): string
    {
        $directory = dirname(__DIR__, 2) . '/storage/cache';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory . '/' . hash('sha256', $key) . '.cache';
    }
}

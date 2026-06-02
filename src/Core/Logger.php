<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Logger
{
    public static function error(Throwable|string $error): void
    {
        $message = $error instanceof Throwable
            ? $error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine()
            : $error;

        $directory = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($directory . '/app.log', '[' . date(DATE_ATOM) . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}

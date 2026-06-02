<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    public static function redirect(string $path): never
    {
        header('Location: ' . $path, true, 302);
        exit;
    }

    public static function abort(int $statusCode = 404, string $message = 'Not found'): never
    {
        http_response_code($statusCode);
        View::render('errors/error', [
            'title' => $statusCode,
            'message' => $message,
        ]);
        exit;
    }
}

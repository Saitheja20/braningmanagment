<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf_token', $token);
        }

        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function validate(?string $token): bool
    {
        return is_string($token) && hash_equals((string) Session::get('_csrf_token'), $token);
    }
}

<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function login(array $data): array
    {
        $errors = [];

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if (strlen((string) ($data['password'] ?? '')) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        return $errors;
    }

    public static function register(array $data): array
    {
        $errors = self::login($data);

        if (trim((string) ($data['name'] ?? '')) === '') {
            $errors['name'] = 'Name is required.';
        }

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        return $errors;
    }

    public static function forgotPassword(array $data): array
    {
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            return ['email' => 'Enter a valid email address.'];
        }

        return [];
    }

    public static function resetPassword(array $data): array
    {
        $errors = [];

        if (strlen((string) ($data['password'] ?? '')) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        if (trim((string) ($data['token'] ?? '')) === '') {
            $errors['token'] = 'Reset token is missing.';
        }

        return $errors;
    }
}

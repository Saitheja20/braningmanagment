<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;

final class AuthService
{
    public static function user(): ?array
    {
        $userId = Session::get('user_id');
        return $userId ? User::findById((int) $userId) : null;
    }

    public static function check(): bool
    {
        return self::user() !== null && self::currentSessionIsValid();
    }

    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        $user = User::findByEmail($email);

        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        self::loginUser($user, $remember);
        return true;
    }

    public static function loginUser(array $user, bool $remember = false): void
    {
        Session::regenerate();
        Session::put('user_id', (int) $user['id']);
        Session::put('permissions', User::permissions((int) $user['id']));

        User::updateLastLogin((int) $user['id']);
        self::persistSession((int) $user['id']);

        if ($remember) {
            self::remember((int) $user['id']);
        }
    }

    public static function logout(): void
    {
        self::deleteCurrentSession();
        self::forgetRememberToken();
        Session::destroy();
    }

    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }

        return in_array($permission, Session::get('permissions', []), true);
    }

    public static function bootRememberedUser(): void
    {
        if (Session::get('user_id') || empty($_COOKIE[self::rememberCookieName()])) {
            return;
        }

        [$selector, $validator] = array_pad(explode(':', (string) $_COOKIE[self::rememberCookieName()], 2), 2, null);

        if (!$selector || !$validator) {
            self::clearRememberCookie();
            return;
        }

        $stmt = Database::connection()->prepare(
            'SELECT * FROM remember_tokens WHERE selector = :selector AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute(['selector' => $selector]);
        $token = $stmt->fetch();

        if (!$token || !hash_equals($token['token_hash'], hash('sha256', $validator))) {
            self::clearRememberCookie();
            return;
        }

        $user = User::findById((int) $token['user_id']);

        if (!$user || $user['status'] !== 'active') {
            self::clearRememberCookie();
            return;
        }

        self::loginUser($user, false);
        self::rotateRememberToken((int) $token['id'], (int) $user['id']);
    }

    public static function createPasswordReset(string $email): ?string
    {
        $user = User::findByEmail($email);

        if (!$user || $user['status'] !== 'active') {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $stmt = Database::connection()->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 60 MINUTE))'
        );
        $stmt->execute([
            'user_id' => $user['id'],
            'token_hash' => hash('sha256', $token),
        ]);

        return $token;
    }

    public static function resetPassword(string $token, string $password): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM password_resets
             WHERE token_hash = :token_hash AND expires_at > NOW() AND used_at IS NULL
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['token_hash' => hash('sha256', $token)]);
        $reset = $stmt->fetch();

        if (!$reset) {
            return false;
        }

        User::updatePassword((int) $reset['user_id'], $password);

        $stmt = Database::connection()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $reset['id']]);

        $stmt = Database::connection()->prepare('DELETE FROM remember_tokens WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $reset['user_id']]);

        return true;
    }

    private static function persistSession(int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO user_sessions (user_id, session_id_hash, ip_address, user_agent, expires_at, last_activity_at)
             VALUES (:user_id, :hash, :ip, :user_agent, DATE_ADD(NOW(), INTERVAL 8 HOUR), NOW())
             ON DUPLICATE KEY UPDATE last_activity_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 8 HOUR)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'hash' => hash('sha256', session_id()),
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    private static function currentSessionIsValid(): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT id
             FROM user_sessions
             WHERE session_id_hash = :hash AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['hash' => hash('sha256', session_id())]);

        if (!$stmt->fetch()) {
            return false;
        }

        $stmt = Database::connection()->prepare(
            'UPDATE user_sessions
             SET last_activity_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 8 HOUR)
             WHERE session_id_hash = :hash'
        );
        $stmt->execute(['hash' => hash('sha256', session_id())]);

        return true;
    }

    private static function deleteCurrentSession(): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM user_sessions WHERE session_id_hash = :hash');
        $stmt->execute(['hash' => hash('sha256', session_id())]);
    }

    private static function remember(int $userId): void
    {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));

        $stmt = Database::connection()->prepare(
            'INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
             VALUES (:user_id, :selector, :token_hash, DATE_ADD(NOW(), INTERVAL 30 DAY))'
        );
        $stmt->execute([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => hash('sha256', $validator),
        ]);

        self::setRememberCookie($selector . ':' . $validator);
    }

    private static function rotateRememberToken(int $tokenId, int $userId): void
    {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));

        $stmt = Database::connection()->prepare(
            'UPDATE remember_tokens
             SET selector = :selector, token_hash = :token_hash, last_used_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'selector' => $selector,
            'token_hash' => hash('sha256', $validator),
            'id' => $tokenId,
            'user_id' => $userId,
        ]);

        self::setRememberCookie($selector . ':' . $validator);
    }

    private static function forgetRememberToken(): void
    {
        if (empty($_COOKIE[self::rememberCookieName()])) {
            return;
        }

        [$selector] = explode(':', (string) $_COOKIE[self::rememberCookieName()], 2);
        $stmt = Database::connection()->prepare('DELETE FROM remember_tokens WHERE selector = :selector');
        $stmt->execute(['selector' => $selector]);
        self::clearRememberCookie();
    }

    private static function rememberCookieName(): string
    {
        return (string) Config::get('REMEMBER_COOKIE', 'branding_pm_remember');
    }

    private static function setRememberCookie(string $value): void
    {
        setcookie(self::rememberCookieName(), $value, [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function clearRememberCookie(): void
    {
        setcookie(self::rememberCookieName(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

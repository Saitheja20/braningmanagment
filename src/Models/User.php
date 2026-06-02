<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT users.*, roles.slug AS role_slug, roles.name AS role_name
             FROM users
             JOIN roles ON roles.id = users.role_id
             WHERE users.id = :id AND users.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT users.*, roles.slug AS role_slug, roles.name AS role_name
             FROM users
             JOIN roles ON roles.id = users.role_id
             WHERE users.email = :email AND users.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['email' => strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO users (role_id, name, email, password_hash, phone, status)
             VALUES (:role_id, :name, :email, :password_hash, :phone, :status)'
        );
        $stmt->execute([
            'role_id' => $data['role_id'],
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'phone' => $data['phone'] ?? null,
            'status' => 'active',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function defaultRegistrationRoleId(): int
    {
        $stmt = Database::connection()->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => 'client']);
        $roleId = $stmt->fetchColumn();

        if ($roleId) {
            return (int) $roleId;
        }

        $stmt = Database::connection()->query('SELECT id FROM roles ORDER BY id LIMIT 1');
        return (int) $stmt->fetchColumn();
    }

    public static function updateLastLogin(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute([
            'hash' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
    }

    public static function permissions(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT permissions.slug
             FROM users
             JOIN role_permissions ON role_permissions.role_id = users.role_id
             JOIN permissions ON permissions.id = role_permissions.permission_id
             WHERE users.id = :id'
        );
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

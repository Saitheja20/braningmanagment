<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Realtime
{
    public static function snapshot(int $userId, int $afterId = 0): array
    {
        return [
            'notifications' => self::notifications($userId),
            'messages' => self::messages($userId, $afterId),
            'activities' => self::activities($afterId),
            'tasks' => self::taskUpdates(),
            'unread_count' => self::unreadCount($userId),
            'server_time' => date(DATE_ATOM),
        ];
    }

    public static function sendMessage(?int $senderId, ?int $receiverId, string $body): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO message_threads (subject, created_by) VALUES (:subject, :created_by)');
        $stmt->execute(['subject' => 'Direct message', 'created_by' => $senderId]);
        $threadId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'INSERT INTO messages (thread_id, sender_id, receiver_id, body)
             VALUES (:thread_id, :sender_id, :receiver_id, :body)'
        );
        $stmt->execute([
            'thread_id' => $threadId,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'body' => trim($body),
        ]);

        if ($receiverId) {
            self::notify($receiverId, 'message.received', 'New internal message', trim($body), '/realtime');
        }

        return self::message((int) $pdo->lastInsertId()) ?: [];
    }

    public static function markNotificationRead(int $id, int $userId): bool
    {
        $stmt = Database::connection()->prepare('UPDATE notifications SET read_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function users(): array
    {
        $stmt = Database::connection()->query('SELECT id, name FROM users WHERE deleted_at IS NULL AND status = "active" ORDER BY name');
        return $stmt->fetchAll();
    }

    private static function notifications(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT 10'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    private static function unreadCount(int $userId): int
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND read_at IS NULL');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    private static function messages(int $userId, int $afterId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT messages.*, sender.name AS sender_name, receiver.name AS receiver_name
             FROM messages
             LEFT JOIN users sender ON sender.id = messages.sender_id
             LEFT JOIN users receiver ON receiver.id = messages.receiver_id
             WHERE messages.id > :after_id
               AND (messages.sender_id = :user_id OR messages.receiver_id = :user_id OR messages.receiver_id IS NULL)
             ORDER BY messages.id DESC
             LIMIT 15'
        );
        $stmt->execute(['after_id' => $afterId, 'user_id' => $userId]);
        return $stmt->fetchAll();
    }

    private static function message(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT messages.*, sender.name AS sender_name
             FROM messages
             LEFT JOIN users sender ON sender.id = messages.sender_id
             WHERE messages.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private static function activities(int $afterId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT activities.*, users.name AS user_name
             FROM activities
             LEFT JOIN users ON users.id = activities.user_id
             WHERE activities.id > :after_id
             ORDER BY activities.id DESC
             LIMIT 20'
        );
        $stmt->execute(['after_id' => $afterId]);
        return $stmt->fetchAll();
    }

    private static function taskUpdates(): array
    {
        $stmt = Database::connection()->query(
            'SELECT tasks.id, tasks.title, tasks.status, tasks.priority, tasks.progress_percent, tasks.updated_at
             FROM tasks
             WHERE tasks.deleted_at IS NULL
             ORDER BY tasks.updated_at DESC
             LIMIT 12'
        );
        return $stmt->fetchAll();
    }

    private static function notify(int $userId, string $type, string $title, string $body, string $url): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO notifications (user_id, type, title, body, action_url)
             VALUES (:user_id, :type, :title, :body, :action_url)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $url,
        ]);
    }
}

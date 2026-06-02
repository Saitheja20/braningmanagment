<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ClientPortal
{
    public static function clientForUser(array $user): ?array
    {
        if (!empty($user['client_id'])) {
            $stmt = Database::connection()->prepare('SELECT * FROM clients WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $user['client_id']]);
            return $stmt->fetch() ?: null;
        }

        $stmt = Database::connection()->prepare('SELECT * FROM clients WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $user['email'] ?? '']);
        return $stmt->fetch() ?: null;
    }

    public static function dashboard(int $clientId): array
    {
        return [
            'projects' => self::projects($clientId),
            'approvals' => self::approvals($clientId),
            'files' => self::files($clientId),
            'invoices' => self::invoices($clientId),
        ];
    }

    public static function decideApproval(int $approvalId, int $clientId, int $userId, string $status, string $feedback): bool
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            return false;
        }

        $stmt = Database::connection()->prepare(
            'UPDATE design_approvals
             SET status = :status, feedback = :feedback, decided_by = :user_id, decided_at = NOW()
             WHERE id = :id AND client_id = :client_id'
        );
        $stmt->execute([
            'status' => $status,
            'feedback' => trim($feedback) ?: null,
            'user_id' => $userId,
            'id' => $approvalId,
            'client_id' => $clientId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function sendFeedback(int $clientId, int $userId, int $projectId, string $message): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO message_threads (project_id, client_id, subject, created_by) VALUES (:project_id, :client_id, "Client feedback", :created_by)');
        $stmt->execute(['project_id' => $projectId, 'client_id' => $clientId, 'created_by' => $userId]);

        $stmt = $pdo->prepare('INSERT INTO messages (thread_id, sender_id, body) VALUES (:thread_id, :sender_id, :body)');
        $stmt->execute([
            'thread_id' => (int) $pdo->lastInsertId(),
            'sender_id' => $userId,
            'body' => trim($message),
        ]);
    }

    private static function projects(int $clientId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT projects.*,
                    COUNT(tasks.id) AS total_tasks,
                    SUM(tasks.status = "completed") AS completed_tasks
             FROM projects
             LEFT JOIN tasks ON tasks.project_id = projects.id AND tasks.deleted_at IS NULL
             WHERE projects.client_id = :client_id AND projects.deleted_at IS NULL
             GROUP BY projects.id
             ORDER BY projects.due_date IS NULL, projects.due_date ASC'
        );
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    private static function approvals(int $clientId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT design_approvals.*, projects.name AS project_name
             FROM design_approvals
             JOIN projects ON projects.id = design_approvals.project_id
             WHERE design_approvals.client_id = :client_id
             ORDER BY design_approvals.created_at DESC'
        );
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    private static function files(int $clientId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT task_attachments.*, tasks.title AS task_title, projects.name AS project_name
             FROM task_attachments
             JOIN tasks ON tasks.id = task_attachments.task_id
             JOIN projects ON projects.id = tasks.project_id
             WHERE projects.client_id = :client_id AND task_attachments.deleted_at IS NULL
             ORDER BY task_attachments.created_at DESC
             LIMIT 30'
        );
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    private static function invoices(int $clientId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM invoices WHERE client_id = :client_id ORDER BY due_date DESC');
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }
}

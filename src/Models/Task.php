<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Config;
use App\Core\Database;
use PDO;

final class Task
{
    public const STATUSES = ['todo', 'in_progress', 'review', 'completed'];
    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    public static function all(array $filters = []): array
    {
        $where = ['tasks.deleted_at IS NULL', 'projects.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'tasks.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['assigned_to'])) {
            $where[] = 'tasks.assigned_to = :assigned_to';
            $params['assigned_to'] = (int) $filters['assigned_to'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(tasks.title LIKE :search OR projects.name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $stmt = Database::connection()->prepare(
            'SELECT
                tasks.*,
                projects.name AS project_name,
                users.name AS assignee_name,
                GROUP_CONCAT(DISTINCT task_labels.name ORDER BY task_labels.name SEPARATOR ", ") AS label_names,
                COUNT(DISTINCT task_checklist_items.id) AS checklist_total,
                SUM(CASE WHEN task_checklist_items.is_completed = 1 THEN 1 ELSE 0 END) AS checklist_done,
                COUNT(DISTINCT task_attachments.id) AS attachment_count,
                COUNT(DISTINCT task_comments.id) AS comment_count
             FROM tasks
             JOIN projects ON projects.id = tasks.project_id
             LEFT JOIN users ON users.id = tasks.assigned_to
             LEFT JOIN task_label_map ON task_label_map.task_id = tasks.id
             LEFT JOIN task_labels ON task_labels.id = task_label_map.label_id
             LEFT JOIN task_checklist_items ON task_checklist_items.task_id = tasks.id
             LEFT JOIN task_attachments ON task_attachments.task_id = tasks.id AND task_attachments.deleted_at IS NULL
             LEFT JOIN task_comments ON task_comments.task_id = tasks.id AND task_comments.deleted_at IS NULL
             WHERE ' . implode(' AND ', $where) . '
             GROUP BY tasks.id
             ORDER BY
                FIELD(tasks.priority, "urgent", "high", "medium", "low"),
                tasks.due_date IS NULL,
                tasks.due_date ASC,
                tasks.updated_at DESC'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT tasks.*, projects.name AS project_name, users.name AS assignee_name
             FROM tasks
             JOIN projects ON projects.id = tasks.project_id
             LEFT JOIN users ON users.id = tasks.assigned_to
             WHERE tasks.id = :id AND tasks.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();

        if (!$task) {
            return null;
        }

        $task['labels'] = self::labelsForTask($id);
        $task['comments'] = self::comments($id);
        $task['checklist'] = self::checklist($id);
        $task['attachments'] = self::attachments($id);

        return $task;
    }

    public static function update(int $id, array $data, array $labelIds): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'UPDATE tasks
             SET project_id = :project_id,
                 assigned_to = :assigned_to,
                 title = :title,
                 description = :description,
                 status = :status,
                 priority = :priority,
                 estimated_hours = :estimated_hours,
                 start_date = :start_date,
                 due_date = :due_date,
                 completed_at = CASE WHEN :status_completed = "completed" THEN COALESCE(completed_at, NOW()) ELSE NULL END
             WHERE id = :id AND deleted_at IS NULL'
        );
        $payload = self::payload($data);
        $payload['status_completed'] = $payload['status'];
        $payload['id'] = $id;
        $stmt->execute($payload);

        self::syncLabels($id, $labelIds);
        self::notifyAssignment($id, self::nullableInt($data['assigned_to'] ?? null), (string) $data['title']);
        $pdo->commit();
    }

    public static function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $stmt = Database::connection()->prepare(
            'UPDATE tasks
             SET status = :status,
                 completed_at = CASE WHEN :status_completed = "completed" THEN COALESCE(completed_at, NOW()) ELSE NULL END,
                 progress_percent = CASE WHEN :status_progress = "completed" THEN 100 ELSE progress_percent END
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([
            'status' => $status,
            'status_completed' => $status,
            'status_progress' => $status,
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function updatePriority(int $id, string $priority): bool
    {
        if (!in_array($priority, self::PRIORITIES, true)) {
            return false;
        }

        $stmt = Database::connection()->prepare('UPDATE tasks SET priority = :priority WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['priority' => $priority, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function addComment(int $taskId, ?int $userId, string $comment): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO task_comments (task_id, user_id, comment) VALUES (:task_id, :user_id, :comment)');
        $stmt->execute([
            'task_id' => $taskId,
            'user_id' => $userId,
            'comment' => trim($comment),
        ]);

        $commentId = (int) $pdo->lastInsertId();
        self::notifyTaskFollowers($taskId, 'task.comment', 'New task comment', 'A comment was added to a task.');
        return self::comment($commentId) ?: [];
    }

    public static function addChecklistItem(int $taskId, string $title): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO task_checklist_items (task_id, title) VALUES (:task_id, :title)');
        $stmt->execute(['task_id' => $taskId, 'title' => trim($title)]);
        self::recalculateProgress($taskId);

        return self::checklistItem((int) $pdo->lastInsertId()) ?: [];
    }

    public static function toggleChecklistItem(int $id, bool $completed, ?int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'UPDATE task_checklist_items
             SET is_completed = :completed,
                 completed_by = CASE WHEN :completed_by_flag = 1 THEN :user_id ELSE NULL END,
                 completed_at = CASE WHEN :completed_at_flag = 1 THEN NOW() ELSE NULL END
             WHERE id = :id'
        );
        $stmt->execute([
            'completed' => $completed ? 1 : 0,
            'completed_by_flag' => $completed ? 1 : 0,
            'completed_at_flag' => $completed ? 1 : 0,
            'user_id' => $userId,
            'id' => $id,
        ]);

        $item = self::checklistItem($id);
        $progress = $item ? self::recalculateProgress((int) $item['task_id']) : 0;

        return ['item' => $item, 'progress' => $progress];
    }

    public static function addAttachment(int $taskId, ?int $userId, array $file): ?array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $original = basename((string) $file['name']);
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip'];
        $maxBytes = ((int) Config::get('MAX_UPLOAD_MB', 10)) * 1024 * 1024;

        if (!in_array($extension, $allowedExtensions, true) || (int) ($file['size'] ?? 0) > $maxBytes) {
            return null;
        }

        $detectedMime = 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, (string) $file['tmp_name']) ?: $detectedMime;
            finfo_close($finfo);
        }

        $stored = 'task-' . $taskId . '-' . bin2hex(random_bytes(12)) . ($extension ? '.' . $extension : '');
        $directory = dirname(__DIR__, 2) . '/storage/uploads/tasks/' . $taskId;

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $target = $directory . '/' . $stored;

        if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
            return null;
        }

        $relativePath = 'storage/uploads/tasks/' . $taskId . '/' . $stored;
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO task_attachments
                (task_id, uploaded_by, original_name, stored_name, mime_type, size_bytes, storage_path)
             VALUES
                (:task_id, :uploaded_by, :original_name, :stored_name, :mime_type, :size_bytes, :storage_path)'
        );
        $stmt->execute([
            'task_id' => $taskId,
            'uploaded_by' => $userId,
            'original_name' => $original,
            'stored_name' => $stored,
            'mime_type' => $detectedMime,
            'size_bytes' => (int) ($file['size'] ?? 0),
            'storage_path' => $relativePath,
        ]);

        self::notifyTaskFollowers($taskId, 'task.attachment', 'New task attachment', $original . ' was uploaded.');
        return self::attachment((int) $pdo->lastInsertId());
    }

    public static function labels(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM task_labels ORDER BY name');
        return $stmt->fetchAll();
    }

    public static function projects(): array
    {
        $stmt = Database::connection()->query('SELECT id, name FROM projects WHERE deleted_at IS NULL ORDER BY name');
        return $stmt->fetchAll();
    }

    public static function employees(): array
    {
        return Project::employees();
    }

    public static function stats(): array
    {
        $stmt = Database::connection()->query(
            'SELECT
                COUNT(*) AS total,
                SUM(status = "completed") AS completed,
                SUM(due_date < CURDATE() AND status != "completed") AS overdue,
                AVG(progress_percent) AS avg_progress
             FROM tasks
             WHERE deleted_at IS NULL'
        );
        return $stmt->fetch() ?: [];
    }

    private static function syncLabels(int $taskId, array $labelIds): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM task_label_map WHERE task_id = :task_id');
        $stmt->execute(['task_id' => $taskId]);

        $stmt = $pdo->prepare('INSERT INTO task_label_map (task_id, label_id) VALUES (:task_id, :label_id)');

        foreach (array_unique(array_filter(array_map('intval', $labelIds))) as $labelId) {
            $stmt->execute(['task_id' => $taskId, 'label_id' => $labelId]);
        }
    }

    private static function recalculateProgress(int $taskId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) AS total, SUM(is_completed = 1) AS done
             FROM task_checklist_items
             WHERE task_id = :task_id'
        );
        $stmt->execute(['task_id' => $taskId]);
        $row = $stmt->fetch() ?: ['total' => 0, 'done' => 0];
        $progress = (int) $row['total'] > 0 ? (int) round(((int) $row['done'] / (int) $row['total']) * 100) : 0;

        $stmt = Database::connection()->prepare(
            'UPDATE tasks
             SET progress_percent = :progress,
                 status = CASE WHEN :progress_completed = 100 THEN "completed" ELSE status END,
                 completed_at = CASE WHEN :progress_finished = 100 THEN COALESCE(completed_at, NOW()) ELSE completed_at END
             WHERE id = :task_id'
        );
        $stmt->execute([
            'progress' => $progress,
            'progress_completed' => $progress,
            'progress_finished' => $progress,
            'task_id' => $taskId,
        ]);

        return $progress;
    }

    private static function notifyAssignment(int $taskId, ?int $assignedTo, string $title): void
    {
        if (!$assignedTo) {
            return;
        }

        self::createNotification($assignedTo, 'task.assigned', 'Task assigned', $title, '/tasks/detail?id=' . $taskId);
    }

    private static function notifyTaskFollowers(int $taskId, string $type, string $title, string $body): void
    {
        $stmt = Database::connection()->prepare('SELECT assigned_to FROM tasks WHERE id = :id AND assigned_to IS NOT NULL');
        $stmt->execute(['id' => $taskId]);
        $userId = $stmt->fetchColumn();

        if ($userId) {
            self::createNotification((int) $userId, $type, $title, $body, '/tasks/detail?id=' . $taskId);
        }
    }

    private static function createNotification(int $userId, string $type, string $title, string $body, string $url): void
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

    private static function labelsForTask(int $taskId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT task_labels.*
             FROM task_labels
             JOIN task_label_map ON task_label_map.label_id = task_labels.id
             WHERE task_label_map.task_id = :task_id
             ORDER BY task_labels.name'
        );
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    private static function comments(int $taskId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT task_comments.*, users.name AS user_name
             FROM task_comments
             LEFT JOIN users ON users.id = task_comments.user_id
             WHERE task_comments.task_id = :task_id AND task_comments.deleted_at IS NULL
             ORDER BY task_comments.created_at DESC'
        );
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    private static function comment(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT task_comments.*, users.name AS user_name
             FROM task_comments
             LEFT JOIN users ON users.id = task_comments.user_id
             WHERE task_comments.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private static function checklist(int $taskId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM task_checklist_items WHERE task_id = :task_id ORDER BY sort_order, id');
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    private static function checklistItem(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM task_checklist_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private static function attachments(int $taskId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT task_attachments.*, users.name AS uploaded_by_name
             FROM task_attachments
             LEFT JOIN users ON users.id = task_attachments.uploaded_by
             WHERE task_attachments.task_id = :task_id AND task_attachments.deleted_at IS NULL
             ORDER BY task_attachments.created_at DESC'
        );
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    private static function attachment(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM task_attachments WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private static function payload(array $data): array
    {
        return [
            'project_id' => (int) $data['project_id'],
            'assigned_to' => self::nullableInt($data['assigned_to'] ?? null),
            'title' => trim((string) $data['title']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'status' => in_array($data['status'] ?? 'todo', self::STATUSES, true) ? $data['status'] : 'todo',
            'priority' => in_array($data['priority'] ?? 'medium', self::PRIORITIES, true) ? $data['priority'] : 'medium',
            'estimated_hours' => is_numeric($data['estimated_hours'] ?? null) ? (float) $data['estimated_hours'] : null,
            'start_date' => self::nullableDate($data['start_date'] ?? null),
            'due_date' => self::nullableDate($data['due_date'] ?? null),
        ];
    }

    private static function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private static function nullableDate(mixed $value): ?string
    {
        $value = trim((string) $value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }
}

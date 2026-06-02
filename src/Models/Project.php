<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Project
{
    public const PROJECT_STATUSES = ['planned', 'active', 'on_hold', 'completed', 'cancelled'];
    public const TASK_STATUSES = ['todo', 'in_progress', 'review', 'completed'];
    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    public static function all(array $filters = []): array
    {
        $where = ['projects.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'projects.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(projects.name LIKE :search OR clients.company_name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql = 'SELECT
                    projects.*,
                    clients.company_name,
                    users.name AS creator_name,
                    COUNT(DISTINCT tasks.id) AS total_tasks,
                    SUM(CASE WHEN tasks.status = "completed" AND tasks.deleted_at IS NULL THEN 1 ELSE 0 END) AS completed_tasks,
                    COUNT(DISTINCT project_members.user_id) AS member_count,
                    GROUP_CONCAT(DISTINCT project_members.user_id) AS member_ids_csv
                FROM projects
                LEFT JOIN clients ON clients.id = projects.client_id
                LEFT JOIN users ON users.id = projects.created_by
                LEFT JOIN tasks ON tasks.project_id = projects.id AND tasks.deleted_at IS NULL
                LEFT JOIN project_members ON project_members.project_id = projects.id
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY projects.id
                ORDER BY
                    FIELD(projects.status, "active", "planned", "on_hold", "completed", "cancelled"),
                    projects.due_date IS NULL,
                    projects.due_date ASC,
                    projects.created_at DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT projects.*, clients.company_name
             FROM projects
             LEFT JOIN clients ON clients.id = projects.client_id
             WHERE projects.id = :id AND projects.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch();

        if (!$project) {
            return null;
        }

        $project['member_ids'] = self::memberIds($id);
        return $project;
    }

    public static function create(array $data, array $memberIds): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO projects
                (client_id, created_by, name, description, status, priority, start_date, due_date, budget)
             VALUES
                (:client_id, :created_by, :name, :description, :status, :priority, :start_date, :due_date, :budget)'
        );
        $stmt->execute(self::projectPayload($data));
        $projectId = (int) $pdo->lastInsertId();

        self::syncMembers($projectId, $memberIds);
        $pdo->commit();

        return $projectId;
    }

    public static function update(int $id, array $data, array $memberIds): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        $payload = self::projectPayload($data);
        $payload['id'] = $id;

        $stmt = $pdo->prepare(
            'UPDATE projects
             SET client_id = :client_id,
                 name = :name,
                 description = :description,
                 status = :status,
                 priority = :priority,
                 start_date = :start_date,
                 due_date = :due_date,
                 budget = :budget,
                 completed_at = CASE WHEN :status_completed = "completed" THEN COALESCE(completed_at, NOW()) ELSE NULL END
             WHERE id = :id AND deleted_at IS NULL'
        );
        unset($payload['created_by']);
        $payload['status_completed'] = $payload['status'];
        $stmt->execute($payload);

        self::syncMembers($id, $memberIds);
        $pdo->commit();
    }

    public static function softDelete(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE projects SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::PROJECT_STATUSES, true)) {
            return false;
        }

        $stmt = Database::connection()->prepare(
            'UPDATE projects
             SET status = :status,
                 completed_at = CASE WHEN :status_completed = "completed" THEN COALESCE(completed_at, NOW()) ELSE NULL END
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['status' => $status, 'status_completed' => $status, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function clients(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, company_name
             FROM clients
             WHERE deleted_at IS NULL AND status = "active"
             ORDER BY company_name'
        );
        return $stmt->fetchAll();
    }

    public static function employees(): array
    {
        $stmt = Database::connection()->query(
            'SELECT users.id, users.name, roles.name AS role_name
             FROM users
             JOIN roles ON roles.id = users.role_id
             WHERE users.deleted_at IS NULL
               AND users.status = "active"
               AND roles.slug IN ("agency-admin", "project-manager", "employee")
             ORDER BY users.name'
        );
        return $stmt->fetchAll();
    }

    public static function stats(): array
    {
        $stmt = Database::connection()->query(
            'SELECT
                COUNT(*) AS total,
                SUM(status = "active") AS active,
                SUM(status = "planned") AS planned,
                SUM(status = "on_hold") AS on_hold,
                SUM(status = "completed") AS completed,
                SUM(due_date < CURDATE() AND status NOT IN ("completed", "cancelled")) AS overdue
             FROM projects
             WHERE deleted_at IS NULL'
        );

        return $stmt->fetch() ?: [];
    }

    public static function taskStats(): array
    {
        $stmt = Database::connection()->query(
            'SELECT status, COUNT(*) AS total
             FROM tasks
             WHERE deleted_at IS NULL
             GROUP BY status'
        );
        return $stmt->fetchAll();
    }

    public static function tasksByStatus(): array
    {
        $stmt = Database::connection()->query(
            'SELECT
                tasks.*,
                projects.name AS project_name,
                users.name AS assignee_name
             FROM tasks
             JOIN projects ON projects.id = tasks.project_id
             LEFT JOIN users ON users.id = tasks.assigned_to
             WHERE tasks.deleted_at IS NULL AND projects.deleted_at IS NULL
             ORDER BY tasks.sort_order ASC, tasks.due_date IS NULL, tasks.due_date ASC, tasks.created_at DESC'
        );

        $columns = array_fill_keys(self::TASK_STATUSES, []);

        foreach ($stmt->fetchAll() as $task) {
            $columns[$task['status']][] = $task;
        }

        return $columns;
    }

    public static function createTask(array $data, array $labelIds = []): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'INSERT INTO tasks
                (project_id, assigned_to, created_by, title, description, status, priority, estimated_hours, start_date, due_date, sort_order)
             VALUES
                (:project_id, :assigned_to, :created_by, :title, :description, :status, :priority, :estimated_hours, :start_date, :due_date, :sort_order)'
        );
        $stmt->execute([
            'project_id' => (int) $data['project_id'],
            'assigned_to' => self::nullableInt($data['assigned_to'] ?? null),
            'created_by' => self::nullableInt($data['created_by'] ?? null),
            'title' => trim((string) $data['title']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'status' => in_array($data['status'] ?? 'todo', self::TASK_STATUSES, true) ? $data['status'] : 'todo',
            'priority' => in_array($data['priority'] ?? 'medium', self::PRIORITIES, true) ? $data['priority'] : 'medium',
            'estimated_hours' => is_numeric($data['estimated_hours'] ?? null) ? (float) $data['estimated_hours'] : null,
            'start_date' => self::nullableDate($data['start_date'] ?? null),
            'due_date' => self::nullableDate($data['due_date'] ?? null),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $taskId = (int) $pdo->lastInsertId();

        if ($labelIds) {
            $stmt = $pdo->prepare('INSERT INTO task_label_map (task_id, label_id) VALUES (:task_id, :label_id)');
            foreach (array_unique(array_filter(array_map('intval', $labelIds))) as $labelId) {
                $stmt->execute(['task_id' => $taskId, 'label_id' => $labelId]);
            }
        }

        if (self::nullableInt($data['assigned_to'] ?? null)) {
            $stmt = $pdo->prepare(
                'INSERT INTO notifications (user_id, type, title, body, action_url)
                 VALUES (:user_id, "task.assigned", "Task assigned", :body, :action_url)'
            );
            $stmt->execute([
                'user_id' => (int) $data['assigned_to'],
                'body' => trim((string) $data['title']),
                'action_url' => '/tasks/detail?id=' . $taskId,
            ]);
        }

        $pdo->commit();
        return $taskId;
    }

    public static function updateTaskStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::TASK_STATUSES, true)) {
            return false;
        }

        $stmt = Database::connection()->prepare(
            'UPDATE tasks
             SET status = :status,
                 completed_at = CASE WHEN :status_completed = "completed" THEN COALESCE(completed_at, NOW()) ELSE NULL END
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['status' => $status, 'status_completed' => $status, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function milestones(): array
    {
        $stmt = Database::connection()->query(
            'SELECT milestones.*, projects.name AS project_name
             FROM milestones
             JOIN projects ON projects.id = milestones.project_id
             WHERE projects.deleted_at IS NULL
             ORDER BY milestones.due_date ASC
             LIMIT 20'
        );
        return $stmt->fetchAll();
    }

    public static function createMilestone(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO milestones (project_id, title, description, due_date, status)
             VALUES (:project_id, :title, :description, :due_date, :status)'
        );
        $stmt->execute([
            'project_id' => (int) $data['project_id'],
            'title' => trim((string) $data['title']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'due_date' => self::nullableDate($data['due_date'] ?? null) ?: date('Y-m-d'),
            'status' => in_array($data['status'] ?? 'pending', ['pending', 'in_progress', 'completed', 'missed'], true)
                ? $data['status']
                : 'pending',
        ]);

        return (int) $pdo->lastInsertId();
    }

    private static function syncMembers(int $projectId, array $memberIds): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM project_members WHERE project_id = :project_id');
        $stmt->execute(['project_id' => $projectId]);

        $stmt = $pdo->prepare(
            'INSERT INTO project_members (project_id, user_id, project_role)
             VALUES (:project_id, :user_id, :project_role)'
        );

        foreach (array_unique(array_filter(array_map('intval', $memberIds))) as $userId) {
            $stmt->execute([
                'project_id' => $projectId,
                'user_id' => $userId,
                'project_role' => 'designer',
            ]);
        }
    }

    private static function memberIds(int $projectId): array
    {
        $stmt = Database::connection()->prepare('SELECT user_id FROM project_members WHERE project_id = :id');
        $stmt->execute(['id' => $projectId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function projectPayload(array $data): array
    {
        $status = in_array($data['status'] ?? 'planned', self::PROJECT_STATUSES, true) ? $data['status'] : 'planned';
        $priority = in_array($data['priority'] ?? 'medium', self::PRIORITIES, true) ? $data['priority'] : 'medium';

        return [
            'client_id' => self::nullableInt($data['client_id'] ?? null),
            'created_by' => self::nullableInt($data['created_by'] ?? null),
            'name' => trim((string) $data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'status' => $status,
            'priority' => $priority,
            'start_date' => self::nullableDate($data['start_date'] ?? null),
            'due_date' => self::nullableDate($data['due_date'] ?? null),
            'budget' => is_numeric($data['budget'] ?? null) ? (float) $data['budget'] : null,
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

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Employee
{
    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT employees.*, users.name, users.email,
                    COUNT(DISTINCT tasks.id) AS assigned_tasks,
                    SUM(tasks.status = "completed") AS completed_tasks,
                    COALESCE(SUM(work_logs.hours), 0) AS logged_hours
             FROM employees
             JOIN users ON users.id = employees.user_id
             LEFT JOIN tasks ON tasks.assigned_to = users.id AND tasks.deleted_at IS NULL
             LEFT JOIN work_logs ON work_logs.employee_id = employees.id
             GROUP BY employees.id
             ORDER BY users.name'
        );
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT employees.*, users.name, users.email, users.phone
             FROM employees
             JOIN users ON users.id = employees.user_id
             WHERE employees.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $employee = $stmt->fetch();

        if (!$employee) {
            return null;
        }

        $employee['tasks'] = self::tasks($id);
        $employee['attendance'] = self::attendance($id);
        $employee['work_logs'] = self::workLogs($id);
        return $employee;
    }

    public static function usersWithoutEmployee(): array
    {
        $stmt = Database::connection()->query(
            'SELECT users.id, users.name, users.email
             FROM users
             LEFT JOIN employees ON employees.user_id = users.id
             JOIN roles ON roles.id = users.role_id
             WHERE employees.id IS NULL
               AND users.deleted_at IS NULL
               AND users.status = "active"
               AND roles.slug IN ("agency-admin", "project-manager", "employee")
             ORDER BY users.name'
        );
        return $stmt->fetchAll();
    }

    public static function create(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO employees
                (user_id, employee_code, designation, department, joining_date, employment_type, hourly_rate, status)
             VALUES
                (:user_id, :employee_code, :designation, :department, :joining_date, :employment_type, :hourly_rate, :status)'
        );
        $stmt->execute(self::payload($data));
    }

    public static function update(int $id, array $data): void
    {
        $payload = self::payload($data);
        $payload['id'] = $id;
        unset($payload['user_id']);

        $stmt = Database::connection()->prepare(
            'UPDATE employees
             SET employee_code = :employee_code,
                 designation = :designation,
                 department = :department,
                 joining_date = :joining_date,
                 employment_type = :employment_type,
                 hourly_rate = :hourly_rate,
                 status = :status
             WHERE id = :id'
        );
        $stmt->execute($payload);
    }

    public static function recordAttendance(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO attendance (employee_id, attendance_date, check_in, check_out, status, notes)
             VALUES (:employee_id, :attendance_date, :check_in, :check_out, :status, :notes)
             ON DUPLICATE KEY UPDATE
                check_in = VALUES(check_in),
                check_out = VALUES(check_out),
                status = VALUES(status),
                notes = VALUES(notes)'
        );
        $stmt->execute([
            'employee_id' => (int) $data['employee_id'],
            'attendance_date' => self::date($data['attendance_date'] ?? null) ?: date('Y-m-d'),
            'check_in' => self::datetime($data['check_in'] ?? null),
            'check_out' => self::datetime($data['check_out'] ?? null),
            'status' => in_array($data['status'] ?? 'present', ['present', 'absent', 'late', 'half_day', 'leave'], true) ? $data['status'] : 'present',
            'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
        ]);
    }

    public static function addWorkLog(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO work_logs (employee_id, task_id, log_date, hours, description)
             VALUES (:employee_id, :task_id, :log_date, :hours, :description)'
        );
        $stmt->execute([
            'employee_id' => (int) $data['employee_id'],
            'task_id' => is_numeric($data['task_id'] ?? null) ? (int) $data['task_id'] : null,
            'log_date' => self::date($data['log_date'] ?? null) ?: date('Y-m-d'),
            'hours' => is_numeric($data['hours'] ?? null) ? (float) $data['hours'] : 0,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
        ]);
    }

    public static function analytics(): array
    {
        $stmt = Database::connection()->query(
            'SELECT
                COUNT(*) AS total_employees,
                SUM(status = "active") AS active_employees,
                (SELECT COUNT(*) FROM attendance WHERE attendance_date = CURDATE() AND status IN ("present", "late", "half_day")) AS present_today,
                (SELECT COALESCE(SUM(hours), 0) FROM work_logs WHERE log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) AS weekly_hours
             FROM employees'
        );
        return $stmt->fetch() ?: [];
    }

    private static function tasks(int $employeeId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT tasks.*, projects.name AS project_name
             FROM tasks
             JOIN employees ON employees.user_id = tasks.assigned_to
             JOIN projects ON projects.id = tasks.project_id
             WHERE employees.id = :id AND tasks.deleted_at IS NULL
             ORDER BY tasks.due_date IS NULL, tasks.due_date ASC'
        );
        $stmt->execute(['id' => $employeeId]);
        return $stmt->fetchAll();
    }

    private static function attendance(int $employeeId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM attendance WHERE employee_id = :id ORDER BY attendance_date DESC LIMIT 14');
        $stmt->execute(['id' => $employeeId]);
        return $stmt->fetchAll();
    }

    private static function workLogs(int $employeeId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT work_logs.*, tasks.title AS task_title
             FROM work_logs
             LEFT JOIN tasks ON tasks.id = work_logs.task_id
             WHERE work_logs.employee_id = :id
             ORDER BY work_logs.log_date DESC, work_logs.id DESC
             LIMIT 20'
        );
        $stmt->execute(['id' => $employeeId]);
        return $stmt->fetchAll();
    }

    private static function payload(array $data): array
    {
        return [
            'user_id' => (int) ($data['user_id'] ?? 0),
            'employee_code' => trim((string) $data['employee_code']),
            'designation' => trim((string) ($data['designation'] ?? '')) ?: null,
            'department' => trim((string) ($data['department'] ?? '')) ?: null,
            'joining_date' => self::date($data['joining_date'] ?? null),
            'employment_type' => in_array($data['employment_type'] ?? 'full_time', ['full_time', 'part_time', 'contract', 'intern'], true) ? $data['employment_type'] : 'full_time',
            'hourly_rate' => is_numeric($data['hourly_rate'] ?? null) ? (float) $data['hourly_rate'] : null,
            'status' => in_array($data['status'] ?? 'active', ['active', 'inactive', 'terminated'], true) ? $data['status'] : 'active',
        ];
    }

    private static function date(mixed $value): ?string
    {
        $value = trim((string) $value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    private static function datetime(mixed $value): ?string
    {
        $value = trim((string) $value);
        return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value) ? str_replace('T', ' ', $value) . ':00' : null;
    }
}

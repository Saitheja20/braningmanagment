<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Task;
use App\Services\AuthService;
use Throwable;

final class TaskController
{
    public function index(): void
    {
        View::render('tasks/index', [
            'title' => 'Tasks',
            'tasks' => Task::all([
                'status' => Request::input('status'),
                'assigned_to' => Request::input('assigned_to'),
                'search' => Request::input('search'),
            ]),
            'projects' => Task::projects(),
            'employees' => Task::employees(),
            'labels' => Task::labels(),
            'stats' => Task::stats(),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ], 'layouts/app');
    }

    public function detail(): void
    {
        $task = Task::find((int) Request::input('id'));

        if (!$task) {
            App::abort(404, 'Task not found.');
        }

        View::render('tasks/detail', [
            'title' => 'Task Detail',
            'task' => $task,
            'projects' => Task::projects(),
            'employees' => Task::employees(),
            'labels' => Task::labels(),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ], 'layouts/app');
    }

    public function update(): void
    {
        $this->validateCsrf();
        $id = (int) Request::input('id');

        if ($id <= 0 || trim((string) Request::input('title')) === '' || (int) Request::input('project_id') <= 0) {
            Session::flash('error', 'Task title and project are required.');
            App::redirect('/tasks');
        }

        try {
            Task::update($id, $this->taskData(), $this->labelIds());
            Session::flash('success', 'Task updated.');
        } catch (Throwable) {
            Session::flash('error', 'Task could not be updated.');
        }

        App::redirect('/tasks/detail?id=' . $id);
    }

    public function status(): void
    {
        $this->jsonGuard();
        $updated = Task::updateStatus((int) Request::input('id'), (string) Request::input('status'));
        $this->json(['success' => $updated]);
    }

    public function priority(): void
    {
        $this->jsonGuard();
        $updated = Task::updatePriority((int) Request::input('id'), (string) Request::input('priority'));
        $this->json(['success' => $updated]);
    }

    public function comment(): void
    {
        $this->jsonGuard();
        $taskId = (int) Request::input('task_id');
        $comment = trim((string) Request::input('comment'));

        if ($taskId <= 0 || $comment === '') {
            $this->json(['success' => false, 'message' => 'Comment is required.']);
        }

        $created = Task::addComment($taskId, AuthService::user()['id'] ?? null, $comment);
        $this->json(['success' => true, 'comment' => $created]);
    }

    public function checklistStore(): void
    {
        $this->jsonGuard();
        $taskId = (int) Request::input('task_id');
        $title = trim((string) Request::input('title'));

        if ($taskId <= 0 || $title === '') {
            $this->json(['success' => false, 'message' => 'Checklist item is required.']);
        }

        $item = Task::addChecklistItem($taskId, $title);
        $this->json(['success' => true, 'item' => $item]);
    }

    public function checklistToggle(): void
    {
        $this->jsonGuard();
        $result = Task::toggleChecklistItem(
            (int) Request::input('id'),
            Request::input('completed') === '1',
            AuthService::user()['id'] ?? null
        );

        $this->json(['success' => (bool) $result['item'], 'progress' => $result['progress']]);
    }

    public function attachmentStore(): void
    {
        $this->jsonGuard();
        $taskId = (int) Request::input('task_id');

        if ($taskId <= 0 || empty($_FILES['attachment'])) {
            $this->json(['success' => false, 'message' => 'Attachment is required.']);
        }

        $attachment = Task::addAttachment($taskId, AuthService::user()['id'] ?? null, $_FILES['attachment']);
        $this->json(['success' => (bool) $attachment, 'attachment' => $attachment]);
    }

    private function taskData(): array
    {
        return [
            'project_id' => Request::input('project_id'),
            'assigned_to' => Request::input('assigned_to'),
            'title' => Request::input('title'),
            'description' => Request::input('description'),
            'status' => Request::input('status'),
            'priority' => Request::input('priority'),
            'estimated_hours' => Request::input('estimated_hours'),
            'start_date' => Request::input('start_date'),
            'due_date' => Request::input('due_date'),
        ];
    }

    private function labelIds(): array
    {
        $labels = $_POST['label_ids'] ?? [];
        return is_array($labels) ? $labels : [];
    }

    private function validateCsrf(): void
    {
        if (!Csrf::validate((string) Request::input('_csrf'))) {
            App::abort(419, 'Invalid or expired security token.');
        }
    }

    private function jsonGuard(): void
    {
        $this->validateCsrf();
        header('Content-Type: application/json');
    }

    private function json(array $payload): never
    {
        echo json_encode($payload);
        exit;
    }
}

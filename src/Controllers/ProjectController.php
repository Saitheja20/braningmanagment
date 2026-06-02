<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Project;
use App\Services\AuthService;
use Throwable;

final class ProjectController
{
    public function index(): void
    {
        View::render('projects/index', [
            'title' => 'Projects',
            'projects' => Project::all([
                'status' => Request::input('status'),
                'search' => Request::input('search'),
            ]),
            'clients' => Project::clients(),
            'employees' => Project::employees(),
            'stats' => Project::stats(),
            'statuses' => Project::PROJECT_STATUSES,
            'priorities' => Project::PRIORITIES,
        ], 'layouts/app');
    }

    public function store(): void
    {
        $this->validateCsrf();
        $this->validateProject();

        try {
            Project::create($this->projectData(), $this->memberIds());
            Session::flash('success', 'Project created successfully.');
        } catch (Throwable) {
            Session::flash('error', 'Project could not be created.');
        }

        App::redirect('/projects');
    }

    public function update(): void
    {
        $this->validateCsrf();
        $this->validateProject();
        $id = (int) Request::input('id');

        if ($id <= 0) {
            Session::flash('error', 'Invalid project selected.');
            App::redirect('/projects');
        }

        try {
            Project::update($id, $this->projectData(), $this->memberIds());
            Session::flash('success', 'Project updated successfully.');
        } catch (Throwable) {
            Session::flash('error', 'Project could not be updated.');
        }

        App::redirect('/projects');
    }

    public function delete(): void
    {
        $this->validateCsrf();
        $id = (int) Request::input('id');

        if ($id > 0) {
            Project::softDelete($id);
            Session::flash('success', 'Project deleted.');
        }

        App::redirect('/projects');
    }

    public function kanban(): void
    {
        View::render('projects/kanban', [
            'title' => 'Project Board',
            'projects' => Project::all(),
            'clients' => Project::clients(),
            'employees' => Project::employees(),
            'tasksByStatus' => Project::tasksByStatus(),
            'milestones' => Project::milestones(),
            'taskStatuses' => Project::TASK_STATUSES,
            'priorities' => Project::PRIORITIES,
            'labels' => \App\Models\Task::labels(),
        ], 'layouts/app');
    }

    public function status(): void
    {
        $this->jsonGuard();
        $updated = Project::updateStatus((int) Request::input('id'), (string) Request::input('status'));
        $this->json(['success' => $updated]);
    }

    public function taskStore(): void
    {
        $this->validateCsrf();

        if (trim((string) Request::input('title')) === '' || (int) Request::input('project_id') <= 0) {
            Session::flash('error', 'Task title and project are required.');
            App::redirect('/projects/kanban');
        }

        Project::createTask([
            'project_id' => Request::input('project_id'),
            'assigned_to' => Request::input('assigned_to'),
            'created_by' => AuthService::user()['id'] ?? null,
            'title' => Request::input('title'),
            'description' => Request::input('description'),
            'status' => Request::input('status', 'todo'),
            'priority' => Request::input('priority', 'medium'),
            'estimated_hours' => Request::input('estimated_hours'),
            'start_date' => Request::input('start_date'),
            'due_date' => Request::input('due_date'),
        ], $_POST['label_ids'] ?? []);

        Session::flash('success', 'Task created.');
        App::redirect($this->taskRedirectPath());
    }

    public function taskStatus(): void
    {
        $this->jsonGuard();
        $updated = Project::updateTaskStatus((int) Request::input('id'), (string) Request::input('status'));
        $this->json(['success' => $updated]);
    }

    public function milestoneStore(): void
    {
        $this->validateCsrf();

        if (trim((string) Request::input('title')) === '' || (int) Request::input('project_id') <= 0) {
            Session::flash('error', 'Milestone title and project are required.');
            App::redirect('/projects/kanban');
        }

        Project::createMilestone([
            'project_id' => Request::input('project_id'),
            'title' => Request::input('title'),
            'description' => Request::input('description'),
            'due_date' => Request::input('due_date'),
            'status' => Request::input('status', 'pending'),
        ]);

        Session::flash('success', 'Milestone created.');
        App::redirect('/projects/kanban');
    }

    private function projectData(): array
    {
        return [
            'client_id' => Request::input('client_id'),
            'created_by' => AuthService::user()['id'] ?? null,
            'name' => Request::input('name'),
            'description' => Request::input('description'),
            'status' => Request::input('status'),
            'priority' => Request::input('priority'),
            'start_date' => Request::input('start_date'),
            'due_date' => Request::input('due_date'),
            'budget' => Request::input('budget'),
        ];
    }

    private function memberIds(): array
    {
        $members = $_POST['member_ids'] ?? [];
        return is_array($members) ? $members : [];
    }

    private function validateProject(): void
    {
        if (trim((string) Request::input('name')) === '') {
            Session::flash('error', 'Project name is required.');
            App::redirect('/projects');
        }
    }

    private function taskRedirectPath(): string
    {
        $path = (string) Request::input('redirect_to', '/projects/kanban');
        return in_array($path, ['/tasks', '/projects/kanban'], true) ? $path : '/projects/kanban';
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

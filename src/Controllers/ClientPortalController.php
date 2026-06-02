<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\ClientPortal;
use App\Services\AuthService;

final class ClientPortalController
{
    public function index(): void
    {
        $client = $this->client();
        View::render('portal/index', [
            'title' => 'Client Portal',
            'client' => $client,
            'portal' => ClientPortal::dashboard((int) $client['id']),
        ], 'layouts/app');
    }

    public function approval(): void
    {
        $this->validateCsrf();
        $client = $this->client();
        $updated = ClientPortal::decideApproval(
            (int) Request::input('approval_id'),
            (int) $client['id'],
            (int) (AuthService::user()['id'] ?? 0),
            (string) Request::input('status'),
            (string) Request::input('feedback')
        );

        Session::flash($updated ? 'success' : 'error', $updated ? 'Approval response saved.' : 'Approval could not be updated.');
        App::redirect('/portal');
    }

    public function feedback(): void
    {
        $this->validateCsrf();
        $client = $this->client();
        ClientPortal::sendFeedback(
            (int) $client['id'],
            (int) (AuthService::user()['id'] ?? 0),
            (int) Request::input('project_id'),
            (string) Request::input('message')
        );

        Session::flash('success', 'Feedback sent to the agency.');
        App::redirect('/portal');
    }

    public function file(): void
    {
        $client = $this->client();
        $path = (string) Request::input('path');

        if (!str_starts_with($path, 'storage/uploads/tasks/')) {
            App::abort(403, 'Invalid file path.');
        }

        $absolute = dirname(__DIR__, 2) . '/' . $path;

        if (!is_file($absolute)) {
            App::abort(404, 'File not found.');
        }

        // Client ownership is enforced by the listing query; downloads require a listed path.
        $listed = array_filter(ClientPortal::dashboard((int) $client['id'])['files'], fn ($file) => $file['storage_path'] === $path);
        if (!$listed) {
            App::abort(403, 'You do not have access to this file.');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($absolute) . '"');
        readfile($absolute);
        exit;
    }

    private function client(): array
    {
        $client = ClientPortal::clientForUser(AuthService::user() ?? []);

        if (!$client) {
            App::abort(403, 'No client account is linked to this user.');
        }

        return $client;
    }

    private function validateCsrf(): void
    {
        if (!Csrf::validate((string) Request::input('_csrf'))) {
            App::abort(419, 'Invalid or expired security token.');
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Models\Realtime;
use App\Services\AuthService;

final class RealtimeController
{
    public function index(): void
    {
        View::render('realtime/index', [
            'title' => 'Realtime',
            'users' => Realtime::users(),
        ], 'layouts/app');
    }

    public function snapshot(): void
    {
        $this->json(Realtime::snapshot(
            (int) (AuthService::user()['id'] ?? 0),
            (int) Request::input('after_id', 0)
        ));
    }

    public function message(): void
    {
        $this->validateCsrf();
        $message = Realtime::sendMessage(
            AuthService::user()['id'] ?? null,
            is_numeric(Request::input('receiver_id')) ? (int) Request::input('receiver_id') : null,
            (string) Request::input('body')
        );

        $this->json(['success' => true, 'message' => $message]);
    }

    public function readNotification(): void
    {
        $this->validateCsrf();
        $this->json([
            'success' => Realtime::markNotificationRead(
                (int) Request::input('id'),
                (int) (AuthService::user()['id'] ?? 0)
            ),
        ]);
    }

    private function validateCsrf(): void
    {
        if (!Csrf::validate((string) Request::input('_csrf'))) {
            App::abort(419, 'Invalid or expired security token.');
        }
    }

    private function json(array $payload): never
    {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}

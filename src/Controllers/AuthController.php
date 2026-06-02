<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\User;
use App\Services\AuthService;
use PDOException;

final class AuthController
{
    public function showLogin(): void
    {
        View::render('auth/login', ['title' => 'Login']);
    }

    public function login(): void
    {
        $this->validateCsrf();

        $data = Request::only(['email', 'password']);
        $errors = Validator::login($data);

        if ($errors) {
            $this->backWithErrors('/login', $errors, ['email' => $data['email']]);
        }

        if (!AuthService::attempt((string) $data['email'], (string) $data['password'], Request::input('remember') === '1')) {
            $this->backWithErrors('/login', ['email' => 'Invalid credentials or inactive account.'], ['email' => $data['email']]);
        }

        App::redirect('/dashboard');
    }

    public function showRegister(): void
    {
        View::render('auth/register', ['title' => 'Register']);
    }

    public function register(): void
    {
        $this->validateCsrf();

        $data = Request::only(['name', 'email', 'phone', 'password', 'password_confirmation']);
        $errors = Validator::register($data);

        if (User::findByEmail((string) $data['email'])) {
            $errors['email'] = 'This email is already registered.';
        }

        if ($errors) {
            $this->backWithErrors('/register', $errors, [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);
        }

        try {
            $userId = User::create([
                'role_id' => User::defaultRegistrationRoleId(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
            ]);
        } catch (PDOException) {
            $this->backWithErrors('/register', ['email' => 'Registration failed. Please try again.'], $data);
        }

        $user = User::findById($userId);
        AuthService::loginUser($user);
        App::redirect('/dashboard');
    }

    public function showForgotPassword(): void
    {
        View::render('auth/forgot-password', ['title' => 'Forgot Password']);
    }

    public function forgotPassword(): void
    {
        $this->validateCsrf();

        $data = Request::only(['email']);
        $errors = Validator::forgotPassword($data);

        if ($errors) {
            $this->backWithErrors('/forgot-password', $errors, $data);
        }

        $token = AuthService::createPasswordReset((string) $data['email']);

        Session::flash('success', 'If that email exists, a reset link has been prepared.');

        if ($token) {
            Session::flash('reset_link', '/reset-password?token=' . urlencode($token));
        }

        App::redirect('/forgot-password');
    }

    public function showResetPassword(): void
    {
        View::render('auth/reset-password', [
            'title' => 'Reset Password',
            'token' => Request::input('token', ''),
        ]);
    }

    public function resetPassword(): void
    {
        $this->validateCsrf();

        $data = Request::only(['token', 'password', 'password_confirmation']);
        $errors = Validator::resetPassword($data);

        if ($errors) {
            $this->backWithErrors('/reset-password?token=' . urlencode((string) $data['token']), $errors);
        }

        if (!AuthService::resetPassword((string) $data['token'], (string) $data['password'])) {
            $this->backWithErrors('/reset-password', ['token' => 'This reset token is invalid or expired.']);
        }

        Session::flash('success', 'Password reset successfully. You can now log in.');
        App::redirect('/login');
    }

    public function logout(): void
    {
        $this->validateCsrf();
        AuthService::logout();
        App::redirect('/login');
    }

    private function validateCsrf(): void
    {
        if (!Csrf::validate((string) Request::input('_csrf'))) {
            App::abort(419, 'Invalid or expired security token.');
        }
    }

    private function backWithErrors(string $path, array $errors, array $old = []): never
    {
        Session::flash('errors', $errors);
        Session::flash('old', $old);
        App::redirect($path);
    }
}

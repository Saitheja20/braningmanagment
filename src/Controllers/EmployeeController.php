<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Employee;
use App\Models\Task;
use Throwable;

final class EmployeeController
{
    public function index(): void
    {
        View::render('employees/index', [
            'title' => 'Employees',
            'employees' => Employee::all(),
            'users' => Employee::usersWithoutEmployee(),
            'analytics' => Employee::analytics(),
        ], 'layouts/app');
    }

    public function detail(): void
    {
        $employee = Employee::find((int) Request::input('id'));

        if (!$employee) {
            App::abort(404, 'Employee not found.');
        }

        View::render('employees/detail', [
            'title' => 'Employee Profile',
            'employee' => $employee,
            'tasks' => Task::all(),
        ], 'layouts/app');
    }

    public function store(): void
    {
        $this->validateCsrf();

        try {
            Employee::create($_POST);
            Session::flash('success', 'Employee profile created.');
        } catch (Throwable) {
            Session::flash('error', 'Employee profile could not be saved.');
        }

        App::redirect('/employees');
    }

    public function update(): void
    {
        $this->validateCsrf();
        $id = (int) Request::input('id');

        try {
            Employee::update($id, $_POST);
            Session::flash('success', 'Employee profile updated.');
        } catch (Throwable) {
            Session::flash('error', 'Employee profile could not be updated.');
        }

        App::redirect('/employees/detail?id=' . $id);
    }

    public function attendance(): void
    {
        $this->validateCsrf();
        Employee::recordAttendance($_POST);
        Session::flash('success', 'Attendance saved.');
        App::redirect('/employees/detail?id=' . (int) Request::input('employee_id'));
    }

    public function workLog(): void
    {
        $this->validateCsrf();
        Employee::addWorkLog($_POST);
        Session::flash('success', 'Work log added.');
        App::redirect('/employees/detail?id=' . (int) Request::input('employee_id'));
    }

    private function validateCsrf(): void
    {
        if (!Csrf::validate((string) Request::input('_csrf'))) {
            App::abort(419, 'Invalid or expired security token.');
        }
    }
}

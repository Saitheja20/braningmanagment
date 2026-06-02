<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/auth'): void
    {
        extract($data, EXTR_SKIP);

        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';
        $layoutPath = dirname(__DIR__) . '/Views/' . $layout . '.php';

        if (!is_file($viewPath)) {
            App::abort(500, 'View not found.');
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}

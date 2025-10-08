<?php
declare(strict_types=1);

namespace App\core;

abstract class Controller
{
    protected string $viewsPath = __DIR__ . '/../Views';
    protected string $layout = 'layouts/main';

    protected function request(): array
    {
        return array_merge($_GET ?? [], $_POST ?? []);
    }

    protected function input(string $key, $default = null)
    {
        return $this->request()[$key] ?? $default;
    }

    protected function setFlash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    protected function getFlash(string $key, $default = null)
    {
        if (!isset($_SESSION['_flash'][$key])) {
            return $default;
        }
        $val = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    protected function render(string $view, array $data = [], ?string $layout = null): void
    {
        $layout = $layout ?? $this->layout;
        $viewFile = rtrim($this->viewsPath, '/\\') . '/' . ltrim($view, '/\\') . '.php';
        $layoutFile = rtrim($this->viewsPath, '/\\') . '/' . ltrim($layout, '/\\') . '.php';

        extract($data, EXTR_SKIP);
        $content = function () use ($viewFile, $data) {
            extract($data, EXTR_SKIP);
            if (is_file($viewFile)) {
                include $viewFile;
            } else {
                echo "View not found: {$viewFile}";
            }
        };

        if (is_file($layoutFile)) {
            include $layoutFile;
        } else {
            $content();
        }
    }
}


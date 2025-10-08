<?php
declare(strict_types=1);

namespace App\middlewares;

use App\core\Auth;

abstract class RoleGuard
{
    abstract protected function role(): string;

    public function handle(): bool
    {
        $auth = new Auth();
        if (!$auth->check()) {
            header('Location: /login', true, 302);
            return false;
        }

        if (!$auth->authorize($this->role())) {
            header('Location: /login', true, 302);
            return false;
        }

        return true;
    }
}

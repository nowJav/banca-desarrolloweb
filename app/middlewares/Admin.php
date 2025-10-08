<?php
declare(strict_types=1);

namespace App\middlewares;

class Admin extends RoleGuard
{
    protected function role(): string
    {
        return 'admin';
    }
}

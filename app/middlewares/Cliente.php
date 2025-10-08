<?php
declare(strict_types=1);

namespace App\middlewares;

class Cliente extends RoleGuard
{
    protected function role(): string
    {
        return 'cliente';
    }
}

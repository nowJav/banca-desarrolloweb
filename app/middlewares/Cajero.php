<?php
declare(strict_types=1);

namespace App\middlewares;

class Cajero extends RoleGuard
{
    protected function role(): string
    {
        return 'cajero';
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;

class LandingController extends Controller
{
    // GET /
    // SP: no aplica (landing)
    public function index(): void
    {
        $this->render('landing/index');
    }
}


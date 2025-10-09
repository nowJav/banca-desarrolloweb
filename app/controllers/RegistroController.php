<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\core\Csrf;
use App\core\Auth;
use App\models\Usuario;
use App\services\AuditoriaService;

class RegistroController extends Controller
{
    // GET /registro
    // SP: no aplica (muestra formulario)
    public function showForm(): void
    {
        $this->render('registro/form');
    }

    // POST /registro
    // SP: sp_registrar_usuario_cliente
    public function register(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'registro_cliente')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /registro');
            return;
        }

        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password_confirmation'] ?? '');

        if ($nombre === '' || $email === '' || $password === '') {
            $this->setFlash('error', 'Todos los campos son obligatorios.');
            header('Location: /registro');
            return;
        }
        if ($password !== $password2) {
            $this->setFlash('error', 'Las contraseñas no coinciden.');
            header('Location: /registro');
            return;
        }

        $usuarioModel = new Usuario();
        $passwordHash = Auth::hashPassword($password);
        $res = $usuarioModel->registrarCliente($nombre, $email, $passwordHash);

        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('registro_cliente', 'Registro de cliente', 'usuario', $res['id'] ?? null);
            $this->setFlash('success', $res['message'] ?? 'Registro exitoso.');
            header('Location: /login');
            return;
        }

        $this->setFlash('error', $res['message'] ?? 'No fue posible registrar.');
        header('Location: /registro');
    }
}

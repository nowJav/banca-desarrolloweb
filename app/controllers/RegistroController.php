<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\core\Csrf;
use App\core\Auth;
use App\models\Usuario;
use App\services\AuditoriaService;
use App\helpers\Validator;

class RegistroController extends Controller
{
    public function showForm(): void
    {
        $this->render('registro/form');
    }

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

        if (!Validator::required($nombre) || !Validator::required($email) || !Validator::required($password)) {
            $this->setFlash('error', 'Todos los campos son obligatorios.');
            header('Location: /registro');
            return;
        }
        if ($password !== $password2) {
            $this->setFlash('error', 'Las contraseñas no coinciden.');
            header('Location: /registro');
            return;
        }
        if (!Validator::email($email)) {
            $this->setFlash('error', 'Formato de correo inválido.');
            header('Location: /registro');
            return;
        }
        if (!Validator::stringLen($nombre, 2, 140)) {
            $this->setFlash('error', 'Nombre fuera de longitud permitida.');
            header('Location: /registro');
            return;
        }
        if (!Validator::stringLen($password, 8, 255)) {
            $this->setFlash('error', 'La contraseña debe tener al menos 8 caracteres.');
            header('Location: /registro');
            return;
        }

        $usuarioModel = new Usuario();
        $passwordHash = Auth::hashPassword($password);
        $res = $usuarioModel->registrarCliente($nombre, $email, $passwordHash);

        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('registro_cliente', 'usuario', $res['id'] ?? null);
            $this->setFlash('success', $res['message'] ?? 'Registro exitoso.');
            header('Location: /login');
            return;
        }

        $this->setFlash('error', 'No fue posible registrar.');
        header('Location: /registro');
    }
}


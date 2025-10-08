<?php
declare(strict_types=1);

namespace App\Config;

class Routes
{
    public static function all(): array
    {
        return [
            // Landing
            ['method' => 'GET',  'path' => '/',                    'handler' => 'LandingController@index'],

            // Autenticación
            ['method' => 'GET',  'path' => '/login',               'handler' => 'AuthController@showLogin'],
            ['method' => 'POST', 'path' => '/login',               'handler' => 'AuthController@login'],
            ['method' => 'POST', 'path' => '/logout',              'handler' => 'AuthController@logout'],

            // Registro de cliente
            ['method' => 'GET',  'path' => '/registro',            'handler' => 'RegistroController@showForm'],
            ['method' => 'POST', 'path' => '/registro',            'handler' => 'RegistroController@register'],

            // Panel admin
            ['method' => 'GET',  'path' => '/admin',               'handler' => 'AdminController@index'],
            ['method' => 'GET',  'path' => '/admin/cajeros',       'handler' => 'AdminController@cajeros'],
            ['method' => 'GET',  'path' => '/admin/cuentas',       'handler' => 'AdminController@cuentas'],

            // Panel cajero
            ['method' => 'GET',  'path' => '/cajero/crear-cuenta', 'handler' => 'CajeroController@showCrearCuenta'],
            ['method' => 'POST', 'path' => '/cajero/crear-cuenta', 'handler' => 'CajeroController@crearCuenta'],
            ['method' => 'GET',  'path' => '/cajero/deposito',     'handler' => 'CajeroController@showDeposito'],
            ['method' => 'POST', 'path' => '/cajero/deposito',     'handler' => 'CajeroController@deposito'],
            ['method' => 'GET',  'path' => '/cajero/retiro',       'handler' => 'CajeroController@showRetiro'],
            ['method' => 'POST', 'path' => '/cajero/retiro',       'handler' => 'CajeroController@retiro'],

            // Panel cliente
            ['method' => 'GET',  'path' => '/cliente/terceros',    'handler' => 'ClienteController@terceros'],
            ['method' => 'POST', 'path' => '/cliente/transferir',  'handler' => 'ClienteController@transferir'],
            ['method' => 'GET',  'path' => '/cliente/estado-cuenta','handler' => 'ClienteController@estadoCuenta'],
        ];
    }
}


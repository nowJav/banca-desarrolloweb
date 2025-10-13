<?php
declare(strict_types=1);

namespace App\config;

class Routes
{
    public static function all(): array
    {
        return [
            // Landing
            ['method' => 'GET',  'path' => '/',                    'handler' => 'LandingController@index'],

            // Autenticación
            ['method' => 'GET',  'path' => '/login',               'handler' => 'AuthController@showLogin'],
            ['method' => 'GET',  'path' => '/login/admin',         'handler' => 'AuthController@showLoginAdmin'],
            ['method' => 'GET',  'path' => '/login/cajero',        'handler' => 'AuthController@showLoginCajero'],
            ['method' => 'GET',  'path' => '/login/cliente',       'handler' => 'AuthController@showLoginCliente'],
            ['method' => 'POST', 'path' => '/login',               'handler' => 'AuthController@login'],
            ['method' => 'POST', 'path' => '/logout',              'handler' => 'AuthController@logout'],

            // Registro de cliente
            ['method' => 'GET',  'path' => '/registro',            'handler' => 'RegistroController@showForm'],
            ['method' => 'POST', 'path' => '/registro',            'handler' => 'RegistroController@register'],

            // Panel admin
            ['method' => 'GET',  'path' => '/admin',               'handler' => 'AdminController@index',   'middleware' => 'admin'],
            ['method' => 'GET',  'path' => '/admin/cajeros',       'handler' => 'AdminController@cajeros', 'middleware' => 'admin'],
            ['method' => 'POST', 'path' => '/admin/cajeros',       'handler' => 'AdminController@crearCajero', 'middleware' => 'admin'],
            ['method' => 'POST', 'path' => '/admin/cajeros/toggle','handler' => 'AdminController@toggleCajero', 'middleware' => 'admin'],
            ['method' => 'GET',  'path' => '/admin/cuentas',       'handler' => 'AdminController@cuentas', 'middleware' => 'admin'],
            ['method' => 'POST', 'path' => '/admin/cuentas/bloquear',    'handler' => 'AdminController@bloquearCuenta',   'middleware' => 'admin'],
            ['method' => 'POST', 'path' => '/admin/cuentas/desbloquear', 'handler' => 'AdminController@desbloquearCuenta','middleware' => 'admin'],
            ['method' => 'GET',  'path' => '/admin/auditoria',     'handler' => 'AdminController@auditoria', 'middleware' => 'admin'],

            // Panel cajero
            ['method' => 'GET',  'path' => '/cajero',              'handler' => 'CajeroController@index',          'middleware' => 'cajero'],
            ['method' => 'GET',  'path' => '/cajero/crear-cuenta', 'handler' => 'CajeroController@showCrearCuenta', 'middleware' => 'cajero'],
            ['method' => 'POST', 'path' => '/cajero/crear-cuenta', 'handler' => 'CajeroController@crearCuenta',     'middleware' => 'cajero'],
            ['method' => 'GET',  'path' => '/cajero/deposito',     'handler' => 'CajeroController@showDeposito',    'middleware' => 'cajero'],
            ['method' => 'POST', 'path' => '/cajero/deposito',     'handler' => 'CajeroController@deposito',        'middleware' => 'cajero'],
            ['method' => 'GET',  'path' => '/cajero/retiro',       'handler' => 'CajeroController@showRetiro',      'middleware' => 'cajero'],
            ['method' => 'POST', 'path' => '/cajero/retiro',       'handler' => 'CajeroController@retiro',          'middleware' => 'cajero'],
            ['method' => 'GET',  'path' => '/cajero/movimientos',  'handler' => 'CajeroController@showMovimientos', 'middleware' => 'cajero'],

            // Panel cliente
            ['method' => 'GET',  'path' => '/cliente',             'handler' => 'ClienteController@index',         'middleware' => 'cliente'],
            ['method' => 'GET',  'path' => '/cliente/terceros',    'handler' => 'ClienteController@terceros',      'middleware' => 'cliente'],
            ['method' => 'POST', 'path' => '/cliente/terceros',    'handler' => 'ClienteController@agregarTercero','middleware' => 'cliente'],
            ['method' => 'POST', 'path' => '/cliente/terceros/toggle', 'handler' => 'ClienteController@toggleTercero','middleware' => 'cliente'],
            ['method' => 'GET',  'path' => '/cliente/transferir',  'handler' => 'ClienteController@showTransferir', 'middleware' => 'cliente'],
            ['method' => 'POST', 'path' => '/cliente/transferir',  'handler' => 'ClienteController@transferir',    'middleware' => 'cliente'],
            ['method' => 'GET',  'path' => '/cliente/estado-cuenta','handler' => 'ClienteController@estadoCuenta', 'middleware' => 'cliente'],

            // API extra
            ['method' => 'GET',  'path' => '/api/tercero-resumen', 'handler' => 'ApiController@terceroResumen'],
            ['method' => 'GET',  'path' => '/api/kpi-series',      'handler' => 'ApiController@kpiSeries'],
        ];
    }
}
 

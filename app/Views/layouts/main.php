<?php
use App\core\Auth;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Banca DesarrolloWeb</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" aria-label="Navbar principal">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="/">
        <span class="rounded bg-light text-dark px-2 py-1 fw-bold" aria-label="Logo">BDW</span>
        <span>Banca</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Mostrar menú">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <?php
          $role = $_SESSION['user_role'] ?? null;
          $isAdmin  = ($role === 'admin' || $role === 1 || $role === '1');
          $isCajero = ($role === 'cajero' || $role === 2 || $role === '2');
          $isCliente= ($role === 'cliente' || $role === 3 || $role === '3');
          $badge = '';
          if ($isAdmin) { $badge = 'Admin'; }
          elseif ($isCajero) { $badge = 'Cajero'; }
          elseif ($isCliente) { $badge = 'Cliente'; }
        ?>
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
          <?php if ($isAdmin): ?>
            <li class="nav-item"><a class="nav-link" href="/admin">Admin</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/cajeros">Cajeros</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/cuentas">Cuentas</a></li>
          <?php endif; ?>
          <?php if ($isCajero): ?>
            <li class="nav-item"><a class="nav-link" href="/cajero/crear-cuenta">Crear cuenta</a></li>
            <li class="nav-item"><a class="nav-link" href="/cajero/deposito">Depósito</a></li>
            <li class="nav-item"><a class="nav-link" href="/cajero/retiro">Retiro</a></li>
          <?php endif; ?>
          <?php if ($isCliente): ?>
            <li class="nav-item"><a class="nav-link" href="/cliente/terceros">Terceros</a></li>
            <li class="nav-item"><a class="nav-link" href="/cliente/transferir">Transferir</a></li>
            <li class="nav-item"><a class="nav-link" href="/cliente/estado-cuenta">Estado de cuenta</a></li>
          <?php endif; ?>
        </ul>
        <ul class="navbar-nav align-items-lg-center gap-2">
          <?php if ($badge): ?>
            <li class="nav-item"><span class="badge text-bg-info" aria-label="Rol actual"><?= htmlspecialchars($badge) ?></span></li>
          <?php endif; ?>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="#" aria-label="Perfil">Perfil</a></li>
            <li class="nav-item">
              <form action="/logout" method="post" class="d-inline" data-confirm="¿Cerrar sesión?">
                <?php $logoutToken = \App\core\Csrf::token('logout'); ?>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($logoutToken) ?>">
                <button class="btn btn-sm btn-outline-light" type="submit">Cerrar sesión</button>
              </form>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container" style="padding-top: 5rem;">
    <?php
      // Breadcrumbs automáticos simples por ruta
      $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
      $crumbs = ['/' => 'Inicio'];
      $map = [
        '/admin' => ['Admin'],
        '/admin/cajeros' => ['Admin','Cajeros'],
        '/admin/cuentas' => ['Admin','Cuentas'],
        '/cajero/crear-cuenta' => ['Cajero','Crear cuenta'],
        '/cajero/deposito' => ['Cajero','Depósito'],
        '/cajero/retiro' => ['Cajero','Retiro'],
        '/cliente/terceros' => ['Cliente','Terceros'],
        '/cliente/transferir' => ['Cliente','Transferir'],
        '/cliente/estado-cuenta' => ['Cliente','Estado de cuenta'],
        '/login' => ['Login'],
        '/registro' => ['Registro'],
      ];
      if (isset($map[$uri])) {
        echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
        echo '<li class="breadcrumb-item"><a href="/">Inicio</a></li>';
        $last = end($map[$uri]); reset($map[$uri]);
        foreach ($map[$uri] as $label) {
          if ($label === $last) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($label) . '</li>';
          } else {
            echo '<li class="breadcrumb-item">' . htmlspecialchars($label) . '</li>';
          }
        }
        echo '</ol></nav>';
      }
    ?>
    <?php
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    $flash = $_SESSION['_flash'] ?? [];
    if (!empty($flash)) {
        foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $k => $bs) {
            if (!empty($flash[$k])) {
                echo '<div class="alert alert-' . $bs . ' alert-dismissible fade show" role="alert">'
                    . htmlspecialchars((string)$flash[$k])
                    . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
        }
        unset($_SESSION['_flash']);
    }
    ?>
    <?php if (isset($content) && is_callable($content)) { $content(); } ?>
  </main>

  <footer class="border-top mt-5 py-3 small text-muted">
    <div class="container d-flex justify-content-between">
      <span>Sistema v<?= htmlspecialchars((string)(\App\config\App::env('APP_VERSION','0.1.0'))) ?></span>
      <span>Servidor: <?= htmlspecialchars(date('Y-m-d H:i:s')) ?></span>
    </div>
  </footer>

  <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmar acción</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body"><p id="confirmText">¿Deseas continuar?</p></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="confirmOk">Sí, continuar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Modal de confirmación para formularios con data-confirm
    (function(){
      const modalEl = document.getElementById('confirmModal');
      if (!modalEl) return;
      const modal = new bootstrap.Modal(modalEl);
      let pendingForm = null;
      document.addEventListener('submit', function(ev){
        const form = ev.target;
        const msg = form.getAttribute('data-confirm');
        if (msg) {
          ev.preventDefault();
          document.getElementById('confirmText').textContent = msg;
          pendingForm = form;
          modal.show();
        }
      }, true);
      document.getElementById('confirmOk')?.addEventListener('click', function(){
        if (pendingForm) pendingForm.submit();
      });
    })();
  </script>
</body>
</html>

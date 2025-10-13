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
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
      <a class="navbar-brand" href="/">Banca</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <?php
          $role = $_SESSION['user_role'] ?? null;
          $isAdmin  = ($role === 'admin' || $role === 1 || $role === '1');
          $isCajero = ($role === 'cajero' || $role === 2 || $role === '2');
          $isCliente= ($role === 'cliente' || $role === 3 || $role === '3');
        ?>
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
          <?php if ($isAdmin): ?>
            <li class="nav-item"><a class="nav-link" href="/admin">Admin</a></li>
          <?php endif; ?>
          <?php if ($isCajero): ?>
            <li class="nav-item"><a class="nav-link" href="/cajero/crear-cuenta">Cajero</a></li>
          <?php endif; ?>
          <?php if ($isCliente): ?>
            <li class="nav-item"><a class="nav-link" href="/cliente/terceros">Cliente</a></li>
          <?php endif; ?>
        </ul>
        <ul class="navbar-nav">
          <?php if (!empty($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <form action="/logout" method="post" class="d-inline">
                <?php $logoutToken = \App\core\Csrf::token('logout'); ?>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($logoutToken) ?>">
                <button class="btn btn-sm btn-outline-light" type="submit">Salir</button>
              </form>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container">
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/app.js"></script>
</body>
</html>

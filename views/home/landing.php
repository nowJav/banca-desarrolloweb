<?php $cards = [
  ['title' => 'Administración', 'desc' => 'Panel de administrador', 'href' => '/admin', 'btn' => 'Ir a Admin'],
  ['title' => 'Cajero', 'desc' => 'Operaciones de caja', 'href' => '/cajero/crear-cuenta', 'btn' => 'Ir a Cajero'],
  ['title' => 'Cliente', 'desc' => 'Operaciones de cliente', 'href' => '/cliente/terceros', 'btn' => 'Ir a Cliente'],
  ['title' => 'Login / Registro', 'desc' => 'Acceso de usuarios', 'href' => '/login', 'btn' => 'Iniciar sesión'],
]; ?>
<div class="row g-3">
  <?php foreach ($cards as $c): ?>
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title"><?= htmlspecialchars($c['title']) ?></h5>
        <p class="card-text flex-grow-1"><?= htmlspecialchars($c['desc']) ?></p>
        <a href="<?= htmlspecialchars($c['href']) ?>" class="btn btn-primary mt-auto"><?= htmlspecialchars($c['btn']) ?></a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  </div>

<?php $csrf = \App\core\Csrf::token('admin_cajeros'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h1 class="h4 m-0">Cajeros</h1>
  <div class="d-flex gap-2">
    <form class="d-flex" method="get" action="/admin/cajeros" role="search">
      <input class="form-control" type="search" name="q" value="<?= htmlspecialchars((string)($q ?? '')) ?>" placeholder="Buscar por nombre o email">
      <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    </form>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearCajeroModal">Crear cajero</button>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead><tr><th>Nombre</th><th>Email</th><th>Estado</th><th>Último acceso</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php if (!empty($cajeros ?? [])): ?>
        <?php foreach ($cajeros as $cj): ?>
          <tr>
            <td><?= htmlspecialchars((string)$cj['nombre']) ?></td>
            <td><?= htmlspecialchars((string)$cj['email']) ?></td>
            <td>
              <?php if ((int)($cj['activo'] ?? 0) === 1): ?>
                <span class="badge text-bg-success">Activo</span>
              <?php else: ?>
                <span class="badge text-bg-secondary">Bloqueado</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars((string)($cj['ultimo_login_at'] ?? '--')) ?></td>
            <td>
              <form action="/admin/cajeros/toggle" method="post" class="d-inline" data-confirm="¿Cambiar estado de este cajero?">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="usuario_id" value="<?= (int)($cj['usuario_id'] ?? 0) ?>">
                <input type="hidden" name="activo" value="<?= (int)($cj['activo'] ?? 0) === 1 ? 0 : 1 ?>">
                <button class="btn btn-sm <?= (int)($cj['activo'] ?? 0) === 1 ? 'btn-outline-warning' : 'btn-outline-success' ?>" type="submit">
                  <?= (int)($cj['activo'] ?? 0) === 1 ? 'Bloquear' : 'Activar' ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center">No hay cajeros. <button class="btn btn-link" data-bs-toggle="modal" data-bs-target="#crearCajeroModal">Crear cajero</button></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="crearCajeroModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Crear cajero</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form action="/admin/cajeros" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" required minlength="2" maxlength="140">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required maxlength="160">
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Contraseña</label>
              <input type="password" class="form-control" name="password" required minlength="8">
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirmar contraseña</label>
              <input type="password" class="form-control" name="password_confirmation" required minlength="8">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Crear</button>
        </div>
      </form>
    </div>
  </div>
</div>


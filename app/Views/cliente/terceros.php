<?php $token = \App\core\Csrf::token('cliente_terceros'); ?>
<h1 class="h4 mb-3">Cuentas de Terceros</h1>
<form action="/cliente/terceros" method="post" class="row g-3 mb-4" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-4">
    <label class="form-label">Número de cuenta destino</label>
    <input type="text" class="form-control" name="numero_cuenta" required pattern="^[A-Za-z0-9-]{6,24}$" placeholder="110-000-0002">
  </div>
  <div class="col-md-4">
    <label class="form-label">Alias</label>
    <input type="text" class="form-control" name="alias" required minlength="2" maxlength="80" placeholder="Beneficiario">
  </div>
  <div class="col-md-2">
    <label class="form-label">Monto máx. op.</label>
    <input type="number" step="0.01" min="0.01" class="form-control" name="monto_max_op" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Máx. tx/día</label>
    <input type="number" min="1" step="1" class="form-control" name="max_tx_diarias" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit">Agregar Tercero</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead><tr><th>Alias</th><th>Número de cuenta</th><th>Límite op.</th><th>Máx/ día</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php if (!empty($terceros ?? [])): ?>
        <?php foreach ($terceros as $t): ?>
          <tr>
            <td><?= htmlspecialchars((string)$t['alias']) ?></td>
            <td><?= htmlspecialchars((string)$t['numero_cuenta']) ?></td>
            <td><?= htmlspecialchars((string)$t['monto_max_op']) ?></td>
            <td><?= htmlspecialchars((string)$t['max_tx_diarias']) ?></td>
            <td><?= (int)$t['activo'] === 1 ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?></td>
            <td>
              <form action="/cliente/terceros/toggle" method="post" class="d-inline" data-confirm="¿Cambiar estado del tercero?">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="tercero_id" value="<?= (int)$t['id'] ?>">
                <input type="hidden" name="activo" value="<?= (int)$t['activo'] === 1 ? 0 : 1 ?>">
                <button type="submit" class="btn btn-sm <?= (int)$t['activo'] === 1 ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                  <?= (int)$t['activo'] === 1 ? 'Desactivar' : 'Activar' ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">No tienes terceros agregados</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

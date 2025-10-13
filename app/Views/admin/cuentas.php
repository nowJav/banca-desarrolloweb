<?php $csrf = \App\core\Csrf::token('admin_cuentas'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4">Cuentas</h1>
  <a href="/cajero/crear-cuenta" class="btn btn-primary">Crear cuenta</a>
  
</div>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Cliente</th>
        <th>Tipo</th>
        <th>Saldo</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($cuentas ?? [])): ?>
        <?php foreach ($cuentas as $c): ?>
          <tr>
            <td><?= (int)($c['id'] ?? 0) ?></td>
            <td><?= htmlspecialchars((string)($c['cliente'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($c['tipo'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($c['saldo'] ?? '')) ?></td>
            <td>
              <span class="badge bg-<?= (($c['estado'] ?? '') === 'bloqueada') ? 'warning' : 'success' ?>"><?= htmlspecialchars((string)($c['estado'] ?? '')) ?></span>
            </td>
            <td>
              <div class="d-flex gap-2">
                <form action="/admin/cuentas/bloquear" method="post" onsubmit="return confirm('¿Bloquear esta cuenta?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="cuenta_id" value="<?= (int)($c['id'] ?? 0) ?>">
                  <button class="btn btn-sm btn-outline-warning" type="submit">Bloquear</button>
                </form>
                <form action="/admin/cuentas/desbloquear" method="post" onsubmit="return confirm('¿Desbloquear esta cuenta?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="cuenta_id" value="<?= (int)($c['id'] ?? 0) ?>">
                  <button class="btn btn-sm btn-outline-success" type="submit">Desbloquear</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">Sin datos</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>


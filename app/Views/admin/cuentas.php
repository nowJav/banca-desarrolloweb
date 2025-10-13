<?php $csrf = \App\core\Csrf::token('admin_cuentas'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h1 class="h4 m-0">Cuentas</h1>
  <form class="d-flex gap-2" method="get" action="/admin/cuentas" role="search">
    <input class="form-control" type="search" name="numero" value="<?= htmlspecialchars((string)($numero ?? '')) ?>" placeholder="Número de cuenta">
    <input class="form-control" type="search" name="dpi" value="<?= htmlspecialchars((string)($dpi ?? '')) ?>" placeholder="DPI">
    <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    <a href="/cajero/crear-cuenta" class="btn btn-primary">Crear cuenta</a>
  </form>
</div>

<?php if (!empty($cuenta ?? null)): ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-1">Cuenta <?= htmlspecialchars((string)$cuenta['numero_cuenta']) ?></h5>
          <div class="small text-muted">Titular: <?= htmlspecialchars((string)$cuenta['nombre']) ?> · DPI: <?= htmlspecialchars((string)$cuenta['dpi']) ?></div>
        </div>
        <div>
          <span class="badge text-bg-<?= ($cuenta['estado']==='bloqueada'?'warning':($cuenta['estado']==='cerrada'?'dark':'success')) ?>"><?= htmlspecialchars((string)$cuenta['estado']) ?></span>
        </div>
      </div>
      <div class="mt-2"><strong>Saldo:</strong> <?= htmlspecialchars((string)$cuenta['saldo']) ?></div>
      <div class="mt-3 d-flex gap-2">
        <form action="/admin/cuentas/bloquear" method="post" data-confirm="¿Bloquear esta cuenta?">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="cuenta_id" value="<?= (int)$cuenta['id'] ?>">
          <button class="btn btn-outline-warning btn-sm" type="submit">Bloquear</button>
        </form>
        <form action="/admin/cuentas/desbloquear" method="post" data-confirm="¿Desbloquear esta cuenta?">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="cuenta_id" value="<?= (int)$cuenta['id'] ?>">
          <button class="btn btn-outline-success btn-sm" type="submit">Desbloquear</button>
        </form>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h6 class="card-title">Últimos movimientos</h6>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead><tr><th>Fecha</th><th>Tipo</th><th>Monto</th><th>id_tx</th><th>Glosa</th></tr></thead>
          <tbody>
            <?php if (!empty($movs ?? [])): foreach ($movs as $m): ?>
              <tr>
                <td><?= htmlspecialchars((string)$m['creado_at']) ?></td>
                <td><?= htmlspecialchars((string)$m['tipo']) ?></td>
                <td><?= htmlspecialchars((string)$m['monto']) ?></td>
                <td><?= htmlspecialchars((string)($m['id_tx'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string)($m['glosa'] ?? '')) ?></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="5" class="text-center">No hay movimientos recientes</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>


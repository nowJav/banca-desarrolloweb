<?php // vista vacía estado de cuenta (cliente) ?>
<?php $token = \App\core\Csrf::token('cliente_estado_cuenta'); ?>
<h1 class="h4 mb-3">Estado de cuenta</h1>
<form action="/cliente/estado-cuenta" method="get" class="row g-3 mb-3">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-4">
    <label class="form-label">Desde</label>
    <input type="date" class="form-control" name="desde" value="<?= htmlspecialchars((string)($desde ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Hasta</label>
    <input type="date" class="form-control" name="hasta" value="<?= htmlspecialchars((string)($hasta ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Cuenta</label>
    <select class="form-select" name="numero">
      <?php foreach (($cuentas ?? []) as $num): ?>
        <option value="<?= htmlspecialchars((string)$num) ?>" <?= ($numero ?? '')===$num? 'selected':'' ?>><?= htmlspecialchars((string)$num) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 d-flex justify-content-end">
    <button class="btn btn-primary" type="submit">Filtrar</button>
    <?php $qs = http_build_query(['desde'=>$desde,'hasta'=>$hasta,'numero'=>$numero,'format'=>'csv']); ?>
    <a class="btn btn-outline-secondary ms-2" href="/cliente/estado-cuenta?<?= $qs ?>">Descargar CSV</a>
  </div>
</form>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Fecha/Hora</th><th>Tipo</th><th>Crédito</th><th>Débito</th><th>id_tx</th><th>Glosa</th></tr></thead>
    <tbody>
      <?php if (!empty($movimientos ?? [])): ?>
        <?php foreach ($movimientos as $m): ?>
          <tr>
            <td><?= htmlspecialchars((string)($m['creado_at'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($m['tipo'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($m['credito'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($m['debito'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($m['id_tx'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($m['glosa'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">No hay movimientos en el rango seleccionado</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

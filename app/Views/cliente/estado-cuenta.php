<?php // vista vacía estado de cuenta (cliente) ?>
<?php $token = \App\core\Csrf::token('cliente_estado_cuenta'); ?>
<h1 class="h4 mb-3">Estado de cuenta</h1>
<form action="#" method="get" class="row g-3 mb-3">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-4">
    <label class="form-label">Desde</label>
    <input type="date" class="form-control" name="desde">
  </div>
  <div class="col-md-4">
    <label class="form-label">Hasta</label>
    <input type="date" class="form-control" name="hasta">
  </div>
  <div class="col-md-4 d-flex align-items-end">
    <button class="btn btn-primary" type="submit">Filtrar</button>
  </div>
</form>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Fecha</th><th>Tipo</th><th>Monto</th><th>Descripción</th></tr></thead>
    <tbody>
      <?php if (!empty($movimientos ?? [])): ?>
        <?php foreach ($movimientos as $m): ?>
          <tr>
            <td><?= htmlspecialchars((string)($m['fecha'] ?? $m['creado_en'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($m['tipo'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($m['monto'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($m['descripcion'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" class="text-center">Sin datos</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

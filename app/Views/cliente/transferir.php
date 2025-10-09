<?php // vista vacía transferir (cliente) ?>
<?php $token = \App\core\Csrf::token('cliente_transferir'); ?>
<h1 class="h4 mb-3">Transferir</h1>
<form action="/cliente/transferir" method="post" class="row g-3">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-6">
    <label class="form-label">Tercero</label>
    <select class="form-select" name="tercero_id" required>
      <option value="">Seleccione...</option>
      <?php foreach (($terceros ?? []) as $t): ?>
        <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars((string)$t['nombre']) ?> (<?= htmlspecialchars((string)$t['cuenta']) ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Monto</label>
    <input type="number" step="0.01" class="form-control" name="monto" required>
  </div>
  <div class="col-12">
    <label class="form-label">Descripción</label>
    <input type="text" class="form-control" name="descripcion">
  </div>
  <div class="col-12">
    <button class="btn btn-success" type="submit">Transferir</button>
  </div>
</form>

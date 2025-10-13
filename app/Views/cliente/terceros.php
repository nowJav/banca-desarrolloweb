<?php // vista vacía terceros (cliente) ?>
<?php $token = \App\core\Csrf::token('cliente_terceros'); ?>
<h1 class="h4 mb-3">Terceros</h1>
<form action="/cliente/terceros" method="post" class="row g-3 mb-4" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-4">
    <label class="form-label">Nombre</label>
    <input type="text" class="form-control" name="nombre" required minlength="2" maxlength="80">
    <div class="form-text">Entre 2 y 80 caracteres.</div>
  </div>
  <div class="col-md-4">
    <label class="form-label">Documento</label>
    <input type="text" class="form-control" name="documento" required pattern="^\d{6,25}$">
    <div class="form-text">Solo dígitos, 6–25.</div>
  </div>
  <div class="col-md-4">
    <label class="form-label">Banco</label>
    <input type="text" class="form-control" name="banco" required minlength="2" maxlength="60">
  </div>
  <div class="col-md-6">
    <label class="form-label">Cuenta</label>
    <input type="text" class="form-control" name="cuenta" required pattern="^[A-Za-z0-9-]{4,30}$">
    <div class="form-text">Letras/números/guion, 4–30.</div>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit">Agregar tercero</button>
  </div>
</form>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Nombre</th><th>Documento</th><th>Banco</th><th>Cuenta</th></tr></thead>
    <tbody>
      <?php if (!empty($terceros ?? [])): ?>
        <?php foreach ($terceros as $t): ?>
          <tr>
            <td><?= htmlspecialchars((string)$t['nombre']) ?></td>
            <td><?= htmlspecialchars((string)$t['documento']) ?></td>
            <td><?= htmlspecialchars((string)$t['banco']) ?></td>
            <td><?= htmlspecialchars((string)$t['cuenta']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" class="text-center">Sin datos</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

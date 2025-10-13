<?php $token = \App\core\Csrf::token('cajero_crear_cuenta'); ?>
<h1 class="h4 mb-3">Crear cuenta</h1>
<form action="/cajero/crear-cuenta" method="post" class="row g-3" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-6">
    <label class="form-label">Nombre del titular</label>
    <input type="text" class="form-control" name="nombre" required minlength="2" maxlength="140" placeholder="Nombre y apellido">
  </div>
  <div class="col-md-6">
    <label class="form-label">DPI</label>
    <input type="text" class="form-control" name="dpi" required pattern="^\d{6,25}$" placeholder="1000000000101">
  </div>
  <div class="col-md-6">
    <label class="form-label">Número de cuenta</label>
    <input type="text" class="form-control" name="numero_cuenta" required pattern="^[A-Za-z0-9-]{6,24}$" placeholder="110-000-0001">
  </div>
  <div class="col-md-6">
    <label class="form-label">Monto inicial (opcional)</label>
    <input type="number" step="0.01" min="0" class="form-control" name="saldo_inicial" value="0">
  </div>
  <div class="col-12">
    <button class="btn btn-success" type="submit">Crear</button>
  </div>
</form>
<?php if (!empty($numero ?? null)): ?>
  <div class="alert alert-success mt-3" role="alert">Cuenta creada: <?= htmlspecialchars((string)$numero) ?></div>
<?php endif; ?>

<?php // vista vacía depósito (cajero) ?>
<?php $token = \App\core\Csrf::token('cajero_deposito'); ?>
<h1 class="h4 mb-3">Depósito</h1>
<form action="/cajero/deposito" method="post" class="row g-3">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-6">
    <label class="form-label">Cuenta</label>
    <input type="text" class="form-control" name="cuenta" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Monto</label>
    <input type="number" step="0.01" class="form-control" name="monto" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit">Registrar depósito</button>
  </div>
</form>

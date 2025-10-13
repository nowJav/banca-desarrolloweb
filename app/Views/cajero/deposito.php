<?php $token = \App\core\Csrf::token('cajero_deposito'); ?>
<h1 class="h4 mb-3">Depósito</h1>
<form action="/cajero/deposito" method="post" class="row g-3" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-6">
    <label class="form-label">Número de cuenta</label>
    <input type="text" class="form-control" name="numero_cuenta" required pattern="^[A-Za-z0-9-]{6,24}$" placeholder="110-000-0001">
  </div>
  <div class="col-md-6">
    <label class="form-label">Monto</label>
    <input type="number" step="0.01" min="0.01" class="form-control" name="monto" required>
    <div class="form-text">Mínimo 0.01</div>
  </div>
  <div class="col-12">
    <label class="form-label">Glosa (opcional)</label>
    <input type="text" class="form-control" name="glosa" maxlength="200" placeholder="Referencia de la operación">
  </div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit">Registrar depósito</button>
  </div>
</form>
<?php if (isset($saldo_antes) && isset($saldo_despues)): ?>
  <div class="card mt-3"><div class="card-body">
    <h6 class="card-title mb-2">Resumen</h6>
    <div>Saldo anterior: <strong><?= htmlspecialchars((string)$saldo_antes) ?></strong></div>
    <div>Saldo actual: <strong><?= htmlspecialchars((string)$saldo_despues) ?></strong></div>
  </div></div>
<?php endif; ?>


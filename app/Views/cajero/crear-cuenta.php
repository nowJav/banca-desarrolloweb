<?php $token = \App\core\Csrf::token('cajero_crear_cuenta'); ?>
<h1 class="h4 mb-3">Crear cuenta</h1>
<form action="/cajero/crear-cuenta" method="post" class="row g-3">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-6">
    <label class="form-label">Cliente ID</label>
    <input type="number" class="form-control" name="cliente_id" required min="1" step="1">
    <div class="form-text">ID numérico del cliente.</div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Tipo de cuenta</label>
    <select class="form-select" name="tipo" required>
      <option value="ahorros">Ahorros</option>
      <option value="corriente">Corriente</option>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Saldo inicial</label>
    <input type="number" step="0.01" min="0" class="form-control" name="saldo_inicial" required>
    <div class="form-text">Mínimo 0.00</div>
  </div>
  <div class="col-12">
    <button class="btn btn-success" type="submit">Crear</button>
  </div>
</form>

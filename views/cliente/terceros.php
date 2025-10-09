<?php $token = \App\core\Csrf::token('cliente_terceros'); ?>
<h1 class="h4 mb-3">Terceros</h1>
<form action="#" method="post" class="row g-3 mb-4">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-4">
    <label class="form-label">Nombre</label>
    <input type="text" class="form-control" name="nombre" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Documento</label>
    <input type="text" class="form-control" name="documento" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Banco</label>
    <input type="text" class="form-control" name="banco" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Cuenta</label>
    <input type="text" class="form-control" name="cuenta" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit">Agregar tercero</button>
  </div>
</form>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Nombre</th><th>Documento</th><th>Banco</th><th>Cuenta</th></tr></thead>
    <tbody>
      <tr><td colspan="4" class="text-center">Sin datos</td></tr>
    </tbody>
  </table>
</div>

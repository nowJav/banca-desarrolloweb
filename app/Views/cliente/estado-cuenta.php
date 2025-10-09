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
      <tr><td colspan="4" class="text-center">Sin datos</td></tr>
    </tbody>
  </table>
</div>

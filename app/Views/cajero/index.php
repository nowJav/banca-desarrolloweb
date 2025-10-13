<section class="py-2">
  <h1 class="h4 mb-3">Panel de Cajero</h1>
  <div class="row g-3 row-cols-1 row-cols-md-3">
    <div class="col">
      <a class="btn btn-outline-primary w-100 py-4" href="/cajero/crear-cuenta">Crear Cuenta</a>
    </div>
    <div class="col">
      <a class="btn btn-outline-success w-100 py-4" href="/cajero/deposito">Depósito</a>
    </div>
    <div class="col">
      <a class="btn btn-outline-warning w-100 py-4" href="/cajero/retiro">Retiro</a>
    </div>
  </div>
  <div class="card mt-4"><div class="card-body d-flex justify-content-between align-items-center">
    <div>Operaciones realizadas hoy</div>
    <div class="fs-4 fw-bold"><?= (int)($hoy ?? 0) ?></div>
  </div></div>
  <div class="mt-3">
    <a class="btn btn-link" href="/cajero/movimientos">Ver movimientos</a>
  </div>
</section>


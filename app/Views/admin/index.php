<?php // vista vacía admin dashboard ?>
<?php $k = $kpis ?? []; ?>
<div class="row mb-4">
  <div class="col-md-3">
    <div class="card text-bg-light"><div class="card-body"><h6 class="mb-1">Total clientes</h6><div class="fs-4" id="kpi_clientes"><?= htmlspecialchars((string)($k['total_clientes'] ?? '--')) ?></div></div></div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-light"><div class="card-body"><h6 class="mb-1">Cuentas activas</h6><div class="fs-4" id="kpi_cuentas"><?= htmlspecialchars((string)($k['cuentas_activas'] ?? '--')) ?></div></div></div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-light"><div class="card-body"><h6 class="mb-1">Depósitos hoy</h6><div class="fs-4" id="kpi_depositos"><?= htmlspecialchars((string)($k['depositos_hoy'] ?? '--')) ?></div></div></div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-light"><div class="card-body"><h6 class="mb-1">Retiros hoy</h6><div class="fs-4" id="kpi_retiros"><?= htmlspecialchars((string)($k['retiros_hoy'] ?? '--')) ?></div></div></div>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <h5 class="card-title">Actividad</h5>
    <canvas id="kpiChart" height="120"></canvas>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  (function(){
    const ctx = document.getElementById('kpiChart');
    if (!ctx) return;
    const data = {
      labels: ['Depósitos', 'Retiros'],
      datasets: [{
        label: 'Operaciones hoy',
        data: [Number(document.getElementById('kpi_depositos')?.textContent || 0), Number(document.getElementById('kpi_retiros')?.textContent || 0)],
        backgroundColor: ['rgba(25,135,84,.5)','rgba(220,53,69,.5)'],
        borderColor: ['#198754','#dc3545'],
        borderWidth: 1
      }]
    };
    new Chart(ctx, { type: 'bar', data, options: { scales: { y: { beginAtZero: true }}}});
  })();
</script>

<?php $k = $kpis ?? []; ?>
<div class="row g-3 mb-4 row-cols-1 row-cols-md-5">
  <div class="col"><div class="card text-bg-light h-100"><div class="card-body"><h6 class="mb-1">Cuentas creadas hoy</h6><div class="fs-4" id="kpi_cuentas_creadas"><?= htmlspecialchars((string)($k['cuentas_creadas'] ?? '--')) ?></div></div></div></div>
  <div class="col"><div class="card text-bg-light h-100"><div class="card-body"><h6 class="mb-1">Clientes registrados hoy</h6><div class="fs-4" id="kpi_clientes_registrados"><?= htmlspecialchars((string)($k['clientes_registrados'] ?? '--')) ?></div></div></div></div>
  <div class="col"><div class="card text-bg-light h-100"><div class="card-body"><h6 class="mb-1">Transacciones hoy</h6><div class="fs-4" id="kpi_transacciones"><?= htmlspecialchars((string)($k['transacciones'] ?? '--')) ?></div></div></div></div>
  <div class="col"><div class="card text-bg-light h-100"><div class="card-body"><h6 class="mb-1">Depósitos hoy</h6><div class="fs-4" id="kpi_depositos"><?= htmlspecialchars((string)($k['depositos'] ?? '--')) ?></div></div></div></div>
  <div class="col"><div class="card text-bg-light h-100"><div class="card-body"><h6 class="mb-1">Retiros hoy</h6><div class="fs-4" id="kpi_retiros"><?= htmlspecialchars((string)($k['retiros'] ?? '--')) ?></div></div></div></div>
</div>

<div class="card mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="card-title m-0">Depósitos vs Retiros</h5>
      <div class="btn-group" role="group" aria-label="Rango de tiempo">
        <button class="btn btn-sm btn-outline-secondary" data-range="today">Hoy</button>
        <button class="btn btn-sm btn-outline-secondary" data-range="yesterday">Ayer</button>
        <button class="btn btn-sm btn-outline-secondary" data-range="7d">Últimos 7 días</button>
      </div>
    </div>
    <canvas id="kpiChart" height="120" aria-label="Gráfica depósitos vs retiros"></canvas>
    <div class="small text-muted mt-2">Estado del sistema: semillas cargadas, versión <?= htmlspecialchars((string)(\App\config\App::env('APP_VERSION','0.1.0'))) ?>, servidor <?= htmlspecialchars(date('Y-m-d H:i:s')) ?></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  (function(){
    const ctx = document.getElementById('kpiChart');
    if (!ctx) return;
    let chart = new Chart(ctx, { type: 'bar', data: { labels:['Depósitos','Retiros'], datasets:[{label:'Totales', data:[0,0], backgroundColor:['rgba(25,135,84,.5)','rgba(220,53,69,.5)'], borderColor:['#198754','#dc3545'], borderWidth:1}]} , options:{scales:{y:{beginAtZero:true}}}});
    async function load(range){
      try {
        const res = await fetch('/api/kpi-series?range='+encodeURIComponent(range));
        const j = await res.json();
        chart.data.datasets[0].data = [Number(j.depositos||0), Number(j.retiros||0)];
        chart.update();
      } catch(e){}
    }
    document.querySelectorAll('[data-range]').forEach(btn=>{
      btn.addEventListener('click', ()=>{ load(btn.getAttribute('data-range')); });
    });
    load('today');
  })();
</script>


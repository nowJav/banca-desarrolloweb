<?php $token = \App\core\Csrf::token('cliente_transferir'); ?>
<h1 class="h4 mb-3">Transferir</h1>
<form action="/cliente/transferir" method="post" class="row g-3" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
  <div class="col-md-6">
    <label class="form-label">Tercero</label>
    <select class="form-select" name="tercero_id" required>
      <option value="">Seleccione...</option>
      <?php foreach (($terceros ?? []) as $t): ?>
        <option value="<?= (int)$t['id'] ?>" data-max="<?= htmlspecialchars((string)($t['monto_max_op'] ?? '')) ?>" data-diarias="<?= htmlspecialchars((string)($t['max_tx_diarias'] ?? '')) ?>" data-numero="<?= htmlspecialchars((string)($t['numero_cuenta'] ?? '')) ?>"><?= htmlspecialchars((string)$t['alias']) ?> (<?= htmlspecialchars((string)$t['numero_cuenta']) ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Monto</label>
    <input type="number" step="0.01" min="0.01" class="form-control" name="monto" required>
    <div class="form-text">Mínimo 0.01</div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h6 class="card-title">Resumen de límites</h6>
      <div>Número: <strong id="res_num">--</strong></div>
      <div>Límite por operación: <strong id="res_max">--</strong></div>
      <div>Máx. transacciones por día: <strong id="res_maxd">--</strong></div>
      <div>Usadas hoy: <strong id="res_usadas">--</strong></div>
      <div>Monto acumulado hoy: <strong id="res_monto">--</strong></div>
    </div></div>
  </div>
  <div class="col-12">
    <button class="btn btn-success" type="submit">Transferir</button>
  </div>
</form>
<script>
  (function(){
    const sel = document.querySelector('select[name="tercero_id"]');
    async function update(){
      const opt = sel.options[sel.selectedIndex]; if (!opt || !opt.value) { set('--','--','--','--','--'); return; }
      const max = opt.getAttribute('data-max')||'--', maxd = opt.getAttribute('data-diarias')||'--', num=opt.getAttribute('data-numero')||'--';
      document.getElementById('res_num').textContent = num; document.getElementById('res_max').textContent = max; document.getElementById('res_maxd').textContent = maxd;
      try{
        const tid = opt.value;
        const res = await fetch('/api/tercero-resumen?tercero_id='+encodeURIComponent(tid));
        const j = await res.json();
        document.getElementById('res_usadas').textContent = String(j.conteo ?? 0);
        document.getElementById('res_monto').textContent = String(j.monto ?? 0);
      }catch(e){ document.getElementById('res_usadas').textContent='--'; document.getElementById('res_monto').textContent='--'; }
    }
    function set(a,b,c,d,e){ document.getElementById('res_num').textContent=a; document.getElementById('res_max').textContent=b; document.getElementById('res_maxd').textContent=c; document.getElementById('res_usadas').textContent=d; document.getElementById('res_monto').textContent=e; }
    sel?.addEventListener('change', update); update();
  })();
</script>

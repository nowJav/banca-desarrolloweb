<h1 class="h4 mb-3">Movimientos</h1>
<form id="movForm" class="row g-3 mb-3" method="get" action="/cajero/movimientos">
  <div class="col-md-6">
    <label class="form-label">Número de cuenta</label>
    <input id="numeroInput" type="text" class="form-control" name="numero" value="<?= htmlspecialchars((string)($numero ?? '')) ?>" placeholder="110-000-0001" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Por página</label>
    <select class="form-select" name="perPage">
      <?php foreach ([10,20,50,100] as $pp): ?>
        <option value="<?= $pp ?>" <?= (int)($perPage ?? 20) === $pp ? 'selected' : '' ?>><?= $pp ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3 d-flex align-items-end">
    <button class="btn btn-primary" type="submit">Buscar</button>
  </div>
  <input type="hidden" name="page" value="<?= (int)($page ?? 1) ?>">
  <input type="hidden" name="numero" value="<?= htmlspecialchars((string)($numero ?? '')) ?>">
  <input type="hidden" name="perPage" value="<?= (int)($perPage ?? 20) ?>">
  <script>
    // Debounce de 500ms para el campo número
    (function(){
      const input = document.getElementById('numeroInput');
      const form = document.getElementById('movForm');
      let t; if (!input || !form) return;
      input.addEventListener('input', function(){
        clearTimeout(t);
        t = setTimeout(()=>{ form.submit(); }, 500);
      });
    })();
  </script>
</form>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Fecha/Hora</th><th>Tipo</th><th>Monto</th><th>Glosa</th><th>Operador</th></tr></thead>
    <tbody>
      <?php if (!empty($rows ?? [])): foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars((string)$r['creado_at']) ?></td>
          <td><?= htmlspecialchars((string)$r['tipo']) ?></td>
          <td><?= htmlspecialchars((string)$r['monto']) ?></td>
          <td><?= htmlspecialchars((string)($r['glosa'] ?? '')) ?></td>
          <td><?= htmlspecialchars((string)($r['operador'] ?? '')) ?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5" class="text-center">Ingresa un número de cuenta para ver movimientos</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php if (($total ?? 0) > 0): $pages = max(1, (int)ceil(($total ?? 0) / max(1,(int)($perPage ?? 20)))); $cur=(int)($page ?? 1); ?>
  <nav aria-label="Paginación" class="mt-2">
    <ul class="pagination">
      <?php for ($p=1;$p<=$pages;$p++): $qs = http_build_query(['numero'=>$numero,'perPage'=>$perPage,'page'=>$p]); ?>
        <li class="page-item <?= $p===$cur ? 'active' : '' ?>"><a class="page-link" href="/cajero/movimientos?<?= $qs ?>"><?= $p ?></a></li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>

<h1 class="h4 mb-3">Auditoría</h1>
<form class="row g-3 mb-3" method="get" action="/admin/auditoria">
  <div class="col-md-2">
    <label class="form-label">Desde</label>
    <input type="date" class="form-control" name="desde" value="<?= htmlspecialchars((string)($desde ?? '')) ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">Hasta</label>
    <input type="date" class="form-control" name="hasta" value="<?= htmlspecialchars((string)($hasta ?? '')) ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">Entidad</label>
    <select class="form-select" name="entidad">
      <option value="">Todas</option>
      <?php foreach (['session','cuenta','usuario','tercero','transferencia'] as $e): ?>
        <option value="<?= $e ?>" <?= ($entidad ?? '') === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Usuario</label>
    <select class="form-select" name="usuario_id">
      <option value="">Todos</option>
      <?php foreach (($users ?? []) as $u): ?>
        <option value="<?= (int)$u['id'] ?>" <?= (int)($usuario_id ?? 0) === (int)$u['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$u['email']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">IP</label>
    <input type="text" class="form-control" name="ip" value="<?= htmlspecialchars((string)($ip ?? '')) ?>">
  </div>
  <div class="col-md-1 d-flex align-items-end">
    <button class="btn btn-primary w-100" type="submit">Filtrar</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead><tr><th>Fecha/Hora</th><th>Usuario</th><th>Entidad</th><th>Acción</th><th>Entidad ID</th><th>IP</th><th>Detalle</th></tr></thead>
    <tbody>
      <?php if (!empty($rows ?? [])): foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars((string)$r['creado_at']) ?></td>
          <td><?= htmlspecialchars((string)($r['usuario'] ?? '')) ?></td>
          <td><?= htmlspecialchars((string)$r['entidad']) ?></td>
          <td><?= htmlspecialchars((string)$r['accion']) ?></td>
          <td><?= htmlspecialchars((string)$r['entidad_id']) ?></td>
          <td><?= htmlspecialchars((string)($r['ip'] ?? '')) ?></td>
          <td>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detailModal" data-prev='<?= htmlspecialchars((string)$r['datos_previos']) ?>' data-nuevos='<?= htmlspecialchars((string)$r['datos_nuevos']) ?>'>Ver</button>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="7" class="text-center">No hay eventos que coincidan</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php if (($total ?? 0) > 0): $pages = max(1, (int)ceil(($total ?? 0) / max(1,(int)($perPage ?? 50)))); $cur=(int)($page ?? 1); ?>
  <nav aria-label="Paginación" class="mt-2">
    <ul class="pagination">
      <?php for ($p=1;$p<=$pages;$p++): $qs = http_build_query(['desde'=>$desde,'hasta'=>$hasta,'entidad'=>$entidad,'usuario_id'=>$usuario_id,'ip'=>$ip,'perPage'=>$perPage,'page'=>$p]); ?>
        <li class="page-item <?= $p===$cur ? 'active' : '' ?>"><a class="page-link" href="/admin/auditoria?<?= $qs ?>"><?= $p ?></a></li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Detalle de auditoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <h6>Datos previos</h6>
            <pre id="detailPrev" class="bg-light p-2 small" style="white-space: pre-wrap;"></pre>
          </div>
          <div class="col-md-6">
            <h6>Datos nuevos</h6>
            <pre id="detailNew" class="bg-light p-2 small" style="white-space: pre-wrap;"></pre>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
  <script>
    const detailModal = document.getElementById('detailModal');
    detailModal?.addEventListener('show.bs.modal', ev => {
      const btn = ev.relatedTarget;
      const prev = btn?.getAttribute('data-prev') || '';
      const nuevos = btn?.getAttribute('data-nuevos') || '';
      document.getElementById('detailPrev').textContent = formatJSON(prev);
      document.getElementById('detailNew').textContent = formatJSON(nuevos);
    });
    function formatJSON(s){ try { return JSON.stringify(JSON.parse(s), null, 2);} catch(e){ return s; } }
  </script>
</div>


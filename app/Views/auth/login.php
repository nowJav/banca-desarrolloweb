<?php $token = \App\core\Csrf::token('login'); ?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <h1 class="h4 mb-1">Iniciar sesión</h1>
    <?php if (!empty($roleLabel)): ?>
      <span class="badge text-bg-secondary mb-3"><?= htmlspecialchars((string)$roleLabel) ?></span>
    <?php endif; ?>
    <form action="/login" method="post" novalidate aria-label="Formulario de ingreso">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required autocomplete="email" maxlength="160" placeholder="tu@correo.com">
        <div class="form-text">Usa tu correo registrado.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="password" required minlength="8" autocomplete="current-password" placeholder="********">
        <div class="form-text">Mínimo 8 caracteres.</div>
      </div>
      <button class="btn btn-primary w-100" type="submit">Ingresar</button>
      <div class="mt-3">
        <a href="/registro" class="small">¿No tienes cuenta? Regístrate</a>
      </div>
    </form>
  </div>
</div>


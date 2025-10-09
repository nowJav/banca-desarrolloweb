<?php $token = \App\core\Csrf::token('login'); ?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <h1 class="h4 mb-3">Iniciar sesión</h1>
    <form action="/login" method="post">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="password" required>
      </div>
      <button class="btn btn-primary w-100" type="submit">Entrar</button>
    </form>
    <div class="mt-3 text-center">
      <a href="/registro">Crear cuenta de cliente</a>
    </div>
  </div>
</div>

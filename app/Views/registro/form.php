<?php // vista vacía registro de cliente ?>
<?php $token = \App\core\Csrf::token('registro_cliente'); ?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h1 class="h4 mb-3">Registro de Cliente</h1>
    <form action="/registro" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
      <div class="mb-3">
        <label class="form-label">Nombre completo</label>
        <input type="text" class="form-control" name="nombre" required minlength="2" maxlength="140" autocomplete="name">
        <div class="form-text">Entre 2 y 140 caracteres.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required maxlength="160" autocomplete="email">
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="password" required minlength="8" autocomplete="new-password">
        <div class="form-text">Mínimo 8 caracteres.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirmar contraseña</label>
        <input type="password" class="form-control" name="password_confirmation" required minlength="8" autocomplete="new-password">
      </div>
      <button class="btn btn-success" type="submit">Registrarse</button>
    </form>
  </div>
</div>

<?php // vista vacía registro de cliente ?>
<?php $token = \App\core\Csrf::token('registro_cliente'); ?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h1 class="h4 mb-3">Registro de Cliente</h1>
    <form action="/registro" method="post">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
      <div class="mb-3">
        <label class="form-label">Nombre completo</label>
        <input type="text" class="form-control" name="nombre" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="password" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirmar contraseña</label>
        <input type="password" class="form-control" name="password_confirmation" required>
      </div>
      <button class="btn btn-success" type="submit">Registrarse</button>
    </form>
  </div>
</div>

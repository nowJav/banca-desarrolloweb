<?php $token = \App\core\Csrf::token('registro_cliente'); ?>
<div class="row justify-content-center">
  <div class="col-md-7 col-lg-6">
    <h1 class="h4 mb-3">Registro de Usuario (Cliente)</h1>
    <form action="/registro" method="post" novalidate aria-label="Formulario de registro de cliente">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Número de cuenta</label>
          <input type="text" class="form-control" name="numero_cuenta" required pattern="^[A-Za-z0-9-]{6,24}$" placeholder="110-000-0001">
          <div class="form-text">Formato: letras/números/guion, 6–24.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">DPI</label>
          <input type="text" class="form-control" name="dpi" required pattern="^\d{6,25}$" placeholder="1000000000101">
          <div class="form-text">Solo dígitos, 6–25.</div>
        </div>
        <div class="col-md-12">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required maxlength="160" autocomplete="email" placeholder="tu@correo.com">
        </div>
        <div class="col-md-6">
          <label class="form-label">Contraseña</label>
          <input type="password" class="form-control" name="password" required minlength="8" autocomplete="new-password" placeholder="********">
          <div class="form-text">Mínimo 8 caracteres.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirmar contraseña</label>
          <input type="password" class="form-control" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="********">
        </div>
        <div class="col-12">
          <button class="btn btn-success w-100" type="submit">Registrarse</button>
        </div>
      </div>
    </form>
    <div class="alert alert-info mt-3" role="alert">
      Requisitos de seguridad: usa una contraseña segura y no compartas tus credenciales. El uso del sistema implica aceptar condiciones de uso responsable.
    </div>
  </div>
</div>


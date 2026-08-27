<?php
$contenidoPagina = '
<div class="auth-container">
    <div class="auth-box">
        <h2>Iniciar sesion</h2>
        ' . (!empty($erroresLogin) ? '<div class="flash-error"><p>Credenciales incorrectas.</p></div>' : '') . '
        <form action="?action=login_process" method="POST">
            <div class="form-group">
                <label for="email">Correo electronico</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contrasena</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
            <p class="link-text"><a href="?action=register">No tienes cuenta? Registrate</a></p>
        </form>
    </div>
</div>';
?>
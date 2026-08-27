<?php
$contenidoPagina = '
<div class="auth-container">
    <div class="auth-box">
        <h2>Crear cuenta</h2>
        ' . (!empty($erroresRegistro) ? '<div class="flash-error"><p>' . htmlspecialchars($erroresRegistro[0]) . '</p></div>' : '') . '
        <form action="?action=register_process" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="email">Correo electronico</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contrasena</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Registrarse</button>
            <p class="link-text"><a href="?action=login">Ya tienes cuenta? Inicia sesion</a></p>
        </form>
    </div>
</div>';
?>
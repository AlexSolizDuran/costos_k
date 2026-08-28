<?php
$redirect = $_GET['redirect'] ?? '';
ob_start();
?>
<div class="auth-container">
    <div class="auth-box">
        <h1>Crear cuenta</h1>

        <?php if (!empty($erroresRegistro)): ?>
            <div class="error">
                <?= htmlspecialchars($erroresRegistro[0]) ?>
            </div>
        <?php endif; ?>

        <form action="?action=register_process" method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES) ?>">
            <label> Nombre </label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES) ?>" required>
            <label> Email </label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" required>
            <label> Contraseña </label>
            <input type="password" name="password" required>
            <button type="submit"> Registrarse </button>
        </form>

        <p class="registro-link">
            ¿Ya tienes cuenta?
            <a href="?action=login<?= $redirect !== '' ? '&redirect=' . urlencode($redirect) : '' ?>"> Inicia sesión </a>
        </p>
    </div>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>
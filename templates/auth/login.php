<?php
$redirect = $_GET['redirect'] ?? '';
ob_start();
?>
<div class="auth-container">
    <div class="auth-box">
        <h1>Iniciar sesión</h1>

        <?php if (!empty($erroresLogin)): ?>
            <div class="error">
                <?= htmlspecialchars($erroresLogin[0]) ?>
            </div>
        <?php endif; ?>

        <form action="?action=login_process" method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES) ?>">
            <label> Email </label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" required>
            <label> Contraseña </label>
            <input type="password" name="password" required>
            <button type="submit"> Ingresar </button>
        </form>

        <p class="registro-link">
            ¿No tienes cuenta?
            <a href="?action=register<?= $redirect !== '' ? '&redirect=' . urlencode($redirect) : '' ?>"> Regístrate </a>
        </p>
    </div>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>
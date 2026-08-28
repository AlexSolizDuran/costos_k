<?php
ob_start();
?>
<div class="cabecera">
    <div>
        <a class="volver" href="?action=dashboard"> ← Panel </a>
        <h1 class="titulo"> Cambiar contraseña </h1>
    </div>
</div>

<?php if (!empty($errores)): ?>
    <div class="error">
        <?php foreach ($errores as $err): ?>
            <?= htmlspecialchars($err) ?><br>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
    <div class="flash-info"> Contraseña actualizada correctamente. </div>
<?php endif; ?>

<div class="seccion" style="max-width:480px;">
    <form method="POST" action="?action=cambiar_password">
        <label style="display:block; font-weight:600; margin:10px 0 5px;"> Contraseña actual </label>
        <input type="password" name="password_actual" required style="width:100%; padding:11px; border:1px solid #ccc; border-radius:7px; font-family:inherit; font-size:14px;">

        <label style="display:block; font-weight:600; margin:10px 0 5px;"> Nueva contraseña </label>
        <input type="password" name="password_nueva" minlength="6" required style="width:100%; padding:11px; border:1px solid #ccc; border-radius:7px; font-family:inherit; font-size:14px;">

        <label style="display:block; font-weight:600; margin:10px 0 5px;"> Repetir nueva contraseña </label>
        <input type="password" name="password_repetida" required style="width:100%; padding:11px; border:1px solid #ccc; border-radius:7px; font-family:inherit; font-size:14px;">

        <div class="acciones" style="margin-top:18px;">
            <a class="btn btn-gris" href="?action=dashboard"> Cancelar </a>
            <button type="submit" class="btn btn-negro"> Guardar </button>
        </div>
    </form>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>
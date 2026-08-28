<?php
$rolActualEditar = $_POST['rol'] ?? $usuarioEditar['rol'];
ob_start();
?>
<div class="cabecera">
    <div>
        <a class="volver" href="?action=admin_usuarios"> ← Usuarios </a>
        <h1 class="titulo"> Modificar usuario </h1>
    </div>
</div>

<?php if (!empty($errores)): ?>
    <div class="error">
        <?php foreach ($errores as $err): ?>
            <?= htmlspecialchars($err) ?><br>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="seccion" style="max-width:480px;">
    <form method="POST" action="?action=admin_usuario_form&id=<?= (int) $usuarioEditar['id'] ?>">
        <label style="display:block; font-weight:600; margin:10px 0 5px;"> Nombre </label>
        <input type="text" name="nombre" maxlength="150" required style="width:100%; padding:11px; border:1px solid #ccc; border-radius:7px; font-family:inherit; font-size:14px;"
               value="<?= htmlspecialchars($_POST['nombre'] ?? $usuarioEditar['nombre'], ENT_QUOTES) ?>">

        <label style="display:block; font-weight:600; margin:10px 0 5px;"> Email </label>
        <input type="email" name="email" required style="width:100%; padding:11px; border:1px solid #ccc; border-radius:7px; font-family:inherit; font-size:14px;"
               value="<?= htmlspecialchars($_POST['email'] ?? $usuarioEditar['email'], ENT_QUOTES) ?>">

        <label style="display:block; font-weight:600; margin:10px 0 5px;"> Rol </label>
        <select name="rol" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:7px; font-family:inherit; font-size:14px;">
            <option value="<?= ROL_MEMBRO ?>" <?= ($rolActualEditar === ROL_MEMBRO ? 'selected' : '') ?>> Miembro </option>
            <option value="<?= ROL_ADMIN ?>" <?= ($rolActualEditar === ROL_ADMIN ? 'selected' : '') ?>> Administrador </option>
        </select>

        <label style="display:block; font-weight:600; margin:10px 0 5px;"> Nueva contraseña <span style="font-weight:normal; color:#777;"> (déjala vacía para no cambiarla) </span></label>
        <input type="password" name="password" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:7px; font-family:inherit; font-size:14px;">

        <div class="acciones" style="margin-top:18px;">
            <a class="btn btn-gris" href="?action=admin_usuarios"> Cancelar </a>
            <button type="submit" class="btn btn-negro"> Guardar </button>
        </div>
    </form>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>
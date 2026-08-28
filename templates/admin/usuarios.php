<?php
ob_start();
?>
<div class="cabecera">
    <div>
        <a class="volver" href="?action=dashboard"> ← Panel </a>
        <h1 class="titulo"> Administración de usuarios </h1>
    </div>
    <div class="acciones">
        <button type="button" class="crear-grupo" onclick="document.getElementById('modalCrearUsuario').style.display='flex';"> + Crear usuario </button>
    </div>
</div>

<?php if (!empty($errores)): ?>
    <div class="error">
        <?php foreach ($errores as $err): ?>
            <?= htmlspecialchars($err) ?><br>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="seccion">
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td> <?= htmlspecialchars($usuario['nombre']) ?> </td>
                    <td> <?= htmlspecialchars($usuario['email']) ?> </td>
                    <td> <?= htmlspecialchars($usuario['rol'] === 'admin' ? 'Administrador' : 'Miembro') ?> </td>
                    <td>
                        <?php if ((bool) $usuario['activo']): ?>
                            <span class="estado estado-activo"> Activo </span>
                        <?php else: ?>
                            <span class="estado estado-desactivado"> Inactivo </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="accion" href="?action=admin_usuario_form&id=<?= (int) $usuario['id'] ?>"> Modificar </a>
                        <br><br>
                        <?php if ((int) $usuario['id'] === (int) $usuarioId): ?>
                            <span style="color:#999;"> (eres tú) </span>
                        <?php else: ?>
                            <a class="accion <?= (bool) $usuario['activo'] ? 'accion-roja' : '' ?>"
                               href="?action=admin_usuario_toggle&id=<?= (int) $usuario['id'] ?>"
                               onclick="return confirm('¿<?= (bool) $usuario['activo'] ? 'Desactivar' : 'Activar' ?> a <?= htmlspecialchars(addslashes($usuario['nombre']), ENT_QUOTES) ?>?');">
                                <?= (bool) $usuario['activo'] ? 'Desactivar' : 'Activar' ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Crear usuario -->
<div class="modal-overlay" id="modalCrearUsuario">
    <div class="modal">
        <div class="modal-cabecera">
            <h2>Crear usuario</h2>
            <button type="button" class="cerrar-modal" onclick="document.getElementById('modalCrearUsuario').style.display='none';"> &times; </button>
        </div>
        <form method="POST" action="?action=admin_usuarios">
            <input type="hidden" name="crear_usuario" value="1">
            <label> Nombre </label>
            <input type="text" name="nombre" maxlength="150" required>
            <label> Email </label>
            <input type="email" name="email" required>
            <label> Contraseña </label>
            <input type="password" name="password" required>
            <label> Rol </label>
            <select name="rol">
                <option value="<?= ROL_MEMBRO ?>"> Miembro </option>
                <option value="<?= ROL_ADMIN ?>"> Administrador </option>
            </select>
            <div class="modal-acciones">
                <button type="button" class="btn-cancelar" onclick="document.getElementById('modalCrearUsuario').style.display='none';"> Cancelar </button>
                <button type="submit" class="btn-guardar"> Crear </button>
            </div>
        </form>
    </div>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>
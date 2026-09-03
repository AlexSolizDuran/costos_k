<?php
$grupoId = (int) $grupo['id'];
$rol = $grupo['mi_rol'];
$esAdminGrupo = ($rol === 'admin');
ob_start();
?>
<div class="cabecera">
    <div>
        <a class="volver" href="?action=dashboard"> ← Mis grupos </a>
        <h1 class="titulo"> <?= htmlspecialchars($grupo['nombre']) ?> </h1>
    </div>
</div>

<!-- ============ Información del grupo ============ -->
<div class="grupo-info">
    <h2>Información del grupo</h2>
    <?php if (!empty($grupo['descripcion'])): ?>
        <div class="descripcion"> <?= nl2br(htmlspecialchars($grupo['descripcion'])) ?> </div>
    <?php else: ?>
        <div class="descripcion"> Este grupo no tiene descripción. </div>
    <?php endif; ?>

    <div class="datos-grupo">
        <div class="dato">
            <strong>Creado por</strong>
            <?= htmlspecialchars($grupo['creador_nombre'] ?? '—') ?>
        </div>
        <div class="dato">
            <strong>Fecha de creación</strong>
            <?= fmtFechaCorta($grupo['fecha_creacion']) ?>
        </div>
        <div class="dato">
            <strong>Estado</strong>
            <?php if ($grupo['estado'] === 'activo'): ?>
                <span class="estado estado-activo"> Activo </span>
            <?php else: ?>
                <span class="estado estado-cerrado"> Cerrado </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============ Administración (solo admin del grupo) ============ -->
<?php if ($esAdminGrupo): ?>
    <div class="administracion">
        <h2>Administración</h2>
        <div class="acciones">
            <button type="button" class="btn btn-negro" onclick="abrirModificarGrupo()"> Modificar grupo </button>
            <a class="btn btn-azul" href="#" onclick="abrirInvitacion(); return false;"> 🔗 Invitar personas al grupo </a>
            <?php if ($grupo['estado'] === 'activo'): ?>
                <a class="btn btn-gris" href="?action=close_group&id=<?= $grupoId ?>" onclick="return confirm('¿Cerrar este grupo?');"> Cerrar grupo </a>
            <?php endif; ?>
            <a class="btn btn-rojo" href="?action=delete_group&id=<?= $grupoId ?>" onclick="return confirm('¿ELIMINAR DEFINITIVAMENTE este grupo y todos sus datos?');"> Eliminar grupo </a>
        </div>
    </div>
<?php endif; ?>

<!-- ============ Integrantes ============ -->
<div class="seccion">
    <div class="seccion-cabecera">
        <h2> Integrantes (<?= count($integrantes) ?>) </h2>
        <?php if ($esAdminGrupo): ?>
            <button type="button" class="btn btn-azul" onclick="abrirAgregarIntegrante()"> + Agregar integrante </button>
        <?php endif; ?>
    </div>

    <?php if (count($integrantes) === 0): ?>
        <div class="sin-datos"> No hay integrantes registrados. </div>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha de unión</th>
                        <?php if ($esAdminGrupo): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($integrantes as $integrante): ?>
                    <tr>
                        <td> <?= htmlspecialchars($integrante['nombre']) ?> </td>
                        <td> <?= htmlspecialchars($integrante['email']) ?> </td>
                        <td> <?= htmlspecialchars($integrante['rol']) ?> </td>
                        <td> <?= fmtFecha($integrante['fecha_union']) ?> </td>
                        <?php if ($esAdminGrupo): ?>
                            <td>
                                <?php if ((int) $integrante['usuario_id'] !== (int) ($grupo['creado_por'] ?? 0)): ?>
                                    <?php if ($integrante['rol'] === 'miembro'): ?>
                                        <a class="accion" href="?action=change_role&grupo_id=<?= $grupoId ?>&usuario_id=<?= (int) $integrante['usuario_id'] ?>&rol=admin" onclick="return confirm('¿Convertir a este usuario en administrador?');"> Hacer admin </a>
                                    <?php else: ?>
                                        <a class="accion" href="?action=change_role&grupo_id=<?= $grupoId ?>&usuario_id=<?= (int) $integrante['usuario_id'] ?>&rol=miembro" onclick="return confirm('¿Cambiar este administrador a miembro?');"> Hacer miembro </a>
                                    <?php endif; ?>
                                    <br><br>
                                    <a class="accion accion-roja" href="?action=remove_member&grupo_id=<?= $grupoId ?>&usuario_id=<?= (int) $integrante['usuario_id'] ?>" onclick="return confirm('¿Eliminar este integrante del grupo?');"> Eliminar </a>
                                <?php else: ?>
                                    <strong>Creador</strong>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="salir" style="text-align: right; margin-top: 10px;">
        <a class="accion accion-roja" href="?action=leave_group&id=<?= $grupoId ?>" onclick="return confirm('¿Seguro que quieres salir de este grupo?');"> Salir del grupo </a>
    </div>
</div>

<!-- ============ Deudores ============ -->
<div class="seccion">
    <div class="seccion-cabecera">
        <h2> Deudores (<?= count($deudores) ?>) </h2>
    </div>

    <?php if (count($deudores) === 0): ?>
        <div class="sin-datos"> No hay deudas pendientes en este grupo. </div>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Total deuda</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($deudores as $deudor): ?>
                    <tr class="deudor-fila" onclick="alternarDeudas(<?= (int) $deudor['usuario_id'] ?>)">
                        <td><strong><?= htmlspecialchars($deudor['nombre']) ?></strong></td>
                        <td><strong class="deuda-total-deudor"><?= bs($deudor['total']) ?></strong></td>
                        <td>
                            <span class="accion"> Ver detalle ▾ </span>
                        </td>
                    </tr>
                    <tr class="deudor-detalle" id="deudor-detalle-<?= (int) $deudor['usuario_id'] ?>" style="display:none;">
                        <td colspan="3">
                            <table class="tabla-interna">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Deuda</th>
                                        <th>Pagar deuda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($deudor['deudas'] as $deuda): ?>
                                    <?php $pendiente = (float) $deuda['monto']; ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($deuda['concepto']) ?></strong>
                                            <br><span style="color:#888; font-size:12px;"><?= fmtFechaCorta($deuda['fecha']) ?></span>
                                        </td>
                                        <td><?= bs($pendiente) ?></td>
                                        <td>
                                            <button type="button" class="accion boton-accion"
                                                onclick="pagarDeuda(<?= (int) $deuda['gasto_id'] ?>, <?= (int) $deudor['usuario_id'] ?>, '<?= e($deudor['nombre']) ?>', <?= number_format($pendiente, 2, '.', '') ?>)">
                                                Pagar deuda
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ============ Gastos ============ -->
<div class="seccion">
    <div class="seccion-cabecera">
        <h2>Gastos</h2>
        <?php if ($grupo['estado'] === 'activo'): ?>
            <a class="btn btn-azul" href="?action=create_expense&grupo_id=<?= $grupoId ?>"> + Agregar gasto </a>
        <?php endif; ?>
    </div>

    <?php if (count($gastos) === 0): ?>
        <div class="sin-datos">
            <h3>No hay gastos todavía</h3>
            <p> Todavía no hay gastos registrados en este grupo. </p>
        </div>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Información</th>
                        <th>Monto</th>
                        <th>Pagado por</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($gastos as $gasto): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($gasto['concepto']) ?></strong></td>
                        <td><?= nl2br(htmlspecialchars($gasto['informacion'] ?? '')) ?></td>
                        <td><strong><?= bs($gasto['monto']) ?></strong></td>
                        <td><?= htmlspecialchars($gasto['pagado_por_nombre']) ?></td>
                        <td><?= fmtFechaCorta($gasto['fecha']) ?></td>
                        <td>
                            <button type="button" class="accion boton-accion" onclick="abrirGasto(<?= (int) $gasto['id'] ?>)"> Ver </button>
                            <?php if ((int) $gasto['user_id'] === (int) $usuarioId): ?>
                                <br><br>
                                <a href="#" class="accion" onclick="abrirModificarGasto(<?= (int) $gasto['id'] ?>); return false;"> Modificar </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/modales_grupo.php'; ?>
<?php
$contenidoPagina = ob_get_clean();
?>
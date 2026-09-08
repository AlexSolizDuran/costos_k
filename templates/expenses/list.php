<?php
$grupoId = (int) $grupo['id'];
ob_start();
?>
<div class="cabecera">
    <div>
        <a class="volver" href="?action=group_detail&id=<?= $grupoId ?>"> ↝ <?= htmlspecialchars($grupo['nombre']) ?> </a>
        <h1 class="titulo"> Gastos del grupo </h1>
    </div>
    <?php if ($grupo['estado'] === 'activo'): ?>
        <div class="acciones">
            <a class="crear-grupo" href="?action=create_expense&grupo_id=<?= $grupoId ?>"> + Agregar gasto </a>
        </div>
    <?php endif; ?>
</div>

<div class="seccion" style="background:#eaf3fb; box-shadow:none; text-align:center;">
    <strong>Tienes pendiente de pagar:</strong>
    <span style="font-size:1.4rem; font-weight:bold; color:#0066cc;"> <?= fmtUsd($totalPendienteUsuario) ?> </span>
</div>

<?php if (count($gastos) === 0): ?>
    <div class="sin-grupos">
        <h2>No hay gastos registrados</h2>
        <p>Todavía no hay gastos en este grupo.</p>
    </div>
<?php else: ?>
    <div class="seccion">
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
                        <td><strong><?= fmtMontoConUsd($gasto['monto'], $gasto['moneda'], $gasto['tasa_usd']) ?></strong></td>
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
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/modales_grupo.php'; ?>
<?php
$contenidoPagina = ob_get_clean();
?>
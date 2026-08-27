<?php
$contenidoPagina = '
<div class="page-header">
    <h1>Grupo: ' . htmlspecialchars($grupo['nombre'] ?? '') . '</h1>
    <p>' . htmlspecialchars($grupo['descripcion'] ?? '') . '</p>
</div>

<div class="group-details">
    <p><strong>Estado:</strong> ' . (($grupo['estado'] ?? '') === 'activo' ? 'Activo' : 'Cerrado') . '</p>
    <p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($grupo['fecha_creacion'] ?? 'now')) . '</p>

    <h3>Integrantes (' . count($integrantes) . ')</h3>
    <table>
        <thead><tr><th>Nombre</th><th>Rol</th></tr></thead>
        <tbody>';

foreach ($integrantes as $integ) {
    $contenidoPagina .= '
        <tr>
            <td>' . htmlspecialchars($integ['nombre']) . '</td>
            <td>' . htmlspecialchars($integ['rol']) . '</td>
        </tr>';
}

$contenidoPagina .= '
        </tbody>
    </table>

    <div style="margin-top:1rem">
        <a href="?action=create_expense&grupo_id=' . ($grupo['id'] ?? '') . '" class="btn-primary">Agregar gasto</a>
        <a href="?action=group_expenses&id=' . ($grupo['id'] ?? '') . '" class="btn-small">Ver gastos</a>
    </div>
</div>';
?>
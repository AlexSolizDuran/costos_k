<?php
$contenidoPagina = '
<div class="page-header">
    <h1>Mis Grupos</h1>
    <a href="?action=create_group" class="btn-primary">Crear nuevo grupo</a>
</div>';

if (empty($grupos)) {
    $contenidoPagina .= '<p>Aun no tienes grupos.</p>';
} else {
    $contenidoPagina .= '
    <table>
        <thead><tr><th>Nombre</th><th>Descripcion</th><th>Rol</th><th>Acciones</th></tr></thead>
        <tbody>';
    foreach ($grupos as $grupo) {
        $desc = htmlspecialchars(substr($grupo['descripcion'], 0, 50));
        $contenidoPagina .= '
        <tr>
            <td><a href="?action=group_detail&id=' . $grupo['id'] . '">' . htmlspecialchars($grupo['nombre']) . '</a></td>
            <td>' . $desc . '</td>
            <td>' . htmlspecialchars($grupo['integrante_rol']) . '</td>
            <td><a href="?action=group_detail&id=' . $grupo['id'] . '" class="btn-small">Ver</a>
                <a href="?action=group_expenses&id=' . $grupo['id'] . '" class="btn-small">Gastos</a></td>
        </tr>';
    }
    $contenidoPagina .= '</tbody></table>';
}
?>
<?php
$contenidoPagina = '
<div class="page-header">
    <h1>Panel de control</h1>
    <p>Bienvenido/a, ' . htmlspecialchars(Auth::getUsuarioNombre()) . '</p>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Mis grupos</h3>
        <p>' . count($grupos) . '</p>
    </div>
</div>';

if (empty($grupos)) {
    $contenidoPagina .= '
    <div class="empty-state">
        <p>No tienes grupos aun. <a href="?action=create_group">Crea tu primer grupo</a></p>
    </div>';
} else {
    $contenidoPagina .= '
    <div class="groups-overview">
        <h2>Tus grupos</h2>
        <table>
            <thead>
                <tr><th>Nombre</th><th>Acciones</th></tr>
            </thead>
            <tbody>';
    foreach ($grupos as $g) {
        $contenidoPagina .= '
                <tr>
                    <td><a href="?action=group_detail&id=' . $g['id'] . '">' . htmlspecialchars($g['nombre']) . '</a></td>
                    <td><a href="?action=group_detail&id=' . $g['id'] . '" class="btn-small">Ver</a></td>
                </tr>';
    }
    $contenidoPagina .= '
            </tbody>
        </table>
    </div>';
}
?>
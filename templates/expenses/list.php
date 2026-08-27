<?php
$contenidoPagina = '
<div class="page-header">
    <h1>Gastos del grupo</h1>
    <a href="?action=create_expense&grupo_id=' . ($grupoId ?? '') . '" class="btn-primary">Agregar gasto</a>
</div>';

if (empty($gastos)) {
    $contenidoPagina .= '<p>No hay gastos registrados.</p>';
} else {
    $contenidoPagina .= '
    <table>
        <thead><tr><th>Concepto</th><th>Monto</th><th>Usuario</th><th>Fecha</th></tr></thead>
        <tbody>';
    foreach ($gastos as $gasto) {
        $contenidoPagina .= '
        <tr>
            <td>' . htmlspecialchars($gasto['concepto']) . '</td>
            <td>$' . number_format($gasto['monto'], 2) . '</td>
            <td>' . htmlspecialchars($gasto['usuario_nombre']) . '</td>
            <td>' . date('d/m/Y', strtotime($gasto['fecha'])) . '</td>
        </tr>';
    }
    $contenidoPagina .= '</tbody></table>';
}
?>
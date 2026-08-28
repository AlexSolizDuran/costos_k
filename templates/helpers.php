<?php
// templates/helpers.php - Funciones de presentación reutilizables (escalables)

// Formatea un monto en bolívares, igual que el original (number_format con 2 decimales)
function bs($monto) {
    return 'Bs ' . number_format((float) $monto, 2);
}

// Formatea una fecha legible
function fmtFecha($fecha) {
    if (empty($fecha)) {
        return '—';
    }
    $ts = strtotime($fecha);
    return $ts === false ? htmlspecialchars($fecha) : date('d/m/Y H:i', $ts);
}

function fmtFechaCorta($fecha) {
    if (empty($fecha)) {
        return '—';
    }
    $ts = strtotime($fecha);
    return $ts === false ? htmlspecialchars($fecha) : date('d/m/Y', $ts);
}

// Escapa un texto para atributos HTML
function e($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
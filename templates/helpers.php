<?php
// templates/helpers.php - Funciones de presentación reutilizables (escalables)

// Formatea un monto usando la etiqueta de su moneda original.
function monedaLabel($moneda) {
    $moneda = strtoupper((string) $moneda);
    return MONEDAS_LABEL[$moneda] ?? $moneda;
}

function fmtMoneda($monto, $moneda = MONEDA_BS) {
    return monedaLabel($moneda) . ' ' . number_format((float) $monto, 2);
}

function fmtMontoConUsd($monto, $moneda, $tasaUsd) {
    $equivalente = (float) $monto * (float) $tasaUsd;
    return fmtMoneda($monto, $moneda) . '<br><small style="color:#666;">≈ US$ ' . number_format($equivalente, 2) . '</small>';
}

// Compatibilidad con vistas antiguas.
function bs($monto) {
    return fmtMoneda($monto, MONEDA_BS);
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
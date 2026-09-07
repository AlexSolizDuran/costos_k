<?php
// config/constants.php - Definiciones únicas para evitar strings mágicos
define('ROL_ADMIN', 'admin');
define('ROL_MEMBRO', 'miembro');
define('ESTATUS_ACTIVO', 'activo');
define('ESTATUS_CERRADO', 'cerrado');
define('ESTATUS_SALIDO', 'salido');
define('TIPO_IGUALITARIO', 'igualitario');
define('TIPO_PERSONALIZADO', 'personalizado');
define('MENSAJE_BIENVENIDA', 'Bienvenido a TOPICOS');
define('TITULO_APP', 'Gestor de Gastos Compartidos');

// ------------------------------------------------------------
// Multi-moneda: monedas soportadas y tasas globales por defecto
// ------------------------------------------------------------
define('MONEDA_USD', 'USD');     // Dólares americanos físicos
define('MONEDA_BS', 'BS');       // Bolivianos
define('MONEDA_USDT', 'USDT');   // USDT (crypto)

define('CLAVE_BS_POR_USD', 'bs_por_usd');     // Bs que vale 1 US$
define('CLAVE_BS_POR_USDT', 'bs_por_usdt');   // Bs que vale 1 USDT
define('DEFAULT_BS_POR_USD', 6.96);
define('DEFAULT_BS_POR_USDT', 7.30);

// Etiquetas de presentación por moneda
define('MONEDAS_LABEL', [
    MONEDA_USD => 'US$',
    MONEDA_BS => 'Bs',
    MONEDA_USDT => 'USDT'
]);
define('MONEDAS_NOMBRE', [
    MONEDA_USD => 'Dólares americanos físicos',
    MONEDA_BS => 'Bolivianos',
    MONEDA_USDT => 'USDT'
]);
?>
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

// ------------------------------------------------------------
// Catálogo de monedas aceptadas AL PAGAR (cualquier moneda).
// El código es la clave; el valor es el nombre mostrado en el selector.
// ------------------------------------------------------------
define('MONEDAS_PAGO', [
    MONEDA_USD  => 'Dólar estadounidense',
    MONEDA_BS   => 'Bolívares',
    MONEDA_USDT => 'USDT (cripto)',
    'PEN' => 'Sol peruano',
    'EUR' => 'Euro',
    'MXN' => 'Peso mexicano',
    'COP' => 'Peso colombiano',
    'CLP' => 'Peso chileno',
    'ARS' => 'Peso argentino',
    'BRL' => 'Real brasileño'
]);

// Etiquetas cortas (símbolo) por moneda del catálogo de pago
define('MONEDAS_PAGO_LABEL', [
    MONEDA_USD  => 'US$',
    MONEDA_BS   => 'Bs',
    MONEDA_USDT => 'USDT',
    'PEN' => 'S/',
    'EUR' => '€',
    'MXN' => 'MX$',
    'COP' => 'CO$',
    'CLP' => 'CL$',
    'ARS' => 'AR$',
    'BRL' => 'R$'
]);
define('MONEDAS_NOMBRE', [
    MONEDA_USD => 'Dólares americanos físicos',
    MONEDA_BS => 'Bolivianos',
    MONEDA_USDT => 'USDT'
]);
?>
<?php
// sql/ejecutar.php
// Aplicador de migraciones. Ejecuta los archivos .sql del directorio sql/
// contra la BD en orden. Uso (desde CLI o navegador):
//   php sql/ejecutar.php
// Son idempotentes: se puede ejecutar varias veces sin romper nada.

require __DIR__ . '/../src/models/Database.php';

$archivos = ['migracion.sql', 'multi_moneda.sql', 'pago_multi_moneda.sql'];

// Separa un archivo SQL en sentencias individuales por ";" al final de línea.
function dividirSql($sql) {
    // Se quitan las líneas de comentario (--) para que no contaminen los bloques
    $sqlSinComentarios = preg_replace('/^\s*--[^\n]*$/m', '', $sql);

    // PDO pgsql con pdo_pgsql no permite múltiples sentencias por defecto;
    // se ejecutan delimitando por ";" al final de línea.
    $sentencias = [];
    foreach (preg_split('/;\s*\n/', $sqlSinComentarios) as $bloque) {
        $bloque = trim($bloque);
        if ($bloque === '') {
            continue;
        }
        $sentencias[] = $bloque;
    }
    return $sentencias;
}

$total = 0;
try {
    $pdo = Database::getInstance();

    foreach ($archivos as $archivo) {
        $sqlPath = __DIR__ . '/' . $archivo;
        if (!file_exists($sqlPath)) {
            echo "[AVISO] No se encontró " . $archivo . ", se omite." . PHP_EOL;
            continue;
        }
        $sql = file_get_contents($sqlPath);
        $sentencias = dividirSql($sql);

        $pdo->beginTransaction();
        foreach ($sentencias as $st) {
            $pdo->exec($st);
        }
        $pdo->commit();

        $total += count($sentencias);
        echo "[OK] " . $archivo . " aplicado (" . count($sentencias) . " sentencias)." . PHP_EOL;
    }

    echo "[OK] Migración completa (" . $total . " sentencias en total)." . PHP_EOL;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("[ERROR] " . $e->getMessage() . PHP_EOL);
}
<?php
// sql/ejecutar.php
// Aplicador de migraciones. Ejecuta migracion.sql contra la BD.
// Uso (desde CLI o navegador):
//   php sql/ejecutar.php
// Es idempotente: se puede ejecutar varias veces sin romper nada.

require __DIR__ . '/../src/models/Database.php';

$sqlPath = __DIR__ . '/migracion.sql';
if (!file_exists($sqlPath)) {
    die("[ERROR] No se encontró migracion.sql en " . $sqlPath . PHP_EOL);
}

try {
    $pdo = Database::getInstance();
    $sql = file_get_contents($sqlPath);

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

    $pdo->beginTransaction();
    foreach ($sentencias as $st) {
        $pdo->exec($st);
    }
    $pdo->commit();

    echo "[OK] Migración aplicada correctamente (" . count($sentencias) . " sentencias)." . PHP_EOL;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("[ERROR] " . $e->getMessage() . PHP_EOL);
}
<?php
// src/models/ExchangeRate.php - Modelo de tipos de cambio (backend puro, sin HTML)
// Base de conciliación: US$. Monedas soportadas: US$ físicos, Bs y USDT.
// La moneda USDT NO se asume 1:1 con el US$, se deriva de las tasas globales.

class ExchangeRate {
    private $db;

    public function __construct($db = null) {
        if ($db === null) {
            $db = Database::getInstance();
        }
        $this->db = $db;
    }

    // Devuelve las tasas globales vigentes ['bs_por_usd'=>float, 'bs_por_usdt'=>float].
    // Si la tabla no tiene valores, usa los valores por defecto.
    public function obtenerTasas() {
        $sql = "SELECT clave, valor FROM configuracion
                WHERE clave IN (:c_usd, :c_usdt)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':c_usd' => CLAVE_BS_POR_USD,
            ':c_usdt' => CLAVE_BS_POR_USDT
        ]);

        $tasas = [
            'bs_por_usd' => (float) DEFAULT_BS_POR_USD,
            'bs_por_usdt' => (float) DEFAULT_BS_POR_USDT
        ];

        foreach ($stmt->fetchAll() as $fila) {
            $valor = (float) $fila['valor'];
            if ($fila['clave'] === CLAVE_BS_POR_USD) {
                $tasas['bs_por_usd'] = $valor;
            } elseif ($fila['clave'] === CLAVE_BS_POR_USDT) {
                $tasas['bs_por_usdt'] = $valor;
            }
        }

        return $tasas;
    }

    public function bsPorUsd() {
        $tasas = $this->obtenerTasas();
        return $tasas['bs_por_usd'];
    }

    public function bsPorUsdt() {
        $tasas = $this->obtenerTasas();
        return $tasas['bs_por_usdt'];
    }

    // Guarda las tasas globales. Devuelve ['ok'=>bool, 'error'=>?]
    public function guardarTasas($bsPorUsd, $bsPorUsdt) {
        $bsPorUsd = trim(($bsPorUsd ?? ''));
        $bsPorUsdt = trim(($bsPorUsdt ?? ''));

        if ($bsPorUsd === '' || $bsPorUsdt === '') {
            return ['ok' => false, 'error' => 'Ambos tipos de cambio son obligatorios.'];
        }
        if (!is_numeric($bsPorUsd) || (float) $bsPorUsd <= 0) {
            return ['ok' => false, 'error' => 'El tipo de cambio "Bs por US$" debe ser un número mayor a 0.'];
        }
        if (!is_numeric($bsPorUsdt) || (float) $bsPorUsdt <= 0) {
            return ['ok' => false, 'error' => 'El tipo de cambio "Bs por USDT" debe ser un número mayor a 0.'];
        }

        $bsPorUsd = round((float) $bsPorUsd, 6);
        $bsPorUsdt = round((float) $bsPorUsdt, 6);

        $sql = "INSERT INTO configuracion (clave, valor, actualizado)
                VALUES (:clave, :valor, NOW())
                ON CONFLICT (clave) DO UPDATE SET valor = EXCLUDED.valor, actualizado = NOW()";
        $stmt = $this->db->prepare($sql);

        try {
            $this->db->beginTransaction();
            $stmt->execute([':clave' => CLAVE_BS_POR_USD, ':valor' => $bsPorUsd]);
            $stmt->execute([':clave' => CLAVE_BS_POR_USDT, ':valor' => $bsPorUsdt]);
            $this->db->commit();
            return ['ok' => true];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'error' => 'Error al guardar los tipos de cambio: ' . $e->getMessage()];
        }
    }

    // Cuántos US$ vale 1 unidad de la moneda indicada, con las tasas vigentes.
    //   USD  -> 1
    //   BS   -> 1 / bs_por_usd
    //   USDT -> bs_por_usdt / bs_por_usd
    public function tasaUsdDe($moneda) {
        $moneda = strtoupper((string) $moneda);
        if ($moneda === MONEDA_USD) {
            return 1.0;
        }
        $tasas = $this->obtenerTasas();
        $rUsd = $tasas['bs_por_usd'];
        if ($moneda === MONEDA_BS) {
            return $rUsd > 0 ? 1.0 / $rUsd : 0.0;
        }
        if ($moneda === MONEDA_USDT) {
            return ($rUsd > 0) ? ($tasas['bs_por_usdt'] / $rUsd) : 0.0;
        }
        return 0.0;
    }

    // Convierte un monto de una moneda a otra (USD, BS, USDT) con las tasas vigentes.
    public function convertir($monto, $desde, $hasta) {
        $monto = (float) $monto;
        $desde = strtoupper((string) $desde);
        $hasta = strtoupper((string) $hasta);

        if ($desde === $hasta) {
            return $monto;
        }

        $enUsd = $monto * $this->tasaUsdDe($desde);
        $factorHasta = $this->tasaUsdDe($hasta);
        return $factorHasta > 0 ? $enUsd / $factorHasta : 0.0;
    }

    // Etiqueta corta de presentación (US$, Bs, USDT).
    public function etiqueta($moneda) {
        $moneda = strtoupper((string) $moneda);
        return MONEDAS_LABEL[$moneda] ?? ('(' . htmlspecialchars($moneda) . ')');
    }

    public function nombre($moneda) {
        $moneda = strtoupper((string) $moneda);
        return MONEDAS_NOMBRE[$moneda] ?? 'Moneda desconocida';
    }

    // True si la moneda está soportada.
    public function esMonedaValida($moneda) {
        return in_array(strtoupper((string) $moneda), [MONEDA_USD, MONEDA_BS, MONEDA_USDT], true);
    }
}
<?php
// src/models/Payment.php - Modelo de pagos (backend puro, sin HTML)

class Payment {
    private $db;

    public function __construct($db = null) {
        if ($db === null) {
            $db = Database::getInstance();
        }
        $this->db = $db;
    }

    // Registra un pago de un participante hacia el pagador del gasto.
    // Acepta cualquier moneda del catálogo MONEDAS_PAGO: el pendiente
    // se valida y se concilia en US$ (monto_usd = monto * tasa_usd).
    // Devuelve ['ok'=>bool, 'error'=>?, 'grupo_id'=>?]
    public function registrar($gastoId, $deUsuario, $monto, $moneda = MONEDA_BS, $tasaUsd = 0.0, $informacion = '') {
        $gastoId = (int) $gastoId;
        $deUsuario = (int) $deUsuario;
        $monto = (float) $monto;
        $moneda = strtoupper(trim((string) $moneda));
        $tasaUsd = (float) $tasaUsd;
        $informacion = trim($informacion);

        if (!in_array($moneda, array_keys(MONEDAS_PAGO), true)) {
            return ['ok' => false, 'error' => 'La moneda seleccionada no es válida.'];
        }

        $sql = "SELECT g.id, g.grupo_id, g.concepto as titulo, g.pagado_por, g.estado, g.tasa_usd, g.moneda
                FROM gastos g
                WHERE g.id = :gasto_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gasto_id' => $gastoId]);
        $gasto = $stmt->fetch();

        if (!$gasto) {
            return ['ok' => false, 'error' => 'Gasto no encontrado.'];
        }

        if ($gasto['estado'] !== 'activo') {
            return ['ok' => false, 'error' => 'El gasto está cerrado y no permite pagos.'];
        }

        $sql = "SELECT monto_correspondiente, monto_pagado, monto_correspondiente_usd, monto_pagado_usd, estado_pago
                FROM gasto_participantes
                WHERE gasto_id = :gasto_id AND usuario_id = :de_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gasto_id' => $gastoId, ':de_usuario' => $deUsuario]);
        $deuda = $stmt->fetch();

        if (!$deuda) {
            return ['ok' => false, 'error' => 'Esta persona no participa en este gasto.'];
        }

        if ($deUsuario === (int) $gasto['pagado_por']) {
            return ['ok' => false, 'error' => 'El pagador no puede pagarse a sí mismo.'];
        }

        $pendienteUsd = round((float) $deuda['monto_correspondiente_usd'] - (float) $deuda['monto_pagado_usd'], 2);

        if ($pendienteUsd <= 0) {
            return ['ok' => false, 'error' => 'Esta deuda ya está pagada.'];
        }
        if ($monto <= 0) {
            return ['ok' => false, 'error' => 'El monto debe ser mayor a 0.'];
        }
        if ($tasaUsd <= 0) {
            return ['ok' => false, 'error' => 'La tasa USD debe ser mayor a 0.'];
        }

        $montoUsd = round($monto * $tasaUsd, 2);
        if ($montoUsd <= 0) {
            return ['ok' => false, 'error' => 'El monto equivale a US$ 0.00. Revisa la tasa.'];
        }
        if ($montoUsd > $pendienteUsd) {
            return ['ok' => false, 'error' => 'El monto no puede ser mayor al pendiente (US$ ' . number_format($pendienteUsd, 2) . ').'];
        }

        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO pagos (grupo_id, gasto_id, de_usuario, a_usuario, monto, moneda, tasa_usd, monto_usd, informacion)
                    VALUES (:grupo_id, :gasto_id, :de_usuario, :a_usuario, :monto, :moneda, :tasa_usd, :monto_usd, :informacion)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':grupo_id' => $gasto['grupo_id'],
                ':gasto_id' => $gastoId,
                ':de_usuario' => $deUsuario,
                ':a_usuario' => $gasto['pagado_por'],
                ':monto' => $monto,
                ':moneda' => $moneda,
                ':tasa_usd' => $tasaUsd,
                ':monto_usd' => $montoUsd,
                ':informacion' => $informacion !== '' ? $informacion : null
            ]);

            $tasaGasto = (float) $gasto['tasa_usd'];
            $factorGasto = $tasaGasto > 0 ? $tasaGasto : 1.0;

            $nuevoPagadoUsd = round((float) $deuda['monto_pagado_usd'] + $montoUsd, 2);
            $nuevoPagadoGasto = round($nuevoPagadoUsd / $factorGasto, 2);
            $nuevoEstado = $nuevoPagadoUsd >= (float) $deuda['monto_correspondiente_usd'] ? 'pagado' : 'parcial';

            $sql = "UPDATE gasto_participantes
                    SET monto_pagado = :monto_pagado,
                    monto_pagado_usd = :monto_pagado_usd,
                        estado_pago = :estado_pago,
                        fecha_pago = CASE WHEN :estado_pago_fecha = 'pagado' THEN CURRENT_TIMESTAMP ELSE fecha_pago END
                    WHERE gasto_id = :gasto_id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':monto_pagado' => $nuevoPagadoGasto,
                ':monto_pagado_usd' => $nuevoPagadoUsd,
                ':estado_pago' => $nuevoEstado,
                ':estado_pago_fecha' => $nuevoEstado,
                ':gasto_id' => $gastoId,
                ':usuario_id' => $deUsuario
            ]);

            $this->db->commit();
            return ['ok' => true, 'grupo_id' => (int) $gasto['grupo_id']];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'error' => 'Error al registrar el pago: ' . $e->getMessage()];
        }
    }
}
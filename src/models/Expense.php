<?php
// src/models/Expense.php - Modelo de gasto (backend puro, sin HTML)

class Expense {
    private $db;

    public function __construct($db = null) {
        if ($db === null) {
            $db = Database::getInstance();
        }
        $this->db = $db;
    }

    // Calcula los montos finales por participante.
    // tipo: 'igual' | 'personalizado' (igual al original)
    private function calcularMontos($tipo, $monto, $participantesIds, $montosPersonalizados, $moneda) {
        $montos = [];
        $error = '';

        if ($tipo === 'igual') {
            $montoCentavos = (int) round($monto * 100);
            $cantidad = count($participantesIds);
            $base = intdiv($montoCentavos, $cantidad);
            $resto = $montoCentavos % $cantidad;

            foreach ($participantesIds as $indice => $id) {
                $centavos = $base;
                if ($indice < $resto) {
                    $centavos++;
                }
                $montos[$id] = $centavos / 100;
            }
        }

        if ($tipo === 'personalizado') {
            $total = 0.0;
            foreach ($participantesIds as $id) {
                $valor = isset($montosPersonalizados[$id]) ? (float) $montosPersonalizados[$id] : 0;
                if ($valor < 0) {
                    return [null, 'Los montos no pueden ser negativos.'];
                }
                $valor = round($valor, 2);
                $montos[$id] = $valor;
                $total += $valor;
            }
            $total = round($total, 2);
            $montoRedondeado = round($monto, 2);
                if (abs($total - $montoRedondeado) > 0.001) {
                    return [
                        null,
                        'La suma de los montos personalizados debe ser exactamente ' .
                        (MONEDAS_LABEL[$moneda] ?? $moneda) . ' ' .
                        number_format($montoRedondeado, 2) . '. Actualmente suma ' .
                        (MONEDAS_LABEL[$moneda] ?? $moneda) . ' ' .
                        number_format($total, 2) . '.'
                    ];
            }
        }

        return [$montos, $error];
    }

    // Crea un gasto con sus participantes. Devuelve ['ok'=>bool, 'id'=>?, 'error'=>?]
    public function create($datos) {
        $grupoId = (int) ($datos['grupo_id'] ?? 0);
        $creadorId = (int) ($datos['user_id'] ?? 0);
        $titulo = trim($datos['concepto'] ?? '');
        $informacion = trim($datos['informacion'] ?? '');
        $imagenUrl = isset($datos['imagen_url']) && $datos['imagen_url'] !== '' ? $datos['imagen_url'] : null;
        $monto = (float) ($datos['monto'] ?? 0);
        $moneda = strtoupper(trim($datos['moneda'] ?? MONEDA_BS));
        $tasaUsd = (float) ($datos['tasa_usd'] ?? 0);
        $fecha = $datos['fecha'] ?? date('Y-m-d');
        $pagadoPor = (int) ($datos['pagado_por'] ?? 0);
        $tipo = ($datos['tipo_division'] ?? 'igual') === 'personalizado' ? 'personalizado' : 'igual';
        $participantes = $datos['participantes'] ?? [];
        $montosPersonalizados = $datos['monto_personalizado'] ?? [];

        $error = '';
        if ($titulo === '') {
            $error = 'El titulo es obligatorio.';
        } elseif (!in_array($moneda, [MONEDA_USD, MONEDA_BS, MONEDA_USDT], true)) {
            $error = 'La moneda seleccionada no es v�lida.';
        } elseif ($tasaUsd <= 0) {
            $error = 'La tasa USD debe ser mayor a 0.';
        } elseif ($monto <= 0) {
            $error = 'El monto debe ser mayor a 0.';
        } elseif ($pagadoPor <= 0) {
            $error = 'Debes seleccionar quien pago.';
        } elseif (!is_array($participantes) || count($participantes) === 0) {
            $error = 'Debes seleccionar al menos un participante.';
        }

        // Limpiar participantes y validar que pertenezcan al grupo
        $idsGrupo = array_column($this->obtenerIntegrantes($grupoId), 'usuario_id');

        $limpios = [];
        if ($error === '') {
            foreach ($participantes as $id) {
                $id = (int) $id;
                if ($id > 0 && in_array($id, $idsGrupo) && !in_array($id, $limpios)) {
                    $limpios[] = $id;
                }
            }
            if (count($limpios) === 0) {
                $error = 'Debes seleccionar participantes que pertenezcan al grupo.';
            }
            if ($error === '' && !in_array($pagadoPor, $idsGrupo)) {
                $error = 'El usuario que pagó no pertenece al grupo.';
            }
        }

        list($montos, $errorMontos) = $error === '' ? $this->calcularMontos($tipo, $monto, $limpios, $montosPersonalizados, $moneda) : [null, $error];
        if ($errorMontos !== '') {
            $error = $errorMontos;
        }

        if ($error !== '') {
            return ['ok' => false, 'error' => $error];
        }

        try {
            $this->db->beginTransaction();

                $sql = "INSERT INTO gastos (grupo_id, user_id, pagado_por, concepto, informacion, imagen_url, monto, moneda, tasa_usd, tipo_division, fecha, estado, creado_en)
                    VALUES (:grupo_id, :user_id, :pagado_por, :titulo, :informacion, :imagen_url, :monto, :moneda, :tasa_usd, :tipo_division, :fecha, 'activo', NOW())
                    RETURNING id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':grupo_id' => $grupoId,
                ':user_id' => $creadorId,
                ':pagado_por' => $pagadoPor,
                ':titulo' => $titulo,
                ':informacion' => $informacion !== '' ? $informacion : null,
                ':imagen_url' => $imagenUrl,
                ':monto' => $monto,
                ':moneda' => $moneda,
                ':tasa_usd' => round($tasaUsd, 6),
                ':tipo_division' => $tipo === 'igual' ? 'igualitaria' : 'personalizada',
                ':fecha' => $fecha
            ]);
            $gastoId = $stmt->fetchColumn();

                $sql = "INSERT INTO gasto_participantes (gasto_id, usuario_id, monto_correspondiente, monto_correspondiente_usd)
                    VALUES (:gasto_id, :usuario_id, :monto, :monto_usd)";
            $stmt = $this->db->prepare($sql);
            foreach ($montos as $participanteId => $montoParticipante) {
                $stmt->execute([
                    ':gasto_id' => $gastoId,
                    ':usuario_id' => $participanteId,
                    ':monto' => $montoParticipante
                    , ':monto_usd' => round($montoParticipante * $tasaUsd, 2)
                ]);
            }

            $this->db->commit();
            return ['ok' => true, 'id' => $gastoId];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'error' => 'Error al guardar el gasto: ' . $e->getMessage()];
        }
    }

    public function obtenerIntegrantes($grupoId) {
        $sql = "SELECT gi.usuario_id, u.nombre, u.email
                FROM grupo_integrantes gi
                JOIN users u ON u.id = gi.usuario_id
                WHERE gi.grupo_id = :grupo_id AND gi.estado = 'activo'
                ORDER BY u.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        return $stmt->fetchAll();
    }

    public function getByGroup($grupoId) {
        $sql = "SELECT ge.*, COALESCE(u.nombre, '—') as pagado_por_nombre
                FROM gastos ge
                LEFT JOIN users u ON u.id = ge.pagado_por
                WHERE ge.grupo_id = :grupo_id AND ge.estado = 'activo'
                ORDER BY ge.fecha DESC, ge.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        return $stmt->fetchAll();
    }

    // Detalle completo para la API JSON (modal "Ver gasto")
    public function getById($id, $usuarioId) {
        $sql = "SELECT g.id, g.grupo_id, g.concepto as titulo, g.informacion, g.imagen_url, g.monto, g.fecha,
                       g.creado_en, g.estado, g.pagado_por, g.tipo_division, g.user_id, g.moneda, g.tasa_usd,
                       COALESCE(u.nombre, '—') AS pagado_por_nombre
                FROM gastos g
                LEFT JOIN users u ON u.id = g.pagado_por
                JOIN grupo_integrantes gi ON gi.grupo_id = g.grupo_id
                WHERE g.id = :gasto_id AND gi.usuario_id = :usuario_id AND gi.estado = 'activo'
                  AND g.estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gasto_id' => $id, ':usuario_id' => $usuarioId]);
        $gasto = $stmt->fetch();
        if (!$gasto) {
            return null;
        }

        $participantes = $this->getParticipantes($id);

        $totalParticipantes = 0.0;
        $totalPendiente = 0.0;
        $totalPendienteUsd = 0.0;
        foreach ($participantes as $p) {
            $totalParticipantes += (float) $p['monto_correspondiente'];
            if ((int) $p['usuario_id'] !== (int) $gasto['pagado_por']) {
                $pendiente = (float) $p['monto_correspondiente'] - (float) $p['monto_pagado'];
                $pendienteUsd = (float) $p['monto_correspondiente_usd'] - (float) $p['monto_pagado_usd'];
                if ($pendiente < 0) {
                    $pendiente = 0;
                }
                if ($pendienteUsd < 0) {
                    $pendienteUsd = 0;
                }
                $totalPendiente += $pendiente;
                $totalPendienteUsd += $pendienteUsd;
            }
        }

        $coincide = abs($totalParticipantes - (float) $gasto['monto']) <= 0.01;

        return [
            'gasto' => $gasto,
            'participantes' => $participantes,
            'resumen' => [
                'total_gasto' => (float) $gasto['monto'],
                'total_participantes' => $totalParticipantes,
                'total_pendiente' => $totalPendiente,
                'total_pendiente_usd' => round($totalPendienteUsd, 2),
                'coincide' => $coincide
            ]
        ];
    }

    public function getParticipantes($gastoId) {
        $sql = "SELECT gp.id, gp.usuario_id, u.nombre, gp.monto_correspondiente, gp.monto_correspondiente_usd,
                   gp.monto_pagado, gp.monto_pagado_usd,
                       gp.estado_pago, gp.fecha_pago
                FROM gasto_participantes gp
                JOIN users u ON u.id = gp.usuario_id
                WHERE gp.gasto_id = :gasto_id
                ORDER BY gp.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gasto_id' => $gastoId]);
        return $stmt->fetchAll();
    }

    public function getTotalPagado($gastoId) {
        $sql = "SELECT COALESCE(SUM(monto_pagado), 0) FROM gasto_participantes WHERE gasto_id = :gasto_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gasto_id' => $gastoId]);
        return (float) $stmt->fetchColumn();
    }

    public function tienePagos($gastoId) {
        return $this->getTotalPagado($gastoId) > 0;
    }

    // Actualiza gasto + reemplaza participantes (solo si no hay pagos). Devuelve ['ok'=>bool, 'error'=>?]
    public function update($id, $datos) {
        $id = (int) $id;
        if ($this->tienePagos($id)) {
            return ['ok' => false, 'error' => 'Este gasto ya tiene pagos registrados. No puedes modificar su distribución.'];
        }

        $actual = $this->obtenerGastoCrudo($id);
        if (!$actual) {
            return ['ok' => false, 'error' => 'Gasto no encontrado.'];
        }

        $titulo = trim($datos['concepto'] ?? '');
        $informacion = trim($datos['informacion'] ?? '');
        $monto = (float) ($datos['monto'] ?? 0);
        $moneda = strtoupper(trim($datos['moneda'] ?? ($actual['moneda'] ?? MONEDA_BS)));
        $tasaUsd = (float) ($datos['tasa_usd'] ?? ($actual['tasa_usd'] ?? 0));
        $fecha = $datos['fecha'] ?? $actual['fecha'];
        $pagadoPor = (int) ($datos['pagado_por'] ?? 0);
        $tipo = ($datos['tipo_division'] ?? 'igual') === 'personalizado' ? 'personalizado' : 'igual';
        $participantes = $datos['participantes'] ?? [];
        $montosPersonalizados = $datos['monto_personalizado'] ?? [];

        $error = '';
        if ($titulo === '') {
            $error = 'El título es obligatorio.';
        } elseif (!in_array($moneda, [MONEDA_USD, MONEDA_BS, MONEDA_USDT], true)) {
            $error = 'La moneda seleccionada no es v�lida.';
        } elseif ($tasaUsd <= 0) {
            $error = 'La tasa USD debe ser mayor a 0.';
        } elseif ($monto <= 0) {
            $error = 'El monto debe ser mayor a 0.';
        } elseif ($pagadoPor <= 0) {
            $error = 'Debes seleccionar quién pagó.';
        } elseif (!is_array($participantes) || count($participantes) === 0) {
            $error = 'Debes seleccionar al menos un participante.';
        }

        $idsGrupo = array_column($this->obtenerIntegrantes($actual['grupo_id']), 'usuario_id');
        $limpios = [];
        if ($error === '') {
            foreach ($participantes as $pid) {
                $pid = (int) $pid;
                if ($pid > 0 && in_array($pid, $idsGrupo) && !in_array($pid, $limpios)) {
                    $limpios[] = $pid;
                }
            }
            if (count($limpios) === 0) {
                $error = 'Debes seleccionar participantes que pertenezcan al grupo.';
            }
            if ($error === '' && !in_array($pagadoPor, $idsGrupo)) {
                $error = 'El usuario que pagó no pertenece al grupo.';
            }
        }

        list($montos, $errorMontos) = $error === '' ? $this->calcularMontos($tipo, $monto, $limpios, $montosPersonalizados, $moneda) : [null, $error];
        if ($errorMontos !== '') {
            $error = $errorMontos;
        }

        if ($error !== '') {
            return ['ok' => false, 'error' => $error];
        }

        try {
            $this->db->beginTransaction();

                $sql = "UPDATE gastos SET concepto = :titulo, informacion = :informacion, monto = :monto,
                    moneda = :moneda, tasa_usd = :tasa_usd, pagado_por = :pagado_por,
                    tipo_division = :tipo_division, fecha = :fecha
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':titulo' => $titulo,
                ':informacion' => $informacion !== '' ? $informacion : null,
                ':monto' => $monto,
                ':moneda' => $moneda,
                ':tasa_usd' => round($tasaUsd, 6),
                ':pagado_por' => $pagadoPor,
                ':tipo_division' => $tipo === 'igual' ? 'igualitaria' : 'personalizada',
                ':fecha' => $fecha,
                ':id' => $id
            ]);

            $stmt = $this->db->prepare("DELETE FROM gasto_participantes WHERE gasto_id = :gasto_id");
            $stmt->execute([':gasto_id' => $id]);

                $sql = "INSERT INTO gasto_participantes (gasto_id, usuario_id, monto_correspondiente, monto_correspondiente_usd)
                    VALUES (:gasto_id, :usuario_id, :monto, :monto_usd)";
            $stmt = $this->db->prepare($sql);
            foreach ($montos as $participanteId => $montoParticipante) {
                $stmt->execute([
                    ':gasto_id' => $id,
                    ':usuario_id' => $participanteId,
                    ':monto' => $montoParticipante
                    , ':monto_usd' => round($montoParticipante * $tasaUsd, 2)
                ]);
            }

            $this->db->commit();
            return ['ok' => true, 'id' => $id];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'error' => 'Error al modificar el gasto: ' . $e->getMessage()];
        }
    }

    private function obtenerGastoCrudo($id) {
        $stmt = $this->db->prepare("SELECT * FROM gastos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Datos para el modal de modificar gasto (GET JSON)
    public function getDatosModificar($id, $usuarioId) {
        $gasto = $this->obtenerGastoCrudo($id);
        if (!$gasto) {
            return ['ok' => false, 'error' => 'Gasto no encontrado.'];
        }

        if ((int) $gasto['user_id'] !== (int) $usuarioId) {
            return ['ok' => false, 'error' => 'No tienes permiso para modificar este gasto.'];
        }

        if ($gasto['estado'] !== 'activo') {
            return ['ok' => false, 'error' => 'Este gasto está anulado.'];
        }

        $integrantes = $this->obtenerIntegrantes($gasto['grupo_id']);
        if (!in_array($usuarioId, array_column($integrantes, 'usuario_id'))) {
            return ['ok' => false, 'error' => 'Ya no perteneces a este grupo.'];
        }

        if ($this->tienePagos($id)) {
            return ['ok' => false, 'error' => 'Este gasto ya tiene pagos registrados. No puedes modificar su distribución.'];
        }

        $participantes = $this->getParticipantes($id);

        return [
            'ok' => true,
            'gasto' => [
                'id' => $gasto['id'],
                'grupo_id' => $gasto['grupo_id'],
                'titulo' => $gasto['concepto'],
                'informacion' => $gasto['informacion'],
                'monto' => $gasto['monto'],
                'moneda' => $gasto['moneda'],
                'tasa_usd' => $gasto['tasa_usd'],
                'fecha' => $gasto['fecha'],
                'pagado_por' => $gasto['pagado_por'],
                'tipo_division' => $gasto['tipo_division'] === 'personalizada' ? 'personalizado' : 'igual',
                'creado_por' => $gasto['user_id']
            ],
            'integrantes' => $integrantes,
            'participantes' => $participantes
        ];
    }

    public function getTotalByGroup($grupoId) {
        $sql = "SELECT SUM(monto) as total, COUNT(*) as cantidad
                FROM gastos WHERE grupo_id = :grupo_id AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        return $stmt->fetch();
    }

    // Deudores del grupo con su deuda total y el desglose por gasto.
    // Un deudor es un integrante con saldo pendiente>0 en un gasto activo
    // donde no es el pagador. Devuelve array con 'usuario_id','nombre','total'
    // y 'deudas' (concepto, monto, gasto_id, fecha).
    public function getDeudores($grupoId) {
        $sql = "SELECT gp.usuario_id, u.nombre,
                       g.id AS gasto_id, g.concepto, g.fecha,
                       (gp.monto_correspondiente - gp.monto_pagado) AS pendiente
                FROM gasto_participantes gp
                JOIN gastos g ON g.id = gp.gasto_id
                JOIN users u ON u.id = gp.usuario_id
                WHERE g.grupo_id = :grupo_id
                  AND g.estado = 'activo'
                  AND gp.usuario_id <> g.pagado_por
                  AND gp.monto_correspondiente - gp.monto_pagado > 0.001
                ORDER BY u.nombre ASC, g.fecha ASC, g.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        $filas = $stmt->fetchAll();

        $deudores = [];
        foreach ($filas as $fila) {
            $uid = (int) $fila['usuario_id'];
            if (!isset($deudores[$uid])) {
                $deudores[$uid] = [
                    'usuario_id' => $uid,
                    'nombre' => $fila['nombre'],
                    'total' => 0.0,
                    'deudas' => []
                ];
            }
            $pendiente = round((float) $fila['pendiente'], 2);
            $deudores[$uid]['total'] = round($deudores[$uid]['total'] + $pendiente, 2);
            $deudores[$uid]['deudas'][] = [
                'gasto_id' => (int) $fila['gasto_id'],
                'concepto' => $fila['concepto'],
                'fecha' => $fila['fecha'],
                'monto' => $pendiente
            ];
        }

        return array_values($deudores);
    }

    // Lo que el usuario aún debe pagar en el grupo (hacia el pagador de cada gasto)
    public function totalPendienteDe($usuarioId, $grupoId) {
        $sql = "SELECT COALESCE(SUM(
                    CASE
                        WHEN gp.monto_correspondiente - gp.monto_pagado < 0 THEN 0
                        ELSE gp.monto_correspondiente - gp.monto_pagado
                    END
                ), 0) AS total
                FROM gasto_participantes gp
                JOIN gastos g ON g.id = gp.gasto_id
                WHERE gp.usuario_id = :usuario_id
                  AND g.grupo_id = :grupo_id
                  AND g.estado = 'activo'
                  AND g.pagado_por <> :usuario_id2";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':grupo_id' => $grupoId,
            ':usuario_id2' => $usuarioId
        ]);
        return (float) $stmt->fetchColumn();
    }
}
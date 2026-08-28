<?php
// src/models/Group.php - Modelo de grupo (backend puro, sin HTML)

class Group {
    private $db;

    public function __construct($db = null) {
        if ($db === null) {
            $db = Database::getInstance();
        }
        $this->db = $db;
    }

    public function findByUser($usuarioId) {
        $sql = "SELECT g.id, g.nombre, g.descripcion, g.fecha_creacion, g.estado,
                       gi.rol as integrante_rol
                FROM grupos g
                JOIN grupo_integrantes gi ON gi.grupo_id = g.id
                WHERE gi.usuario_id = :usuario_id AND gi.estado = 'activo'
                ORDER BY g.fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function findById($id, $usuarioId) {
        $sql = "SELECT g.*, gi.rol as mi_rol, u.nombre as creador_nombre
                FROM grupos g
                JOIN grupo_integrantes gi ON gi.grupo_id = g.id
                LEFT JOIN users u ON u.id = g.creado_por
                WHERE g.id = :grupo_id AND gi.usuario_id = :usuario_id AND gi.estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $id, ':usuario_id' => $usuarioId]);
        return $stmt->fetch();
    }

    public function findByIdSolo($id) {
        $stmt = $this->db->prepare("SELECT g.*, u.nombre as creador_nombre FROM grupos g LEFT JOIN users u ON u.id = g.creado_por WHERE g.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear($datos, $usuarioId) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO grupos (nombre, descripcion, creado_por, fecha_creacion, estado)
                    VALUES (:nombre, :descripcion, :creado_por, NOW(), 'activo')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':descripcion' => trim($datos['descripcion'] ?? '') !== '' ? trim($datos['descripcion']) : null,
                ':creado_por' => $usuarioId
            ]);
            $grupoId = $this->db->lastInsertId();

            $sql = "INSERT INTO grupo_integrantes (grupo_id, usuario_id, rol, estado, fecha_union)
                    VALUES (:grupo_id, :usuario_id, 'admin', 'activo', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':grupo_id' => $grupoId,
                ':usuario_id' => $usuarioId
            ]);

            $this->db->commit();
            return $grupoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE grupos SET nombre = :nombre, descripcion = :descripcion, fecha_modificacion = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':descripcion' => trim($datos['descripcion'] ?? '') !== '' ? trim($datos['descripcion']) : null,
            ':id' => $id
        ]);
    }

    public function cerrarGrupo($id) {
        $sql = "UPDATE grupos SET estado = 'cerrado', fecha_modificacion = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM grupos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function esAdmin($grupoId, $usuarioId) {
        $sql = "SELECT COUNT(*) as total FROM grupo_integrantes
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id
                  AND rol = 'admin' AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId, ':usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    public function esIntegrante($grupoId, $usuarioId) {
        $sql = "SELECT COUNT(*) as total FROM grupo_integrantes
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId, ':usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    public function integranteExistente($grupoId, $usuarioId) {
        return $this->esIntegrante($grupoId, $usuarioId);
    }

    public function getRol($grupoId, $usuarioId) {
        $sql = "SELECT rol FROM grupo_integrantes
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId, ':usuario_id' => $usuarioId]);
        return $stmt->fetchColumn();
    }

    public function agregarIntegrante($grupoId, $usuarioId, $rol = 'miembro') {
        // Si ya existe (salido), se reactiva.
        $sql = "SELECT estado FROM grupo_integrantes
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId, ':usuario_id' => $usuarioId]);
        $existente = $stmt->fetch();

        if ($existente) {
            if ($existente['estado'] === 'activo') {
                return false; // ya pertenece
            }
            $sql = "UPDATE grupo_integrantes SET estado = 'activo', rol = :rol, fecha_union = NOW()
                    WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':grupo_id' => $grupoId,
                ':usuario_id' => $usuarioId,
                ':rol' => $rol
            ]);
        }

        $sql = "INSERT INTO grupo_integrantes (grupo_id, usuario_id, rol, estado, fecha_union)
                VALUES (:grupo_id, :usuario_id, :rol, 'activo', NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':grupo_id' => $grupoId,
            ':usuario_id' => $usuarioId,
            ':rol' => $rol
        ]);
    }

    public function actualizarRol($grupoId, $usuarioId, $nuevoRol) {
        $sql = "UPDATE grupo_integrantes SET rol = :rol
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':rol' => $nuevoRol,
            ':grupo_id' => $grupoId,
            ':usuario_id' => $usuarioId
        ]);
    }

    public function contarAdmins($grupoId) {
        $sql = "SELECT COUNT(*) FROM grupo_integrantes
                WHERE grupo_id = :grupo_id AND rol = 'admin' AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        return (int) $stmt->fetchColumn();
    }

    public function quitarIntegrante($grupoId, $integranteUsuarioId) {
        $sql = "UPDATE grupo_integrantes SET estado = 'salido'
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':grupo_id' => $grupoId, ':usuario_id' => $integranteUsuarioId]);
    }

    public function salirGrupo($grupoId, $usuarioId) {
        $sql = "UPDATE grupo_integrantes SET estado = 'salido'
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':grupo_id' => $grupoId, ':usuario_id' => $usuarioId]);
    }

    public function getIntegrantes($grupoId) {
        $sql = "SELECT gi.usuario_id, u.nombre, u.email, gi.rol, gi.fecha_union
                FROM grupo_integrantes gi
                JOIN users u ON gi.usuario_id = u.id
                WHERE gi.grupo_id = :grupo_id AND gi.estado = 'activo'
                ORDER BY CASE WHEN gi.rol = 'admin' THEN 0 ELSE 1 END, u.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        return $stmt->fetchAll();
    }

    // Usuarios activos del sistema que aún no pertenecen al grupo (para agregar manualmente)
    public function getUsuariosCandidatos($grupoId) {
        $sql = "SELECT u.id, u.nombre, u.email
                FROM users u
                WHERE u.activo = TRUE
                  AND NOT EXISTS (
                      SELECT 1 FROM grupo_integrantes gi
                      WHERE gi.grupo_id = :grupo_id
                        AND gi.usuario_id = u.id
                        AND gi.estado = 'activo'
                  )
                ORDER BY u.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        return $stmt->fetchAll();
    }

    public function getGastos($grupoId) {
        $sql = "SELECT ge.*, u.nombre as usuario_nombre
                FROM gastos ge
                JOIN users u ON ge.user_id = u.id
                WHERE ge.grupo_id = :grupo_id
                ORDER BY ge.fecha DESC LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        return $stmt->fetchAll();
    }

    // ---------- Invitaciones ----------

    public function generarInvitacion($grupoId, $usuarioId) {
        $grupo = $this->findByIdSolo($grupoId);
        if (!$grupo) {
            return ['ok' => false, 'error' => 'Grupo no encontrado.'];
        }

        $codigo = bin2hex(random_bytes(16));
        $sql = "INSERT INTO invitaciones (grupo_id, codigo, creado_por)
                VALUES (:grupo_id, :codigo, :creado_por)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':grupo_id' => $grupoId,
            ':codigo' => $codigo,
            ':creado_por' => $usuarioId
        ]);

        $enlace = '?action=accept_invitation&codigo=' . $codigo;
        return [
            'ok' => true,
            'grupo' => $grupo['nombre'],
            'codigo' => $codigo,
            'enlace' => $enlace
        ];
    }

    public function obtenerInvitacion($codigo) {
        $sql = "SELECT i.id, i.grupo_id, i.fecha_expiracion, i.estado, g.nombre as grupo_nombre
                FROM invitaciones i
                JOIN grupos g ON g.id = i.grupo_id
                WHERE i.codigo = :codigo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':codigo' => $codigo]);
        return $stmt->fetch();
    }

    // Acepta una invitación validada. Devuelve grupo_id o array con error.
    public function aceptarInvitacion($comprobada, $usuarioId) {
        $grupoId = (int) $comprobada['grupo_id'];

        if ($this->esIntegrante($grupoId, $usuarioId)) {
            return ['ok' => true, 'grupo_id' => $grupoId, 'ya_integrante' => true];
        }

        $ok = $this->agregarIntegrante($grupoId, $usuarioId, 'miembro');
        if (!$ok) {
            return ['ok' => false, 'error' => 'No se pudo agregar al grupo.'];
        }

        $sql = "UPDATE invitaciones SET estado = 'usada', usado_por = :usuario_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $comprobada['id'], ':usuario_id' => $usuarioId]);

        return ['ok' => true, 'grupo_id' => $grupoId, 'ya_integrante' => false];
    }
}
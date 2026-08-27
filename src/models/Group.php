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
        $sql = "SELECT g.*, gi.rol as mi_rol 
                FROM grupos g
                JOIN grupo_integrantes gi ON gi.grupo_id = g.id
                WHERE g.id = :grupo_id AND gi.usuario_id = :usuario_id AND gi.estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $id, ':usuario_id' => $usuarioId]);
        return $stmt->fetch();
    }

    public function crear($datos, $usuarioId) {
        // Crear transacción
        $this->db->beginTransaction();
        try {
            // Insertar grupo
            $sql = "INSERT INTO grupos (nombre, descripcion, fecha_creacion, estado) 
                    VALUES (:nombre, :descripcion, NOW(), 'activo')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?? ''
            ]);
            $grupoId = $this->db->lastInsertId();
            
            // Agregar creador como admin
            $sql = "INSERT INTO grupo_integrantes (grupo_id, usuario_id, rol, estado) 
                    VALUES (:grupo_id, :usuario_id, 'admin', 'activo')";
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
        $sql = "UPDATE grupos SET nombre = :nombre, descripcion = :descripcion 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':id' => $id
        ]);
    }

    public function eliminar($id) {
        // Cambiar estado a 'cerrado' en lugar de borrar físicamente
        $sql = "UPDATE grupos SET estado = 'cerrado' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function integranteExistente($grupoId, $usuarioId) {
        $sql = "SELECT COUNT(*) as total FROM grupo_integrantes 
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId, ':usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    public function agregarIntegrante($grupoId, $usuarioId, $rol = 'miembro') {
        // Verificar que el grupo existe y está activo
        $sql = "INSERT INTO grupo_integrantes (grupo_id, usuario_id, rol, estado) 
                VALUES (:grupo_id, :usuario_id, :rol, 'activo')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':grupo_id' => $grupoId,
            ':usuario_id' => $usuarioId,
            ':rol' => $rol
        ]);
    }

    public function actualizarRol($grupoId, $usuarioId, $nuevoRol) {
        $sql = "UPDATE grupo_integrantes SET rol = :rol 
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':rol' => $nuevoRol,
            ':grupo_id' => $grupoId,
            ':usuario_id' => $usuarioId
        ]);
    }

    public function salirGrupo($grupoId, $usuarioId) {
        $sql = "UPDATE grupo_integrantes SET estado = 'salido' 
                WHERE grupo_id = :grupo_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':grupo_id' => $grupoId, ':usuario_id' => $usuarioId]);
    }

    public function getIntegrantes($grupoId) {
        $sql = "SELECT u.id, u.nombre, gi.rol 
                FROM grupo_integrantes gi
                JOIN users u ON gi.usuario_id = u.id
                WHERE gi.grupo_id = :grupo_id AND gi.estado = 'activo'";
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
}
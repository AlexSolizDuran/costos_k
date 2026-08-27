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

    public function create($datos) {
        $sql = "INSERT INTO gastos (grupo_id, user_id, concepto, monto, fecha, tipo, creado_en) 
                VALUES (:grupo_id, :user_id, :concepto, :monto, NOW(), :tipo, NOW())";
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            ':grupo_id' => $datos['grupo_id'],
            ':user_id' => $datos['user_id'],
            ':concepto' => $datos['concepto'],
            ':monto' => $datos['monto'],
            ':tipo' => $datos['tipo'] ?? 'personalizado'
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getByGroup($grupoId, $usuarioId = null) {
        $sql = "SELECT ge.*, u.nombre as usuario_nombre 
                FROM gastos ge
                JOIN users u ON ge.user_id = u.id
                WHERE ge.grupo_id = :grupo_id";
        $params = [':grupo_id' => $grupoId];
        
        if ($usuarioId !== null) {
            $sql .= " AND ge.user_id != :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        
        $sql .= " ORDER BY ge.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTotalByGroup($grupoId) {
        $sql = "SELECT SUM(monto) as total, COUNT(*) as cantidad 
                FROM gastos WHERE grupo_id = :grupo_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo_id' => $grupoId]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $sql = "DELETE FROM gastos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function update($id, $datos) {
        $sql = "UPDATE gastos SET concepto = :concepto, monto = :monto, tipo = :tipo 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':concepto' => $datos['concepto'],
            ':monto' => $datos['monto'],
            ':tipo' => $datos['tipo'] ?? 'personalizado'
        ]);
    }
}
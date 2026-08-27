<?php
// src/models/User.php - Modelo de usuario (backend puro, sin HTML)
// Usa la conexión PDO de Database

class User {
    private $db;

    public function __construct($db = null) {
        if ($db === null) {
            $db = Database::getInstance();
        }
        $this->db = $db;
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, nombre, email, password_hash, rol, creado_en 
                                     FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, nombre, email, password_hash, rol, creado_en 
                                     FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function existsWithEmail($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params[':excludeId'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    public function crear($datos) {
        $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (nombre, email, password_hash, rol) 
                VALUES (:nombre, :email, :password_hash, :rol)";
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email'],
            ':password_hash' => $hash,
            ':rol' => $datos['rol'] ?? 'miembro'
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE users SET nombre = :nombre, email = :email";
        $params = [
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email']
        ];
        
        if (isset($datos['password']) && !empty($datos['password'])) {
            $sql .= ", password_hash = :password_hash";
            $params[':password_hash'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
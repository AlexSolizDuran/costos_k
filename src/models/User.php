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

    private function columnas() {
        return "id, nombre, email, password_hash, rol, activo, creado_en";
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT " . $this->columnas() . " FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT " . $this->columnas() . " FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function getAll($excludeIds = [], $soloActivos = true) {
        $sql = "SELECT id, nombre, email, rol, activo, creado_en FROM users";
        $condiciones = [];
        $params = [];

        if ($soloActivos) {
            $condiciones[] = "activo = TRUE";
        }

        if (!empty($excludeIds)) {
            $ids = array_map('intval', (array) $excludeIds);
            $lugares = [];
            foreach ($ids as $i => $id) {
                $lugares[] = ":exc_" . $i;
                $params[":exc_" . $i] = $id;
            }
            $condiciones[] = "id NOT IN (" . implode(', ', $lugares) . ")";
        }

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(' AND ', $condiciones);
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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
        $sql = "INSERT INTO users (nombre, email, password_hash, rol, activo)
                VALUES (:nombre, :email, :password_hash, :rol, TRUE)";
        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email'],
            ':password_hash' => $hash,
            ':rol' => $datos['rol'] ?? ROL_MEMBRO
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

        if (isset($datos['rol'])) {
            $sql .= ", rol = :rol";
            $params[':rol'] = $datos['rol'];
        }

        if (isset($datos['password']) && !empty($datos['password'])) {
            $sql .= ", password_hash = :password_hash";
            $params[':password_hash'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Desactivación suave (el historial se conserva, como el original)
    public function desactivar($id) {
        $sql = "UPDATE users SET activo = FALSE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function activar($id) {
        $sql = "UPDATE users SET activo = TRUE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Borrado físico (no se usa en la interfaz, se prefiere desactivar)
    public function eliminar($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
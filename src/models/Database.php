<?php
// src/models/Database.php - Clase de conexión a base de datos usando .env
// PHP puro, ningún framework externo requerido

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Cargar variables de entorno desde .env
        $envPath = __DIR__ . '/../../config/.env';
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $config = [
            'host' => '',
            'dbname' => '',
            'username' => '',
            'password' => ''
        ];

        foreach ($lines as $line) {
            if (strpos(trim($line), '=') === false) continue;
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            switch ($key) {
                case 'DB_HOST': $config['host'] = $value; break;
                case 'DB_NAME': $config['dbname'] = $value; break;
                case 'DB_USER': $config['username'] = $value; break;
                case 'DB_PASS': $config['password'] = $value; break;
            }
        }

        // Construir DSN PDO
        $dsn = "pgsql:host={$config['host']};dbname={$config['dbname']};sslmode=require";
        
        try {
            $this->conn = new PDO($dsn, $config['username'], $config['password']);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }

    public function getConn() {
        return $this->conn;
    }

    // Cerrar conexión al final del request
    public function __destruct() {
        // PDO connections are automatically closed when script ends
        // but we can explicitly null it
        $this->conn = null;
    }
}
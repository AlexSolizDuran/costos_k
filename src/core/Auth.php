<?php
// src/core/Auth.php - Manejo de autenticación y sesiones
// PHP puro, lógica de backend sin HTML

class Auth {
    public static function iniciar() {
        // session_start() ya se llama en index.php, evitamos duplicado en PHP 8.4+
        // if (session_status() !== PHP_SESSION_ACTIVE) {
        //     session_start();
        // }
        
        // Regenerar ID de sesión para prevenir fixation (si ya hay sesión)
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function estaAutenticada() {
        return isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nombre']);
    }

    public static function autenticar($usuarioId, $usuarioNombre) {
        self::iniciar();
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_nombre'] = $usuarioNombre;
        $_SESSION['login_time'] = time();
    }

    public static function cerrarSesion() {
        self::iniciar();
        $_SESSION = [];
        session_destroy();
        session_regenerate_id();
        header('Location: /login.php');
        exit;
    }

    public static function getUsuarioId() {
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function getUsuarioNombre() {
        return $_SESSION['usuario_nombre'] ?? null;
    }

    // Verificar si la sesión expiró (ej. 2 horas)
    public static function sesionExpirada() {
        if (!isset($_SESSION['login_time'])) return true;
        return (time() - $_SESSION['login_time'] > 7200); // 2 horas
    }
}
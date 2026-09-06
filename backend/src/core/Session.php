<?php

namespace Backend\Core;

/**
 * Envoltorio sobre las sesiones nativas de PHP.
 *
 * Guarda en $_SESSION sólo lo mínimo (id, nivel de acceso y nombre) para
 * poder proteger los endpoints de administración y para que el panel sepa
 * quién está conectado, sin repetir consultas a la base en cada request.
 */
final class Session
{
    private static bool $iniciada = false;

    public static function iniciar(): void
    {
        if (self::$iniciada || session_status() === PHP_SESSION_ACTIVE) {
            self::$iniciada = true;
            return;
        }

        $config = require __DIR__ . '/../config/config.php';

        session_name($config['session']['name']);
        session_set_cookie_params([
            'lifetime' => $config['session']['lifetime_minutos'] * 60,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            // 'secure' queda en false para poder probar en http://localhost (XAMPP);
            // en Debian con HTTPS activo, ponerlo en true.
            'secure'   => false,
        ]);

        session_start();
        self::$iniciada = true;
    }

    public static function iniciarSesionUsuario(array $usuario): void
    {
        self::iniciar();
        session_regenerate_id(true); // evita fijación de sesión al loguearse
        $_SESSION['usuario'] = [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nom_usuario' => $usuario['nom_usuario'],
            'email'       => $usuario['email'],
            'id_nivel'    => (int) $usuario['id_nivel'],
            'rol'         => $usuario['rol'] ?? null, // 'administrador' | 'alumno'
        ];
    }

    public static function usuarioActual(): ?array
    {
        self::iniciar();
        return $_SESSION['usuario'] ?? null;
    }

    public static function estaAutenticado(): bool
    {
        return self::usuarioActual() !== null;
    }

    public static function esAdministrador(): bool
    {
        $usuario = self::usuarioActual();
        return $usuario !== null && $usuario['rol'] === 'administrador';
    }

    public static function cerrarSesion(): void
    {
        self::iniciar();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path']);
        }
        session_destroy();
    }

    /** Corta la ejecución con 401 si no hay usuario logueado. */
    public static function exigirAutenticacion(): array
    {
        $usuario = self::usuarioActual();
        if ($usuario === null) {
            Response::noAutorizado('Necesitás iniciar sesión para continuar.');
        }
        return $usuario;
    }

    /** Corta la ejecución con 403 si el usuario logueado no es administrador. */
    public static function exigirAdministrador(): array
    {
        $usuario = self::exigirAutenticacion();
        if ($usuario['rol'] !== 'administrador') {
            Response::prohibido('Esta acción requiere permisos de administrador.');
        }
        return $usuario;
    }
}

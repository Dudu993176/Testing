<?php
/**
 * Configuración central de la aplicación.
 *
 * Los valores por defecto están pensados para un XAMPP recién instalado
 * (Apache + MySQL/MariaDB + phpMyAdmin en Windows/Linux). En un servidor
 * Debian con Apache + MySQL en producción, se recomienda NO tocar este
 * archivo y en cambio definir variables de entorno (con Apache SetEnv,
 * un archivo .env cargado por el propio servidor, o systemd Environment=):
 *
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, APP_ENV
 *
 * getenv() siempre tiene prioridad sobre el valor por defecto, así que el
 * mismo código corre sin cambios en XAMPP y en Debian.
 */

if (!defined('APP_BOOTSTRAP')) {
    define('APP_BOOTSTRAP', true);
}

return [
    // Entorno: 'local' (XAMPP, errores visibles) o 'production' (Debian, errores ocultos y logueados)
    'env' => getenv('APP_ENV') ?: 'local',

    'db' => [
        'driver'   => 'mysql',
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_NAME') ?: 'fadecode',
        'user'     => getenv('DB_USER') ?: 'root',
        // XAMPP trae MySQL sin contraseña para root por defecto.
        'pass'     => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
        'charset'  => 'utf8mb4',
    ],

    'session' => [
        'name'            => 'fadecode_sesion',
        'lifetime_minutos' => 120,
    ],

    // Nombre público de la institución, usado en mensajes/textos del backend.
    'institucion' => 'Escuela Técnica 1 "Mtro. Téc. Sergio González Olaizola"',

    // País por defecto cuando el formulario no lo solicita explícitamente.
    'pais_por_defecto' => 'Uruguay',
];

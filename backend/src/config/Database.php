<?php

namespace Backend\Config;

use PDO;
use PDOException;

/**
 * Conexión única (Singleton) a MySQL vía PDO.
 *
 * Toda consulta del backend pasa por aquí para reutilizar la misma conexión
 * y para garantizar que SIEMPRE se usan sentencias preparadas (ver
 * repositories/*), evitando inyección SQL.
 */
final class Database
{
    private static ?PDO $instancia = null;

    private function __construct()
    {
        // No instanciable: uso exclusivamente estático.
    }

    public static function getConexion(): PDO
    {
        if (self::$instancia === null) {
            $config = require __DIR__ . '/config.php';
            $db = $config['db'];

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $db['driver'],
                $db['host'],
                $db['port'],
                $db['database'],
                $db['charset']
            );

            try {
                self::$instancia = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // usa prepared statements reales del driver
                ]);
            } catch (PDOException $e) {
                // No exponemos detalles de la conexión (host/usuario) al cliente.
                error_log('[Database] Error de conexión: ' . $e->getMessage());
                throw new PDOException('No se pudo conectar a la base de datos.', (int) $e->getCode());
            }
        }

        return self::$instancia;
    }

    /** Útil en pruebas/CLI para forzar una reconexión. */
    public static function resetear(): void
    {
        self::$instancia = null;
    }
}

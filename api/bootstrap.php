<?php

/**
 * Arranque común de todos los endpoints de /api.
 *
 * Cada archivo de /api/*.php empieza con:
 *   require __DIR__ . '/bootstrap.php';
 *
 * y a partir de ahí ya tiene disponibles el autoload de clases (Backend\...),
 * la sesión iniciada y un manejador de errores que responde JSON en vez de
 * dejar escapar HTML de error (evita romper el `await response.json()` del
 * frontend si algo inesperado falla).
 */

declare(strict_types=1);

require __DIR__ . '/../backend/autoload.php';

use Backend\Core\Response;
use Backend\Core\Session;

$config = require __DIR__ . '/../backend/src/config/config.php';

if ($config['env'] === 'local') {
    ini_set('display_errors', '0'); // igual mostramos JSON propio, no HTML de PHP
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
}

set_exception_handler(function (\Throwable $e): void {
    error_log('[api] Excepción no controlada: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    Response::errorServidor();
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

Session::iniciar();

header('X-Content-Type-Options: nosniff');

/** Lee el cuerpo de la petición como JSON, o como $_POST si vino como formulario. */
function leerCuerpoJson(): array
{
    $tipoContenido = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($tipoContenido, 'application/json')) {
        $crudo = file_get_contents('php://input');
        $datos = json_decode($crudo, true);
        return is_array($datos) ? $datos : [];
    }
    return $_POST;
}

/** IP real del cliente (best effort, sin confiar ciegamente en cabeceras si no hay proxy configurado). */
function ipDelCliente(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function metodoHttp(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

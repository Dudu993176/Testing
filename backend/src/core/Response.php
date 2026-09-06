<?php

namespace Backend\Core;

/**
 * Da forma homogénea a TODAS las respuestas JSON de la API.
 *
 * Contrato usado por el frontend (assets/javascript/*.js):
 *   {
 *     "success": true|false,
 *     "message": "texto general para mostrar al usuario",
 *     "data": {...} | [...],
 *     "errors": { "nombre_campo": "mensaje contextual del campo" }
 *   }
 *
 * El objeto "errors" es la base del "Sistema de mensajes de error
 * contextualizados": el frontend usa la clave (nombre de campo) para pintar
 * el mensaje justo debajo del input correspondiente.
 */
final class Response
{
    public static function json(array $payload, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function exito(string $message = '', array $data = [], int $statusCode = 200): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => new \stdClass(), // objeto vacío, no array, para que json_encode no lo mande como []
        ], $statusCode);
    }

    /**
     * @param array<string,string> $errores Clave = nombre de campo, valor = mensaje contextual.
     */
    public static function errorValidacion(array $errores, string $message = 'Revisá los datos marcados.'): never
    {
        self::json([
            'success' => false,
            'message' => $message,
            'data'    => new \stdClass(),
            'errors'  => $errores,
        ], 422);
    }

    public static function error(string $message, int $statusCode = 400): never
    {
        self::json([
            'success' => false,
            'message' => $message,
            'data'    => new \stdClass(),
            'errors'  => new \stdClass(),
        ], $statusCode);
    }

    public static function noAutorizado(string $message = 'Necesitás iniciar sesión para continuar.'): never
    {
        self::error($message, 401);
    }

    public static function prohibido(string $message = 'No tenés permisos para realizar esta acción.'): never
    {
        self::error($message, 403);
    }

    public static function noEncontrado(string $message = 'El recurso solicitado no existe.'): never
    {
        self::error($message, 404);
    }

    public static function metodoNoPermitido(): never
    {
        self::error('Método HTTP no permitido para este recurso.', 405);
    }

    public static function errorServidor(string $message = 'Ocurrió un error inesperado. Intentá nuevamente.'): never
    {
        self::error($message, 500);
    }
}

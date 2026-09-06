<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Backend\Core\Response;
use Backend\Core\Session;
use Backend\Services\BorradorService;

/**
 * "Guardado de progreso" del panel de administración.
 *   GET    /api/borradores.php?formulario=oferta_educativa   -> recupera el último borrador
 *   PUT    /api/borradores.php?formulario=oferta_educativa   -> autoguarda (body JSON = contenido)
 *   DELETE /api/borradores.php?formulario=oferta_educativa   -> lo descarta (al enviar el formulario)
 */

$admin = Session::exigirAdministrador();
$servicio = new BorradorService();
$formulario = $_GET['formulario'] ?? '';

if ($formulario === '') {
    Response::error('Falta indicar el formulario (parámetro "formulario").', 400);
}

switch (metodoHttp()) {
    case 'GET':
        Response::exito('', ['borrador' => $servicio->obtener($admin['id_usuario'], $formulario)]);
        break;

    case 'PUT':
        $contenido = leerCuerpoJson();
        $ok = $servicio->guardar($admin['id_usuario'], $formulario, $contenido);
        if (!$ok) {
            Response::error('No se pudo guardar el progreso.', 400);
        }
        Response::exito('Progreso guardado.');
        break;

    case 'DELETE':
        $servicio->eliminar($admin['id_usuario'], $formulario);
        Response::exito('Borrador eliminado.');
        break;

    default:
        Response::metodoNoPermitido();
}

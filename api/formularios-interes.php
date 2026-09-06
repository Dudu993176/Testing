<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Backend\Core\Response;
use Backend\Core\Session;
use Backend\Services\FormularioInteresService;

$servicio = new FormularioInteresService();

switch (metodoHttp()) {
    case 'GET':
        Session::exigirAdministrador();
        Response::exito('', ['formularios' => $servicio->listarParaAdmin()]);
        break;

    case 'POST':
        // multipart/form-data (por el archivo "comprobante"): $_POST/$_FILES, no JSON.
        $resultado = $servicio->preinscribir($_POST, $_FILES['comprobante'] ?? null);
        if (isset($resultado['errores'])) {
            Response::errorValidacion($resultado['errores']);
        }
        Response::exito('¡Preinscripción enviada correctamente!', ['formulario' => $resultado['formulario']], 201);
        break;

    case 'DELETE':
        Session::exigirAdministrador();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if ($id === null) {
            Response::error('Falta indicar el id del formulario (parámetro "id").', 400);
        }
        $ok = $servicio->eliminar($id);
        if (!$ok) {
            Response::noEncontrado('El formulario de interés indicado no existe.');
        }
        Response::exito('Formulario de interés eliminado correctamente.');
        break;

    default:
        Response::metodoNoPermitido();
}

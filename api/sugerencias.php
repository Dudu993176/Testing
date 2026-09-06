<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Backend\Core\Response;
use Backend\Core\Session;
use Backend\Services\SugerenciaService;

$servicio = new SugerenciaService();

switch (metodoHttp()) {
    case 'GET':
        // Listado de moderación: sólo para el panel de administración.
        Session::exigirAdministrador();
        Response::exito('', ['sugerencias' => $servicio->listarParaAdmin()]);
        break;

    case 'POST':
        // Cualquiera puede sugerir: alumno logueado, administrador o visitante anónimo.
        $resultado = $servicio->crear(leerCuerpoJson(), ipDelCliente());
        if (isset($resultado['errores'])) {
            Response::errorValidacion($resultado['errores']);
        }
        Response::exito('¡Tu sugerencia fue enviada correctamente!', ['sugerencia' => $resultado['sugerencia']], 201);
        break;

    case 'DELETE':
        Session::exigirAdministrador();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if ($id === null) {
            Response::error('Falta indicar el id de la sugerencia (parámetro "id").', 400);
        }
        $ok = $servicio->eliminar($id);
        if (!$ok) {
            Response::noEncontrado('La sugerencia indicada no existe.');
        }
        Response::exito('Sugerencia eliminada correctamente.');
        break;

    default:
        Response::metodoNoPermitido();
}

<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Backend\Core\Response;
use Backend\Core\Session;
use Backend\Services\OfertaEducativaService;

$servicio = new OfertaEducativaService();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

switch (metodoHttp()) {
    case 'GET':
        // Público: sólo ofertas vigentes. El panel de admin pide ?todas=1 (requiere sesión de administrador).
        if (isset($_GET['todas'])) {
            Session::exigirAdministrador();
            Response::exito('', ['ofertas' => $servicio->listarTodas()]);
        }
        Response::exito('', ['ofertas' => $servicio->listarPublicas()]);
        break;

    case 'POST':
        $admin = Session::exigirAdministrador();
        $resultado = $servicio->crear(leerCuerpoJson(), $admin['id_usuario']);
        if (isset($resultado['errores'])) {
            Response::errorValidacion($resultado['errores']);
        }
        Response::exito('Oferta educativa creada correctamente.', ['oferta' => $resultado['oferta']], 201);
        break;

    case 'PUT':
    case 'PATCH':
        Session::exigirAdministrador();
        if ($id === null) {
            Response::error('Falta indicar el id de la oferta educativa (parámetro "id").', 400);
        }

        if (metodoHttp() === 'PATCH' && isset($_GET['estado'])) {
            $ok = $servicio->cambiarEstado($id, $_GET['estado']);
            if (!$ok) {
                Response::error('No se pudo cambiar el estado de la oferta educativa.', 400);
            }
            Response::exito('Estado actualizado correctamente.');
        }

        $resultado = $servicio->actualizar($id, leerCuerpoJson());
        if (isset($resultado['errores'])) {
            Response::errorValidacion($resultado['errores']);
        }
        Response::exito('Oferta educativa actualizada correctamente.', ['oferta' => $resultado['oferta']]);
        break;

    case 'DELETE':
        Session::exigirAdministrador();
        if ($id === null) {
            Response::error('Falta indicar el id de la oferta educativa (parámetro "id").', 400);
        }
        $ok = $servicio->eliminar($id);
        if (!$ok) {
            Response::noEncontrado('La oferta educativa indicada no existe.');
        }
        Response::exito('Oferta educativa eliminada correctamente.');
        break;

    default:
        Response::metodoNoPermitido();
}

<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Backend\Core\Response;
use Backend\Core\Session;
use Backend\Services\NoticiaService;

$servicio = new NoticiaService();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

switch (metodoHttp()) {
    case 'GET':
        // Público: cualquiera puede ver las noticias.
        Response::exito('', ['noticias' => $servicio->listar()]);
        break;

    case 'POST':
        $admin = Session::exigirAdministrador();
        $resultado = $servicio->crear(leerCuerpoJson(), $admin['id_usuario']);
        if (isset($resultado['errores'])) {
            Response::errorValidacion($resultado['errores']);
        }
        Response::exito('Noticia publicada correctamente.', ['noticia' => $resultado['noticia']], 201);
        break;

    case 'PUT':
    case 'PATCH':
        Session::exigirAdministrador();
        if ($id === null) {
            Response::error('Falta indicar el id de la noticia (parámetro "id").', 400);
        }
        $resultado = $servicio->actualizar($id, leerCuerpoJson());
        if (isset($resultado['errores'])) {
            Response::errorValidacion($resultado['errores']);
        }
        Response::exito('Noticia actualizada correctamente.', ['noticia' => $resultado['noticia']]);
        break;

    case 'DELETE':
        Session::exigirAdministrador();
        if ($id === null) {
            Response::error('Falta indicar el id de la noticia (parámetro "id").', 400);
        }
        $ok = $servicio->eliminar($id);
        if (!$ok) {
            Response::noEncontrado('La noticia indicada no existe.');
        }
        Response::exito('Noticia eliminada correctamente.');
        break;

    default:
        Response::metodoNoPermitido();
}

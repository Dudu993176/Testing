<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Backend\Core\Response;
use Backend\Services\AuthService;

if (metodoHttp() !== 'POST') {
    Response::metodoNoPermitido();
}

$datos = leerCuerpoJson();
$resultado = (new AuthService())->registrar($datos);

if (isset($resultado['errores'])) {
    Response::errorValidacion($resultado['errores']);
}

Response::exito('¡Registro completado correctamente!', ['usuario' => $resultado['usuario']], 201);

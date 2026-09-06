<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Backend\Core\Response;
use Backend\Core\Session;

if (metodoHttp() !== 'GET') {
    Response::metodoNoPermitido();
}

$usuario = Session::usuarioActual();

Response::exito('', ['usuario' => $usuario, 'autenticado' => $usuario !== null]);

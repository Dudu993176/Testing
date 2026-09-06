<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Backend\Core\Response;
use Backend\Core\Session;
use Backend\Services\UsuarioService;

if (metodoHttp() !== 'GET') {
    Response::metodoNoPermitido();
}

Session::exigirAdministrador();

Response::exito('', ['estudiantes' => (new UsuarioService())->listarAlumnos()]);

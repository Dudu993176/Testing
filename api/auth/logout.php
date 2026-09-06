<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Backend\Core\Response;
use Backend\Services\AuthService;

if (metodoHttp() !== 'POST') {
    Response::metodoNoPermitido();
}

(new AuthService())->logout();

Response::exito('Sesión cerrada correctamente.');

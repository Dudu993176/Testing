<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Backend\Core\Response;
use Backend\Core\Session;
use Backend\Services\HistorialService;

if (metodoHttp() !== 'GET') {
    Response::metodoNoPermitido();
}

Session::exigirAdministrador();

$limite = isset($_GET['limite']) ? max(1, min(500, (int) $_GET['limite'])) : 100;

Response::exito('', ['historial' => (new HistorialService())->listarRecientes($limite)]);

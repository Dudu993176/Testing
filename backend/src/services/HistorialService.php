<?php

namespace Backend\Services;

use Backend\Repositories\HistorialRepository;

/** Lectura del historial de accesos/eventos para el panel de administración. */
class HistorialService
{
    private HistorialRepository $historiales;

    public function __construct()
    {
        $this->historiales = new HistorialRepository();
    }

    public function listarRecientes(int $limite = 100): array
    {
        return $this->historiales->listarRecientes($limite);
    }
}

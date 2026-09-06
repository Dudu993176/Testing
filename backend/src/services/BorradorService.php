<?php

namespace Backend\Services;

use Backend\Repositories\BorradorRepository;

/**
 * "Guardado de progreso" del panel de administración: cada admin tiene un
 * borrador por tipo de formulario, autoguardado por el frontend cada pocos
 * segundos con fetch + async/await (ver assets/javascript/main.js -> autosaveBorrador).
 */
class BorradorService
{
    private const FORMULARIOS_PERMITIDOS = ['oferta_educativa', 'noticia'];

    private BorradorRepository $borradores;

    public function __construct()
    {
        $this->borradores = new BorradorRepository();
    }

    public function obtener(int $idUsuario, string $formulario): ?array
    {
        if (!in_array($formulario, self::FORMULARIOS_PERMITIDOS, true)) {
            return null;
        }
        return $this->borradores->obtener($idUsuario, $formulario);
    }

    public function guardar(int $idUsuario, string $formulario, array $contenido): bool
    {
        if (!in_array($formulario, self::FORMULARIOS_PERMITIDOS, true)) {
            return false;
        }
        // Nunca se guardan más de ~20 campos de texto corto: evita que el
        // autoguardado se use para almacenar datos arbitrarios/pesados.
        if (count($contenido) > 20) {
            return false;
        }
        return $this->borradores->guardar($idUsuario, $formulario, $contenido);
    }

    public function eliminar(int $idUsuario, string $formulario): bool
    {
        return $this->borradores->eliminar($idUsuario, $formulario);
    }
}

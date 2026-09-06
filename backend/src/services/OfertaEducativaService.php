<?php

namespace Backend\Services;

use Backend\Core\Validator;
use Backend\Repositories\OfertaEducativaRepository;
use Backend\Repositories\TurnoRepository;

/** Casos de uso de la sección "Ofertas Educativas" del panel de administración. */
class OfertaEducativaService
{
    private OfertaEducativaRepository $ofertas;
    private TurnoRepository $turnos;

    public function __construct()
    {
        $this->ofertas = new OfertaEducativaRepository();
        $this->turnos = new TurnoRepository();
    }

    public function listarPublicas(): array
    {
        return array_map(fn ($o) => $o->toArray(), $this->ofertas->listar('vigente'));
    }

    public function listarTodas(): array
    {
        return array_map(fn ($o) => $o->toArray(), $this->ofertas->listar());
    }

    private function validar(array $datos): Validator
    {
        $v = new Validator($datos);
        $v->requerido('nom_oferta', 'Ingresá el nombre de la oferta educativa.')
          ->maxLength('nom_oferta', 40, 'El nombre no puede superar los 40 caracteres.')
          ->requerido('descrip_oferta', 'Ingresá una descripción de la oferta.')
          ->maxLength('descrip_oferta', 200, 'La descripción no puede superar los 200 caracteres.')
          ->requerido('duracion_oferta', 'Ingresá la duración de la oferta (ej: "3 años").')
          ->requerido('perfil_egreso', 'Ingresá el perfil de egreso.')
          ->requerido('id_turno', 'Seleccioná un turno.')
          ->numerico('id_turno', 'El turno seleccionado no es válido.')
          ->personalizado('id_turno', fn ($v) => $v === null || $v === '' || $this->turnos->buscarPorId((int) $v) !== null, 'El turno seleccionado no existe.');
        return $v;
    }

    public function crear(array $datos, int $idUsuarioAdmin): array
    {
        $v = $this->validar($datos);
        if ($v->tieneErrores()) {
            return ['errores' => $v->getErrores()];
        }

        $idOferta = $this->ofertas->crear([
            'nom_oferta'      => trim($datos['nom_oferta']),
            'descrip_oferta'  => trim($datos['descrip_oferta']),
            'duracion_oferta' => trim($datos['duracion_oferta']),
            'estado_oferta'   => 'vigente',
            'requisitos'      => $datos['requisitos'] ?? null,
            'perfil_egreso'   => trim($datos['perfil_egreso']),
            'id_usuario'      => $idUsuarioAdmin,
            'id_turno'        => (int) $datos['id_turno'],
        ]);

        if ($idOferta === 0) {
            return ['errores' => ['general' => 'No se pudo crear la oferta educativa.']];
        }
        return ['oferta' => $this->ofertas->buscarPorId($idOferta)->toArray()];
    }

    public function actualizar(int $idOferta, array $datos): array
    {
        if ($this->ofertas->buscarPorId($idOferta) === null) {
            return ['errores' => ['general' => 'La oferta educativa indicada no existe.']];
        }
        $v = $this->validar($datos);
        if ($v->tieneErrores()) {
            return ['errores' => $v->getErrores()];
        }

        $ok = $this->ofertas->actualizar($idOferta, [
            'nom_oferta'      => trim($datos['nom_oferta']),
            'descrip_oferta'  => trim($datos['descrip_oferta']),
            'duracion_oferta' => trim($datos['duracion_oferta']),
            'estado_oferta'   => $datos['estado_oferta'] ?? 'vigente',
            'requisitos'      => $datos['requisitos'] ?? null,
            'perfil_egreso'   => trim($datos['perfil_egreso']),
            'id_turno'        => (int) $datos['id_turno'],
        ]);

        if (!$ok) {
            return ['errores' => ['general' => 'No se pudo actualizar la oferta educativa.']];
        }
        return ['oferta' => $this->ofertas->buscarPorId($idOferta)->toArray()];
    }

    public function cambiarEstado(int $idOferta, string $estado): bool
    {
        if (!in_array($estado, ['vigente', 'archivada'], true)) {
            return false;
        }
        return $this->ofertas->cambiarEstado($idOferta, $estado);
    }

    public function eliminar(int $idOferta): bool
    {
        return $this->ofertas->eliminar($idOferta);
    }
}

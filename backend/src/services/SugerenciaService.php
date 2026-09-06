<?php

namespace Backend\Services;

use Backend\Core\Session;
use Backend\Core\Validator;
use Backend\Models\Sugerencia;
use Backend\Repositories\SugerenciaRepository;
use Backend\Repositories\VisitanteRepository;

/** Casos de uso de Sugerencias.html y de su moderación en el panel de administración. */
class SugerenciaService
{
    private SugerenciaRepository $sugerencias;
    private VisitanteRepository $visitantes;

    public function __construct()
    {
        $this->sugerencias = new SugerenciaRepository();
        $this->visitantes = new VisitanteRepository();
    }

    /**
     * @param array $datos ['sugerencia' => string]
     * @return array{errores?: array<string,string>, sugerencia?: array}
     */
    public function crear(array $datos, string $ipRemota): array
    {
        $v = new Validator($datos);
        $v->requerido('sugerencia', 'Por favor, escribí una sugerencia antes de enviar.')
          ->maxLength('sugerencia', 200, 'La sugerencia no puede superar los 200 caracteres.');

        if ($v->tieneErrores()) {
            return ['errores' => $v->getErrores()];
        }

        $usuarioSesion = Session::usuarioActual();
        $idUsuario = $usuarioSesion['id_usuario'] ?? null;
        $idVisitante = null;

        if ($idUsuario === null) {
            // Visitante anónimo: se identifica (y limita) por IP anonimizada, no por cuenta.
            $visitante = $this->visitantes->buscarOCrear($ipRemota);
            $idVisitante = $visitante->getIdVisitante();

            if ($this->sugerencias->contarRecientesDeVisitante($idVisitante, 5) >= 3) {
                return ['errores' => ['sugerencia' => 'Ya enviaste varias sugerencias recientemente. Probá de nuevo en unos minutos.']];
            }
        }

        $sugerencia = new Sugerencia(trim($datos['sugerencia']), $idUsuario, $idVisitante);
        if (!$sugerencia->enviar($this->sugerencias)) {
            return ['errores' => ['general' => 'No se pudo enviar la sugerencia. Intentá nuevamente.']];
        }

        return ['sugerencia' => $sugerencia->toArray() + ['id_sugerencia' => $sugerencia->getIdSugerencia()]];
    }

    public function listarParaAdmin(): array
    {
        return $this->sugerencias->listarConAutor();
    }

    public function eliminar(int $idSugerencia): bool
    {
        return $this->sugerencias->eliminar($idSugerencia);
    }
}

<?php

namespace Backend\Services;

use Backend\Core\Validator;
use Backend\Repositories\NoticiaRepository;

/** Casos de uso de la sección "Noticias" del panel de administración. */
class NoticiaService
{
    private NoticiaRepository $noticias;

    public function __construct()
    {
        $this->noticias = new NoticiaRepository();
    }

    public function listar(): array
    {
        return array_map(fn ($n) => $n->toArray(), $this->noticias->listar());
    }

    private function validar(array $datos): Validator
    {
        $v = new Validator($datos);
        $v->requerido('descrip_noticia', 'Ingresá el contenido de la noticia.')
          ->maxLength('descrip_noticia', 200, 'La noticia no puede superar los 200 caracteres.');
        return $v;
    }

    public function crear(array $datos, int $idUsuarioAdmin): array
    {
        $v = $this->validar($datos);
        if ($v->tieneErrores()) {
            return ['errores' => $v->getErrores()];
        }

        $idNoticia = $this->noticias->crear([
            'descrip_noticia'                => trim($datos['descrip_noticia']),
            'id_usuario'                     => $idUsuarioAdmin,
            'aviso_polideportivo_compartido' => !empty($datos['aviso_polideportivo_compartido']),
        ]);

        if ($idNoticia === 0) {
            return ['errores' => ['general' => 'No se pudo publicar la noticia.']];
        }
        return ['noticia' => $this->noticias->buscarPorId($idNoticia)->toArray()];
    }

    public function actualizar(int $idNoticia, array $datos): array
    {
        if ($this->noticias->buscarPorId($idNoticia) === null) {
            return ['errores' => ['general' => 'La noticia indicada no existe.']];
        }
        $v = $this->validar($datos);
        if ($v->tieneErrores()) {
            return ['errores' => $v->getErrores()];
        }

        $ok = $this->noticias->actualizar($idNoticia, [
            'descrip_noticia'                => trim($datos['descrip_noticia']),
            'aviso_polideportivo_compartido' => !empty($datos['aviso_polideportivo_compartido']),
        ]);

        if (!$ok) {
            return ['errores' => ['general' => 'No se pudo actualizar la noticia.']];
        }
        return ['noticia' => $this->noticias->buscarPorId($idNoticia)->toArray()];
    }

    public function eliminar(int $idNoticia): bool
    {
        return $this->noticias->eliminar($idNoticia);
    }
}

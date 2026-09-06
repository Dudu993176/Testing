<?php

namespace Backend\Models;

/** Sugerencia (ver UML): puede venir de un Usuario logueado o de un Visitante anónimo. */
class Sugerencia
{
    private int $idSugerencia = 0;
    private string $contenido;
    private ?string $fechaSugerencia = null;
    private ?int $idUsuario;
    private ?int $idVisitante;

    public function __construct(string $contenido, ?int $idUsuario, ?int $idVisitante)
    {
        $this->contenido = $contenido;
        $this->idUsuario = $idUsuario;
        $this->idVisitante = $idVisitante;
    }

    public static function desdeFila(array $fila): self
    {
        $sugerencia = new self(
            $fila['contenido'],
            isset($fila['id_usuario']) ? (int) $fila['id_usuario'] : null,
            isset($fila['id_visitante']) ? (int) $fila['id_visitante'] : null
        );
        $sugerencia->idSugerencia = (int) $fila['id_sugerencia'];
        $sugerencia->fechaSugerencia = $fila['fecha_sugerencia'] ?? null;
        return $sugerencia;
    }

    public function getIdSugerencia(): int
    {
        return $this->idSugerencia;
    }

    public function getContenido(): string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): void
    {
        $this->contenido = $contenido;
    }

    public function getFechaSugerencia(): ?string
    {
        return $this->fechaSugerencia;
    }

    public function getIdUsuario(): ?int
    {
        return $this->idUsuario;
    }

    public function getIdVisitante(): ?int
    {
        return $this->idVisitante;
    }

    public function esAnonima(): bool
    {
        return $this->idUsuario === null;
    }

    public function enviar(\Backend\Repositories\SugerenciaRepository $repo): bool
    {
        if (trim($this->contenido) === '') {
            return false;
        }
        $this->idSugerencia = $repo->crear($this->toArray());
        return $this->idSugerencia > 0;
    }

    public function toArray(): array
    {
        return [
            'contenido'    => $this->contenido,
            'id_usuario'   => $this->idUsuario,
            'id_visitante' => $this->idVisitante,
        ];
    }
}

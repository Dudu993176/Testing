<?php

namespace Backend\Models;

/** Historial (ver UML): bitácora de accesos/eventos, de un Usuario o un Visitante. */
class Historial
{
    private int $idHistorial = 0;
    private ?string $fechaIngreso = null;
    private string $descripHistorial;
    private ?int $idUsuario;
    private ?int $idVisitante;

    public function __construct(string $descripHistorial, ?int $idUsuario, ?int $idVisitante)
    {
        $this->descripHistorial = $descripHistorial;
        $this->idUsuario = $idUsuario;
        $this->idVisitante = $idVisitante;
    }

    public static function desdeFila(array $fila): self
    {
        $historial = new self(
            $fila['descrip_historial'] ?? '',
            isset($fila['id_usuario']) ? (int) $fila['id_usuario'] : null,
            isset($fila['id_visitante']) ? (int) $fila['id_visitante'] : null
        );
        $historial->idHistorial = (int) $fila['id_historial'];
        $historial->fechaIngreso = $fila['fecha_ingreso'] ?? null;
        return $historial;
    }

    public function getIdHistorial(): int
    {
        return $this->idHistorial;
    }

    public function getFechaIngreso(): ?string
    {
        return $this->fechaIngreso;
    }

    public function getDescripHistorial(): string
    {
        return $this->descripHistorial;
    }

    public function setDescripHistorial(string $descripcion): void
    {
        $this->descripHistorial = $descripcion;
    }

    public function getIdUsuario(): ?int
    {
        return $this->idUsuario;
    }

    public function getIdVisitante(): ?int
    {
        return $this->idVisitante;
    }

    public function esAccesoAnonimo(): bool
    {
        return $this->idUsuario === null && $this->idVisitante !== null;
    }

    public function registrarEvento(\Backend\Repositories\HistorialRepository $repo): bool
    {
        $this->idHistorial = $repo->crear($this->toArray());
        return $this->idHistorial > 0;
    }

    public function toArray(): array
    {
        return [
            'descrip_historial' => $this->descripHistorial,
            'id_usuario'        => $this->idUsuario,
            'id_visitante'      => $this->idVisitante,
        ];
    }
}

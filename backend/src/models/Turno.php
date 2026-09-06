<?php

namespace Backend\Models;

/** Turno (ver UML). */
class Turno
{
    private int $idTurno = 0;
    private string $nomTurno;
    private string $descripTurno;

    public function __construct(string $nomTurno, string $descripTurno)
    {
        $this->nomTurno = $nomTurno;
        $this->descripTurno = $descripTurno;
    }

    public static function desdeFila(array $fila): self
    {
        $turno = new self($fila['nom_turno'], $fila['descrip_turno']);
        $turno->idTurno = (int) $fila['id_turno'];
        return $turno;
    }

    public function getIdTurno(): int
    {
        return $this->idTurno;
    }

    public function getNomTurno(): string
    {
        return $this->nomTurno;
    }

    public function setNomTurno(string $nombre): void
    {
        $this->nomTurno = $nombre;
    }

    public function getDescripTurno(): string
    {
        return $this->descripTurno;
    }

    public function setDescripTurno(string $descripcion): void
    {
        $this->descripTurno = $descripcion;
    }

    public function toArray(): array
    {
        return [
            'id_turno'      => $this->idTurno,
            'nom_turno'     => $this->nomTurno,
            'descrip_turno' => $this->descripTurno,
        ];
    }
}

<?php

namespace Backend\Models;

/** Materia (ver UML). */
class Materia
{
    private int $idMateria = 0;
    private string $nomMateria;
    private string $descripMateria;

    public function __construct(string $nomMateria, string $descripMateria)
    {
        $this->nomMateria = $nomMateria;
        $this->descripMateria = $descripMateria;
    }

    public static function desdeFila(array $fila): self
    {
        $materia = new self($fila['nom_materia'], $fila['descrip_materia']);
        $materia->idMateria = (int) $fila['id_materia'];
        return $materia;
    }

    public function getIdMateria(): int
    {
        return $this->idMateria;
    }

    public function getNomMateria(): string
    {
        return $this->nomMateria;
    }

    public function setNomMateria(string $nombre): void
    {
        $this->nomMateria = $nombre;
    }

    public function getDescripMateria(): string
    {
        return $this->descripMateria;
    }

    public function setDescripMateria(string $descripcion): void
    {
        $this->descripMateria = $descripcion;
    }

    public function toArray(): array
    {
        return [
            'id_materia'      => $this->idMateria,
            'nom_materia'     => $this->nomMateria,
            'descrip_materia' => $this->descripMateria,
        ];
    }
}

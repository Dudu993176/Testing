<?php

namespace Backend\Models;

/** Curso (ver UML). */
class Curso
{
    private int $idCurso = 0;
    private string $nomCurso;
    private string $descrip;

    public function __construct(string $nomCurso, string $descrip)
    {
        $this->nomCurso = $nomCurso;
        $this->descrip = $descrip;
    }

    public static function desdeFila(array $fila): self
    {
        $curso = new self($fila['nom_curso'], $fila['descrip']);
        $curso->idCurso = (int) $fila['id_curso'];
        return $curso;
    }

    public function getIdCurso(): int
    {
        return $this->idCurso;
    }

    public function getNomCurso(): string
    {
        return $this->nomCurso;
    }

    public function setNomCurso(string $nombre): void
    {
        $this->nomCurso = $nombre;
    }

    public function getDescrip(): string
    {
        return $this->descrip;
    }

    public function setDescrip(string $descripcion): void
    {
        $this->descrip = $descripcion;
    }

    public function toArray(): array
    {
        return [
            'id_curso'  => $this->idCurso,
            'nom_curso' => $this->nomCurso,
            'descrip'   => $this->descrip,
        ];
    }
}

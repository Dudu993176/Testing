<?php

namespace Backend\Models;

/** NivelAcceso (ver UML): Administrador, Alumno o Visitante. */
class NivelAcceso
{
    public const ADMINISTRADOR = 1;
    public const ALUMNO = 2;
    public const VISITANTE = 3;

    private int $idNivel = 0;
    private string $nombreNivel;
    private string $descripNivel;

    public function __construct(string $nombreNivel, string $descripNivel)
    {
        $this->nombreNivel = $nombreNivel;
        $this->descripNivel = $descripNivel;
    }

    public static function desdeFila(array $fila): self
    {
        $nivel = new self($fila['nombre_nivel'], $fila['descrip_nivel']);
        $nivel->idNivel = (int) $fila['id_nivel'];
        return $nivel;
    }

    public function getIdNivel(): int
    {
        return $this->idNivel;
    }

    public function getNombreNivel(): string
    {
        return $this->nombreNivel;
    }

    public function setNombreNivel(string $nombre): void
    {
        $this->nombreNivel = $nombre;
    }

    public function getDescripNivel(): string
    {
        return $this->descripNivel;
    }

    public function setDescripNivel(string $descripcion): void
    {
        $this->descripNivel = $descripcion;
    }

    /** Reglas simples de permisos por nivel de acceso, usadas por Session::exigir*(). */
    public function tienePermiso(string $accion): bool
    {
        $permisosPorNivel = [
            self::ADMINISTRADOR => ['*'],
            self::ALUMNO        => ['ver_ofertas', 'enviar_sugerencia', 'enviar_formulario_interes'],
            self::VISITANTE     => ['ver_ofertas', 'enviar_sugerencia'],
        ];
        $permisos = $permisosPorNivel[$this->idNivel] ?? [];
        return in_array('*', $permisos, true) || in_array($accion, $permisos, true);
    }

    public function toArray(): array
    {
        return [
            'id_nivel'      => $this->idNivel,
            'nombre_nivel'  => $this->nombreNivel,
            'descrip_nivel' => $this->descripNivel,
        ];
    }
}

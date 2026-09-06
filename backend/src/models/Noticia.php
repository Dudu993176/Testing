<?php

namespace Backend\Models;

/** Noticia (ver UML), con el aviso RFE-06 sobre uso compartido del Polideportivo. */
class Noticia
{
    private int $idNoticia = 0;
    private string $descripNoticia;
    private ?string $fechaNoticia = null;
    private int $idUsuario;
    private bool $avisoPolideportivoCompartido;

    public function __construct(string $descripNoticia, int $idUsuario, bool $avisoPolideportivoCompartido = false)
    {
        $this->descripNoticia = $descripNoticia;
        $this->idUsuario = $idUsuario;
        $this->avisoPolideportivoCompartido = $avisoPolideportivoCompartido;
    }

    public static function desdeFila(array $fila): self
    {
        $noticia = new self(
            $fila['descrip_noticia'],
            (int) $fila['id_usuario'],
            (bool) ($fila['aviso_polideportivo_compartido'] ?? false)
        );
        $noticia->idNoticia = (int) $fila['id_noticia'];
        $noticia->fechaNoticia = $fila['fecha_noticia'] ?? null;
        return $noticia;
    }

    public function getIdNoticia(): int
    {
        return $this->idNoticia;
    }

    public function setIdNoticia(int $id): void
    {
        $this->idNoticia = $id;
    }

    public function getDescripNoticia(): string
    {
        return $this->descripNoticia;
    }

    public function setDescripNoticia(string $descripcion): void
    {
        $this->descripNoticia = $descripcion;
    }

    public function getFechaNoticia(): ?string
    {
        return $this->fechaNoticia;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function avisaPolideportivoCompartido(): bool
    {
        return $this->avisoPolideportivoCompartido;
    }

    public function setAvisoPolideportivoCompartido(bool $aviso): void
    {
        $this->avisoPolideportivoCompartido = $aviso;
    }

    public function toArray(): array
    {
        return [
            'id_noticia'                     => $this->idNoticia,
            'descrip_noticia'                => $this->descripNoticia,
            'fecha_noticia'                  => $this->fechaNoticia,
            'id_usuario'                     => $this->idUsuario,
            'aviso_polideportivo_compartido' => $this->avisoPolideportivoCompartido,
        ];
    }
}

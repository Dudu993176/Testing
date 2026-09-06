<?php

namespace Backend\Models;

/** OfertaEducativa (ver UML), con la extensión RFE-07 (adjunto PDF). */
class OfertaEducativa
{
    private int $idOferta = 0;
    private string $nomOferta;
    private string $descripOferta;
    private string $duracionOferta;
    private string $estadoOferta; // 'vigente' | 'archivada'
    private ?string $requisitos;
    private string $perfilEgreso;
    private int $idUsuario;
    private int $idTurno;
    private ?string $archivoProgramaPdf = null;

    public function __construct(
        string $nomOferta,
        string $descripOferta,
        string $duracionOferta,
        ?string $requisitos,
        string $perfilEgreso,
        int $idUsuario,
        int $idTurno,
        string $estadoOferta = 'vigente'
    ) {
        $this->nomOferta = $nomOferta;
        $this->descripOferta = $descripOferta;
        $this->duracionOferta = $duracionOferta;
        $this->requisitos = $requisitos;
        $this->perfilEgreso = $perfilEgreso;
        $this->idUsuario = $idUsuario;
        $this->idTurno = $idTurno;
        $this->estadoOferta = $estadoOferta;
    }

    public static function desdeFila(array $fila): self
    {
        $oferta = new self(
            $fila['nom_oferta'],
            $fila['descrip_oferta'],
            $fila['duración_oferta'] ?? $fila['duracion_oferta'] ?? '',
            $fila['requisitos'] ?? null,
            $fila['perfil_egreso'],
            (int) $fila['id_usuario'],
            (int) $fila['id_turno'],
            $fila['estado_oferta'] ?? 'vigente'
        );
        $oferta->idOferta = (int) $fila['id_oferta'];
        $oferta->archivoProgramaPdf = $fila['archivo_programa_pdf'] ?? null;
        return $oferta;
    }

    public function getIdOferta(): int
    {
        return $this->idOferta;
    }

    public function setIdOferta(int $id): void
    {
        $this->idOferta = $id;
    }

    public function getNomOferta(): string
    {
        return $this->nomOferta;
    }

    public function setNomOferta(string $nombre): void
    {
        $this->nomOferta = $nombre;
    }

    public function getDescripOferta(): string
    {
        return $this->descripOferta;
    }

    public function setDescripOferta(string $descripcion): void
    {
        $this->descripOferta = $descripcion;
    }

    public function getDuracionOferta(): string
    {
        return $this->duracionOferta;
    }

    public function setDuracionOferta(string $duracion): void
    {
        $this->duracionOferta = $duracion;
    }

    public function getEstadoOferta(): string
    {
        return $this->estadoOferta;
    }

    public function setEstadoOferta(string $estado): void
    {
        $this->estadoOferta = $estado;
    }

    public function getRequisitos(): ?string
    {
        return $this->requisitos;
    }

    public function setRequisitos(?string $requisitos): void
    {
        $this->requisitos = $requisitos;
    }

    public function getPerfilEgreso(): string
    {
        return $this->perfilEgreso;
    }

    public function setPerfilEgreso(string $perfil): void
    {
        $this->perfilEgreso = $perfil;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getIdTurno(): int
    {
        return $this->idTurno;
    }

    public function setIdTurno(int $idTurno): void
    {
        $this->idTurno = $idTurno;
    }

    public function getArchivoProgramaPdf(): ?string
    {
        return $this->archivoProgramaPdf;
    }

    /** RFE-07: sólo se aceptan adjuntos .pdf. */
    public function setArchivoProgramaPdf(?string $rutaArchivo): bool
    {
        if ($rutaArchivo !== null && !str_ends_with(strtolower($rutaArchivo), '.pdf')) {
            return false;
        }
        $this->archivoProgramaPdf = $rutaArchivo;
        return true;
    }

    public function publicar(): void
    {
        $this->estadoOferta = 'vigente';
    }

    public function archivar(): void
    {
        $this->estadoOferta = 'archivada';
    }

    public function toArray(): array
    {
        return [
            'id_oferta'             => $this->idOferta,
            'nom_oferta'            => $this->nomOferta,
            'descrip_oferta'        => $this->descripOferta,
            'duracion_oferta'       => $this->duracionOferta,
            'estado_oferta'         => $this->estadoOferta,
            'requisitos'            => $this->requisitos,
            'perfil_egreso'         => $this->perfilEgreso,
            'id_usuario'            => $this->idUsuario,
            'id_turno'              => $this->idTurno,
            'archivo_programa_pdf'  => $this->archivoProgramaPdf,
        ];
    }
}

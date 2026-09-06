<?php

namespace Backend\Models;

/** Horario (ver UML). */
class Horario
{
    private int $idHorario = 0;
    private string $diaSemana;
    private string $horaInicio; // 'HH:MM:SS'
    private string $horaFin;    // 'HH:MM:SS'

    public function __construct(string $diaSemana, string $horaInicio, string $horaFin)
    {
        $this->diaSemana = $diaSemana;
        $this->horaInicio = $horaInicio;
        $this->horaFin = $horaFin;
    }

    public static function desdeFila(array $fila): self
    {
        $horario = new self(
            $fila['día_semana'] ?? $fila['dia_semana'] ?? '',
            $fila['hora_inicio'],
            $fila['hora_fin']
        );
        $horario->idHorario = (int) $fila['id_horario'];
        return $horario;
    }

    public function getIdHorario(): int
    {
        return $this->idHorario;
    }

    public function getDiaSemana(): string
    {
        return $this->diaSemana;
    }

    public function setDiaSemana(string $dia): void
    {
        $this->diaSemana = $dia;
    }

    public function getHoraInicio(): string
    {
        return $this->horaInicio;
    }

    public function setHoraInicio(string $hora): void
    {
        $this->horaInicio = $hora;
    }

    public function getHoraFin(): string
    {
        return $this->horaFin;
    }

    public function setHoraFin(string $hora): void
    {
        $this->horaFin = $hora;
    }

    /** Duración en minutos entre horaInicio y horaFin. */
    public function calcularDuracion(): int
    {
        $inicio = \DateTime::createFromFormat('H:i:s', $this->horaInicio);
        $fin = \DateTime::createFromFormat('H:i:s', $this->horaFin);
        if (!$inicio || !$fin) {
            return 0;
        }
        return (int) (($fin->getTimestamp() - $inicio->getTimestamp()) / 60);
    }

    public function seSuperponeCon(Horario $otroHorario): bool
    {
        if ($this->diaSemana !== $otroHorario->diaSemana) {
            return false;
        }
        return $this->horaInicio < $otroHorario->horaFin && $otroHorario->horaInicio < $this->horaFin;
    }

    public function toArray(): array
    {
        return [
            'id_horario'  => $this->idHorario,
            'dia_semana'  => $this->diaSemana,
            'hora_inicio' => $this->horaInicio,
            'hora_fin'    => $this->horaFin,
        ];
    }
}

<?php

namespace Backend\Models;

/**
 * Alumno (ver UML). Extiende Usuario y agrega `cursoActual` más los campos
 * de RFE-03/RFE-04 (03_RNE_y_triggers.sql): menor de edad, autorización de
 * un adulto responsable y consentimiento de uso de imagen.
 */
class Alumno extends Usuario
{
    private ?string $cursoActual;
    private bool $esMenor;
    private bool $autorizacionAdulto;
    private bool $consentimientoImagen;

    public function __construct(
        int $cedula,
        string $nomUsuario,
        string $email,
        string $passUsuario,
        string $pais,
        int $idNivel,
        ?string $cursoActual = null,
        bool $esMenor = false,
        bool $autorizacionAdulto = false,
        bool $consentimientoImagen = false
    ) {
        parent::__construct($cedula, $nomUsuario, $email, $passUsuario, $pais, $idNivel);
        $this->cursoActual = $cursoActual;
        $this->esMenor = $esMenor;
        $this->autorizacionAdulto = $autorizacionAdulto;
        $this->consentimientoImagen = $consentimientoImagen;
    }

    public static function desdeFila(array $fila): static
    {
        $alumno = new static(
            (int) $fila['cedula'],
            $fila['nom_usuario'],
            $fila['email'],
            $fila['pass_usuario'],
            $fila['pais'],
            (int) $fila['id_nivel'],
            $fila['curso_actual'] ?? null,
            (bool) ($fila['es_menor'] ?? false),
            (bool) ($fila['autorizacion_adulto'] ?? false),
            (bool) ($fila['consentimiento_imagen'] ?? false)
        );
        $alumno->idUsuario = (int) $fila['id_usuario'];
        $alumno->fechaRegistro = $fila['fecha_registro'] ?? null;
        return $alumno;
    }

    public function getCursoActual(): ?string
    {
        return $this->cursoActual;
    }

    public function setCursoActual(string $curso): void
    {
        $this->cursoActual = $curso;
    }

    public function esMenorDeEdad(): bool
    {
        return $this->esMenor;
    }

    public function tieneAutorizacionAdulto(): bool
    {
        return $this->autorizacionAdulto;
    }

    /** RNE-03: un menor sin autorización de un adulto no puede completar el trámite. */
    public function cumpleRequisitoDeEdad(): bool
    {
        return !$this->esMenor || $this->autorizacionAdulto;
    }

    public function dioConsentimientoImagen(): bool
    {
        return $this->consentimientoImagen;
    }

    /**
     * enviarFormularioInteres(formulario) del UML: delega la persistencia en
     * FormularioInteresService, que valida cupo/edad/anti-SQLi antes de
     * insertar. Se deja documentado acá para que quede explícito el vínculo
     * Alumno -> FormularioInteres del diagrama de clases.
     */

    public function toArray(): array
    {
        return parent::toArray() + [
            'curso_actual'          => $this->cursoActual,
            'es_menor'              => $this->esMenor,
            'autorizacion_adulto'   => $this->autorizacionAdulto,
            'consentimiento_imagen' => $this->consentimientoImagen,
        ];
    }
}

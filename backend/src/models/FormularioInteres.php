<?php

namespace Backend\Models;

/**
 * FormularioInteres (ver UML): la preinscripción a un curso, con los campos
 * de 03_RNE_y_triggers.sql (RFE-01/02/03/04).
 */
class FormularioInteres
{
    private int $idFormulario = 0;
    private string $cursoInteres; // columna real: curso_lista_interes
    private string $descripFormulario;
    private ?string $mensajeUsuario;
    private ?string $fechaForm = null;
    private int $idUsuario;
    private bool $avisoCupoAceptado;

    public function __construct(
        string $cursoInteres,
        string $descripFormulario,
        ?string $mensajeUsuario,
        int $idUsuario,
        bool $avisoCupoAceptado = true
    ) {
        $this->cursoInteres = $cursoInteres;
        $this->descripFormulario = $descripFormulario;
        $this->mensajeUsuario = $mensajeUsuario;
        $this->idUsuario = $idUsuario;
        $this->avisoCupoAceptado = $avisoCupoAceptado;
    }

    public static function desdeFila(array $fila): self
    {
        $formulario = new self(
            $fila['curso_lista_interes'] ?? $fila['curso_interés'] ?? '',
            $fila['descrip_formulario'],
            $fila['mensaje_usuario'] ?? null,
            (int) $fila['id_usuario'],
            (bool) ($fila['aviso_cupo_aceptado'] ?? true)
        );
        $formulario->idFormulario = (int) $fila['id_formulario'];
        $formulario->fechaForm = $fila['fecha_form'] ?? null;
        return $formulario;
    }

    public function getIdFormulario(): int
    {
        return $this->idFormulario;
    }

    public function setIdFormulario(int $id): void
    {
        $this->idFormulario = $id;
    }

    public function getCursoInteres(): string
    {
        return $this->cursoInteres;
    }

    public function setCursoInteres(string $curso): void
    {
        $this->cursoInteres = $curso;
    }

    public function getDescripFormulario(): string
    {
        return $this->descripFormulario;
    }

    public function setDescripFormulario(string $descripcion): void
    {
        $this->descripFormulario = $descripcion;
    }

    public function getMensajeUsuario(): ?string
    {
        return $this->mensajeUsuario;
    }

    public function setMensajeUsuario(?string $mensaje): void
    {
        $this->mensajeUsuario = $mensaje;
    }

    public function getFechaForm(): ?string
    {
        return $this->fechaForm;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function fueAvisadoDeCupo(): bool
    {
        return $this->avisoCupoAceptado;
    }

    /**
     * validarDatos() del UML: reglas de negocio propias de la entidad
     * (además de las de Validator, que cubren formato de campos sueltos).
     * RFE-02: el aviso de "no garantiza cupo" es obligatorio.
     */
    public function validarDatos(): bool
    {
        return $this->cursoInteres !== ''
            && $this->descripFormulario !== ''
            && $this->avisoCupoAceptado === true;
    }

    /** enviar(): delega el guardado real en el repositorio vía el servicio. */
    public function enviar(\Backend\Repositories\FormularioInteresRepository $repo): bool
    {
        if (!$this->validarDatos()) {
            return false;
        }
        $this->idFormulario = $repo->crear($this->toArray());
        return $this->idFormulario > 0;
    }

    public function toArray(): array
    {
        return [
            'curso_lista_interes'  => $this->cursoInteres,
            'descrip_formulario'   => $this->descripFormulario,
            'mensaje_usuario'      => $this->mensajeUsuario,
            'id_usuario'           => $this->idUsuario,
            'aviso_cupo_aceptado'  => $this->avisoCupoAceptado,
        ];
    }
}

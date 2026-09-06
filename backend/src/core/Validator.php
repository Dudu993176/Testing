<?php

namespace Backend\Core;

/**
 * Motor de validación del lado del servidor.
 *
 * Es la mitad "backend" de la Verificación dual (frontend en JavaScript +
 * backend en PHP): el JavaScript de cada formulario ya valida antes de
 * enviar, pero el servidor NUNCA debe confiar en eso, así que repite (y
 * amplía) las mismas reglas acá antes de tocar la base de datos.
 *
 * Uso típico dentro de un servicio:
 *
 *   $v = new Validator($datos);
 *   $v->requerido('nombre', 'Ingresá tu nombre.')
 *     ->email('correo', 'El correo electrónico no es válido.')
 *     ->minLength('password', 6, 'La contraseña debe tener al menos 6 caracteres.');
 *
 *   if ($v->tieneErrores()) {
 *       Response::errorValidacion($v->getErrores());
 *   }
 */
final class Validator
{
    /** @var array<string,mixed> */
    private array $datos;

    /** @var array<string,string> nombre_campo => mensaje contextual */
    private array $errores = [];

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    private function valor(string $campo): mixed
    {
        $v = $this->datos[$campo] ?? null;
        return is_string($v) ? trim($v) : $v;
    }

    /** Evita sobreescribir un error ya detectado para el mismo campo. */
    private function agregarError(string $campo, string $mensaje): void
    {
        if (!isset($this->errores[$campo])) {
            $this->errores[$campo] = $mensaje;
        }
    }

    public function requerido(string $campo, string $mensaje): self
    {
        $valor = $this->valor($campo);
        if ($valor === null || $valor === '' || (is_array($valor) && count($valor) === 0)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function checkboxAceptado(string $campo, string $mensaje): self
    {
        $valor = $this->valor($campo);
        $aceptado = $valor === true || $valor === 1 || $valor === '1' || $valor === 'on' || $valor === 'true';
        if (!$aceptado) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function email(string $campo, string $mensaje): self
    {
        $valor = $this->valor($campo);
        if ($valor !== null && $valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function minLength(string $campo, int $min, string $mensaje): self
    {
        $valor = $this->valor($campo);
        if (is_string($valor) && $valor !== '' && mb_strlen($valor) < $min) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function maxLength(string $campo, int $max, string $mensaje): self
    {
        $valor = $this->valor($campo);
        if (is_string($valor) && mb_strlen($valor) > $max) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function numerico(string $campo, string $mensaje): self
    {
        $valor = $this->valor($campo);
        if ($valor !== null && $valor !== '' && !preg_match('/^\d+$/', (string) $valor)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function coincideCon(string $campo, string $otroCampo, string $mensaje): self
    {
        if ($this->valor($campo) !== $this->valor($otroCampo)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function fecha(string $campo, string $mensaje, string $formato = 'Y-m-d'): self
    {
        $valor = $this->valor($campo);
        if ($valor === null || $valor === '') {
            return $this;
        }
        $d = \DateTime::createFromFormat($formato, (string) $valor);
        if (!$d || $d->format($formato) !== $valor) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function enLista(string $campo, array $opciones, string $mensaje): self
    {
        $valor = $this->valor($campo);
        if ($valor !== null && $valor !== '' && !in_array($valor, $opciones, true)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    /** Regla personalizada: si el callback devuelve false, agrega el error. */
    public function personalizado(string $campo, callable $validoSi, string $mensaje): self
    {
        if (!isset($this->errores[$campo]) && !$validoSi($this->valor($campo), $this->datos)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function tieneErrores(): bool
    {
        return count($this->errores) > 0;
    }

    /** @return array<string,string> */
    public function getErrores(): array
    {
        return $this->errores;
    }

    /**
     * Dígito verificador de la cédula de identidad uruguaya (módulo 10).
     * No se exige por defecto (los datos de prueba de 02_Datos_prueba.sql no
     * son cédulas reales), pero queda disponible para reforzar la validación
     * en un entorno con datos reales.
     */
    public static function esCedulaUruguayaValida(string $cedula): bool
    {
        $cedula = preg_replace('/\D/', '', $cedula);
        if ($cedula === '' || strlen($cedula) > 8) {
            return false;
        }
        $cedula = str_pad($cedula, 8, '0', STR_PAD_LEFT);
        $pesos = [2, 9, 8, 7, 6, 3, 4];
        $suma = 0;
        for ($i = 0; $i < 7; $i++) {
            $suma += ((int) $cedula[$i]) * $pesos[$i];
        }
        $resto = $suma % 10;
        $digitoEsperado = $resto === 0 ? 0 : 10 - $resto;
        return $digitoEsperado === (int) $cedula[7];
    }
}

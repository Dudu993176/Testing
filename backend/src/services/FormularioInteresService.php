<?php

namespace Backend\Services;

use Backend\Core\Session;
use Backend\Core\Validator;
use Backend\Models\Alumno;
use Backend\Models\FormularioInteres;
use Backend\Models\NivelAcceso;
use Backend\Models\Usuario;
use Backend\Repositories\AlumnoRepository;
use Backend\Repositories\FormularioInteresRepository;
use Backend\Repositories\HistorialRepository;
use Backend\Repositories\UsuarioRepository;

/**
 * Caso de uso de Preinscribirse.html: crea (o autentica) la cuenta del
 * interesado y guarda su interés por un curso en `Formularios_interes`.
 *
 * Implementa además las reglas de negocio agregadas en
 * 03_RNE_y_triggers.sql:
 *   RFE-01/02: el curso elegido y el aviso de "no garantiza cupo" son obligatorios.
 *   RFE-03: si la persona es menor de edad, exige autorización de un adulto.
 *   RFE-04: registra el consentimiento (opcional) de uso de imagen.
 */
class FormularioInteresService
{
    private const CURSOS_VALIDOS = [
        'administracion', 'agrario', 'bienestar-y-salud', 'construccion-muebles-por-diseno',
        'construccion', 'deporte-y-recreacion', 'electromecanica', 'estetica-capilar',
        'gastronomia', 'informatica', 'instalaciones-electricas', 'mecanica-en-produccion',
    ];

    private const EXTENSIONES_COMPROBANTE = ['pdf', 'jpg', 'jpeg', 'png'];
    private const TAMANO_MAXIMO_COMPROBANTE = 5 * 1024 * 1024; // 5 MB

    private UsuarioRepository $usuarios;
    private AlumnoRepository $alumnos;
    private FormularioInteresRepository $formularios;
    private HistorialRepository $historiales;
    private array $config;

    public function __construct()
    {
        $this->usuarios = new UsuarioRepository();
        $this->alumnos = new AlumnoRepository();
        $this->formularios = new FormularioInteresRepository();
        $this->historiales = new HistorialRepository();
        $this->config = require __DIR__ . '/../config/config.php';
    }

    /**
     * @param array $datos Campos de texto del formulario (ya con trim aplicado por el controlador).
     * @param array|null $archivoComprobante Entrada cruda de $_FILES['comprobante'] (o null si no se adjuntó).
     */
    public function preinscribir(array $datos, ?array $archivoComprobante): array
    {
        $v = new Validator($datos);
        $v->requerido('cedula', 'Ingresá tu cédula.')
          ->numerico('cedula', 'La cédula sólo puede contener números.')
          ->requerido('password', 'Creá una contraseña.')
          ->minLength('password', 6, 'La contraseña debe tener al menos 6 caracteres.')
          ->requerido('curso', 'Seleccioná el curso al que te querés preinscribir.')
          ->enLista('curso', self::CURSOS_VALIDOS, 'Seleccioná un curso válido de la lista.')
          ->requerido('pais_ciudad', 'Ingresá tu país y ciudad de residencia.')
          ->checkboxAceptado('aviso_cupo', 'Tenés que confirmar que entendiste que la preinscripción no garantiza un cupo.')
          ->checkboxAceptado('terminos', 'Tenés que aceptar las normas y la política de privacidad para continuar.');

        if ($v->tieneErrores()) {
            return ['errores' => $v->getErrores()];
        }

        $cedula = (int) $datos['cedula'];
        $esMenor = !empty($datos['menor']);
        $autorizacionAdulto = !empty($datos['autorizacion_adulto']);
        $consentimientoImagen = !empty($datos['consentimiento_imagen']);

        // RFE-03: un menor sin autorización no puede continuar.
        if ($esMenor && !$autorizacionAdulto) {
            return ['errores' => ['autorizacion_adulto' => 'Si sos menor de edad, necesitás la autorización de un adulto responsable para continuar.']];
        }

        $usuarioExistente = $this->usuarios->buscarPorCedula($cedula);

        if ($usuarioExistente !== null) {
            // Ya tiene cuenta: la preinscripción también sirve para iniciar sesión.
            if (!$usuarioExistente->autenticar($datos['password'])) {
                return ['errores' => ['password' => 'Esa cédula ya está registrada. La contraseña ingresada no coincide.']];
            }
            $idUsuario = $usuarioExistente->getIdUsuario();
        } else {
            // Cuenta nueva: se exigen los datos mínimos para crearla.
            $v2 = new Validator($datos);
            $v2->requerido('nombre', 'Ingresá tu nombre completo.')
               ->requerido('correo', 'Ingresá tu correo electrónico.')
               ->email('correo', 'El correo electrónico no es válido.');
            if ($v2->tieneErrores()) {
                return ['errores' => $v2->getErrores()];
            }
            if ($this->usuarios->existeEmail(trim($datos['correo']))) {
                return ['errores' => ['correo' => 'Ya existe una cuenta registrada con ese correo electrónico.']];
            }

            $idUsuario = $this->usuarios->crear([
                'cedula'       => $cedula,
                'nom_usuario'  => trim($datos['nombre']),
                'email'        => trim($datos['correo']),
                'pass_usuario' => Usuario::encriptarPassword($datos['password']),
                'pais'         => trim($datos['pais_ciudad']),
                'id_nivel'     => NivelAcceso::ALUMNO,
            ]);
            if ($idUsuario === 0) {
                return ['errores' => ['general' => 'No se pudo crear la cuenta. Intentá nuevamente.']];
            }
            $this->alumnos->crear($idUsuario, [
                'es_menor'              => $esMenor,
                'autorizacion_adulto'   => $autorizacionAdulto,
                'consentimiento_imagen' => $consentimientoImagen,
            ]);
        }

        $rutaComprobante = null;
        if ($archivoComprobante !== null && ($archivoComprobante['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $resultado = $this->guardarComprobante($archivoComprobante);
            if (isset($resultado['error'])) {
                return ['errores' => ['comprobante' => $resultado['error']]];
            }
            $rutaComprobante = $resultado['ruta'];
        }

        $formulario = new FormularioInteres(
            $datos['curso'],
            'Preinscripción enviada desde el formulario web.',
            !empty($datos['mensaje']) ? trim($datos['mensaje']) : null,
            $idUsuario,
            true
        );

        $idFormulario = $this->formularios->crear($formulario->toArray() + ['archivo_comprobante' => $rutaComprobante]);
        if ($idFormulario === 0) {
            return ['errores' => ['general' => 'No se pudo registrar la preinscripción. Intentá nuevamente.']];
        }

        $this->historiales->crear([
            'descrip_historial' => 'Preinscripción enviada para el curso "' . $datos['curso'] . '".',
            'id_usuario'        => $idUsuario,
        ]);

        $usuarioFinal = $this->usuarios->buscarPorId($idUsuario);
        Session::iniciarSesionUsuario($usuarioFinal->toArray() + ['rol' => 'alumno']);

        return ['formulario' => ['id_formulario' => $idFormulario] + $formulario->toArray()];
    }

    /**
     * Valida y mueve el comprobante a backend/storage/comprobantes/ (fuera
     * del webroot). Devuelve ['ruta' => ...] o ['error' => 'mensaje'].
     */
    private function guardarComprobante(array $archivo): array
    {
        if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['error' => 'Hubo un problema al subir el archivo. Intentá nuevamente.'];
        }
        if (($archivo['size'] ?? 0) > self::TAMANO_MAXIMO_COMPROBANTE) {
            return ['error' => 'El comprobante no puede pesar más de 5 MB.'];
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONES_COMPROBANTE, true)) {
            return ['error' => 'El comprobante debe ser un archivo PDF, JPG o PNG.'];
        }

        $carpetaDestino = __DIR__ . '/../../storage/comprobantes';
        if (!is_dir($carpetaDestino) && !mkdir($carpetaDestino, 0750, true) && !is_dir($carpetaDestino)) {
            return ['error' => 'No se pudo guardar el comprobante. Intentá nuevamente.'];
        }

        $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extension;
        $rutaAbsoluta = $carpetaDestino . '/' . $nombreArchivo;

        if (!is_uploaded_file($archivo['tmp_name']) || !move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
            return ['error' => 'No se pudo guardar el comprobante. Intentá nuevamente.'];
        }

        return ['ruta' => 'comprobantes/' . $nombreArchivo];
    }

    public function listarParaAdmin(): array
    {
        return $this->formularios->listarConDatosUsuario();
    }

    public function eliminar(int $idFormulario): bool
    {
        return $this->formularios->eliminar($idFormulario);
    }
}

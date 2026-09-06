<?php

namespace Backend\Services;

use Backend\Core\Session;
use Backend\Core\Validator;
use Backend\Models\Usuario;
use Backend\Models\NivelAcceso;
use Backend\Repositories\AlumnoRepository;
use Backend\Repositories\HistorialRepository;
use Backend\Repositories\UsuarioRepository;

/**
 * Casos de uso de autenticación: registro, login y logout.
 *
 * Implementa la "Verificación dual": el JavaScript de Registrarse.html /
 * Login-in.html ya validó lo mismo del lado del cliente, pero acá se repite
 * TODO de nuevo porque una petición puede llegar sin pasar por ese
 * formulario (o con JavaScript deshabilitado / manipulado).
 */
class AuthService
{
    private UsuarioRepository $usuarios;
    private AlumnoRepository $alumnos;
    private HistorialRepository $historiales;
    private array $config;

    public function __construct()
    {
        $this->usuarios = new UsuarioRepository();
        $this->alumnos = new AlumnoRepository();
        $this->historiales = new HistorialRepository();
        $this->config = require __DIR__ . '/../config/config.php';
    }

    /**
     * @param array $datos Campos crudos del formulario de Registrarse.html.
     * @return array{errores?: array<string,string>, usuario?: array}
     */
    public function registrar(array $datos): array
    {
        $v = new Validator($datos);
        $v->requerido('nombre', 'Ingresá tu nombre.')
          ->requerido('apellido', 'Ingresá tu apellido.')
          ->requerido('cedula', 'Ingresá tu cédula.')
          ->numerico('cedula', 'La cédula sólo puede contener números.')
          ->requerido('correo', 'Ingresá tu correo electrónico.')
          ->email('correo', 'El correo electrónico no es válido.')
          ->requerido('password', 'Creá una contraseña.')
          ->minLength('password', 6, 'La contraseña debe tener al menos 6 caracteres.')
          ->requerido('confirmar_password', 'Confirmá tu contraseña.')
          ->coincideCon('confirmar_password', 'password', 'Las contraseñas no coinciden.')
          ->requerido('fecha_nacimiento', 'Ingresá tu fecha de nacimiento.')
          ->fecha('fecha_nacimiento', 'La fecha de nacimiento no es válida.')
          ->checkboxAceptado('terminos', 'Tenés que aceptar los Términos y Condiciones para continuar.');

        if ($v->tieneErrores()) {
            return ['errores' => $v->getErrores()];
        }

        $cedula = (int) $datos['cedula'];
        $correo = trim($datos['correo']);

        if ($this->usuarios->existeCedula($cedula)) {
            return ['errores' => ['cedula' => 'Ya existe una cuenta registrada con esa cédula.']];
        }
        if ($this->usuarios->existeEmail($correo)) {
            return ['errores' => ['correo' => 'Ya existe una cuenta registrada con ese correo electrónico.']];
        }

        $edad = $this->calcularEdad($datos['fecha_nacimiento']);
        if ($edad === null || $edad < 0 || $edad > 120) {
            return ['errores' => ['fecha_nacimiento' => 'Ingresá una fecha de nacimiento válida.']];
        }

        $idUsuario = $this->usuarios->crear([
            'cedula'       => $cedula,
            'nom_usuario'  => trim($datos['nombre']) . ' ' . trim($datos['apellido']),
            'email'        => $correo,
            'pass_usuario' => Usuario::encriptarPassword($datos['password']),
            'pais'         => $datos['pais'] ?? $this->config['pais_por_defecto'],
            'id_nivel'     => NivelAcceso::ALUMNO,
        ]);

        if ($idUsuario === 0) {
            return ['errores' => ['general' => 'No se pudo completar el registro. Intentá nuevamente.']];
        }

        $this->alumnos->crear($idUsuario, [
            'curso_actual' => null,
            'es_menor'     => $edad < 18,
            // Un menor recién registrado todavía no presentó autorización: se
            // completa al enviar el Formulario de Interés (RFE-03).
            'autorizacion_adulto'   => false,
            'consentimiento_imagen' => false,
        ]);

        $this->historiales->crear([
            'descrip_historial' => 'Registro de nuevo usuario (Alumno) desde el formulario web.',
            'id_usuario'        => $idUsuario,
        ]);

        $usuario = $this->usuarios->buscarPorId($idUsuario);
        Session::iniciarSesionUsuario($usuario->toArray() + ['rol' => 'alumno']);

        return ['usuario' => $usuario->toArray() + ['rol' => 'alumno']];
    }

    /**
     * @param array $datos ['cedula' => ..., 'password' => ...]
     * @return array{errores?: array<string,string>, usuario?: array}
     */
    public function login(array $datos): array
    {
        $v = new Validator($datos);
        $v->requerido('cedula', 'Ingresá tu cédula.')
          ->numerico('cedula', 'La cédula sólo puede contener números.')
          ->requerido('password', 'Ingresá tu contraseña.');

        if ($v->tieneErrores()) {
            return ['errores' => $v->getErrores()];
        }

        $usuario = $this->usuarios->buscarPorCedula((int) $datos['cedula']);
        if ($usuario === null) {
            return ['errores' => ['cedula' => 'No existe una cuenta registrada con esa cédula.']];
        }

        if (!$usuario->autenticar($datos['password'])) {
            $this->historiales->crear([
                'descrip_historial' => 'Intento de inicio de sesión fallido (contraseña incorrecta).',
                'id_usuario'        => $usuario->getIdUsuario(),
            ]);
            return ['errores' => ['password' => 'La contraseña es incorrecta.']];
        }

        $rol = $this->usuarios->obtenerRol($usuario->getIdUsuario()) ?? 'alumno';
        Session::iniciarSesionUsuario($usuario->toArray() + ['rol' => $rol]);

        $this->historiales->crear([
            'descrip_historial' => 'Inicio de sesión exitoso.',
            'id_usuario'        => $usuario->getIdUsuario(),
        ]);

        return ['usuario' => $usuario->toArray() + [
            'rol'          => $rol,
            'redireccion'  => $rol === 'administrador' ? 'admin/panel.html' : 'index.html',
        ]];
    }

    public function logout(): void
    {
        $usuario = Session::usuarioActual();
        if ($usuario !== null) {
            $this->historiales->crear([
                'descrip_historial' => 'Cierre de sesión.',
                'id_usuario'        => $usuario['id_usuario'],
            ]);
        }
        Session::cerrarSesion();
    }

    private function calcularEdad(string $fechaNacimiento): ?int
    {
        try {
            $nacimiento = new \DateTime($fechaNacimiento);
            $hoy = new \DateTime('today');
            if ($nacimiento > $hoy) {
                return null;
            }
            return (int) $nacimiento->diff($hoy)->y;
        } catch (\Exception) {
            return null;
        }
    }
}

<?php

namespace Backend\Services;

use Backend\Repositories\AdministradorRepository;
use Backend\Repositories\AlumnoRepository;

/**
 * Listados de usuarios para la vista "Ver Usuarios" del panel de
 * administración (admin/admin.js espera exactamente estos nombres de
 * campo: id_usuario, cedula, nombre_completo, email, pais, fecha_registro
 * y curso_actual/cargo según la pestaña).
 */
class UsuarioService
{
    private AlumnoRepository $alumnos;
    private AdministradorRepository $administradores;

    public function __construct()
    {
        $this->alumnos = new AlumnoRepository();
        $this->administradores = new AdministradorRepository();
    }

    public function listarAlumnos(): array
    {
        return array_map(function ($alumno) {
            $datos = $alumno->toArray();
            return [
                'id_usuario'      => $datos['id_usuario'],
                'cedula'          => $datos['cedula'],
                'nombre_completo' => $datos['nom_usuario'],
                'email'           => $datos['email'],
                'pais'            => $datos['pais'],
                'fecha_registro'  => $datos['fecha_registro'],
                'curso_actual'    => $datos['curso_actual'],
            ];
        }, $this->alumnos->listar());
    }

    public function listarAdministradores(): array
    {
        return array_map(function ($admin) {
            $datos = $admin->toArray();
            return [
                'id_usuario'      => $datos['id_usuario'],
                'cedula'          => $datos['cedula'],
                'nombre_completo' => $datos['nom_usuario'],
                'email'           => $datos['email'],
                'pais'            => $datos['pais'],
                'fecha_registro'  => $datos['fecha_registro'],
                'cargo'           => $datos['cargo'],
            ];
        }, $this->administradores->listar());
    }
}

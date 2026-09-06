<?php

/**
 * Autoloader PSR-4 mínimo (sin depender de Composer) para el namespace
 * `Backend\`, que mapea a `backend/src/`.
 *
 *   Backend\Config\Database        -> backend/src/config/Database.php
 *   Backend\Models\Usuario         -> backend/src/models/Usuario.php
 *   Backend\Repositories\UsuarioRepository -> backend/src/repositories/UsuarioRepository.php
 *   Backend\Services\AuthService   -> backend/src/services/AuthService.php
 *
 * Nota: las carpetas físicas están en minúscula (config, models,
 * repositories, services, core) para respetar la convención del proyecto,
 * mientras que el namespace usa PascalCase (más idiomático en PHP). Por eso
 * el mapeo busca el archivo probando el segmento tal cual y en minúscula.
 */

spl_autoload_register(function (string $clase): void {
    $prefijo = 'Backend\\';
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    $resto = substr($clase, strlen($prefijo));       // Ej: Repositories\UsuarioRepository
    $partes = explode('\\', $resto);
    $nombreClase = array_pop($partes);                // UsuarioRepository
    $carpeta = strtolower(implode('/', $partes));     // repositories

    $ruta = __DIR__ . '/src/' . $carpeta . '/' . $nombreClase . '.php';

    if (is_file($ruta)) {
        require_once $ruta;
    }
});

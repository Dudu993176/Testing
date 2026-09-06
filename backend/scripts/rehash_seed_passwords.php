<?php

/**
 * Script de línea de comandos, se ejecuta UNA sola vez después de importar
 * database/02_Datos_prueba.sql:
 *
 *   php backend/scripts/rehash_seed_passwords.php
 *
 * 02_Datos_prueba.sql carga contraseñas en texto plano ('abcd1234') sólo
 * para tener datos de ejemplo rápido. El backend real
 * (Backend\Models\Usuario::autenticar / encriptarPassword) trabaja siempre
 * con hashes bcrypt (password_hash), así que este script convierte esas
 * contraseñas de ejemplo sin tener que volver a registrarse a mano.
 *
 * Es idempotente: si una contraseña ya es un hash bcrypt, la deja igual.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script sólo puede ejecutarse desde la línea de comandos.');
}

require __DIR__ . '/../autoload.php';

use Backend\Config\Database;
use Backend\Models\Usuario;

$pdo = Database::getConexion();

$filas = $pdo->query('SELECT id_usuario, pass_usuario FROM Usuarios')->fetchAll();

$actualizados = 0;
$stmtUpdate = $pdo->prepare('UPDATE Usuarios SET pass_usuario = :hash WHERE id_usuario = :id');

foreach ($filas as $fila) {
    $passActual = $fila['pass_usuario'];
    $yaEsHash = str_starts_with($passActual, '$2y$') || str_starts_with($passActual, '$argon2');

    if ($yaEsHash) {
        continue;
    }

    $stmtUpdate->execute([
        ':hash' => Usuario::encriptarPassword($passActual),
        ':id'   => $fila['id_usuario'],
    ]);
    $actualizados++;
}

echo "Listo. Contraseñas de ejemplo convertidas a hash bcrypt: {$actualizados}." . PHP_EOL;
echo 'Las contraseñas siguen siendo las mismas de 02_Datos_prueba.sql (ej: "abcd1234"), sólo cambia cómo se guardan.' . PHP_EOL;

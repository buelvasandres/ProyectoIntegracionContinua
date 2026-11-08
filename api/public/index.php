<?php

require_once __DIR__ . '/../src/Database.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Función auxiliar para responder en JSON y terminar.
function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

// GET /health -> verifica que la API está viva (no usa la base de datos).
if ($method === 'GET' && $path === '/health') {
    responder(200, [
        'status' => 'ok',
        'message' => 'API funcionando correctamente'
    ]);
}

// /usuarios -> estas rutas sí usan la base de datos (contenedor "db"),
// sobre la tabla "usuarios" que crea el script db/init/01-esquema.sql.
if ($path === '/usuarios') {
    try {
        $db = Database::connect();
    } catch (PDOException $e) {
        responder(503, [
            'status' => 'error',
            'message' => 'No se pudo conectar a la base de datos'
        ]);
    }

    // GET /usuarios -> lista los usuarios.
    if ($method === 'GET') {
        $usuarios = $db->query('SELECT id, nombre, apellido, edad, sexo FROM usuarios ORDER BY id')->fetchAll();
        responder(200, ['status' => 'ok', 'data' => $usuarios]);
    }

    // POST /usuarios -> crea un usuario.
    // Body JSON: {"nombre": "...", "apellido": "...", "edad": 25, "sexo": "M"}
    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $nombre = trim((string) ($body['nombre'] ?? ''));
        $apellido = trim((string) ($body['apellido'] ?? ''));
        $sexo = trim((string) ($body['sexo'] ?? ''));
        $edad = filter_var($body['edad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        // Validaciones según las columnas de la tabla (todas NOT NULL).
        if ($nombre === '' || $apellido === '' || $sexo === '' || $edad === false) {
            responder(400, [
                'status' => 'error',
                'message' => 'Campos obligatorios: nombre, apellido, edad (entero >= 0) y sexo'
            ]);
        }
        if (mb_strlen($nombre) > 100 || mb_strlen($apellido) > 100 || mb_strlen($sexo) > 10) {
            responder(400, [
                'status' => 'error',
                'message' => 'nombre y apellido admiten hasta 100 caracteres; sexo hasta 10'
            ]);
        }

        $stmt = $db->prepare(
            'INSERT INTO usuarios (nombre, apellido, edad, sexo)
             VALUES (:nombre, :apellido, :edad, :sexo)
             RETURNING id, nombre, apellido, edad, sexo'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'edad' => $edad,
            'sexo' => $sexo,
        ]);
        responder(201, ['status' => 'ok', 'data' => $stmt->fetch()]);
    }

    responder(405, ['status' => 'error', 'message' => 'Método no permitido']);
}

responder(404, [
    'status' => 'error',
    'message' => 'Endpoint no encontrado'
]);

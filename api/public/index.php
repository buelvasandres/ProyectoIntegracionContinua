<?php

/**
 * Punto de entrada HTTP de la API.
 *
 * Expone GET /health para comprobar el servicio y GET/POST /usuarios para
 * consultar y crear registros. Las respuestas usan JSON con la forma
 * {"status": "ok|error", ...}; los errores de conexión devuelven HTTP 503.
 */
require_once __DIR__ . '/../src/Database.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Envía una respuesta JSON con el código HTTP indicado y termina la petición.
 *
 * @param int   $codigo Código de estado HTTP.
 * @param array $datos Datos que se serializan como JSON.
 */
function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

// GET /health -> verifica que la API está viva sin conectarse a la base de datos.
if ($method === 'GET' && $path === '/health') {
    responder(200, [
        'status' => 'ok',
        'message' => 'API funcionando correctamente'
    ]);
}

// /usuarios -> estas rutas usan el contenedor "db" y la tabla "usuarios".
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
        $usuarios = $db->query(
            'SELECT id, nombre, apellido, edad, sexo
             FROM usuarios
             ORDER BY id'
        )->fetchAll();
        responder(200, ['status' => 'ok', 'data' => $usuarios]);
    }

    // POST /usuarios -> crea un usuario y devuelve el registro creado.
    // Body JSON: {"nombre": "...", "apellido": "...", "edad": 25, "sexo": "M"}.
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

<?php

/**
 * Conexión a PostgreSQL.
 *
 * Los datos de conexión NO están escritos en el código: se leen de variables
 * de entorno que Docker Compose le entrega al contenedor desde el archivo .env.
 * Usamos las MISMAS variables que el contenedor de PostgreSQL (POSTGRES_USER,
 * POSTGRES_PASSWORD, POSTGRES_DB), así ambos servicios comparten un solo .env.
 *
 * DB_HOST es el nombre del servicio de la base de datos en docker-compose ("db"):
 * dentro de la red de Docker, ese nombre funciona como dirección.
 */
class Database
{
    public static function connect(): PDO
    {
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '5432';
        $name = getenv('POSTGRES_DB') ?: 'mibase';
        $user = getenv('POSTGRES_USER') ?: 'usuario';
        $pass = getenv('POSTGRES_PASSWORD') ?: '';

        $dsn = "pgsql:host={$host};port={$port};dbname={$name}";

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}

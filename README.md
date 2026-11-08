# Proyecto Integración Continua
# Entrega 1: Contenedores con Docker

Este proyecto levanta dos contenedores que se comunican entre sí:

- **api**: aplicación web en PHP 8.2 (servidor integrado de PHP) que responde en el puerto 5000.
- **db**: base de datos PostgreSQL donde la API guarda y consulta información.

Ambos se ejecutan con Docker Compose dentro de una red interna. Solo la API es accesible desde el equipo; la base de datos queda protegida dentro de la red.

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (incluye Docker Compose)
- Git

## Cómo ejecutarlo

1. Clonar el repositorio:

   ```bash
   git clone <url-del-repositorio>
   cd <nombre-del-repositorio>
   ```

2. Crear el archivo de variables de entorno a partir del ejemplo:

   ```bash
   cp .env.example .env
   ```

3. Construir y levantar los contenedores:

   ```bash
   docker compose up --build
   ```

4. Abrir en el navegador `http://localhost:5000/health`. Si responde, todo está funcionando.

Para detener los contenedores: `docker compose down`

## Endpoints

| Método | Ruta      | Descripción                          |
| ------ | --------- | ------------------------------------ |
| GET    | `/health` | Verifica que la API está activa      |
| GET    | `/usuarios` | Lista los usuarios de la base      |
| POST   | `/usuarios` | Crea un usuario nuevo (JSON: nombre, apellido, edad, sexo) |

## Estructura del proyecto

```
├── api/                 # Código y Dockerfile de la API
├── db/                  # Script inicial de la base de datos
├── docs/evidencias/     # Capturas de las pruebas
├── docker-compose.yml   # Define y conecta los dos contenedores
├── .env.example         # Variables de ejemplo (no subir el .env real)
└── README.md
```

## Integrantes

| Nombre       | Rol                          |
| ------------ | ---------------------------- |
| Integrante 1 | Gestión del repositorio      |
| Integrante 2 | Contenedor de la API         |
| Integrante 3 | Contenedor de base de datos  |
| Integrante 4 | Orquestación y pruebas       |

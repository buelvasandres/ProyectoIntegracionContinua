# Imagen base: PHP 8.2 en modo línea de comandos (la misma que ya descargamos).
FROM php:8.2-cli

# Instalamos la librería de PostgreSQL del sistema (libpq-dev) y compilamos
# la extensión pdo_pgsql para que PHP pueda conectarse a la base de datos.
# Al final borramos la caché de apt para que la imagen pese menos.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Carpeta de trabajo dentro del contenedor.
WORKDIR /app

# Copiamos el código de la API dentro de la imagen.
COPY src/ ./src/
COPY public/ ./public/

# Documenta que la API escucha en el puerto 5000.
EXPOSE 5000

# Al arrancar el contenedor se inicia el servidor web integrado de PHP.
# 0.0.0.0 = acepta conexiones desde fuera del contenedor.
# -t public = solo la carpeta public/ es accesible desde el navegador.
CMD ["php", "-S", "0.0.0.0:5000", "-t", "public"]

# Usar la imagen oficial de PHP
FROM php:8.2-cli

# Configurar directorio de trabajo
WORKDIR /app

# Copiar los archivos del proyecto
COPY . /app

# Exponer el puerto
EXPOSE 8080

# Comando para levantar el servidor
CMD ["php", "-S", "0.0.0.0:8080"]
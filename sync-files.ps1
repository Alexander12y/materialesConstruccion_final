# Script para sincronizar archivos HTML al contenedor - Materiales de Construcción
# Ejecutar cuando agregues nuevos archivos PHP

Write-Host "Sincronizando archivos HTML al contenedor..." -ForegroundColor Green

# Verificar que el contenedor esté ejecutándose
$containerStatus = docker ps --filter "name=materialesconstruccion_final-web-1" --format "{{.Status}}"
if (-not $containerStatus) {
    Write-Host "Error: El contenedor web no está ejecutándose" -ForegroundColor Red
    Write-Host "Ejecuta: docker-compose up -d" -ForegroundColor Yellow
    exit 1
}

# Copiar todos los archivos de la carpeta html
Write-Host "Copiando archivos HTML..."
docker cp html/. materialesconstruccion_final-web-1:/var/www/html/

# Copiar las imágenes de productos
Write-Host "Copiando imágenes de productos..."
docker cp public/. materialesconstruccion_final-web-1:/var/www/html/public/

# Establecer permisos correctos
Write-Host "Estableciendo permisos..."
docker exec materialesconstruccion_final-web-1 chown -R www-data:www-data /var/www/html/

# Mostrar archivos en el contenedor
Write-Host "Archivos en el contenedor:" -ForegroundColor Cyan
docker exec materialesconstruccion_final-web-1 ls -la /var/www/html/

Write-Host "Sincronizacion completada!" -ForegroundColor Green

<?php declare(strict_types=1);
/**
 * Punto de Entrada Principal de Zbin
 *
 * Este archivo es el unico punto de acceso publico a la aplicacion.
 * Todas las solicitudes HTTP se dirigen aqui (via .htaccess o configuracion del servidor).
 *
 * Proceso de inicio:
 * 1. Define PATH: ruta base donde estan los archivos PHP y datos
 *    (cambiar si los archivos estan fuera del document root del servidor web)
 * 2. Define PUBLIC_PATH: directorio publico accesible via web
 * 3. Carga el autoloader de Composer para resolver las clases automaticamente
 * 4. Instancia el controlador principal que procesa la solicitud
 *
 * 
 */

// Cambiar PATH si los archivos PHP y datos estan fuera del document root del servidor web
define('PATH', '');

define('PUBLIC_PATH', __DIR__);
require PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
new Zbin\Controller;

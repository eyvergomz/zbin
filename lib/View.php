<?php declare(strict_types=1);
/**
 * Motor de Vistas de Zbin
 *
 * Este archivo implementa un sistema de plantillas simple pero efectivo.
 * Permite asignar variables y renderizar archivos PHP como plantillas HTML.
 *
 * El flujo es:
 * 1. El controlador crea una instancia de View
 * 2. Asigna variables con assign() (ej: VERSION, ERROR, etc.)
 * 3. Llama a draw() con el nombre de la plantilla
 * 4. Las variables quedan disponibles como variables locales en la plantilla
 *
 * 
 */

namespace Zbin;

use Exception;

/**
 * Vista
 *
 * Renderiza las plantillas HTML con las variables asignadas.
 */
class View
{
    /**
     * Variables disponibles dentro de la plantilla
     * Se extraen como variables locales al momento de renderizar.
     *
     * @var array
     */
    private $_variables = array();

    /**
     * Asigna una variable para usar dentro de la plantilla
     *
     * Ejemplo: $view->assign('VERSION', '1.0.0')
     * Luego en la plantilla se puede usar $VERSION
     *
     * @param string $name  Nombre de la variable (se usara como $nombre en la plantilla)
     * @param mixed  $value Valor de la variable
     */
    public function assign($name, $value)
    {
        $this->_variables[$name] = $value;
    }

    /**
     * Renderiza una plantilla PHP
     *
     * Busca el archivo de plantilla en el directorio tpl/, extrae las variables
     * asignadas como variables locales y lo incluye para su ejecucion.
     *
     * Las plantillas "bootstrap-*" (variantes) usan el mismo archivo "bootstrap.php"
     * porque comparten la misma estructura HTML base.
     *
     * @param string $template Nombre de la plantilla (sin extension .php)
     * @throws Exception Si la plantilla no existe o no se encuentra en el directorio
     */
    public function draw($template)
    {
        $dir  = PATH . 'tpl' . DIRECTORY_SEPARATOR;
        // Las variantes de bootstrap (bootstrap-dark, bootstrap-compact, etc.)
        // usan el mismo archivo base "bootstrap.php"
        $file = substr($template, 0, 10) === 'bootstrap-' ? 'bootstrap' : $template;
        $path = $dir . $file . '.php';
        if (!is_file($path)) {
            throw new Exception('Template ' . $template . ' not found in file ' . $path . '!', 80);
        }
        // Verificacion de seguridad: asegurar que el archivo este dentro del directorio de plantillas
        if (!in_array($path, glob($dir . '*.php', GLOB_NOSORT | GLOB_ERR), true)) {
            throw new Exception('Template ' . $file . '.php not found in ' . $dir . '!', 81);
        }
        // extract() convierte las claves del array en variables locales
        // ej: $_variables['VERSION'] => $VERSION
        extract($this->_variables);
        include $path;
    }

    /**
     * Genera una etiqueta <script> con hash SRI para un archivo JavaScript
     *
     * SRI (Subresource Integrity) permite al navegador verificar que el archivo
     * JS no fue modificado, comparando su hash con el esperado.
     * Si el archivo no tiene version en su nombre, se agrega un cache buster
     * basado en la version de la aplicacion.
     *
     * @param string $file       Ruta del archivo JS
     * @param string $attributes Atributos adicionales para la etiqueta script
     */
    private function _scriptTag($file, $attributes = '')
    {
        // Obtener el hash SRI si esta disponible para este archivo
        $sri = array_key_exists($file, $this->_variables['SRI']) ?
            ' integrity="' . $this->_variables['SRI'][$file] . '"' : '';
        // Si el archivo no tiene numero de version en su nombre, agregar cache buster
        $cacheBuster = (bool) preg_match('#[0-9]\.js$#', (string) $file) ?
            '' : '?' . rawurlencode($this->_variables['VERSION']);
        echo '<script ', $attributes,
        ' type="text/javascript" data-cfasync="false" src="', $file,
        $cacheBuster, '"', $sri, ' crossorigin="anonymous"></script>', PHP_EOL;
    }
}

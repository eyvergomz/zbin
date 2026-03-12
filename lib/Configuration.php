<?php declare(strict_types=1);
/**
 * Gestor de Configuracion de Zbin
 *
 * Este archivo se encarga de leer, validar y proporcionar acceso a toda la
 * configuracion de la aplicacion. Lee el archivo conf.php (formato INI)
 * y garantiza que siempre existan valores por defecto para cada opcion.
 *
 * La configuracion se organiza en secciones:
 * - main: opciones generales (nombre, plantilla, funcionalidades habilitadas)
 * - expire: opcion de expiracion por defecto
 * - expire_options: periodos de expiracion disponibles (en segundos)
 * - formatter_options: formatos de texto disponibles
 * - traffic: control de trafico anti-spam
 * - purge: configuracion de limpieza automatica de pastes expirados
 * - model / model_options: backend de almacenamiento (Filesystem, Database, etc.)
 * - sri: hashes de integridad de subrecursos para archivos JS
 *
 * 
 */

namespace Zbin;

use Exception;
use Zbin\Exception\TranslatedException;

/**
 * Configuracion
 *
 * Analiza el archivo de configuracion y asegura que los valores
 * por defecto esten presentes para todas las opciones necesarias.
 */
class Configuration
{
    /**
     * Configuracion procesada y lista para usar
     * Estructura: array asociativo con secciones como claves
     *
     * @var array
     */
    protected $_configuration;

    /**
     * Valores de configuracion por defecto
     *
     * Estos valores se usan cuando no se especifican en el archivo conf.php.
     * Tambien sirven como referencia de todas las opciones disponibles.
     *
     * @var array
     */
    private static $_defaults = array(
        'main' => array(
            'name'                     => 'Zbin',
            'basepath'                 => '',
            'discussion'               => true,
            'opendiscussion'           => false,
            'discussiondatedisplay'    => true,
            'password'                 => true,
            'fileupload'               => false,
            'burnafterreadingselected' => false,
            'defaultformatter'         => 'plaintext',
            'syntaxhighlightingtheme'  => '',
            'sizelimit'                => 10485760, // 10 MB en bytes
            'templateselection'        => false,
            'template'                 => 'bootstrap5',
            'availabletemplates'       => array(
                'bootstrap5',
                'bootstrap',
                'bootstrap-page',
                'bootstrap-dark',
                'bootstrap-dark-page',
                'bootstrap-compact',
                'bootstrap-compact-page',
            ),
            'info'                     => 'More information on the <a href=\' page</a>.',
            'notice'                   => '',
            'languageselection'        => false,
            'languagedefault'          => '',
            'urlshortener'             => '',
            'shortenbydefault'         => false,
            'qrcode'                   => true,
            'email'                    => true,
            'icon'                     => 'jdenticon',
            'cspheader'                => 'default-src \'none\'; base-uri \'self\'; form-action \'none\'; manifest-src \'self\'; connect-src * blob:; script-src \'self\' \'wasm-unsafe-eval\'; style-src \'self\'; font-src \'self\'; frame-ancestors \'none\'; frame-src blob:; img-src \'self\' data: blob:; media-src blob:; object-src blob:; sandbox allow-same-origin allow-scripts allow-forms allow-modals allow-downloads',
            'httpwarning'              => true,
            'compression'              => 'zlib',
        ),
        'expire' => array(
            'default' => '1week',
        ),
        // Opciones de expiracion: clave = etiqueta, valor = segundos (0 = nunca)
        'expire_options' => array(
            '5min'   => 300,
            '10min'  => 600,
            '1hour'  => 3600,
            '1day'   => 86400,
            '1week'  => 604800,
            '1month' => 2592000,
            '1year'  => 31536000,
            'never'  => 0,
        ),
        // Formatos de texto disponibles para los pastes
        'formatter_options' => array(
            'plaintext'          => 'Plain Text',
            'syntaxhighlighting' => 'Source Code',
            'markdown'           => 'Markdown',
        ),
        // Control de trafico para prevenir abuso
        'traffic' => array(
            'limit'     => 10,    // segundos minimos entre creaciones
            'header'    => '',    // cabecera HTTP para identificar IP (ej: X-Forwarded-For)
            'exempted'  => '',    // IPs exentas del limite
            'creators'  => '',    // IPs que pueden crear (si se restringe)
        ),
        // Configuracion de purga automatica de pastes expirados
        'purge' => array(
            'limit'     => 300,   // segundos entre purgas
            'batchsize' => 10,    // pastes a revisar por purga
        ),
        // Backend de almacenamiento (Filesystem por defecto)
        'model' => array(
            'class' => 'Filesystem',
        ),
        'model_options' => array(
            'dir' => 'data',
        ),
        // Configuracion del acortador YOURLS
        'yourls' => array(
            'signature' => '',
            'apiurl'    => '',
        ),
        // Configuracion del acortador Shlink
        'shlink' => array(
            'apikey'    => '',
            'apiurl'    => '',
        ),
        // Hashes SRI (Subresource Integrity) para verificar la integridad de los archivos JS
        'sri' => array(
            'js/base-x-5.0.1.js'     => 'sha512-FmhlnjIxQyxkkxQmzf0l6IRGsGbgyCdgqPxypFsEtHMF1naRqaLLo6mcyN5rEaT16nKx1PeJ4g7+07D6gnk/Tg==',
            'js/bootstrap-3.4.1.js'  => 'sha512-oBTprMeNEKCnqfuqKd6sbvFzmFQtlXS3e0C/RGFV0hD6QzhHV+ODfaQbAlmY6/q0ubbwlAM/nCJjkrgA3waLzg==',
            'js/bootstrap-5.3.8.js'  => 'sha512-BkZvJ5rZ3zbDCod5seWHpRGg+PRd6ZgE8Nua/OMtcxqm8Wtg0PqwhUUXK5bqvl3oclMt5O+3zjRVX0L+L2j7fA==',
            'js/dark-mode-switch.js' => 'sha512-BhY7dNU14aDN5L+muoUmA66x0CkYUWkQT0nxhKBLP/o2d7jE025+dvWJa4OiYffBGEFgmhrD/Sp+QMkxGMTz2g==',
            'js/jquery-3.7.1.js'     => 'sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==',
            'js/kjua-0.10.0.js'      => 'sha512-BYj4xggowR7QD150VLSTRlzH62YPfhpIM+b/1EUEr7RQpdWAGKulxWnOvjFx1FUlba4m6ihpNYuQab51H6XlYg==',
            'js/legacy.js'           => 'sha512-3sX1UFJ55u6OIiHQekmuMGqcASypxiUgIzP1FEo2V5uVGGZT9MwJmLam6AvJMIEgF0QVsDeBY/c+2XT8HxOSiw==',
            'js/prettify.js'         => 'sha512-puO0Ogy++IoA2Pb9IjSxV1n4+kQkKXYAEUtVzfZpQepyDPyXk8hokiYDS7ybMogYlyyEIwMLpZqVhCkARQWLMg==',
            'js/zbin.js'       => 'sha512-FzrZzhKE5iM3DuIKRCC2Blvru8eeDp5QU+WuN3dGxKM97S62iNettgvnuVXIh9EThQkRc2fzJFo5lJh/FbWX+g==',
            'js/purify-3.3.2.js'     => 'sha512-I6igPVpf3xNghG92mujwqB6Zi3LpUTsni4bRuLnMThEGH6BDbsumv7373+AXHzA4OUlxGsym8ZxKFHy4xjYvkQ==',
            'js/showdown-2.1.0.js'   => 'sha512-WYXZgkTR0u/Y9SVIA4nTTOih0kXMEd8RRV6MLFdL6YU8ymhR528NLlYQt1nlJQbYz4EW+ZsS0fx1awhiQJme1Q==',
            'js/zlib-1.3.1-2.js'     => 'sha512-4gT+v+BkBqdVBbKOO4qKGOAzuay+v1FmOLksS+bMgQ08Oo4xEb3X48Xq1Kv2b4HtiCQA7xq9dFRzxal7jmQI7w==',
        ),
    );

    /**
     * Constructor - Lee y procesa el archivo de configuracion
     *
     * Algoritmo:
     * 1. Busca el archivo conf.php en las rutas configuradas (CONFIG_PATH o cfg/)
     * 2. Verifica que las secciones obligatorias existan
     * 3. Recorre todas las secciones por defecto:
     *    - Si la seccion no existe en el archivo, usa los valores por defecto completos
     *    - Si es model_options, ajusta los valores segun el backend seleccionado
     *    - Para secciones "*_options", reemplaza completamente con los valores del archivo
     *    - Para otras secciones, mezcla valores del archivo con los por defecto
     * 4. Convierte los tipos de datos correctamente (bool, int, string, array)
     * 5. Valida la clave de expiracion por defecto
     * 6. Asegura que basepath termine en "/"
     *
     * @throws TranslatedException Si faltan secciones obligatorias en la configuracion
     */
    public function __construct()
    {
        $basePaths  = array();
        $config     = array();
        // Permitir especificar la ruta de configuracion via variable de entorno
        $configPath = getenv('CONFIG_PATH');
        if ($configPath !== false && !empty($configPath)) {
            $basePaths[] = $configPath;
        }
        $basePaths[] = PATH . 'cfg';
        // Buscar el archivo de configuracion en las rutas disponibles
        foreach ($basePaths as $basePath) {
            $configFile = $basePath . DIRECTORY_SEPARATOR . 'conf.php';
            if (is_readable($configFile)) {
                $config = parse_ini_file($configFile, true);
                // Verificar que las secciones obligatorias existan
                foreach (array('main', 'model', 'model_options') as $section) {
                    if (!array_key_exists($section, $config)) {
                        $name = $config['main']['name'] ?? self::getDefaults()['main']['name'];
                        throw new TranslatedException(array('%s requires configuration section [%s] to be present in configuration file.', I18n::_($name), $section), 2);
                    }
                }
                break;
            }
        }

        $opts = '_options';
        // Recorrer todas las secciones de valores por defecto
        foreach (self::getDefaults() as $section => $values) {
            // Si la seccion no esta en el archivo o esta vacia, usar valores por defecto
            if (!array_key_exists($section, $config) || count($config[$section]) === 0) {
                $this->_configuration[$section] = $values;
                if (array_key_exists('dir', $this->_configuration[$section])) {
                    $this->_configuration[$section]['dir'] = PATH . $this->_configuration[$section]['dir'];
                }
                continue;
            }
            // Proporcionar valores por defecto especificos para el backend de base de datos
            elseif (
                $section === 'model_options' &&
                $this->_configuration['model']['class'] === 'Database'
            ) {
                $values = array(
                    'dsn' => 'sqlite:' . PATH . 'data' . DIRECTORY_SEPARATOR . 'db.sq3',
                    'tbl' => null,
                    'usr' => null,
                    'pwd' => null,
                    'opt' => array(),
                );
            } elseif (
                $section === 'model_options' &&
                $this->_configuration['model']['class'] === 'GoogleCloudStorage'
            ) {
                $values = array(
                    'bucket'     => getenv('PRIVATEBIN_GCS_BUCKET') ? getenv('PRIVATEBIN_GCS_BUCKET') : null,
                    'prefix'     => 'pastes',
                    'uniformacl' => false,
                );
            } elseif (
                $section === 'model_options' &&
                $this->_configuration['model']['class'] === 'S3Storage'
            ) {
                $values = array(
                    'region'                  => null,
                    'version'                 => null,
                    'endpoint'                => null,
                    'accesskey'               => null,
                    'secretkey'               => null,
                    'use_path_style_endpoint' => null,
                    'bucket'                  => null,
                    'prefix'                  => '',
                );
            }

            // Las secciones "*_options" (excepto model_options) se reemplazan completamente
            // con los valores del archivo de configuracion
            if (
                $section !== 'model_options' &&
                ($from = strlen($section) - strlen($opts)) >= 0 &&
                strpos($section, $opts, $from) !== false
            ) {
                if (is_int(current($values))) {
                    $config[$section] = array_map('intval', $config[$section]);
                }
                $this->_configuration[$section] = $config[$section];
            }
            // Para otras secciones, verificar cada clave y aplicar valor por defecto si falta
            else {
                // Preservar los hashes SRI configurados por el usuario
                if ($section === 'sri' && array_key_exists($section, $config)) {
                    $this->_configuration[$section] = $config[$section];
                }
                foreach ($values as $key => $val) {
                    if ($key === 'dir') {
                        $val = PATH . $val;
                    }
                    $result = $val;
                    // Si la clave existe en la configuracion del usuario, convertir al tipo correcto
                    if (array_key_exists($key, $config[$section])) {
                        if ($val === null) {
                            $result = $config[$section][$key];
                        } elseif (is_bool($val)) {
                            // Convertir cadenas como "true", "yes", "on" a booleano
                            $val = strtolower($config[$section][$key]);
                            if (in_array($val, array('true', 'yes', 'on'))) {
                                $result = true;
                            } elseif (in_array($val, array('false', 'no', 'off'))) {
                                $result = false;
                            } else {
                                $result = (bool) $config[$section][$key];
                            }
                        } elseif (is_int($val)) {
                            $result = (int) $config[$section][$key];
                        } elseif (is_string($val) && !empty($config[$section][$key])) {
                            $result = (string) $config[$section][$key];
                        } elseif (is_array($val) && is_array($config[$section][$key])) {
                            $result = $config[$section][$key];
                        }
                    }
                    $this->_configuration[$section][$key] = $result;
                }
            }
        }

        // Asegurar que la clave de expiracion por defecto sea valida
        if (!array_key_exists($this->_configuration['expire']['default'], $this->_configuration['expire_options'])) {
            $this->_configuration['expire']['default'] = key($this->_configuration['expire_options']);
        }

        // Asegurar que el basepath termine con "/"
        if (
            !empty($this->_configuration['main']['basepath']) &&
            substr_compare($this->_configuration['main']['basepath'], '/', -1) !== 0
        ) {
            $this->_configuration['main']['basepath'] .= '/';
        }
    }

    /**
     * Obtiene toda la configuracion como un array
     *
     * @return array Configuracion completa
     */
    public function get()
    {
        return $this->_configuration;
    }

    /**
     * Obtiene los valores de configuracion por defecto
     *
     * Util para comparar con la configuracion actual o para restaurar valores.
     *
     * @return array Valores por defecto
     */
    public static function getDefaults()
    {
        return self::$_defaults;
    }

    /**
     * Obtiene un valor especifico de la configuracion
     *
     * @param  string $key     Nombre de la clave a buscar
     * @param  string $section Seccion donde buscar (por defecto: 'main')
     * @throws Exception Si la clave no existe en la seccion
     * @return mixed Valor de la configuracion
     */
    public function getKey($key, $section = 'main')
    {
        $options = $this->getSection($section);
        if (!array_key_exists($key, $options)) {
            throw new Exception(I18n::_('Invalid data.') . " $section / $key", 4);
        }
        return $this->_configuration[$section][$key];
    }

    /**
     * Obtiene una seccion completa de la configuracion
     *
     * @param  string $section Nombre de la seccion
     * @throws TranslatedException Si la seccion no existe
     * @return mixed Array con todas las claves de la seccion
     */
    public function getSection($section)
    {
        if (!array_key_exists($section, $this->_configuration)) {
            throw new TranslatedException(array('%s requires configuration section [%s] to be present in configuration file.', I18n::_($this->getKey('name')), $section), 3);
        }
        return $this->_configuration[$section];
    }
}

<?php declare(strict_types=1);
/**
 * Manejador de Solicitudes HTTP de Zbin
 *
 * Este archivo analiza las solicitudes HTTP entrantes y determina que
 * operacion debe ejecutarse. Detecta automaticamente si la solicitud
 * es una llamada a la API JSON o una solicitud de pagina HTML.
 *
 * Operaciones soportadas:
 * - create: crear un nuevo paste o comentario (POST/PUT/DELETE)
 * - read: leer un paste existente (GET con pasteid)
 * - delete: eliminar un paste (GET con pasteid + deletetoken)
 * - view: mostrar la pagina principal (GET sin parametros)
 * - jsonld: servir contexto JSON-LD
 * - yourlsproxy / shlinkproxy: acortar URLs
 *
 * 
 */

namespace Zbin;

use Zbin\Exception\JsonException;
use Zbin\Model\Paste;

/**
 * Request (Solicitud)
 *
 * Analiza los parametros de la solicitud HTTP y proporciona funciones
 * auxiliares para el enrutamiento de la aplicacion.
 */
class Request
{
    /**
     * Tipo MIME para respuestas JSON
     *
     * @const string
     */
    const MIME_JSON = 'application/json';

    /**
     * Tipo MIME para respuestas HTML
     *
     * @const string
     */
    const MIME_HTML = 'text/html';

    /**
     * Tipo MIME para respuestas XHTML
     *
     * @const string
     */
    const MIME_XHTML = 'application/xhtml+xml';

    /**
     * Fuente del flujo de entrada (para leer el cuerpo de POST/PUT)
     * Se puede sobreescribir para pruebas unitarias.
     *
     * @var string
     */
    private static $_inputStream = 'php://input';

    /**
     * Operacion a realizar (create, read, delete, view, jsonld, etc.)
     * Por defecto es 'view' (mostrar la pagina principal).
     *
     * @var string
     */
    private $_operation = 'view';

    /**
     * Parametros extraidos de la solicitud HTTP
     *
     * @var array
     */
    private $_params = array();

    /**
     * Indica si la solicitud actual es una llamada a la API JSON
     * (en lugar de una solicitud de pagina HTML normal)
     *
     * @var bool
     */
    private $_isJsonApi = false;

    /**
     * Extrae el ID del paste de los parametros GET
     *
     * El ID del paste se pasa como clave en la URL (ej: ?abc123def456789a)
     * Se valida que tenga el formato correcto (16 caracteres hexadecimales).
     *
     * @return string ID del paste o 'invalid id'
     */
    private function getPasteId()
    {
        foreach ($_GET as $key => $value) {
            // Solo retornar si el valor esta vacio y la clave es un ID valido de 16 caracteres hex
            $key = (string) $key;
            if (empty($value) && Paste::isValidId($key)) {
                return $key;
            }
        }

        return 'invalid id';
    }

    /**
     * Constructor - Analiza la solicitud HTTP y determina la operacion
     *
     * Algoritmo de deteccion de operacion:
     * 1. Detectar si es una llamada JSON o HTML
     * 2. Para POST/PUT/DELETE: intentar decodificar el cuerpo como JSON (operacion 'create')
     * 3. Para GET: sanitizar y extraer parametros de la URL
     * 4. Determinar la operacion final segun los parametros presentes:
     *    - pasteid + deletetoken => 'delete'
     *    - pasteid (sin POST) => 'read'
     *    - jsonld => 'jsonld'
     *    - link + shortenviayourls => 'yourlsproxy'
     *    - link + shortenviashlink => 'shlinkproxy'
     */
    public function __construct()
    {
        // Detectar si el cliente espera JSON o HTML
        $this->_isJsonApi = $this->_detectJsonRequest();

        // Analizar parametros segun el metodo HTTP
        switch ($_SERVER['REQUEST_METHOD'] ?? 'GET') {
            case 'DELETE':
            case 'PUT':
            case 'POST':
                // Podria ser creacion o eliminacion (la eliminacion se detecta abajo)
                $this->_operation = 'create';
                try {
                    $data          = file_get_contents(self::$_inputStream);
                    $this->_params = Json::decode($data);
                } catch (JsonException $e) {
                    // Si falla el JSON, los parametros quedan vacios
                }
                break;
            default:
                // Para GET: sanitizar los parametros de la URL
                $this->_params = filter_var_array($_GET, array(
                    'deletetoken'      => FILTER_SANITIZE_SPECIAL_CHARS,
                    'jsonld'           => FILTER_SANITIZE_SPECIAL_CHARS,
                    'link'             => FILTER_SANITIZE_URL,
                    'pasteid'          => FILTER_SANITIZE_SPECIAL_CHARS,
                    'shortenviayourls' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'shortenviashlink' => FILTER_SANITIZE_SPECIAL_CHARS,
                ), false);
        }
        // Si no hay pasteid ni jsonld ni link, intentar extraer el pasteid del query string
        if (
            !array_key_exists('pasteid', $this->_params) &&
            !array_key_exists('jsonld', $this->_params) &&
            !array_key_exists('link', $this->_params) &&
            array_key_exists('QUERY_STRING', $_SERVER) &&
            !empty($_SERVER['QUERY_STRING'])
        ) {
            $this->_params['pasteid'] = $this->getPasteId();
        }

        // Determinar la operacion final basandose en los parametros disponibles
        if (array_key_exists('pasteid', $this->_params) && !empty($this->_params['pasteid'])) {
            if (array_key_exists('deletetoken', $this->_params) && !empty($this->_params['deletetoken'])) {
                $this->_operation = 'delete';
            } elseif ($this->_operation !== 'create') {
                $this->_operation = 'read';
            }
        } elseif (array_key_exists('jsonld', $this->_params) && !empty($this->_params['jsonld'])) {
            $this->_operation = 'jsonld';
        } elseif (array_key_exists('link', $this->_params) && !empty($this->_params['link'])) {
            if (str_contains($this->getRequestUri(), '/shortenviayourls') || array_key_exists('shortenviayourls', $this->_params)) {
                $this->_operation = 'yourlsproxy';
            }
            if (str_contains($this->getRequestUri(), '/shortenviashlink') || array_key_exists('shortenviashlink', $this->_params)) {
                $this->_operation = 'shlinkproxy';
            }
        }
    }

    /**
     * Obtiene la operacion actual que debe ejecutarse
     *
     * @return string Nombre de la operacion (create, read, delete, view, etc.)
     */
    public function getOperation()
    {
        return $this->_operation;
    }

    /**
     * Obtiene los datos del paste o comentario desde la solicitud
     *
     * Extrae y estructura los datos necesarios del cuerpo de la solicitud POST.
     * Para comentarios se requieren pasteid y parentid adicionales.
     *
     * @return array Datos estructurados del paste/comentario
     */
    public function getData()
    {
        $data = array(
            'adata' => $this->getParam('adata'),
        );
        $required_keys = array('v', 'ct');
        $meta          = $this->getParam('meta');
        if (empty($meta)) {
            // Si no hay meta, es un comentario y necesita pasteid y parentid
            $required_keys[] = 'pasteid';
            $required_keys[] = 'parentid';
        } else {
            $data['meta'] = $meta;
        }
        foreach ($required_keys as $key) {
            $data[$key] = $this->getParam($key, $key === 'v' ? 1 : '');
        }
        // Forzar conversion a numero (int o float) para la version
        $data['v'] = $data['v'] + 0;
        return $data;
    }

    /**
     * Obtiene un parametro de la solicitud con valor por defecto
     *
     * @param  string $param   Nombre del parametro
     * @param  string $default Valor por defecto si no existe
     * @return string Valor del parametro
     */
    public function getParam($param, $default = '')
    {
        return $this->_params[$param] ?? $default;
    }

    /**
     * Obtiene el nombre del host solicitado por el cliente
     *
     * @return string Nombre del host (ej: 'example.com') o 'localhost'
     */
    public function getHost()
    {
        $host = array_key_exists('HTTP_HOST', $_SERVER) ? filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL) : '';
        return empty($host) ? 'localhost' : $host;
    }

    /**
     * Obtiene la URI de la solicitud actual
     *
     * @return string URI de la solicitud (ej: '/zbin/' o '/')
     */
    public function getRequestUri()
    {
        $uri = array_key_exists('REQUEST_URI', $_SERVER) ? filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL) : '';
        return empty($uri) ? '/' : $uri;
    }

    /**
     * Indica si la solicitud actual es una llamada a la API JSON
     *
     * @return bool true si el cliente espera JSON, false si espera HTML
     */
    public function isJsonApiCall()
    {
        return $this->_isJsonApi;
    }

    /**
     * Sobreescribe la fuente del flujo de entrada (para pruebas unitarias)
     *
     * @param string $input Ruta al archivo o flujo de entrada
     */
    public static function setInputStream($input)
    {
        self::$_inputStream = $input;
    }

    /**
     * Detecta si el cliente solicita una respuesta JSON o HTML
     *
     * Algoritmo de negociacion de tipo de contenido:
     * 1. Caso simple: cabecera X-Requested-With = JSONHttpRequest
     * 2. Caso simple: Accept contiene JSON pero no HTML ni XHTML
     * 3. Caso avanzado: negociacion completa del tipo de medio (media type negotiation)
     *    - Analiza la cabecera Accept con sus factores de calidad (q)
     *    - Ordena por prioridad descendente
     *    - El primer tipo soportado que encuentre determina el resultado
     *
     * @return bool true si se detecta una solicitud JSON
     */
    private function _detectJsonRequest()
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';

        // Casos simples de deteccion
        if (
            ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'JSONHttpRequest' ||
            (
                str_contains($acceptHeader, self::MIME_JSON) &&
                !str_contains($acceptHeader, self::MIME_HTML) &&
                !str_contains($acceptHeader, self::MIME_XHTML)
            )
        ) {
            return true;
        }

        // Caso avanzado: negociacion de tipo de medio con factores de calidad
        if (!empty($acceptHeader)) {
            $mediaTypes = array();
            foreach (explode(',', trim($acceptHeader)) as $mediaTypeRange) {
                if (preg_match(
                    '#(\*/\*|[a-z\-]+/[a-z\-+*]+(?:\s*;\s*[^q]\S*)*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?#',
                    trim($mediaTypeRange), $match
                )) {
                    if (!isset($match[2])) {
                        $match[2] = '1.0'; // calidad por defecto
                    } else {
                        $match[2] = (string) floatval($match[2]);
                        if ($match[2] === '0.0') {
                            continue; // calidad 0 = no aceptado
                        }
                    }
                    if (!isset($mediaTypes[$match[2]])) {
                        $mediaTypes[$match[2]] = array();
                    }
                    $mediaTypes[$match[2]][] = strtolower($match[1]);
                }
            }
            // Ordenar por calidad descendente (mayor prioridad primero)
            krsort($mediaTypes);
            foreach ($mediaTypes as $acceptedQuality => $acceptedValues) {
                foreach ($acceptedValues as $acceptedValue) {
                    if (
                        str_starts_with($acceptedValue, self::MIME_HTML) ||
                        str_starts_with($acceptedValue, self::MIME_XHTML)
                    ) {
                        return false; // prefiere HTML
                    } elseif (str_starts_with($acceptedValue, self::MIME_JSON)) {
                        return true;  // prefiere JSON
                    }
                }
            }
        }
        return false;
    }
}

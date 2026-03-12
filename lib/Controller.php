<?php declare(strict_types=1);
/**
 * Controlador Principal de Zbin
 *
 * Este archivo contiene el controlador central de la aplicacion Zbin.
 * Coordina todas las operaciones principales: crear, leer, eliminar pastes,
 * renderizar la interfaz y manejar las respuestas JSON/HTML.
 *
 * Es el punto de entrada logico de la aplicacion despues de index.php.
 * Actua como un "orquestador" que conecta el modelo, la vista y la configuracion.
 *
 * 
 */

namespace Zbin;

use Exception;
use Zbin\Exception\JsonException;
use Zbin\Exception\TranslatedException;
use Zbin\Persistence\ServerSalt;
use Zbin\Persistence\TrafficLimiter;
use Zbin\Proxy\AbstractProxy;
use Zbin\Proxy\ShlinkProxy;
use Zbin\Proxy\YourlsProxy;

/**
 * Controlador
 *
 * Clase principal que une todos los componentes de Zbin.
 * Recibe la solicitud HTTP, la procesa y genera la respuesta adecuada.
 */
class Controller
{
    /**
     * Version actual de la aplicacion
     *
     * @const string
     */
    const VERSION = '1.0.0';

    /**
     * Version minima de PHP requerida para ejecutar Zbin
     *
     * @const string
     */
    const MIN_PHP_VERSION = '7.4.0';

    /**
     * Mensaje de error generico que se muestra cuando un documento no existe,
     * ha expirado o fue eliminado. Se usa el mismo mensaje en todos los casos
     * por seguridad (para no revelar si el documento existio alguna vez).
     *
     * @const string
     */
    const GENERIC_ERROR = 'Document does not exist, has expired or has been deleted.';

    /**
     * Instancia de la configuracion de la aplicacion
     *
     * @var Configuration
     */
    private $_conf;

    /**
     * Mensaje de error actual (si lo hay)
     *
     * @var string
     */
    private $_error = '';

    /**
     * Mensaje de estado (exito, informacion, etc.)
     *
     * @var string
     */
    private $_status = '';

    /**
     * Indica si el documento fue eliminado exitosamente
     *
     * @var bool
     */
    private $_is_deleted = false;

    /**
     * Respuesta JSON codificada para enviar al cliente
     *
     * @var string
     */
    private $_json = '';

    /**
     * Fabrica de modelos (Paste, Comment, etc.)
     * Centraliza el acceso a los datos a traves del patron Factory
     *
     * @var Model
     */
    private $_model;

    /**
     * Objeto que representa la solicitud HTTP actual
     *
     * @var Request
     */
    private $_request;

    /**
     * URL base de la aplicacion, usada para construir enlaces
     *
     * @var string
     */
    private $_urlBase;

    /**
     * Constructor - Punto de entrada principal de la aplicacion
     *
     * Este metodo ejecuta toda la logica de la aplicacion:
     * 1. Verifica la version de PHP
     * 2. Carga la configuracion
     * 3. Determina la operacion a realizar (crear, leer, eliminar, etc.)
     * 4. Ejecuta la operacion correspondiente
     * 5. Envia la respuesta al cliente (JSON o HTML)
     *
     * @param ?Configuration $config Configuracion opcional (si no se pasa, se carga del archivo .ini)
     * @throws Exception
     */
    public function __construct(?Configuration $config = null)
    {
        // Verificar que la version de PHP sea suficiente
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION) < 0) {
            error_log(I18n::_('%s requires php %s or above to work. Sorry.', I18n::_('Zbin'), self::MIN_PHP_VERSION));
            return;
        }
        // Verificar que la constante PATH termine con el separador de directorio
        if (strlen(PATH) < 0 && substr(PATH, -1) !== DIRECTORY_SEPARATOR) {
            error_log(I18n::_('%s requires the PATH to end in a "%s". Please update the PATH in your index.php.', I18n::_('Zbin'), DIRECTORY_SEPARATOR));
            return;
        }

        // Cargar configuracion (archivo .ini por defecto) e inicializar clases necesarias
        $this->_conf = $config ?? new Configuration();
        $this->_init();

        // Enrutar la solicitud segun la operacion detectada por el objeto Request
        switch ($this->_request->getOperation()) {
            case 'create':
                $this->_create();
                break;
            case 'delete':
                $this->_delete(
                    $this->_request->getParam('pasteid'),
                    $this->_request->getParam('deletetoken')
                );
                break;
            case 'read':
                $this->_read($this->_request->getParam('pasteid'));
                break;
            case 'jsonld':
                $this->_jsonld($this->_request->getParam('jsonld'));
                return; // retorna directamente, sin cabeceras de cache ni vista
            case 'yourlsproxy':
                $this->_shortenerproxy(new YourlsProxy($this->_conf, $this->_request->getParam('link')));
                break;
            case 'shlinkproxy':
                $this->_shortenerproxy(new ShlinkProxy($this->_conf, $this->_request->getParam('link')));
                break;
        }

        // Desactivar cache del navegador para todas las respuestas
        $this->_setCacheHeaders();

        // Enviar respuesta: JSON para llamadas API, HTML para navegador
        if ($this->_request->isJsonApiCall()) {
            header('Content-type: ' . Request::MIME_JSON);
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
            header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
            header('X-Uncompressed-Content-Length: ' . strlen($this->_json));
            header('Access-Control-Expose-Headers: X-Uncompressed-Content-Length');
            echo $this->_json;
        } else {
            $this->_view();
        }
    }

    /**
     * Inicializa los componentes principales de Zbin
     *
     * Crea el modelo de datos, el objeto de solicitud y configura
     * el idioma y la plantilla por defecto.
     *
     * @throws Exception
     */
    private function _init()
    {
        $this->_model   = new Model($this->_conf);
        $this->_request = new Request;
        $this->_urlBase = $this->_request->getRequestUri();

        $this->_setDefaultLanguage();
        $this->_setDefaultTemplate();
    }

    /**
     * Configura el idioma por defecto de la aplicacion
     *
     * Si la seleccion de idioma esta desactivada y hay un idioma por defecto configurado,
     * se fuerza ese idioma mediante una cookie.
     *
     * @throws Exception
     */
    private function _setDefaultLanguage()
    {
        $lang = $this->_conf->getKey('languagedefault');
        I18n::setLanguageFallback($lang);
        // Si la seleccion de idioma esta deshabilitada y hay un idioma valido configurado, forzarlo
        if (!$this->_conf->getKey('languageselection') && strlen($lang) === 2) {
            $_COOKIE['lang'] = $lang;
            setcookie('lang', $lang, array('SameSite' => 'Lax', 'Secure' => true));
        }
    }

    /**
     * Configura la plantilla visual por defecto
     *
     * Si la seleccion de plantilla esta desactivada, elimina cualquier cookie
     * de plantilla existente para forzar la plantilla por defecto.
     *
     * @throws Exception
     */
    private function _setDefaultTemplate()
    {
        $templates = $this->_conf->getKey('availabletemplates');
        $template  = $this->_conf->getKey('template');
        if (!in_array($template, $templates, true)) {
            $templates[] = $template;
        }
        TemplateSwitcher::setAvailableTemplates($templates);
        TemplateSwitcher::setTemplateFallback($template);

        // Si la seleccion de plantilla esta deshabilitada, eliminar la cookie para no reutilizar un valor anterior
        if (!$this->_conf->getKey('templateselection') && array_key_exists('template', $_COOKIE)) {
            unset($_COOKIE['template']);
            $expiredInAllTimezones = time() - 86400;
            setcookie('template', '', array('expires' => $expiredInAllTimezones, 'SameSite' => 'Lax', 'Secure' => true));
        }
    }

    /**
     * Configura las cabeceras HTTP para desactivar el cache del navegador
     *
     * Esto es importante para que el contenido sensible no se almacene
     * en el cache del navegador.
     */
    private function _setCacheHeaders()
    {
        $time = gmdate('D, d M Y H:i:s \G\M\T');
        header('Cache-Control: no-store, no-cache, no-transform, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: ' . $time);
        header('Last-Modified: ' . $time);
        header('Vary: Accept');
    }

    /**
     * Crea un nuevo paste o comentario
     *
     * Flujo de creacion:
     * 1. Verifica el limite de trafico (anti-spam por IP)
     * 2. Valida el formato de los datos recibidos (version 2)
     * 3. Verifica que el contenido no exceda el limite de tamano
     * 4. Si es un comentario, lo asocia al paste padre
     * 5. Si es un paste nuevo, purga documentos expirados y almacena
     *
     * Estructura del POST (JSON):
     *   v = 2 (version del formato)
     *   adata (datos autenticados - no encriptados)
     *   ct (texto cifrado en base64)
     *   meta (opcional): expire = periodo de expiracion
     *   parentid (opcional): ID del comentario padre (para discusiones)
     *   pasteid (opcional): ID del paste al que pertenece el comentario
     *
     * @throws Exception
     */
    private function _create()
    {
        // Verificar que la IP del visitante no haya creado un paste muy recientemente (anti-spam)
        ServerSalt::setStore($this->_model->getStore());
        TrafficLimiter::setConfiguration($this->_conf);
        TrafficLimiter::setStore($this->_model->getStore());
        try {
            TrafficLimiter::canPass();
        } catch (TranslatedException $e) {
            $this->_json_error($e->getMessage());
            return;
        }

        $data      = $this->_request->getData();
        // Determinar si es un comentario (tiene pasteid y parentid)
        $isComment = array_key_exists('pasteid', $data) &&
            !empty($data['pasteid']) &&
            array_key_exists('parentid', $data) &&
            !empty($data['parentid']);
        // Validar que los datos cumplan con el formato V2
        if (!FormatV2::isValid($data, $isComment)) {
            $this->_json_error(I18n::_('Invalid data.'));
            return;
        }
        $sizelimit = $this->_conf->getKey('sizelimit');
        // Verificar que el contenido cifrado no exceda el limite configurado
        if (strlen($data['ct']) > $sizelimit) {
            $this->_json_error(
                I18n::_(
                    'Document is limited to %s of encrypted data.',
                    Filter::formatHumanReadableSize($sizelimit)
                )
            );
            return;
        }

        // Caso: el usuario publica un comentario en una discusion
        if ($isComment) {
            $paste = $this->_model->getPaste($data['pasteid']);
            if ($paste->exists()) {
                try {
                    $comment = $paste->getComment($data['parentid']);
                    $comment->setData($data);
                    $comment->store();
                    $this->_json_result($comment->getId());
                } catch (Exception $e) {
                    $this->_json_error($e->getMessage());
                }
            } else {
                $this->_json_error(I18n::_('Invalid data.'));
            }
        }
        // Caso: el usuario publica un paste estandar
        else {
            try {
                // Purgar pastes expirados antes de crear uno nuevo
                $this->_model->purge();
                $paste = $this->_model->getPaste();
                $paste->setData($data);
                $paste->store();
                // Devolver el ID del paste y el token de eliminacion
                $this->_json_result($paste->getId(), array('deletetoken' => $paste->getDeleteToken()));
            } catch (Exception $e) {
                $this->_json_error($e->getMessage());
            }
        }
    }

    /**
     * Elimina un documento existente
     *
     * Verifica que el documento exista y que el token de eliminacion sea valido.
     * El token se genera con HMAC-SHA256 usando el salt del paste, lo que garantiza
     * que solo quien recibio el token original puede eliminar el documento.
     *
     * @param string $dataid     ID del paste a eliminar
     * @param string $deletetoken Token de eliminacion para verificar autorizacion
     */
    private function _delete($dataid, $deletetoken)
    {
        try {
            $paste = $this->_model->getPaste($dataid);
            if ($paste->exists()) {
                // Al acceder a get(), se verifica si el documento ya expiro y se elimina automaticamente
                $paste->get();
                // Comparacion segura contra ataques de tiempo (timing attacks)
                if (hash_equals($paste->getDeleteToken(), $deletetoken)) {
                    // Token valido: eliminar el documento
                    $paste->delete();
                    $this->_status     = 'Document was properly deleted.';
                    $this->_is_deleted = true;
                } else {
                    $this->_error = 'Wrong deletion token. Document was not deleted.';
                }
            } else {
                $this->_error = self::GENERIC_ERROR;
            }
        } catch (TranslatedException $e) {
            $this->_error = $e->getMessage();
        }
        // Si es una llamada API, responder en JSON
        if ($this->_request->isJsonApiCall()) {
            if (empty($this->_error)) {
                $this->_json_result($dataid);
            } else {
                $this->_json_error(I18n::_($this->_error));
            }
        }
    }

    /**
     * Lee un documento existente (solo permitido via API JSON)
     *
     * Si el paste existe, devuelve sus datos (sin el salt del servidor,
     * que es informacion interna de seguridad).
     *
     * @param string $dataid ID del paste a leer
     */
    private function _read($dataid)
    {
        // Solo permitir lectura via API JSON (el navegador carga la pagina y luego pide los datos via AJAX)
        if (!$this->_request->isJsonApiCall()) {
            return;
        }

        try {
            $paste = $this->_model->getPaste($dataid);
            if ($paste->exists()) {
                $data = $paste->get();
                // Eliminar el salt de la respuesta por seguridad
                if (array_key_exists('salt', $data['meta'])) {
                    unset($data['meta']['salt']);
                }
                $this->_json_result($dataid, (array) $data);
            } else {
                $this->_json_error(I18n::_(self::GENERIC_ERROR));
            }
        } catch (TranslatedException $e) {
            $this->_json_error($e->getMessage());
        }
    }

    /**
     * Renderiza la interfaz de usuario (frontend HTML)
     *
     * Configura todas las cabeceras de seguridad (CSP, CORS, etc.),
     * prepara todas las variables necesarias para la plantilla
     * y renderiza la vista correspondiente.
     *
     * @throws Exception
     */
    private function _view()
    {
        // Cabeceras de seguridad HTTP para proteger contra XSS, clickjacking, etc.
        header('Content-Security-Policy: ' . $this->_conf->getKey('cspheader'));
        header('Cross-Origin-Resource-Policy: same-origin');
        header('Cross-Origin-Embedder-Policy: require-corp');
        // Cross-Origin-Opener-Policy deshabilitado porque impide abrir enlaces del mismo sitio
        header('Permissions-Policy: browsing-topics=()');
        header('Referrer-Policy: no-referrer');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: deny');
        header('X-XSS-Protection: 1; mode=block');

        // Preparar las opciones de expiracion con etiquetas legibles
        $expire = array();
        foreach ($this->_conf->getSection('expire_options') as $time => $seconds) {
            $expire[$time] = ($seconds === 0) ? I18n::_(ucfirst($time)) : Filter::formatHumanReadableTime($time);
        }

        // Traducir las opciones de formato (plaintext, markdown, etc.)
        $formatters = array_map('Zbin\\I18n::_', $this->_conf->getSection('formatter_options'));

        // Configurar cookie de idioma si la seleccion esta habilitada
        $languageselection = '';
        if ($this->_conf->getKey('languageselection')) {
            $languageselection = I18n::getLanguage();
            setcookie('lang', $languageselection, array('SameSite' => 'Lax', 'Secure' => true));
        }

        // Configurar cookie de plantilla si la seleccion esta habilitada
        $templateselection = '';
        if ($this->_conf->getKey('templateselection')) {
            $templateselection = TemplateSwitcher::getTemplate();
            setcookie('template', $templateselection, array('SameSite' => 'Lax', 'Secure' => true));
        }

        // Eliminar directivas CSP que no son compatibles con la etiqueta <meta>
        $metacspheader = str_replace(
            array(
                'frame-ancestors \'none\'; ',
                '; sandbox allow-same-origin allow-scripts allow-forms allow-modals allow-downloads',
            ),
            '',
            $this->_conf->getKey('cspheader')
        );

        // Asignar todas las variables a la vista y renderizar la plantilla
        $page = new View;
        $page->assign('CSPHEADER', $metacspheader);
        $page->assign('ERROR', I18n::_($this->_error));
        $page->assign('NAME', $this->_conf->getKey('name'));
        if (in_array($this->_request->getOperation(), array('shlinkproxy', 'yourlsproxy'), true)) {
            $page->assign('SHORTURL', $this->_status);
            $page->draw('shortenerproxy');
            return;
        }
        $page->assign('BASEPATH', I18n::_($this->_conf->getKey('basepath')));
        $page->assign('STATUS', I18n::_($this->_status));
        $page->assign('ISDELETED', $this->_is_deleted);
        $page->assign('VERSION', self::VERSION);
        $page->assign('DISCUSSION', $this->_conf->getKey('discussion'));
        $page->assign('OPENDISCUSSION', $this->_conf->getKey('opendiscussion'));
        $page->assign('MARKDOWN', array_key_exists('markdown', $formatters));
        $page->assign('SYNTAXHIGHLIGHTING', array_key_exists('syntaxhighlighting', $formatters));
        $page->assign('SYNTAXHIGHLIGHTINGTHEME', $this->_conf->getKey('syntaxhighlightingtheme'));
        $page->assign('FORMATTER', $formatters);
        $page->assign('FORMATTERDEFAULT', $this->_conf->getKey('defaultformatter'));
        $page->assign('INFO', I18n::_(str_replace("'", '"', $this->_conf->getKey('info'))));
        $page->assign('NOTICE', I18n::_($this->_conf->getKey('notice')));
        $page->assign('BURNAFTERREADINGSELECTED', $this->_conf->getKey('burnafterreadingselected'));
        $page->assign('PASSWORD', $this->_conf->getKey('password'));
        $page->assign('FILEUPLOAD', $this->_conf->getKey('fileupload'));
        $page->assign('LANGUAGESELECTION', $languageselection);
        $page->assign('LANGUAGES', I18n::getLanguageLabels(I18n::getAvailableLanguages()));
        $page->assign('TEMPLATESELECTION', $templateselection);
        $page->assign('TEMPLATES', TemplateSwitcher::getAvailableTemplates());
        $page->assign('EXPIRE', $expire);
        $page->assign('EXPIREDEFAULT', $this->_conf->getKey('default', 'expire'));
        $page->assign('URLSHORTENER', $this->_conf->getKey('urlshortener'));
        $page->assign('SHORTENBYDEFAULT', $this->_conf->getKey('shortenbydefault'));
        $page->assign('QRCODE', $this->_conf->getKey('qrcode'));
        $page->assign('EMAIL', $this->_conf->getKey('email'));
        $page->assign('HTTPWARNING', $this->_conf->getKey('httpwarning'));
        $page->assign('HTTPSLINK', 'https://' . $this->_request->getHost() . $this->_request->getRequestUri());
        $page->assign('COMPRESSION', $this->_conf->getKey('compression'));
        $page->assign('SRI', $this->_conf->getSection('sri'));
        $page->draw(TemplateSwitcher::getTemplate());
    }

    /**
     * Genera y envia el contexto JSON-LD solicitado
     *
     * JSON-LD es un formato de datos enlazados (Linked Data) que permite
     * describir la estructura de los pastes y comentarios de forma estandarizada.
     *
     * @param string $type Tipo de contexto solicitado (paste, comment, types, etc.)
     */
    private function _jsonld($type)
    {
        // Validar que el tipo solicitado sea uno de los permitidos
        if (!in_array($type, array(
            'comment',
            'commentmeta',
            'paste',
            'pastemeta',
            'types',
        ))) {
            $type = '';
        }
        $content = '{}';
        $file    = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $type . '.jsonld';
        if (is_readable($file)) {
            // Reemplazar las URLs relativas con la URL base actual
            $content = str_replace(
                '?jsonld=',
                $this->_urlBase . '?jsonld=',
                file_get_contents($file)
            );
        }
        // Para el tipo 'types', actualizar las opciones de expiracion con las configuradas
        if ($type === 'types') {
            $content = str_replace(
                implode('", "', array_keys($this->_conf->getDefaults()['expire_options'])),
                implode('", "', array_keys($this->_conf->getSection('expire_options'))),
                $content
            );
        }

        header('Content-type: application/ld+json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        echo $content;
    }

    /**
     * Prepara un mensaje de error en formato JSON
     *
     * @param string $error Mensaje de error a enviar
     * @throws JsonException
     */
    private function _json_error($error)
    {
        $result = array(
            'status'  => 1,  // 1 = error
            'message' => $error,
        );
        $this->_json = Json::encode($result);
    }

    /**
     * Prepara un mensaje de resultado exitoso en formato JSON
     *
     * @param string $dataid ID del paste
     * @param array  $other  Datos adicionales (ej: deletetoken)
     * @throws JsonException
     */
    private function _json_result($dataid, $other = array())
    {
        $result = array(
            'status' => 0,   // 0 = exito
            'id'     => $dataid,
            'url'    => $this->_urlBase . '?' . $dataid,
        ) + $other;
        $this->_json = Json::encode($result);
    }

    /**
     * Ejecuta un proxy de acortador de URLs y actualiza el estado o error con la respuesta
     *
     * Soporta Yourls y Shlink como servicios de acortamiento de URLs.
     *
     * @param AbstractProxy $proxy Instancia del proxy a utilizar
     */
    private function _shortenerproxy(AbstractProxy $proxy)
    {
        if ($proxy->isError()) {
            $this->_error = $proxy->getError();
        } else {
            $this->_status = $proxy->getUrl();
        }
    }
}

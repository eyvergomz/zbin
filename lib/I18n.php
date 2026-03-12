<?php declare(strict_types=1);
/**
 * Sistema de Internacionalizacion (i18n) de Zbin
 *
 * Este archivo maneja toda la logica de traduccion de la aplicacion.
 * Funcionalidades principales:
 * - Detectar el idioma preferido del navegador del usuario
 * - Cargar archivos de traduccion JSON desde el directorio i18n/
 * - Traducir cadenas con soporte para plurales (diferentes reglas por idioma)
 * - Codificar HTML para prevenir ataques XSS en las traducciones
 *
 * El idioma por defecto es ingles ('en'), que esta integrado en el codigo
 * y no requiere archivo de traduccion.
 *
 * 
 */

namespace Zbin;

use AppendIterator;
use GlobIterator;

/**
 * I18n (Internacionalizacion)
 *
 * Herramientas de internacionalizacion: traduccion, deteccion de idioma, etc.
 * Todas las propiedades y metodos son estaticos porque el idioma es global.
 */
class I18n
{
    /**
     * Idioma actual seleccionado
     *
     * @var string
     */
    protected static $_language = 'en';

    /**
     * Idioma de respaldo cuando no se encuentra una traduccion
     *
     * @var string
     */
    protected static $_languageFallback = 'en';

    /**
     * Etiquetas de los idiomas (ej: 'es' => 'Espanol')
     *
     * @var array
     */
    protected static $_languageLabels = array();

    /**
     * Lista de idiomas disponibles (detectados de los archivos JSON)
     *
     * @var array
     */
    protected static $_availableLanguages = array();

    /**
     * Ruta al directorio de archivos de idioma
     *
     * @var string
     */
    protected static $_path = '';

    /**
     * Cache de traducciones cargadas del archivo JSON del idioma actual
     *
     * @var array
     */
    protected static $_translations = array();

    /**
     * Alias corto para translate() - Traduce una cadena
     *
     * Uso: I18n::_('Hello %s', 'World') => "Hola World" (si el idioma es espanol)
     *
     * @param  string|array $messageId Clave de traduccion o array [singular, plural]
     * @param  mixed        $args      Parametros para insertar en los marcadores %s y %d
     * @return string Cadena traducida
     */
    public static function _($messageId, ...$args)
    {
        return forward_static_call_array('Zbin\I18n::translate', func_get_args());
    }

    /**
     * Traduce una cadena de texto
     *
     * Proceso de traduccion:
     * 1. Si las traducciones no estan cargadas, las carga del archivo JSON
     * 2. Busca la traduccion en el cache
     * 3. Si el messageId es un array, aplica reglas de plural segun el idioma
     * 4. Codifica los parametros como HTML para prevenir XSS
     * 5. Inserta los parametros en la cadena usando sprintf
     *
     * Los parametros no enteros se codifican como entidades HTML porque
     * pueden provenir de fuentes no confiables (input del usuario).
     * El messageId NO se codifica porque proviene de fuentes confiables
     * (codigo fuente o archivos de traduccion JSON).
     *
     * @param  string|array $messageId Clave de traduccion
     * @param  mixed        $args      Parametros para los marcadores de posicion
     * @return string Cadena traducida y formateada
     */
    public static function translate($messageId, ...$args)
    {
        if (empty($messageId)) {
            return $messageId;
        }
        if (empty(self::$_translations)) {
            self::loadTranslations();
        }
        $messages = $messageId;
        if (is_array($messageId)) {
            $messageId = count($messageId) > 1 ? $messageId[1] : $messageId[0];
        }
        if (!array_key_exists($messageId, self::$_translations)) {
            self::$_translations[$messageId] = $messages;
        }
        array_unshift($args, $messageId);
        // Si la traduccion es un array, aplicar forma plural
        if (is_array(self::$_translations[$messageId])) {
            $number = (int) $args[1];
            $key    = self::_getPluralForm($number);
            $max    = count(self::$_translations[$messageId]) - 1;
            if ($key > $max) {
                $key = $max;
            }

            $args[0] = self::$_translations[$messageId][$key];
            $args[1] = $number;
        } else {
            $args[0] = self::$_translations[$messageId];
        }
        // Codificar argumentos no enteros como HTML para prevenir XSS
        $argsCount = count($args);
        for ($i = 1; $i < $argsCount; ++$i) {
            if (is_int($args[$i])) {
                continue;
            }
            $args[$i] = self::encode($args[$i]);
        }
        return call_user_func_array('sprintf', $args);
    }

    /**
     * Codifica entidades HTML para salida segura en documentos HTML5
     *
     * Previene ataques XSS al convertir caracteres especiales en entidades HTML.
     *
     * @param  string $string Cadena a codificar
     * @return string Cadena con entidades HTML codificadas
     */
    public static function encode($string)
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5 | ENT_DISALLOWED, 'UTF-8', false);
    }

    /**
     * Carga las traducciones del idioma detectado
     *
     * Algoritmo de seleccion de idioma:
     * 1. Si existe la cookie 'lang', usar ese idioma
     * 2. Si no, detectar el idioma preferido del navegador (cabecera Accept-Language)
     * 3. Cargar el archivo JSON correspondiente (ej: i18n/es.json)
     * 4. Si el idioma es ingles, no se carga ningun archivo (esta integrado)
     *
     * @throws JsonException Si el archivo de traduccion tiene JSON invalido
     */
    public static function loadTranslations()
    {
        $availableLanguages = self::getAvailableLanguages();

        // Verificar si hay cookie de idioma y si ese idioma esta disponible
        if (
            array_key_exists('lang', $_COOKIE) &&
            ($key = array_search($_COOKIE['lang'], $availableLanguages)) !== false
        ) {
            self::$_language = $availableLanguages[$key];
        }
        // Si no hay cookie, buscar coincidencia con los idiomas del navegador
        else {
            self::$_language = self::_getMatchingLanguage(
                self::getBrowserLanguages(), $availableLanguages
            );
        }

        // Cargar traducciones (ingles no necesita archivo, es el idioma base)
        if (self::$_language === 'en') {
            self::$_translations = array();
        } else {
            $data                = file_get_contents(self::_getPath(self::$_language . '.json'));
            self::$_translations = Json::decode($data);
        }
    }

    /**
     * Obtiene la lista de idiomas disponibles basandose en los archivos encontrados
     *
     * Busca archivos JSON en el directorio i18n/ con nombres de 2 o 3 caracteres
     * (ej: es.json, fr.json, jbo.json).
     *
     * @return array Lista de codigos de idioma disponibles
     */
    public static function getAvailableLanguages()
    {
        if (count(self::$_availableLanguages) === 0) {
            self::$_availableLanguages[] = 'en'; // Ingles siempre disponible (integrado)
            $languageIterator            = new AppendIterator();
            $languageIterator->append(new GlobIterator(self::_getPath('??.json')));   // idiomas de 2 letras
            $languageIterator->append(new GlobIterator(self::_getPath('???.json'))); // idiomas de 3 letras (ej: jbo)
            foreach ($languageIterator as $file) {
                $language = $file->getBasename('.json');
                if ($language !== 'en') {
                    self::$_availableLanguages[] = $language;
                }
            }
        }
        return self::$_availableLanguages;
    }

    /**
     * Detecta los idiomas preferidos del navegador y los ordena por preferencia
     *
     * Analiza la cabecera HTTP Accept-Language que envia el navegador.
     * Cada idioma tiene un factor de calidad (q) que indica la preferencia.
     * Ejemplo: "es-ES,es;q=0.9,en;q=0.8" => espanol > ingles
     *
     * @return array Idiomas ordenados por preferencia descendente
     */
    public static function getBrowserLanguages()
    {
        $languages = array();
        if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            $languageRanges = explode(',', trim($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            foreach ($languageRanges as $languageRange) {
                if (preg_match(
                    '/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/',
                    trim($languageRange), $match
                )) {
                    if (!isset($match[2])) {
                        $match[2] = '1.0';
                    } else {
                        $match[2] = (string) floatval($match[2]);
                    }
                    if (!isset($languages[$match[2]])) {
                        $languages[$match[2]] = array();
                    }
                    $languages[$match[2]][] = strtolower($match[1]);
                }
            }
            krsort($languages); // Ordenar por calidad descendente
        }
        return $languages;
    }

    /**
     * Obtiene el idioma actualmente cargado
     *
     * @return string Codigo del idioma (ej: 'es', 'en', 'fr')
     */
    public static function getLanguage()
    {
        return self::$_language;
    }

    /**
     * Obtiene las etiquetas legibles de los idiomas (ej: 'es' => 'Espanol')
     *
     * Si se proporcionan codigos especificos, solo retorna esos.
     * Los datos se cargan del archivo languages.json.
     *
     * @param  array $languages Codigos de idioma a filtrar (vacio = todos)
     * @throws JsonException
     * @return array Mapa de codigo => etiqueta
     */
    public static function getLanguageLabels($languages = array())
    {
        $file = self::_getPath('languages.json');
        if (count(self::$_languageLabels) === 0 && is_readable($file)) {
            $data                  = file_get_contents($file);
            self::$_languageLabels = Json::decode($data);
        }
        if (count($languages) === 0) {
            return self::$_languageLabels;
        }
        return array_intersect_key(self::$_languageLabels, array_flip($languages));
    }

    /**
     * Determina si el idioma actual se escribe de derecha a izquierda (RTL)
     *
     * Actualmente solo arabe ('ar') y hebreo ('he') son RTL.
     *
     * @return bool true si el idioma es RTL
     */
    public static function isRtl()
    {
        return in_array(self::$_language, array('ar', 'he'));
    }

    /**
     * Establece el idioma de respaldo por defecto
     *
     * Se usa cuando no se puede determinar el idioma del usuario.
     *
     * @param string $lang Codigo del idioma de respaldo
     */
    public static function setLanguageFallback($lang)
    {
        if (in_array($lang, self::getAvailableLanguages())) {
            self::$_languageFallback = $lang;
        }
    }

    /**
     * Obtiene la ruta al directorio de archivos de idioma
     *
     * @param  string $file Nombre del archivo (opcional)
     * @return string Ruta completa al archivo
     */
    protected static function _getPath($file = '')
    {
        if (empty(self::$_path)) {
            self::$_path = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'i18n';
        }
        return self::$_path . (empty($file) ? '' : DIRECTORY_SEPARATOR . $file);
    }

    /**
     * Determina la forma plural correcta segun el idioma y el numero dado
     *
     * Cada idioma tiene reglas diferentes para los plurales. Por ejemplo:
     * - Ingles/Espanol: 2 formas (singular: n=1, plural: n!=1)
     * - Arabe: 6 formas diferentes
     * - Japones/Chino: 1 forma (sin distincion de plural)
     * - Ruso/Ucraniano: 3 formas con reglas complejas
     *
     * El valor retornado es el indice del array de traducciones plurales.
     *
     * @param  int $n Numero para determinar la forma plural
     * @return int Indice de la forma plural a usar
     */
    protected static function _getPluralForm($n)
    {
        switch (self::$_language) {
            case 'ar':
                return $n === 0 ? 0 : ($n === 1 ? 1 : ($n === 2 ? 2 : ($n % 100 >= 3 && $n % 100 <= 10 ? 3 : ($n % 100 >= 11 ? 4 : 5))));
            case 'cs':
            case 'sk':
                return $n === 1 ? 0 : ($n >= 2 && $n <= 4 ? 1 : 2);
            case 'co':
            case 'fa':
            case 'fr':
            case 'oc':
            case 'tr':
            case 'zh':
                return $n > 1 ? 1 : 0;
            case 'he':
                return $n === 1 ? 0 : ($n === 2 ? 1 : (($n < 0 || $n > 10) && ($n % 10 === 0) ? 2 : 3));
            case 'id':
            case 'ja':
            case 'jbo':
            case 'th':
                return 0;
            case 'lt':
                return $n % 10 === 1 && $n % 100 !== 11 ? 0 : (($n % 10 >= 2 && $n % 100 < 10 || $n % 100 >= 20) ? 1 : 2);
            case 'pl':
                return $n === 1 ? 0 : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? 1 : 2);
            case 'ro':
                return $n === 1 ? 0 : (($n === 0 || ($n % 100 > 0 && $n % 100 < 20)) ? 1 : 2);
            case 'ru':
            case 'uk':
                return $n % 10 === 1 && $n % 100 !== 11 ? 0 : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? 1 : 2);
            case 'sl':
                return $n % 100 === 1 ? 1 : ($n % 100 === 2 ? 2 : ($n % 100 === 3 || $n % 100 === 4 ? 3 : 0));
            default:
                // bg, ca, de, el, en, es, et, fi, hu, it, nl, no, pt, sv
                return $n !== 1 ? 1 : 0;
        }
    }

    /**
     * Compara los idiomas aceptados por el navegador con los disponibles
     * y retorna la mejor coincidencia
     *
     * Usa un sistema de puntuacion que considera:
     * - La calidad (q) del idioma aceptado
     * - El grado de coincidencia entre idiomas (ej: 'es-AR' coincide parcialmente con 'es')
     *
     * Si no hay coincidencia, retorna el idioma de respaldo configurado.
     *
     * @param  array $acceptedLanguages   Idiomas aceptados (del navegador)
     * @param  array $availableLanguages  Idiomas disponibles (archivos JSON)
     * @return string Codigo del mejor idioma coincidente
     */
    protected static function _getMatchingLanguage($acceptedLanguages, $availableLanguages)
    {
        $matches = array();
        $any     = false;
        foreach ($acceptedLanguages as $acceptedQuality => $acceptedValues) {
            $acceptedQuality = floatval($acceptedQuality);
            if ($acceptedQuality === 0.0) {
                continue;
            }
            foreach ($availableLanguages as $availableValue) {
                $availableQuality = 1.0;
                foreach ($acceptedValues as $acceptedValue) {
                    if ($acceptedValue === '*') {
                        $any = true;
                    }
                    $matchingGrade = self::_matchLanguage($acceptedValue, $availableValue);
                    if ($matchingGrade > 0) {
                        $q = (string) ($acceptedQuality * $availableQuality * $matchingGrade);
                        if (!isset($matches[$q])) {
                            $matches[$q] = array();
                        }
                        if (!in_array($availableValue, $matches[$q])) {
                            $matches[$q][] = $availableValue;
                        }
                    }
                }
            }
        }
        // Si el navegador acepta cualquier idioma (*), usar los disponibles
        if (count($matches) === 0 && $any) {
            if (count($availableLanguages) > 0) {
                $matches['1.0'] = $availableLanguages;
            }
        }
        if (count($matches) === 0) {
            return self::$_languageFallback;
        }
        // Retornar el idioma con mayor puntuacion
        krsort($matches);
        $topmatches = current($matches);
        return current($topmatches);
    }

    /**
     * Compara dos identificadores de idioma y retorna el grado de coincidencia
     *
     * Compara segmento por segmento (separados por '-').
     * Ejemplo: 'es-AR' vs 'es' => 0.5 (coincide 1 de 2 segmentos)
     *          'es' vs 'es' => 1.0 (coincidencia total)
     *          'en' vs 'es' => 0.0 (sin coincidencia)
     *
     * @param  string $a Primer identificador de idioma
     * @param  string $b Segundo identificador de idioma
     * @return float Grado de coincidencia (0.0 a 1.0)
     */
    protected static function _matchLanguage($a, $b)
    {
        $a = explode('-', $a);
        $b = explode('-', $b);
        for ($i = 0, $n = min(count($a), count($b)); $i < $n; ++$i) {
            if ($a[$i] !== $b[$i]) {
                break;
            }
        }
        return $i === 0 ? 0 : (float) $i / count($a);
    }
}

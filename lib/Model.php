<?php declare(strict_types=1);
/**
 * Fabrica de Modelos de Zbin
 *
 * Este archivo implementa el patron Factory para crear instancias de los
 * modelos de datos (Paste). Tambien gestiona la conexion al backend de
 * almacenamiento y la purga automatica de documentos expirados.
 *
 * Es el intermediario entre el controlador y el almacenamiento de datos.
 * El controlador nunca accede directamente al almacenamiento; siempre
 * lo hace a traves de esta clase.
 *
 * 
 */

namespace Zbin;

use Zbin\Model\Paste;
use Zbin\Persistence\PurgeLimiter;

/**
 * Modelo
 *
 * Fabrica de instancias de modelos de Zbin.
 * Centraliza la creacion de objetos Paste y el acceso al almacenamiento.
 */
class Model
{
    /**
     * Instancia de la configuracion
     *
     * @var Configuration
     */
    private $_conf;

    /**
     * Backend de almacenamiento (Filesystem, Database, etc.)
     * Se inicializa de forma perezosa (lazy) la primera vez que se necesita.
     *
     * @var Data\AbstractData
     */
    private $_store = null;

    /**
     * Constructor - Recibe la configuracion para saber que backend usar
     *
     * @param Configuration $conf Configuracion de la aplicacion
     */
    public function __construct(Configuration $conf)
    {
        $this->_conf = $conf;
    }

    /**
     * Obtiene una instancia de Paste, opcionalmente con un ID especifico
     *
     * Si se proporciona un ID, se crea un objeto Paste vinculado a ese documento.
     * Si no se proporciona, se crea un Paste nuevo (el ID se generara al almacenarlo).
     *
     * @param  string|null $pasteId ID del paste (null para crear uno nuevo)
     * @return Paste Instancia del modelo de paste
     */
    public function getPaste($pasteId = null)
    {
        $paste = new Paste($this->_conf, $this->getStore());
        if ($pasteId !== null) {
            $paste->setId($pasteId);
        }
        return $paste;
    }

    /**
     * Verifica si es necesario purgar documentos expirados y lo ejecuta
     *
     * La purga no se ejecuta en cada solicitud para evitar sobrecarga.
     * PurgeLimiter controla la frecuencia usando la configuracion [purge].
     * Se procesan en lotes (batchsize) para no bloquear la aplicacion.
     */
    public function purge()
    {
        PurgeLimiter::setConfiguration($this->_conf);
        PurgeLimiter::setStore($this->getStore());
        if (PurgeLimiter::canPurge()) {
            $this->getStore()->purge($this->_conf->getKey('batchsize', 'purge'));
        }
    }

    /**
     * Obtiene (o crea si no existe) el objeto de almacenamiento
     *
     * Usa inicializacion perezosa: la primera vez crea la instancia
     * del backend configurado (ej: Zbin\Data\Filesystem o Zbin\Data\Database).
     * Las siguientes llamadas reutilizan la misma instancia.
     *
     * @return Data\AbstractData Backend de almacenamiento
     */
    public function getStore()
    {
        if ($this->_store === null) {
            // Construir el nombre de clase completo del backend (ej: Zbin\Data\Filesystem)
            $class        = 'Zbin\\Data\\' . $this->_conf->getKey('class', 'model');
            $this->_store = new $class($this->_conf->getSection('model_options'));
        }
        return $this->_store;
    }
}

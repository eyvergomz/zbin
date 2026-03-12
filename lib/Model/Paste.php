<?php declare(strict_types=1);
/**
 * Modelo de Paste de Zbin
 *
 * Este archivo define la logica de negocio para los documentos (pastes).
 * Un "paste" es un documento de texto cifrado que se almacena temporalmente.
 *
 * Responsabilidades:
 * - Leer y validar datos del paste
 * - Verificar expiracion y "burn after reading" (destruir despues de leer)
 * - Generar tokens de eliminacion seguros (HMAC-SHA256)
 * - Gestionar los comentarios asociados al paste
 * - Validar las opciones del paste contra la configuracion del servidor
 *
 * Estructura de datos autenticados (adata):
 *   [0] = datos adicionales (no documentados)
 *   [1] = formato del texto (plaintext, syntaxhighlighting, markdown)
 *   [2] = flag de discusion abierta (0 o 1)
 *   [3] = flag de destruir despues de leer (0 o 1)
 *
 * 
 */

namespace Zbin\Model;

use Zbin\Controller;
use Zbin\Exception\TranslatedException;
use Zbin\Persistence\ServerSalt;

/**
 * Paste
 *
 * Modelo de un documento Zbin. Hereda de AbstractModel que proporciona
 * funcionalidad comun como setId(), getId(), setData().
 */
class Paste extends AbstractModel
{
    /**
     * Indice del formato en el array de datos autenticados
     * Valores posibles: 'plaintext', 'syntaxhighlighting', 'markdown'
     *
     * @const int
     */
    const ADATA_FORMATTER = 1;

    /**
     * Indice del flag de discusion abierta en el array de datos autenticados
     * 0 = discusion deshabilitada, 1 = discusion habilitada
     *
     * @const int
     */
    const ADATA_OPEN_DISCUSSION = 2;

    /**
     * Indice del flag "destruir despues de leer" en el array de datos autenticados
     * 0 = deshabilitado, 1 = el paste se elimina al ser leido una vez
     *
     * @const int
     */
    const ADATA_BURN_AFTER_READING = 3;

    /**
     * Obtiene los datos completos del paste
     *
     * Este metodo realiza varias verificaciones importantes:
     * 1. Lee los datos del almacenamiento
     * 2. Verifica si el paste ha expirado (y lo elimina si es asi)
     * 3. Calcula el tiempo restante antes de la expiracion
     * 4. Si es "burn after reading", elimina el paste despues de leerlo
     * 5. Carga todos los comentarios asociados
     *
     * @throws TranslatedException Si el paste no existe o ha expirado
     * @return array Datos completos del paste incluyendo comentarios
     */
    public function get()
    {
        $data = $this->_store->read($this->getId());
        if ($data === false) {
            throw new TranslatedException(Controller::GENERIC_ERROR, 64);
        }

        // Verificar si el paste ha expirado
        if (array_key_exists('expire_date', $data['meta'])) {
            $now = time();
            if ($data['meta']['expire_date'] < $now) {
                // El paste ha expirado: eliminarlo y lanzar error
                $this->delete();
                throw new TranslatedException(Controller::GENERIC_ERROR, 63);
            }
            // Informar al cliente cuanto tiempo queda antes de la expiracion
            $data['meta']['time_to_live'] = $data['meta']['expire_date'] - $now;
            unset($data['meta']['expire_date']);
        }
        // Eliminar timestamp de creacion de la respuesta (informacion interna)
        if (array_key_exists('created', $data['meta'])) {
            unset($data['meta']['created']);
        }

        // Si es "burn after reading", eliminar el paste despues de entregarlo
        if (($data['adata'][self::ADATA_BURN_AFTER_READING] ?? 0) === 1) {
            $this->delete();
        }

        // Agregar comentarios y metadatos de discusion
        $data['comments']       = array_values($this->getComments());
        $data['comment_count']  = count($data['comments']);
        $data['comment_offset'] = 0;
        $data['@context']       = '?jsonld=paste';
        $this->_data            = $data;

        return $this->_data;
    }

    /**
     * Almacena un nuevo paste
     *
     * Genera un salt unico para el paste (usado en el token de eliminacion)
     * y lo guarda en el backend de almacenamiento.
     *
     * @throws TranslatedException Si ya existe un paste con el mismo ID (colision)
     */
    public function store()
    {
        // Verificar colision (improbable pero posible con IDs aleatorios)
        if ($this->exists()) {
            throw new TranslatedException(self::COLLISION_ERROR, 75);
        }

        // Generar salt unico para este paste (se usa para el token de eliminacion)
        $this->_data['meta']['salt'] = ServerSalt::generate();

        // Almacenar en el backend configurado
        if (
            $this->_store->create(
                $this->getId(),
                $this->_data
            ) === false
        ) {
            throw new TranslatedException('Error saving document. Sorry.', 76);
        }
    }

    /**
     * Elimina el paste y todos sus comentarios del almacenamiento
     */
    public function delete()
    {
        $this->_store->delete($this->getId());
    }

    /**
     * Verifica si el paste existe en el almacenamiento
     *
     * @return bool true si existe
     */
    public function exists()
    {
        return $this->_store->exists($this->getId());
    }

    /**
     * Obtiene un comentario asociado a este paste
     *
     * @param  string $parentId  ID del comentario padre
     * @param  string $commentId ID del comentario (vacio para crear uno nuevo)
     * @throws TranslatedException Si el paste no existe
     * @return Comment Instancia del modelo de comentario
     */
    public function getComment($parentId, $commentId = '')
    {
        if (!$this->exists()) {
            throw new TranslatedException(self::INVALID_DATA_ERROR, 62);
        }
        $comment = new Comment($this->_conf, $this->_store);
        $comment->setPaste($this);
        $comment->setParentId($parentId);
        if (!empty($commentId)) {
            $comment->setId($commentId);
        }
        return $comment;
    }

    /**
     * Obtiene todos los comentarios del paste
     *
     * Si la configuracion tiene deshabilitada la visualizacion de fechas
     * en discusiones, elimina el timestamp de creacion de cada comentario.
     *
     * @return array Lista de comentarios
     */
    public function getComments()
    {
        if ($this->_conf->getKey('discussiondatedisplay')) {
            return $this->_store->readComments($this->getId());
        }
        // Eliminar fechas de creacion si la configuracion lo indica
        return array_map(function ($comment) {
            if (array_key_exists('created', $comment['meta'])) {
                unset($comment['meta']['created']);
            }
            return $comment;
        }, $this->_store->readComments($this->getId()));
    }

    /**
     * Genera el token de eliminacion del paste
     *
     * El token es un HMAC-SHA256 del ID del paste firmado con el salt unico
     * del paste. Solo quien conoce este token puede eliminar el documento.
     *
     * Se genera al crear el paste y se devuelve al usuario en la respuesta.
     * El usuario lo necesita para eliminar el paste manualmente.
     *
     * @return string Token de eliminacion (hash hexadecimal)
     */
    public function getDeleteToken()
    {
        if (!array_key_exists('salt', $this->_data['meta'])) {
            $this->get();
        }
        return hash_hmac('sha256', $this->getId(), $this->_data['meta']['salt']);
    }

    /**
     * Verifica si el paste tiene discusion abierta habilitada
     *
     * @return bool true si la discusion esta habilitada
     */
    public function isOpendiscussion()
    {
        if (!array_key_exists('adata', $this->_data) && !array_key_exists('data', $this->_data)) {
            $this->get();
        }
        return ($this->_data['adata'][self::ADATA_OPEN_DISCUSSION] ?? 0) === 1;
    }

    /**
     * Sanitiza los datos del paste segun la configuracion actual
     *
     * Calcula la fecha de expiracion absoluta sumando los segundos
     * configurados al timestamp actual. Si la expiracion es 0 (nunca),
     * no se establece fecha de expiracion.
     *
     * @param array $data Datos del paste a sanitizar (se modifica por referencia)
     */
    protected function _sanitize(array &$data)
    {
        $expiration = $data['meta']['expire'] ?? 0;
        unset($data['meta']['expire']);
        // Obtener los segundos de expiracion segun la opcion seleccionada
        $expire_options = $this->_conf->getSection('expire_options');
        $expire = $expire_options[$expiration] ??
            $this->_conf->getKey($this->_conf->getKey('default', 'expire'), 'expire_options');
        if ($expire > 0) {
            $data['meta']['expire_date'] = time() + $expire;
        }
    }

    /**
     * Valida los datos del paste contra las reglas de negocio
     *
     * Reglas de validacion:
     * 1. El formato debe ser uno de los habilitados en la configuracion
     * 2. No se puede habilitar discusion si esta deshabilitada en la config
     * 3. No se puede habilitar discusion y "burn after reading" al mismo tiempo
     * 4. Los flags de discusion y "burn after reading" deben ser 0 o 1
     *
     * @param  array $data Datos del paste a validar
     * @throws TranslatedException Si alguna validacion falla
     */
    protected function _validate(array &$data)
    {
        // Verificar que el formato sea uno de los habilitados
        if (!array_key_exists($data['adata'][self::ADATA_FORMATTER], $this->_conf->getSection('formatter_options'))) {
            throw new TranslatedException(self::INVALID_DATA_ERROR, 75);
        }

        // Validar combinacion de discusion y burn-after-reading
        if (
            ($data['adata'][self::ADATA_OPEN_DISCUSSION] === 1 && (
                !$this->_conf->getKey('discussion') ||
                $data['adata'][self::ADATA_BURN_AFTER_READING] === 1
            )) ||
            ($data['adata'][self::ADATA_OPEN_DISCUSSION] !== 0 && $data['adata'][self::ADATA_OPEN_DISCUSSION] !== 1)
        ) {
            throw new TranslatedException(self::INVALID_DATA_ERROR, 74);
        }

        // Validar que burn-after-reading sea un valor valido (0 o 1)
        if (
            $data['adata'][self::ADATA_BURN_AFTER_READING] !== 0 &&
            $data['adata'][self::ADATA_BURN_AFTER_READING] !== 1
        ) {
            throw new TranslatedException(self::INVALID_DATA_ERROR, 73);
        }
    }
}

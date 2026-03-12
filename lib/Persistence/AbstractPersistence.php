<?php declare(strict_types=1);
/**
 * Zbin
 *
 * a zero-knowledge paste bin
 *


 * 
 */

namespace Zbin\Persistence;

use Zbin\Data\AbstractData;

/**
 * AbstractPersistence
 *
 * persists data in PHP files
 */
abstract class AbstractPersistence
{
    /**
     * data storage to use to persist something
     *
     * @access private
     * @static
     * @var AbstractData
     */
    protected static $_store;

    /**
     * set the path
     *
     * @access public
     * @static
     * @param  AbstractData $store
     */
    public static function setStore(AbstractData $store)
    {
        self::$_store = $store;
    }
}

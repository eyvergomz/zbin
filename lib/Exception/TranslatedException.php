<?php declare(strict_types=1);
/**
 * Zbin
 *
 * a zero-knowledge paste bin
 *


 * 
 */

namespace Zbin\Exception;

use Exception;
use Zbin\I18n;

/**
 * TranslatedException
 *
 * An Exception that translates it's message.
 */
class TranslatedException extends Exception
{
    /**
     * Translating exception constructor with mandatory messageId.
     *
     * @access public
     * @param  string|array $messageId message ID or array of message ID and parameters
     * @param  int $code
     */
    public function __construct($messageId, int $code = 0)
    {
        $message = is_string($messageId) ? I18n::translate($messageId) : forward_static_call_array('Zbin\I18n::translate', $messageId);
        parent::__construct($message, $code);
    }
}

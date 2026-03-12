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

/**
 * JsonException
 *
 * An Exception representing JSON en- or decoding errors.
 */
class JsonException extends Exception
{
    /**
     * Exception constructor with mandatory JSON error code.
     *
     * @access public
     * @param  int $code
     */
    public function __construct(int $code)
    {
        $message = 'A JSON error occurred';
        if (function_exists('json_last_error_msg')) {
            $message .= ': ' . json_last_error_msg();
        }
        $message .= ' (' . $code . ')';
        parent::__construct($message, 90);
    }
}

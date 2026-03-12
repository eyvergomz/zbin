<?php declare(strict_types=1);
/**
 * Zbin
 *
 * a zero-knowledge paste bin
 *


 * 
 */

namespace Zbin\Proxy;

use Zbin\Configuration;

/**
 * YourlsProxy
 *
 * Forwards a URL for shortening to YOURLS (your own URL shortener) and stores
 * the result.
 */
class YourlsProxy extends AbstractProxy
{
    /**
     * Overrides the abstract parent function to get the proxy URL.
     *
     * @param Configuration $conf
     * @return string
     */
    protected function _getProxyUrl(Configuration $conf): string
    {
        return $conf->getKey('apiurl', 'yourls');
    }

    /**
     * Overrides the abstract parent function to get contents from YOURLS API.
     *
     * @access protected
     * @param Configuration $conf
     * @param string $link
     * @return array
     */
    protected function _getProxyPayload(Configuration $conf, string $link): array
    {
        return array(
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query(
                array(
                    'signature' => $conf->getKey('signature', 'yourls'),
                    'format'    => 'json',
                    'action'    => 'shorturl',
                    'url'       => $link,
                )
            ),
        );
    }

    /**
     * Extracts the short URL from the YOURLS API response.
     *
     * @access protected
     * @param array $data
     * @return ?string
     */
    protected function _extractShortUrl(array $data): ?string
    {
        if (($data['statusCode'] ?? 0) === 200) {
            return $data['shorturl'] ?? 0;
        }
        return null;
    }
}

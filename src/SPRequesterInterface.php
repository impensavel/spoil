<?php
/**
 * This file is part of the SPOIL library.
 *
 * @author     Quetzy Garcia <quetzyg@impensavel.com>
 * @copyright  2014-2016
 *
 * For the full copyright and license information,
 * please view the LICENSE.md file that was distributed
 * with this source code.
 */

namespace Impensavel\Spoil;

use Impensavel\Spoil\Exception\SPRuntimeException;

interface SPRequesterInterface
{
    /**
     * Send an HTTP request
     *
     * @access  public
     * @param   string $url     URL to make the request to
     * @param   array  $options HTTP client options
     * @param   string $method  HTTP method name (GET, POST, PUT, DELETE, ...)
     * @param   bool   $json    Return JSON if true, return Response object otherwise
     * @throws  SPRuntimeException
     * @return  \Psr\Http\Message\ResponseInterface|array
     */
    public function request($url, array $options = [], $method = 'GET', $json = true);

    /**
     * Get the current SharePoint Access Token
     *
     * @access  public
     * @throws  SPRuntimeException
     * @return  SPAccessToken
     */
    public function getSPAccessToken();

    /**
     * Get the current SharePoint Form Digest
     *
     * @access  public
     * @throws  SPRuntimeException
     * @return  SPFormDigest
     */
    public function getSPFormDigest();
}

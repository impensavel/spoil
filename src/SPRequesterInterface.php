<?php
/**
 * This file is part of the SPOIL library.
 *
 * @author     Quetzy Garcia <quetzyg@impensavel.com>
 * @copyright  2014-2015
 *
 * For the full copyright and license information,
 * please view the LICENSE.md file that was distributed
 * with this source code.
 */

namespace Impensavel\Spoil;

interface SPRequesterInterface
{
    /**
     * Send an HTTP request
     *
     * @access  public
     * @param   string $url     URL to make the request to
     * @param   array  $options HTTP client options (see GuzzleHttp\Client options)
     * @param   string $method  HTTP method name (GET, POST, PUT, DELETE, ...)
     * @param   bool   $json    Return JSON if true, return Response object otherwise
     * @throws  SPException
     * @return  \GuzzleHttp\Message\Response|array
     */
    public function request($url, array $options = [], $method = 'GET', $json = true);

    /**
     * Get the current SharePoint Access Token
     *
     * @access  public
     * @throws  SPException
     * @return  SPAccessToken
     */
    public function getSPAccessToken();

    /**
     * Get the current SharePoint Form Digest
     *
     * @access  public
     * @throws  SPException
     * @return  SPFormDigest
     */
    public function getSPFormDigest();
}

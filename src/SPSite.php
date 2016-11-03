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

use Http\Client\Exception as HttpClientException;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Psr\Http\Message\ResponseInterface;

use Impensavel\Spoil\Exception\SPInvalidArgumentException;
use Impensavel\Spoil\Exception\SPObjectNotFoundException;
use Impensavel\Spoil\Exception\SPRuntimeException;

class SPSite implements SPRequesterInterface
{
    /**
     * Azure Access Control System URL
     *
     * @var string
     */
    const ACS = 'https://accounts.accesscontrol.windows.net/tokens/OAuth/2';

    /**
     * HTTP client
     *
     * @var  \Http\Client\HttpClient
     */
    protected $client;

    /**
     * Message factory
     *
     * @var  \Http\Message\MessageFactory
     */
    protected $message;

    /**
     * Access Token
     *
     * @var  SPAccessToken
     */
    protected $token;

    /**
     * Context Info
     *
     * @var  SPContextInfo
     */
    protected $contextInfo;

    /**
     * Site Hostname
     *
     * @var  string
     */
    protected $hostname;

    /**
     * Site Path
     *
     * @var  string
     */
    protected $path;

    /**
     * Site Configuration
     *
     * @var  array
     */
    protected $config = [];

    /**
     * SharePoint Site constructor
     *
     * @param   string                       $url     SharePoint Site URL
     * @param   array                        $config  SharePoint Site configuration
     * @param   \Http\Client\HttpClient      $client  HTTP client
     * @param   \Http\Message\MessageFactory $message Message factory
     * @throws  SPInvalidArgumentException
     */
    public function __construct($url, array $config, HttpClient $client, MessageFactory $message)
    {
        $this->config = array_replace_recursive([
            'acs' => static::ACS,
        ], $config);

        // Set HTTP client and Message factory
        $this->client = $client;
        $this->message = $message;

        // Ensure the URL has a trailing slash
        $url = sprintf('%s/', rtrim($url, '/'));

        // Set Site hostname and path
        $components = parse_url($url);

        if (! isset($components['scheme'], $components['host'], $components['path'])) {
            throw new SPInvalidArgumentException('The SharePoint Site URL is invalid');
        }

        $this->hostname = $components['scheme'].'://'.$components['host'];
        $this->path = rtrim($components['path'], '/');
    }

    /**
     * Get the SharePoint Site configuration
     *
     * @return  array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get SharePoint Site Hostname
     *
     * @param   string $path Path to append
     * @return  string
     */
    public function getHostname($path = null)
    {
        return sprintf('%s/%s', $this->hostname, ltrim($path, '/'));
    }

    /**
     * Get SharePoint Site Path
     *
     * @param   string $path Path to append
     * @return  string
     */
    public function getPath($path = null)
    {
        return sprintf('%s/%s', $this->path, ltrim($path, '/'));
    }

    /**
     * Get SharePoint Site URL
     *
     * @param   string $path Path to append
     * @return  string
     */
    public function getUrl($path = null)
    {
        return $this->getHostname($this->getPath($path));
    }

    /**
     * Get the SharePoint Site logout URL
     *
     * @return  string
     */
    public function getLogoutUrl()
    {
        return $this->getUrl('_layouts/SignOut.aspx');
    }

    /**
     * Parse the SharePoint API response
     *
     * @param   \Psr\Http\Message\ResponseInterface $response
     * @throws  SPObjectNotFoundException|SPRuntimeException
     * @return  array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $code = $response->getStatusCode();
        $json = json_decode($response->getBody(), true);

        if ($code >= 400) {
            $message = 'The SharePoint API failed to provide an error message!';

            // If the response body can't be parsed as JSON,
            // it will be used as the error message itself
            if ($json === null) {
                $message = $response->getBody()->getContents();
            }

            // Error message assignment
            if (isset($json['error'])) {
                $message = $json['error'];
            }

            if (isset($json['error_description'])) {
                $message = $json['error_description'];
            }

            if (isset($json['odata.error'])) {
                $message = $json['odata.error'];
            }

            if (isset($json['odata.error']['message']['value'])) {
                $message = $json['odata.error']['message']['value'];
            }

            if ($code == 404) {
                throw new SPObjectNotFoundException($message, $code);
            }

            throw new SPRuntimeException($message, $code);
        }

        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function request($url, array $options = [], $method = 'GET', $json = true)
    {
        try {
            $options = array_replace([
                'body'    => null,
                'headers' => [],
                'query'   => [],
            ], $options);

            // Prepend the SharePoint Site URL when only the path is passed
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $url = $this->getUrl($url);
            }

            if (! empty($options['query'])) {
                $url = sprintf('%s?%s', $url, http_build_query($options['query']));
            }

            $request = $this->message->createRequest($method, $url, $options['headers'], $options['body']);
            $response = $this->client->sendRequest($request);

            return $json ? $this->parseResponse($response) : $response;

        } catch (HttpClientException $e) {
            throw new SPRuntimeException('Unable to make HTTP request', 0, $e);
        }
    }

    /**
     * Create SharePoint Access Token
     *
     * @param   string $contextToken SharePoint Context Token
     * @param   array  $extra        Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPSite
     */
    public function createSPAccessToken($contextToken = null, $extra = [])
    {
        if (empty($contextToken)) {
            $this->token = SPAccessToken::createAppOnlyPolicy($this, $extra);
        } else {
            $this->token = SPAccessToken::createUserOnlyPolicy($this, $contextToken, $extra);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSPAccessToken()
    {
        if (! $this->token instanceof SPAccessToken) {
            throw new SPRuntimeException('Invalid SharePoint Access Token');
        }

        if ($this->token->hasExpired()) {
            throw new SPRuntimeException('Expired SharePoint Access Token');
        }

        return $this->token;
    }

    /**
     * Set the SharePoint Access Token
     *
     * @param   SPAccessToken $token SharePoint Access Token
     * @throws  SPRuntimeException
     * @return  void
     */
    public function setSPAccessToken(SPAccessToken $token)
    {
        if ($token->hasExpired()) {
            throw new SPRuntimeException('Expired SharePoint Access Token');
        }

        $this->token = $token;
    }

    /**
     * Create a SharePoint Context Info
     *
     * @param   array $extra Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPSite
     */
    public function createSPContextInfo($extra = [])
    {
        $this->contextInfo = SPContextInfo::create($this, $extra);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSPContextInfo()
    {
        if (! $this->contextInfo instanceof SPContextInfo) {
            throw new SPRuntimeException('Invalid SharePoint Context Info');
        }

        if ($this->contextInfo->formDigestHasExpired()) {
            throw new SPRuntimeException('SharePoint Context Info with expired Form Digest');
        }

        return $this->contextInfo;
    }

    /**
     * Set the SharePoint Context Info
     *
     * @param   SPContextInfo $contextInfo SharePoint Context Info
     * @throws  SPRuntimeException
     * @return  void
     */
    public function setSPContextInfo(SPContextInfo $contextInfo)
    {
        if ($contextInfo->formDigestHasExpired()) {
            throw new SPRuntimeException('SharePoint Context Info with expired Form Digest');
        }

        $this->contextInfo = $contextInfo;
    }
}

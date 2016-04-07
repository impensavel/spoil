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
     * @access  protected
     * @var     \Http\Client\HttpClient
     */
    protected $client;

    /**
     * Message factory
     *
     * @access  protected
     * @var     \Http\Message\MessageFactory
     */
    protected $message;

    /**
     * Access Token
     *
     * @access  protected
     * @var     SPAccessToken
     */
    protected $token;

    /**
     * Form Digest
     *
     * @access  protected
     * @var     SPFormDigest
     */
    protected $digest;

    /**
     * Site Hostname
     *
     * @access  protected
     * @var     string
     */
    protected $hostname;

    /**
     * Site Path
     *
     * @access  protected
     * @var     string
     */
    protected $path;

    /**
     * Site Configuration
     *
     * @access  protected
     * @var     array
     */
    protected $config = [];

    /**
     * SharePoint Site constructor
     *
     * @access  public
     * @param   string                       $url     SharePoint Site URL
     * @param   array                        $config  SharePoint Site configuration
     * @param   \Http\Client\HttpClient      $client  HTTP client
     * @param   \Http\Message\MessageFactory $message Message factory
     * @throws  SPInvalidArgumentException
     * @return  SPSite
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
     * @access  public
     * @return  array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get SharePoint Site Hostname
     *
     * @access  public
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
     * @access  public
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
     * @access  public
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
     * @access  public
     * @return  string
     */
    public function getLogoutUrl()
    {
        return $this->getUrl('_layouts/SignOut.aspx');
    }

    /**
     * Create a SharePoint Site
     *
     * @static
     * @access  public
     * @param   string $url      SharePoint Site URL
     * @param   array  $settings Instantiation settings
     * @throws  SPRuntimeException
     * @return  SPSite
     */
    public static function create($url, array $settings)
    {
        $settings = array_replace_recursive([
            // HTTP client class
            'client'  => '\Http\Adapter\Guzzle6\Client',

            // HTTP message factory class
            'message' => '\Http\Message\MessageFactory\GuzzleMessageFactory',
        ], $settings, [
            // SharePoint Site configuration
            'site' => [],
        ]);

        foreach (['client', 'message'] as $class) {
            if (! class_exists($settings[$class])) {
                throw new SPRuntimeException(sprintf('Class "%s" not found', $settings[$class]));
            }
        }

        return new static($url, $settings['site'], new $settings['client'], new $settings['message']);
    }

    /**
     * Parse the SharePoint API response
     *
     * @access  protected
     * @param   \Psr\Http\Message\ResponseInterface $response
     * @throws  SPObjectNotFoundException|SPRuntimeException
     * @return  array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $code = $response->getStatusCode();
        $json = json_decode($response->getBody(), true);

        if ($code >= 400) {
            $message = null;

            // If the response body cannot be parsed as JSON,
            // the body will be used as the error message
            if (json_last_error() !== JSON_ERROR_NONE) {
                $message = $response->getBody();
            } else {
                if (isset($json['odata.error']['message']['value']) && $message === null) {
                    $message = $json['odata.error']['message']['value'];
                }

                if (isset($json['error_description']) && $message === null) {
                    $message = $json['error_description'];
                }

                if (isset($json['odata.error']) && $message === null) {
                    $message = $json['odata.error'];
                }

                if (isset($json['error']) && $message === null) {
                    $message = $json['error'];
                }
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

            $url = filter_var($url, FILTER_VALIDATE_URL) === false ? $this->getUrl($url) : $url;

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
     * @access  public
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
     * @access  public
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
     * Create a SharePoint Form Digest
     *
     * @access  public
     * @param   array  $extra Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPSite
     */
    public function createSPFormDigest($extra = [])
    {
        $this->digest = SPFormDigest::create($this, $extra);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSPFormDigest()
    {
        if (! $this->digest instanceof SPFormDigest) {
            throw new SPRuntimeException('Invalid SharePoint Form Digest');
        }

        if ($this->digest->hasExpired()) {
            throw new SPRuntimeException('Expired SharePoint Form Digest');
        }

        return $this->digest;
    }

    /**
     * Set the SharePoint Form Digest
     *
     * @access  public
     * @param   SPFormDigest $digest SharePoint Form Digest
     * @throws  SPRuntimeException
     * @return  void
     */
    public function setSPFormDigest(SPFormDigest $digest)
    {
        if ($digest->hasExpired()) {
            throw new SPRuntimeException('Expired SharePoint Form Digest');
        }

        $this->digest = $digest;
    }
}

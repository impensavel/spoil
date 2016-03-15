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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;

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
     * HTTP Client object
     *
     * @access  protected
     * @var     \GuzzleHttp\Client
     */
    protected $http;

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
     * @param   \GuzzleHttp\Client $http   Guzzle HTTP client
     * @param   array              $config SharePoint Site configuration
     * @throws  SPInvalidArgumentException
     * @return  SPSite
     */
    public function __construct(Client $http, array $config)
    {
        $this->config = array_replace_recursive([
            'acs' => static::ACS,
        ], $config);

        // Set Guzzle HTTP client
        $this->http = $http;

        // Set Site Hostname and Path
        $components = parse_url($this->http->getBaseUrl());

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
     * @return  SPSite
     */
    public static function create($url, array $settings = [])
    {
        // Ensure we have a trailing slash
        if (is_string($url)) {
            $url = sprintf('%s/', rtrim($url, '/'));
        }

        $settings = array_replace_recursive($settings, [
            'site' => [], // SharePoint Site configuration
            'http' => [   // Guzzle HTTP Client configuration
                'base_url' => $url,
            ],
        ]);

        $http = new Client($settings['http']);

        return new static($http, $settings['site']);
    }

    /**
     * Check for errors in the SharePoint API response
     *
     * @access  protected
     * @param   \GuzzleHttp\Message\ResponseInterface $response
     * @throws  SPObjectNotFoundException|SPRuntimeException
     * @return  void
     */
    protected function checkSPErrors(ResponseInterface $response)
    {
        $code = $response->getStatusCode();

        if ($code >= 400) {
            $json = $response->json();

            $message = null;

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

            if ($message === null) {
                $message = $response->getBody();
            }

            switch ($code) {
                case 404:
                    throw new SPObjectNotFoundException($message, $code);

                default:
                    throw new SPRuntimeException($message, $code);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function request($url, array $options = [], $method = 'GET', $json = true)
    {
        try {
            $options = array_replace_recursive($options, [
                'exceptions' => false, // Avoid throwing exceptions when we get HTTP errors (4XX, 5XX)
            ]);

            $response = $this->http->send($this->http->createRequest($method, $url, $options));

            if ($json) {
                $this->checkSPErrors($response);

                return $response->json();
            }

            return $response;
        } catch (ParseException $e) {
            throw new SPRuntimeException('Could not parse response body as JSON', 0, $e);
        } catch (RequestException $e) {
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

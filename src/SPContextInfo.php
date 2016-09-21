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

use Serializable;

use Carbon\Carbon;

use Impensavel\Spoil\Exception\SPBadMethodCallException;
use Impensavel\Spoil\Exception\SPRuntimeException;

class SPContextInfo extends SPObject implements Serializable
{
    /**
     * Library version
     *
     * @var  string
     */
    protected $libraryVersion;

    /**
     * Supported REST/CSOM schema versions
     *
     * @var  array
     */
    protected $schemaVersions = [];

    /**
     * Form Digest
     *
     * @var  string
     */
    protected $formDigest;

    /**
     * Form Digest expiration date
     *
     * @var  \Carbon\Carbon
     */
    protected $formDigestExpiration;

    /**
     * {@inheritdoc}
     */
    protected function hydrate($data, $exceptions = true)
    {
        if (array_key_exists('FormDigestTimeoutSeconds', $data)) {
            $data['FormDigestTimeoutSeconds'] = Carbon::now()->addSeconds($data['FormDigestTimeoutSeconds']);
        }

        return parent::hydrate($data, $exceptions);
    }

    /**
     * SharePoint Context Info constructor
     *
     * @param   array $payload OData response payload
     * @param   array $extra   Extra payload values to map
     * @throws  SPBadMethodCallException
     */
    public function __construct(array $payload, array $extra = [])
    {
        $this->mapper = array_merge([
            'libraryVersion'       => 'LibraryVersion',
            'schemaVersions'       => 'SupportedSchemaVersions',
            'formDigest'           => 'FormDigestValue',
            'formDigestExpiration' => 'FormDigestTimeoutSeconds',
        ], $extra);

        $this->hydrate($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'library_version'        => $this->libraryVersion,
            'schema_versions'        => $this->schemaVersions,
            'form_digest'            => $this->formDigest,
            'form_digest_expiration' => $this->formDigestExpiration,
            'extra'                  => $this->extra,
        ];
    }

    /**
     * Serialize SharePoint Context Info
     *
     * @return  string
     */
    public function serialize()
    {
        return serialize([
            $this->libraryVersion,
            $this->schemaVersions,
            $this->formDigest,
            $this->formDigestExpiration->getTimestamp(),
            $this->formDigestExpiration->getTimezone()->getName(),
        ]);
    }

    /**
     * Recreate SharePoint Context Info
     *
     * @param   string $serialized
     * @return  void
     */
    public function unserialize($serialized)
    {
        list(
            $this->libraryVersion,
            $this->schemaVersions,
            $this->formDigest,
            $timestamp,
            $timezone
        ) = unserialize($serialized);

        $this->formDigestExpiration = Carbon::createFromTimeStamp($timestamp, $timezone);
    }

    /**
     * Create a SharePoint Context Info
     *
     * @param   SPSite $site  SharePoint Site
     * @param   array  $extra Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPContextInfo
     */
    public static function create(SPSite $site, array $extra = [])
    {
        $json = $site->request('_api/contextinfo', [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ], 'POST');

        return new static($json, $extra);
    }

    /**
     * Get the Library version
     *
     * @return  string
     */
    public function getLibraryVersion()
    {
        return $this->libraryVersion;
    }

    /**
     * Get the REST/CSOM schema versions
     *
     * @return  array
     */
    public function getSchemaVersions()
    {
        return $this->schemaVersions;
    }

    /**
     * Get the Form Digest string value
     *
     * @return  string
     */
    public function getFormDigest()
    {
        return $this->formDigest;
    }

    /**
     * Check if the Form Digest has expired
     *
     * @return  bool
     */
    public function formDigestHasExpired()
    {
        return $this->formDigestExpiration->isPast();
    }

    /**
     * Get the Form Digest expiration date
     *
     * @return  Carbon
     */
    public function formDigestExpirationDate()
    {
        return $this->formDigestExpiration;
    }
}

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

use Impensavel\Spoil\Exception\SPBadMethodCallException;
use Impensavel\Spoil\Exception\SPRuntimeException;

class SPFolder extends SPListObject implements SPItemInterface
{
    use SPTimestampsTrait;

    /**
     * System Folder names
     *
     * @var  array
     */
    public static $systemFolders = [
        'forms',
    ];

    /**
     * SharePoint List GUID
     *
     * @var  string
     */
    protected $listGUID;

    /**
     * SharePoint List Title
     *
     * @var  string
     */
    protected $listTitle;

    /**
     * Folder Name
     *
     * @var  string
     */
    protected $name;

    /**
     * SharePoint Folder constructor
     *
     * @param   SPSite $site     SharePoint Site
     * @param   array  $payload  OData response payload
     * @param   array  $settings Instantiation settings
     * @throws  SPBadMethodCallException|SPRuntimeException
     */
    public function __construct(SPSite $site, array $payload, array $settings = [])
    {
        $settings = array_replace_recursive([
            'fetch' => false, // Fetch SharePoint Items (Folders/Files)?
        ], $settings, [
            'extra' => [],    // Extra SharePoint Folder properties to map
            'items' => [],    // SharePoint Item instantiation settings
        ]);

        $this->mapper = array_merge([
            'spType'      => 'odata.type',
            'guid'        => 'UniqueId',
            'name'        => 'Name',
            'title'       => 'Name',
            'created'     => 'TimeCreated',
            'modified'    => 'TimeLastModified',
            'relativeUrl' => 'ServerRelativeUrl',
            'itemCount'   => 'ItemCount',

            // Only available in sub Folders
            'listGUID'    => 'ListItemAllFields/ParentList/Id',

            // Only available in the root Folder
            'listTitle'   => 'Properties/vti_x005f_listtitle',
        ], $settings['extra']);

        $this->site = $site;

        $this->hydrate($payload);

        if ($settings['fetch'] && $this->itemCount > 0) {
            $this->getSPItems($settings['items']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'sp_type'      => $this->spType,
            'guid'         => $this->guid,
            'title'        => $this->title,
            'name'         => $this->name,
            'created'      => $this->created,
            'modified'     => $this->modified,
            'relative_url' => $this->relativeUrl,
            'item_count'   => $this->itemCount,
            'extra'        => $this->extra,
        ];
    }

    /**
     * Get SharePoint Name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Is this a SharePoint root Folder?
     *
     * @return  bool
     */
    public function isRootFolder()
    {
        return ($this->listTitle !== null);
    }

    /**
     * {@inheritdoc}
     */
     public function getUrl($path = null)
     {
         return $this->site->getHostname($this->getRelativeUrl($path));
     }

    /**
     * {@inheritdoc}
     */
    public function isWritable($exception = false)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function toSPList(array $settings = [])
    {
        if ($this->isRootFolder()) {
            return SPList::getByTitle($this->site, $this->listTitle, $settings);
        }

        return SPList::getByGUID($this->site, $this->listGUID, $settings);
    }

    /**
     * Check if a name matches a SharePoint System Folder
     *
     * @param   string $name SharePoint Folder name
     * @return  bool
     */
    public static function isSystemFolder($name)
    {
        $normalized = strtolower(basename($name));

        return in_array($normalized, static::$systemFolders);
    }

    /**
     * Get a SharePoint Folder by GUID
     *
     * @param   SPSite $site     SharePoint Site
     * @param   string $guid     SharePoint Folder GUID
     * @param   array  $settings Instantiation settings
     * @throws  SPRuntimeException
     * @return  SPFolder
     */
    public static function getByGUID(SPSite $site, $guid, array $settings = [])
    {
        $json = $site->request("_api/web/GetFolderById('".$guid."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields/ParentList,Properties',
            ],
        ]);

        return new static($site, $json, $settings);
    }

    /**
     * Get a SharePoint Folder by Relative URL
     *
     * @param   SPSite $site        SharePoint Site
     * @param   string $relativeUrl SharePoint Folder relative URL
     * @param   array  $settings    Instantiation settings
     * @throws  SPBadMethodCallException
     * @return  SPFolder
     */
    public static function getByRelativeUrl(SPSite $site, $relativeUrl, array $settings = [])
    {
        if (static::isSystemFolder($relativeUrl)) {
            throw new SPBadMethodCallException('Trying to get a SharePoint System Folder');
        }

        $json = $site->request("_api/web/GetFolderByServerRelativeUrl('".$relativeUrl."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields/ParentList,Properties',
            ],
        ]);

        return new static($site, $json, $settings);
    }

    /**
     * Get SubFolders of a SharePoint Folder
     *
     * @param   SPSite $site        SharePoint Site
     * @param   string $relativeUrl SharePoint Folder relative URL
     * @param   array  $settings    Instantiation settings
     * @throws  SPRuntimeException
     * @return  array
     */
    public static function getSubFolders(SPSite $site, $relativeUrl, array $settings = [])
    {
        $json = $site->request("_api/web/GetFolderByServerRelativeUrl('".$relativeUrl."')/Folders", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields/ParentList,Properties',
            ],
        ]);

        $folders = [];

        foreach ($json['value'] as $subFolder) {
            // Skip System Folders
            if (! static::isSystemFolder($subFolder['Name'])) {
                $folders[$subFolder['UniqueId']] = new static($site, $subFolder, $settings);
            }
        }

        return $folders;
    }

    /**
     * Create a SharePoint Folder
     *
     * @param   SPFolderInterface $folder   Parent SharePoint Folder
     * @param   array             $name     SharePoint Folder name
     * @param   array             $settings Instantiation settings
     * @throws  SPRuntimeException
     * @return  SPFolder
     */
    public static function create(SPFolderInterface $folder, $name, array $settings = [])
    {
        if (! $folder->isWritable()) {
            throw new SPRuntimeException(sprintf(
                'Folder/File operations are not allowed on a SPList Template Type [%s]',
                $folder->getTemplate()
            ));
        }

        $body = json_encode([
            'odata.type'        => 'SP.Folder',
            'ServerRelativeUrl' => $folder->getRelativeUrl($name),
        ]);

        $json = $folder->request('_api/web/Folders', [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => $folder->getSPContextInfo()->getFormDigest(),
                'Content-type'    => 'application/json',
                'Content-length'  => strlen($body),
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields/ParentList,Properties',
            ],

            'body'    => $body,
        ], 'POST');

        return new static($folder->getSPSite(), $json, $settings);
    }

    /**
     * Update a SharePoint Folder
     *
     * @param   array $properties SharePoint Folder properties (Name, ...)
     * @throws  SPRuntimeException
     * @return  SPFolder
     */
    public function update(array $properties)
    {
        $properties = array_replace_recursive($properties, [
            'odata.type' => 'SP.Folder',
        ]);

        $body = json_encode($properties);

        $this->request("_api/web/GetFolderByServerRelativeUrl('".$this->relativeUrl."')", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => $this->getSPContextInfo()->getFormDigest(),
                'X-HTTP-Method'   => 'MERGE',
                'IF-MATCH'        => '*',
                'Content-type'    => 'application/json',
                'Content-length'  => strlen($body),
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields/ParentList,Properties',
            ],

            'body'    => $body,
        ], 'POST');

        // Rehydration is done using the $properties array,
        // since the SharePoint API doesn't return a response
        // on a successful update
        return $this->hydrate($properties, true);
    }

    /**
     * Delete a SharePoint Folder
     *
     * @throws  SPRuntimeException
     * @return  bool
     */
    public function delete()
    {
        $this->request("_api/web/GetFolderByServerRelativeUrl('".$this->relativeUrl."')", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->getSPAccessToken(),
                'X-RequestDigest' => $this->getSPContextInfo()->getFormDigest(),
                'X-HTTP-Method'   => 'DELETE',
                'IF-MATCH'        => '*',
            ],
        ], 'POST');

        return true;
    }

    /**
     * Get the SharePoint Folder Item count (Folders and Files)
     *
     * @throws  SPRuntimeException
     * @return  int
     */
    public function getSPItemCount()
    {
        $json = $this->request("_api/web/GetFolderByServerRelativeUrl('".$this->relativeUrl."')/itemCount", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ]);

        return $this->itemCount = $json['ItemCount'];
    }

    /**
     * Get all SharePoint Items (Folders/Files)
     *
     * @param   array $settings Instantiation settings
     * @throws  SPRuntimeException
     * @return  array
     */
    public function getSPItems(array $settings = [])
    {
        $settings = array_replace_recursive($settings, [
            'folders' => [
                'extra' => [], // Extra SharePoint Folder properties to map
            ],

            'files' => [
                'extra' => [], // Extra SharePoint File properties to map
            ],
        ]);

        $folders = static::getSubFolders($this->site, $this->relativeUrl, $settings['folders']);
        $files = SPFile::getAll($this, $settings['files']['extra']);

        $this->items = array_merge($folders, $files);

        return $this->items;
    }
}

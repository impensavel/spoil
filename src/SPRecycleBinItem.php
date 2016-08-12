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

class SPRecycleBinItem extends SPObject
{
    use SPPropertiesTrait;

    /**
     * SharePoint Recycle Bin Item States (SharePoint 2013)
     *
     * @static
     * @link   https://msdn.microsoft.com/en-us/library/microsoft.sharepoint.client.recyclebinitemstate.aspx
     * @var    array
     */
    static $states = [
        0 => 'None',                  // Unspecified state
        1 => 'FirstStageRecycleBin',  // Item is in the User Recycle Bin (first stage)
        2 => 'SecondStageRecycleBin', // Item is in the Site Collection Recycle Bin (second stage)
    ];

    /**
     * SharePoint Recycle Bin Item Types (SharePoint 2013)
     *
     * @static
     * @link   https://msdn.microsoft.com/en-us/library/microsoft.sharepoint.client.recyclebinitemtype.aspx
     * @var    array
     */
    static $types = [
        0 => 'None',            // Unspecified type
        1 => 'File',            // File
        2 => 'FileVersion',     // Historical version of a File
        3 => 'ListItem',        // List Item
        4 => 'List',            // List
        5 => 'Folder',          // Folder
        6 => 'FolderWithLists', // Folder containing a List
        7 => 'Attachment',      // Attachment
        8 => 'ListItemVersion', // Historical version of a List Item
        9 => 'CascadeParent',   // Parent List Item of one or more List Items
    ];

    /**
     * SharePoint Site
     *
     * @access  protected
     * @var     SPSite
     */
    protected $site;

    /**
     * Item State
     *
     * @access  protected
     * @var     int
     */
    protected $state;

    /**
     * Item Type
     *
     * @access  protected
     * @var     int
     */
    protected $type;

    /**
     * Item Deletion Time
     *
     * @access  protected
     * @var     \Carbon\Carbon
     */
    protected $deleted;

    /**
     * Relative Directory
     *
     * @access  protected
     * @var     string
     */
    protected $relativeDir;

    /**
     * Item Name
     *
     * @access  protected
     * @var     string
     */
    protected $name;

    /**
     * Item Size
     *
     * @access  protected
     * @var     int
     */
    protected $size = 0;

    /**
     * Item Author
     *
     * @access  protected
     * @var     string
     */
    protected $author;

    /**
     * Item Deleter
     *
     * @access  protected
     * @var     string
     */
    protected $deleter;

    /**
     * SharePoint Recycle Bin Item constructor
     *
     * @access  public
     * @param   SPSite $site    SharePoint Site
     * @param   array  $payload OData response payload
     * @param   array  $extra   Extra properties to map
     * @throws  \Impensavel\Spoil\Exception\SPBadMethodCallException
     * @return  SPRecycleBinItem
     */
    public function __construct(SPSite $site, array $payload, array $extra = [])
    {
        $this->mapper = array_merge([
            'spType'      => 'odata.type',
            'state'       => 'ItemState',
            'type'        => 'ItemType',
            'guid'        => 'Id',
            'title'       => 'Title',
            'relativeDir' => 'DirName',
            'name'        => 'LeafName',
            'size'        => 'Size',
            'deleted'     => 'DeletedDate',
            'author'      => 'AuthorName',
            'deleter'     => 'DeletedByName',
        ], $extra);

        $this->site = $site;

        $this->hydrate($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'sp_type'      => $this->spType,
            'state'        => $this->state,
            'type'         => $this->type,
            'guid'         => $this->guid,
            'title'        => $this->title,
            'relative_dir' => $this->relativeDir,
            'name'         => $this->name,
            'size'         => $this->size,
            'deleted'      => $this->deleted,
            'author'       => $this->author,
            'deleter'      => $this->deleter,
            'extra'        => $this->extra,
        ];
    }

    /**
     * Get Item State
     *
     * @access  public
     * @return  int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get Item Type
     *
     * @access  public
     * @return  int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get Relative Directory
     *
     * @access  public
     * @return  string
     */
    public function getRelativeDir()
    {
        return $this->relativeDir;
    }

    /**
     * Get Item Name
     *
     * @access  public
     * @return  string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Item Size (in KiloBytes)
     *
     * @access  public
     * @return  int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get Deletion Time
     *
     * @access  public
     * @return  \Carbon\Carbon
     */
    public function getTimeDeleted()
    {
        return $this->deleted;
    }

    /**
     * Get Author
     *
     * @access  public
     * @return  string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get Deleter
     *
     * @access  public
     * @return  string
     */
    public function getDeleter()
    {
        return $this->deleter;
    }

    /**
     * Get a SharePoint RecycleBinItem by GUID
     *
     * @static
     * @access  public
     * @param   SPSite $site  SharePoint Site
     * @param   string $guid  SharePoint RecycleBinItem GUID
     * @param   array  $extra Extra properties to map
     * @throws  \Impensavel\Spoil\Exception\SPRuntimeException
     * @return  SPRecycleBinItem
     */
    public static function getByGUID(SPSite $site, $guid, array $extra = [])
    {
        $json = $site->request("_api/web/RecycleBin('".$guid."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ]);

        return new static($site, $json, $extra);
    }

    /**
     * Restore a SharePoint RecycleBin Item
     *
     * @access  public
     * @throws  \Impensavel\Spoil\Exception\SPRuntimeException
     * @return  bool
     */
    public function restore()
    {
        $this->site->request("_api/web/RecycleBin('".$this->guid."')/restore()", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ], 'POST');

        return true;
    }

    /**
     * Permanently delete a SharePoint RecycleBin Item
     *
     * @access  public
     * @throws  \Impensavel\Spoil\Exception\SPRuntimeException
     * @return  bool
     */
    public function delete()
    {
        $this->site->request("_api/web/RecycleBin('".$this->guid."')/deleteObject()", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ], 'POST');

        return true;
    }
}

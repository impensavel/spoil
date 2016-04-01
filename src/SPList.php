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

class SPList extends SPListObject
{
    use SPTimestampsTrait;

    /**
     * SharePoint List Template Types (SharePoint 2013)
     *
     * @link http://msdn.microsoft.com/en-us/library/office/microsoft.sharepoint.client.listtemplatetype%28v=office.15%29.aspx
     * @link http://techtrainingnotes.blogspot.co.uk/2008/01/sharepoint-registrationid-list-template.html
     * @var  int
     */
    const TPL_GENERICLIST     = 100; // Custom list
    const TPL_DOCUMENTLIBRARY = 101; // Document library
    const TPL_SURVEY          = 102; // Survey
    const TPL_LINKS           = 103; // Links
    const TPL_ANNOUNCEMENTS   = 104; // Announcements
    const TPL_CONTACTS        = 105; // Contacts
    const TPL_EVENTS          = 106; // Calendar
    const TPL_TASKS           = 107; // Tasks
    const TPL_DISCUSSIONBOARD = 108; // Discussion board
    const TPL_PICTURELIBRARY  = 109; // Picture library
    const TPL_WEBPAGELIBRARY  = 119; // Web page library
    const TPL_PAGES           = 850; // Publishing pages

    /**
     * Allowed SharePoint List Types
     *
     * @static
     * @access  public
     * @var     array
     */
    public static $allowedListTypes = [
        self::TPL_GENERICLIST,
        self::TPL_DOCUMENTLIBRARY,
        self::TPL_SURVEY,
        self::TPL_LINKS,
        self::TPL_ANNOUNCEMENTS,
        self::TPL_CONTACTS,
        self::TPL_EVENTS,
        self::TPL_TASKS,
        self::TPL_DISCUSSIONBOARD,
        self::TPL_PICTURELIBRARY,
        self::TPL_WEBPAGELIBRARY,
        self::TPL_PAGES,
    ];

    /**
     * SharePoint List Types that allow
     * Folder/File operations
     *
     * @static
     * @access  public
     * @var     array
     */
    public static $writableListTypes = [
        self::TPL_DOCUMENTLIBRARY,
        self::TPL_PICTURELIBRARY,
        self::TPL_WEBPAGELIBRARY,
        self::TPL_PAGES,
    ];

    /**
     * SharePoint List Field Types (SharePoint 2013)
     *
     * @link http://msdn.microsoft.com/en-us/library/office/microsoft.sharepoint.client.fieldtype%28v=office.15%29.aspx
     * @var  int
     */
    const FLD_INTEGER          = 1;  // Field contains an integer value
    const FLD_TEXT             = 2;  // Field contains a single line of text
    const FLD_NOTE             = 3;  // Field contains multiple lines of text
    const FLD_DATETIME         = 4;  // Field contains a date and time value or a date-only value
    const FLD_COUNTER          = 5;  // Field contains a monotonically increasing integer
    const FLD_CHOICE           = 6;  // Field contains a single value from a set of specified values
    const FLD_LOOKUP           = 7;  // Field is a lookup field
    const FLD_BOOLEAN          = 8;  // Field contains a Boolean value
    const FLD_NUMBER           = 9;  // Field contains a floating-point number value
    const FLD_CURRENCY         = 10; // Field contains a currency value
    const FLD_URL              = 11; // Field contains a URI and an optional description of the URI
    const FLD_COMPUTED         = 12; // Field is a computed field
    const FLD_THREADING        = 13; // Field indicates the thread for a discussion item in a threaded view of a discussion board
    const FLD_GUID             = 14; // Field contains a GUID value
    const FLD_MULTICHOICE      = 15; // Field contains one or more values from a set of specified values
    const FLD_GRIDCHOICE       = 16; // Field contains rating scale values for a survey list
    const FLD_CALCULATED       = 17; // Field is a calculated field
    const FLD_FILE             = 18; // Field contains the leaf name of a document as a value
    const FLD_ATTACHMENTS      = 19; // Field indicates whether the list item has attachments
    const FLD_USER             = 20; // Field contains one or more users and groups as values
    const FLD_RECURRENCE       = 21; // Field indicates whether a meeting in a calendar list recurs
    const FLD_CROSSPROJECTLINK = 22; // Field contains a link between projects in a Meeting Workspace site
    const FLD_MODSTAT          = 23; // Field indicates moderation status
    const FLD_ERROR            = 24; // Field type was set to an invalid value
    const FLD_CONTENTPLID      = 25; // Field contains a content type identifier as a value
    const FLD_PAGESEPARATOR    = 26; // Field separates questions in a survey list onto multiple pages
    const FLD_THREADINDEX      = 27; // Field indicates the position of a discussion item in a threaded view of a discussion board
    const FLD_WORKFLOWSTATUS   = 28; // Field indicates the status of a workflow instance on a list item
    const FLD_ALLDAYEVENT      = 29; // Field indicates whether a meeting in a calendar list is an all-day event
    const FLD_WORKFLOWEVENTPL  = 30; // Field contains the most recent event in a workflow instance

    /**
     * List Template Type
     *
     * @access  protected
     * @var     int
     */
    protected $template = 0;

    /**
     * List Item Entity Type Full Name
     *
     * @access  protected
     * @var     string
     */
    protected $itemType;

    /**
     * List Description
     *
     * @access  protected
     * @var     string
     */
    protected $description;

    /**
     * SharePoint List constructor
     *
     * @access  public
     * @param   SPSite $site     SharePoint Site
     * @param   array  $payload  OData response payload
     * @param   array  $settings Instantiation settings
     * @throws  SPBadMethodCallException|SPRuntimeException
     * @return  SPList
     */
    public function __construct(SPSite $site, array $payload, array $settings = [])
    {
        $settings = array_replace_recursive([
            'fetch' => false, // Fetch SharePoint Items?
        ], $settings, [
            'extra' => [],    // Extra SharePoint List properties to map
            'items' => [],    // SharePoint Item instantiation settings
        ]);

        $this->mapper = array_merge([
            'template'    => 'BaseTemplate',
            'spType'      => 'odata.type',
            'itemType'    => 'ListItemEntityTypeFullName',
            'guid'        => 'Id',
            'title'       => 'Title',
            'relativeUrl' => 'RootFolder/ServerRelativeUrl',
            'description' => 'Description',
            'itemCount'   => 'ItemCount',
            'created'     => 'RootFolder/TimeCreated',
            'modified'    => 'RootFolder/TimeLastModified',
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
            'template'     => $this->template,
            'item_type'    => $this->itemType,
            'description'  => $this->description,
            'relative_url' => $this->relativeUrl,
            'items'        => $this->items,
            'item_count'   => $this->itemCount,
            'created'      => $this->created,
            'modified'     => $this->modified,
            'extra'        => $this->extra,
        ];
    }

    /**
     * Get List Template Type
     *
     * @access  public
     * @return  string
     */
    public function getTemplate()
    {
        return $this->template;
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
        $writable = in_array($this->template, static::$writableListTypes);

        if (! $writable && $exception) {
            throw new SPRuntimeException('SPList Template Type ['.$this->template.'] does not allow SharePoint Folder/File operations');
        }

        return $writable;
    }

    /**
     * {@inheritdoc}
     */
    public function toSPList(array $settings = [])
    {
        return new static($this->site, $this->payload, $settings);
    }

    /**
     * Get List Description
     *
     * @access  public
     * @return  string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the List Item Entity Type Full Name
     *
     * @access  public
     * @return  string
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * Check if a List Type is allowed
     *
     * @static
     * @access  public
     * @param   int    $listType SharePoint List Type
     * @return  bool
     */
    public static function isListTypeAllowed($listType)
    {
        return in_array($listType, static::$allowedListTypes);
    }

    /**
     * Get all SharePoint Lists
     *
     * @static
     * @access  public
     * @param   SPSite $site     SharePoint Site
     * @param   array  $settings Instantiation settings
     * @throws  SPRuntimeException
     * @return  array
     */
    public static function getAll(SPSite $site, array $settings = [])
    {
        $json = $site->request('_api/web/Lists', [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query' => [
                '$expand' => 'RootFolder',
            ],
        ]);

        $lists = [];

        foreach ($json['value'] as $list) {
            // Allowed SharePoint List Types
            if (static::isListTypeAllowed($list['BaseTemplate'])) {
                $lists[$list['Id']] = new static($site, $list, $settings);
            }
        }

        return $lists;
    }

    /**
     * Get a SharePoint List by GUID
     *
     * @static
     * @access  public
     * @param   SPSite $site     SharePoint Site
     * @param   string $guid     SharePoint List GUID
     * @param   array  $settings Instantiation settings
     * @throws  SPRuntimeException
     * @return  SPList
     */
    public static function getByGUID(SPSite $site, $guid, array $settings = [])
    {
        $json = $site->request("_api/web/Lists(guid'".$guid."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query' => [
                '$expand' => 'RootFolder',
            ],
        ]);

        return new static($site, $json, $settings);
    }

    /**
     * Get a SharePoint List by Title
     *
     * @static
     * @access  public
     * @param   SPSite $site     SharePoint Site
     * @param   string $title    SharePoint List Title
     * @param   array  $settings Instantiation settings
     * @throws  SPRuntimeException
     * @return  SPList
     */
    public static function getByTitle(SPSite $site, $title, array $settings = [])
    {
        $json = $site->request("_api/web/Lists/GetByTitle('".$title."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query' => [
                '$expand' => 'RootFolder',
            ],
        ]);

        return new static($site, $json, $settings);
    }

    /**
     * Create a SharePoint List
     *
     * @static
     * @access  public
     * @param   SPSite $site       SharePoint Site
     * @param   array  $properties SharePoint List properties (Title, Description, ...)
     * @param   array  $settings   Instantiation settings
     * @throws  SPRuntimeException
     * @return  SPList
     */
    public static function create(SPSite $site, array $properties, array $settings = [])
    {
        $properties = array_replace_recursive([
            'BaseTemplate' => static::TPL_DOCUMENTLIBRARY,
        ], $properties, [
            'odata.type' => 'SP.List',
        ]);

        $body = json_encode($properties);

        $json = $site->request('_api/web/Lists', [
            'headers' => [
                'Authorization'   => 'Bearer '.$site->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $site->getSPFormDigest(),
                'Content-type'    => 'application/json',
                'Content-length'  => strlen($body),
            ],

            'query' => [
                '$expand' => 'RootFolder',
            ],

            'body'    => $body,
        ], 'POST');

        return new static($site, $json, $settings);
    }

    /**
     * Update a SharePoint List
     *
     * @access  public
     * @param   array  $properties SharePoint List properties (Title, Description, ...)
     * @throws  SPRuntimeException
     * @return  SPList
     */
    public function update(array $properties)
    {
        $properties = array_replace_recursive($properties, [
            'odata.type' => 'SP.List',
        ]);

        $body = json_encode($properties);

        $this->request("_api/web/Lists(guid'".$this->guid."')", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->getSPFormDigest(),
                'X-HTTP-Method'   => 'MERGE',
                'IF-MATCH'        => '*',
                'Content-type'    => 'application/json',
                'Content-length'  => strlen($body),
            ],

            'body'    => $body,
        ], 'POST');

        // Rehydration is done using the $properties array,
        // since the SharePoint API doesn't return a response
        // on a successful update
        return $this->hydrate($properties, true);
    }

    /**
     * Delete a List and all it's content
     *
     * @access  public
     * @throws  SPRuntimeException
     * @return  bool
     */
    public function delete()
    {
        $this->request("_api/web/Lists(guid'".$this->guid."')", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->getSPFormDigest(),
                'X-HTTP-Method'   => 'DELETE',
                'IF-MATCH'        => '*',
            ],
        ], 'POST');

        return true;
    }

    /**
     * Create a SharePoint Field
     *
     * @access  public
     * @param   array  $properties Field properties (Title, FieldTypeKind, ...)
     * @throws  SPRuntimeException
     * @return  string
     */
    public function createSPField(array $properties)
    {
        $properties = array_replace_recursive([
            'FieldTypeKind' => static::FLD_TEXT,
        ], $properties, [
            'odata.type' => 'SP.Field',
        ]);

        $body = json_encode($properties);

        $json = $this->request("_api/web/Lists(guid'".$this->guid."')/Fields", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->getSPFormDigest(),
                'Content-type'    => 'application/json',
                'Content-length'  => strlen($body),
            ],

            'body'    => $body,
        ], 'POST');

        return $json['Id'];
    }

    /**
     * Get the SharePoint List Item count
     *
     * @access  public
     * @throws  SPRuntimeException
     * @return  int
     */
    public function getSPItemCount()
    {
        $json = $this->request("_api/web/Lists(guid'".$this->guid."')/itemCount", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ]);

        return $this->itemCount = $json['ItemCount'];
    }

    /**
     * Get all SharePoint Items
     *
     * @static
     * @access  public
     * @param   array  $settings Instantiation settings
     * @throws  SPRuntimeException
     * @return  array
     */
    public function getSPItems(array $settings = [])
    {
        $settings = array_replace_recursive([
            'top'   => 5000, // SharePoint Item threshold
        ], $settings, [
            'extra' => [],   // Extra SharePoint Item properties to map
        ]);

        $this->items = SPItem::getAll($this, $settings);

        return $this->items;
    }

    /**
     * Get SharePoint Item by ID
     *
     * @static
     * @access  public
     * @param   int    $id    Item ID
     * @param   array  $extra Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPItem
     */
    public function getSPItem($id, array $extra = [])
    {
        $item = SPItem::getByID($this, $id, $extra);

        $this[] = $item;

        return $item;
    }

    /**
     * Create a SharePoint Item
     *
     * @access  public
     * @param   array  $properties List properties (Title, ...)
     * @throws  SPRuntimeException
     * @return  array
     */
    public function createSPItem(array $properties)
    {
        $item = SPItem::create($this, $properties);

        $this[] = $item;

        return $item;
    }

    /**
     * Update a SharePoint Item
     *
     * @access  public
     * @param   string $guid       SharePoint Item GUID
     * @param   array  $properties SharePoint Item properties (Title, ...)
     * @throws  SPRuntimeException
     * @return  SPItem
     */
    public function updateSPItem($guid, array $properties)
    {
        return $this[$guid]->update($properties);
    }

    /**
     * Delete a SharePoint Item
     *
     * @access  public
     * @param   string $guid SharePoint Item index
     * @throws  SPRuntimeException
     * @return  bool
     */
    public function deleteSPItem($guid)
    {
        if ($this[$guid]->delete()) {
            unset($this[$guid]);
        }

        return true;
    }
}

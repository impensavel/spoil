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

use SplFileInfo;

use Carbon\Carbon;

use Impensavel\Spoil\Exception\SPBadMethodCallException;
use Impensavel\Spoil\Exception\SPInvalidArgumentException;
use Impensavel\Spoil\Exception\SPRuntimeException;

class SPFile extends SPObject implements SPItemInterface
{
    use SPPropertiesTrait;
    use SPTimestampsTrait;

    /**
     * SharePoint Check In Types (SharePoint 2013)
     *
     * @link https://msdn.microsoft.com/en-us/library/office/microsoft.sharepoint.client.checkintype.aspx
     * @var  int
     */
    const CHECKIN_MINOR     = 0; // Minor check in
    const CHECKIN_MAJOR     = 1; // Major check in
    const CHECKIN_OVERWRITE = 2; // Overwrite check in

    /**
     * SharePoint Check Out Types (SharePoint 2013)
     *
     * @link https://msdn.microsoft.com/en-us/library/microsoft.sharepoint.spfile.spcheckouttype.aspx
     * @var  int
     */
    static $checkOutTypes = [
        0 => 'Online',  // The file is checked out for editing on the server
        1 => 'Offline', // The file is checked out for editing on the local computer
        2 => 'None',    // The file is not checked out (Published)
    ];

    /**
     * SharePoint Folder
     *
     * @access  protected
     * @var     SPFolderInterface
     */
    protected $folder;

    /**
     * SharePoint ID
     *
     * @access  protected
     * @var     int
     */
    protected $id = 0;

    /**
     * File Name
     *
     * @access  protected
     * @var     string
     */
    protected $name;

    /**
     * File Size
     *
     * @access  protected
     * @var     int
     */
    protected $size = 0;

    /**
     * File Relative URL
     *
     * @access  protected
     * @var     string
     */
    protected $relativeUrl;

    /**
     * File Author
     *
     * @access  protected
     * @var     string
     */
    protected $author;

    /**
     * Check In Comment
     *
     * @access  protected
     * @var     string
     */
    protected $checkInComment;

    /**
     * Check Out Type
     *
     * @access  protected
     * @var     int
     */
    protected $checkOutType;

    /**
     * SharePoint File constructor
     *
     * @access  public
     * @param   SPFolderInterface $folder  SharePoint Folder
     * @param   array             $payload OData response payload
     * @param   array             $extra   Extra payload values to map
     * @throws  SPBadMethodCallException
     * @return  SPFile
     */
    public function __construct(SPFolderInterface $folder, array $payload, array $extra = [])
    {
        $this->mapper = array_merge([
            'spType'         => 'odata.type',
            'id'             => 'ListItemAllFields/ID',
            'guid'           => 'ListItemAllFields/GUID',
            'title'          => 'Title',
            'name'           => 'Name',
            'size'           => 'Length',
            'created'        => 'TimeCreated',
            'modified'       => 'TimeLastModified',
            'relativeUrl'    => 'ServerRelativeUrl',
            'author'         => 'Author/LoginName',
            'checkInComment' => 'CheckInComment',
            'checkOutType'   => 'CheckOutType',
        ], $extra);

        $this->folder = $folder;

        $this->hydrate($payload);
    }

    /**
     * Get SharePoint Folder
     *
     * @access  public
     * @return  SPFolderInterface
     */
    public function getSPFolder()
    {
        return $this->folder;
    }

    /**
     * Get SharePoint ID
     *
     * @access  public
     * @return  int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'sp_type'         => $this->spType,
            'id'              => $this->id,
            'guid'            => $this->guid,
            'title'           => $this->title,
            'name'            => $this->name,
            'size'            => $this->size,
            'created'         => $this->created,
            'modified'        => $this->modified,
            'relative_url'    => $this->relativeUrl,
            'author'          => $this->author,
            'checkin_comment' => $this->checkInComment,
            'checkout_type'   => $this->checkOutType,
            'extra'           => $this->extra,
        ];
    }

    /**
     * Get File Name
     *
     * @access  public
     * @return  string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get File Size (in KiloBytes)
     *
     * @access  public
     * @return  int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get File Relative URL
     *
     * @access  public
     * @return  string
     */
    public function getRelativeUrl()
    {
        return $this->relativeUrl;
    }

    /**
     * Get File URL
     *
     * @access  public
     * @return  string
     */
    public function getUrl()
    {
        return $this->folder->getUrl($this->name);
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
     * Get Check In Comment
     *
     * @access  public
     * @return  string
     */
    public function getCheckInComment()
    {
        return $this->checkInComment;
    }

    /**
     * Get Check Out Type
     *
     * @access  public
     * @return  int
     */
    public function getCheckOutType()
    {
        return $this->checkOutType;
    }

    /**
     * Get File Contents
     *
     * @access  public
     * @return  string
     */
    public function getContents()
    {
        $response = $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/\$value", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->folder->getSPAccessToken(),
            ],
        ], 'GET', false);

        return (string) $response->getBody();
    }

    /**
     * Get File Metadata
     *
     * @access  public
     * @return  array
     */
    public function getMetadata()
    {
        return [
            'id'       => $this->id,
            'guid'     => $this->guid,
            'name'     => $this->name,
            'size'     => $this->size,
            'created'  => $this->created,
            'modified' => $this->modified,
            'url'      => $this->getUrl(),
        ];
    }

    /**
     * Get the SharePoint Item equivalent
     *
     * @access  public
     * @param   array  $extra Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPItem
     */
    public function toSPItem(array $extra = [])
    {
        return $this->folder->toSPList()->getSPItem($this->id, $extra);
    }

    /**
     * Get all SharePoint Files
     *
     * @static
     * @access  public
     * @param   SPFolderInterface $folder SharePoint Folder
     * @param   array             $extra  Extra payload values to map
     * @throws  SPRuntimeException
     * @return  array
     */
    public static function getAll(SPFolderInterface $folder, array $extra = [])
    {
        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeUrl()."')/Files", [
            'headers' => [
                'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields,Author',
            ],
        ]);

        $files = [];

        foreach ($json['value'] as $file) {
            $files[$file['UniqueId']] = new static($folder, $file, $extra);
        }

        return $files;
    }

    /**
     * Get a SharePoint File by Relative URL
     *
     * @static
     * @access  public
     * @param   SPSite $site        SharePoint Site
     * @param   string $relativeUrl SharePoint Folder relative URL
     * @param   array  $extra       Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPFile
     */
    public static function getByRelativeUrl(SPSite $site, $relativeUrl, array $extra = [])
    {
        $json = $site->request("_api/web/GetFileByServerRelativeUrl('".$relativeUrl."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields,Author',
            ],
        ]);

        $folder = SPFolder::getByRelativeUrl($site, dirname($relativeUrl));

        return new static($folder, $json, $extra);
    }

    /**
     * Get a SharePoint File by Name
     *
     * @static
     * @access  public
     * @param   SPFolderInterface $folder SharePoint Folder
     * @param   string            $name   File Name
     * @param   array             $extra  Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPFile
     */
    public static function getByName(SPFolderInterface $folder, $name, array $extra = [])
    {
        $folder->isWritable(true);

        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeUrl()."')/Files('".$name."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields,Author',
            ],
        ]);

        return new static($folder, $json, $extra);
    }

    /**
     * Content type handler
     *
     * @static
     * @access  protected
     * @param   mixed   $input
     * @throws  SPRuntimeException|SPInvalidArgumentException
     * @return  string
     */
    protected static function contentTypeHandler($input)
    {
        if ($input instanceof SplFileInfo) {
            $data = file_get_contents($input->getPathname());

            if ($data === false) {
                throw new SPRuntimeException('Unable to get file contents');
            }

            return $data;
        }

        if (is_string($input)) {
            return $input;
        }

        if (is_resource($input)) {
            $type = get_resource_type($input);

            if ($type != 'stream') {
                throw new SPInvalidArgumentException('Invalid resource type: '.$type);
            }

            $data = stream_get_contents($input);

            if ($data === false) {
                throw new SPRuntimeException('Failed to get data from stream');
            }

            return $data;
        }

        throw new SPInvalidArgumentException('Invalid input type: '.gettype($input));
    }

    /**
     * Create a SharePoint File
     *
     * @static
     * @access  public
     * @param   SPFolderInterface $folder    SharePoint Folder
     * @param   mixed             $content   File content
     * @param   string            $name      Name for the file being uploaded
     * @param   bool              $overwrite Overwrite if file already exists?
     * @param   array             $extra     Extra payload values to map
     * @throws  SPBadMethodCallException
     * @return  SPFile
     */
    public static function create(SPFolderInterface $folder, $content, $name = null, $overwrite = false, array $extra = [])
    {
        $folder->isWritable(true);

        if (empty($name)) {
            if ($content instanceof SplFileInfo) {
                $name = $content->getFilename();
            }

            if (is_resource($content) || is_string($content)) {
                throw new SPBadMethodCallException('SharePoint File Name is empty/not set');
            }
        }

        $data = static::contentTypeHandler($content);

        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeUrl()."')/Files/Add(url='".$name."',overwrite=".($overwrite ? 'true' : 'false').")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $folder->getSPFormDigest(),
                'Content-length'  => strlen($data),
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields',
            ],

            'body'    => $data,
        ], 'POST');

        return new static($folder, $json, $extra);
    }

    /**
     * Update a SharePoint File
     *
     * @access  public
     * @param   mixed $content File content
     * @throws  SPRuntimeException
     * @return  SPFile
     */
    public function update($content)
    {
        $data = static::contentTypeHandler($content);

        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/\$value", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
                'X-HTTP-Method'   => 'PUT',
                'Content-length'  => strlen($data),
            ],

            'body'    => $data,

        ], 'POST');

        // Rehydration is done in a best effort manner, since the SharePoint
        // API doesn't return a response on a successful update
        return $this->hydrate([
            'Length'           => strlen($data),
            'TimeLastModified' => Carbon::now(),
        ], true);
    }

    /**
     * Move a SharePoint File
     *
     * @access  public
     * @param   SPFolderInterface $folder SharePoint Folder to move to
     * @param   string            $name   SharePoint File name
     * @param   array             $extra  Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPFile
     */
    public function move(SPFolderInterface $folder, $name = null, array $extra = [])
    {
        $folder->isWritable(true);

        $newUrl = $folder->getRelativeUrl($name ?: $this->name);

        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/moveTo(newUrl='".$newUrl."',flags=1)", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ],
        ], 'POST');

        // Since the SharePoint API doesn't return a proper response on
        // a successful move operation, we do a second request to get an
        // updated SPFile to rehydrate the current object
        $file = static::getByRelativeUrl($folder->getSPSite(), $newUrl, $extra);

        return $this->hydrate($file);
    }

    /**
     * Copy a SharePoint File
     *
     * @access  public
     * @param   SPFolderInterface $folder    SharePoint Folder to copy to
     * @param   string            $name      SharePoint File name
     * @param   bool              $overwrite Overwrite if file already exists?
     * @param   array             $extra     Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPFile
     */
    public function copy(SPFolderInterface $folder, $name = null, $overwrite = false, array $extra = [])
    {
        $folder->isWritable(true);

        $newUrl = $folder->getRelativeUrl($name ?: $this->name);

        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/copyTo(strNewUrl='".$newUrl."',boverwrite=".($overwrite ? 'true' : 'false').")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ],
        ], 'POST');

        // Since the SharePoint API doesn't return a proper response on
        // a successful copy operation, we do a second request to get the
        // copied SPFile
        return static::getByRelativeUrl($folder->getSPSite(), $newUrl, $extra);
    }

    /**
     * Recycle a SharePoint File
     *
     * @access  public
     * @throws  SPRuntimeException
     * @return  string
     */
    public function recycle()
    {
        $json = $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/recycle", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ],
        ], 'POST');

        // Return the the recycle bin item GUID
        return $json['value'];
    }

    /**
     * Delete a SharePoint File
     *
     * @access  public
     * @throws  SPRuntimeException
     * @return  bool
     */
    public function delete()
    {
        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
                'IF-MATCH'        => '*',
                'X-HTTP-Method'   => 'DELETE',
            ],
        ], 'POST');

        return true;
    }

    /**
     * Check in a SharePoint File
     *
     * @access  public
     * @param   string $comment Check in comment
     * @param   int    $type    Check in Type
     * @throws  SPRuntimeException
     * @return  bool
     */
    public function checkIn($comment, $type = self::CHECKIN_MINOR)
    {
        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/checkin(comment='".$comment."',checkintype=".$type.")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ],
        ], 'POST');

        return true;
    }

    /**
     * Check out a SharePoint File
     *
     * @access  public
     * @throws  SPRuntimeException
     * @return  bool
     */
    public function checkOut()
    {
        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/checkout()", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ],
        ], 'POST');

        return true;
    }
}

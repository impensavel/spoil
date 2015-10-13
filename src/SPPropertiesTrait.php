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

trait SPPropertiesTrait
{
    /**
     * SharePoint Type
     *
     * @access  protected
     * @var     string
     */
    protected $type;

    /**
     * SharePoint GUID
     *
     * @access  protected
     * @var     string
     */
    protected $guid;

    /**
     * SharePoint Title
     *
     * @access  protected
     * @var     string
     */
    protected $title;

    /**
     * Get SharePoint Type
     *
     * @access  public
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get SharePoint GUID
     *
     * @access  public
     * @return  string
     */
    public function getGUID()
    {
        return $this->guid;
    }

    /**
     * Get SharePoint Title
     *
     * @access  public
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }
}

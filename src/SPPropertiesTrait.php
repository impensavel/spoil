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

trait SPPropertiesTrait
{
    /**
     * SharePoint Type
     *
     * @var  string
     */
    protected $spType;

    /**
     * SharePoint GUID
     *
     * @var  string
     */
    protected $guid;

    /**
     * SharePoint Title
     *
     * @var  string
     */
    protected $title;

    /**
     * Get SharePoint Type
     *
     * @return  string
     */
    public function getSPType()
    {
        return $this->spType;
    }

    /**
     * Get SharePoint GUID
     *
     * @return  string
     */
    public function getGUID()
    {
        return $this->guid;
    }

    /**
     * Get SharePoint Title
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }
}

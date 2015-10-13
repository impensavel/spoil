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

interface SPItemInterface
{
    /**
     * Get SharePoint GUID
     *
     * @access  public
     * @return  string
     */
    public function getGUID();

    /**
     * Get SharePoint Title
     *
     * @access  public
     * @return  string
     */
    public function getTitle();
}

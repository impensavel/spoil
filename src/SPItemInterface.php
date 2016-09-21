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

interface SPItemInterface
{
    /**
     * Get SharePoint GUID
     *
     * @return  string
     */
    public function getGUID();

    /**
     * Get SharePoint Title
     *
     * @return  string
     */
    public function getTitle();
}

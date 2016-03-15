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

interface SPObjectInterface
{
    /**
     * Get an array with the SPObject properties
     *
     * @access  public
     * @return  array
     */
    public function toArray();
}

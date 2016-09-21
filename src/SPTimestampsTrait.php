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

trait SPTimestampsTrait
{
    /**
     * Creation Time
     *
     * @var  \Carbon\Carbon
     */
    protected $created;

    /**
     * Modification Time
     *
     * @var  \Carbon\Carbon
     */
    protected $modified;

    /**
     * Get Creation Time
     *
     * @return  \Carbon\Carbon
     */
    public function getTimeCreated()
    {
        return $this->created;
    }

    /**
     * Get Modification Time
     *
     * @return  \Carbon\Carbon
     */
    public function getTimeModified()
    {
        return $this->modified;
    }
}

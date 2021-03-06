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

namespace Impensavel\Spoil\Exception;

use RuntimeException;

class SPRuntimeException extends RuntimeException
{
    /**
     * Get the previous Exception message
     *
     * @return  string|null
     */
    public function getPreviousMessage()
    {
        $previous = $this->getPrevious();

        return $previous ? $previous->getMessage() : null;
    }
}

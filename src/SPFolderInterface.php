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

use Impensavel\Spoil\Exception\SPRuntimeException;

interface SPFolderInterface extends SPRequesterInterface
{
    /**
     * Get SharePoint Site
     *
     * @access  public
     * @return  SPSite
     */
    public function getSPSite();

    /**
     * Get Relative URL
     *
     * @access  public
     * @param   string $path Path to append to the Relative URL
     * @return  string
     */
    public function getRelativeUrl($path = null);

    /**
     * Get URL
     *
     * @access  public
     * @param   string $path Path to append to the URL
     * @return  string
     */
    public function getUrl($path = null);

    /**
     * Is the folder writable?
     *
     * @access  public
     * @param   bool   $exception Throw exception if not writable?
     * @throws  SPRuntimeException
     * @return  bool
     */
    public function isWritable($exception = false);
}

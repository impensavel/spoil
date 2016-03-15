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

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

use Impensavel\Spoil\Exception\SPBadMethodCallException;
use Impensavel\Spoil\Exception\SPInvalidArgumentException;

abstract class SPListObject extends SPObject implements ArrayAccess, Countable, IteratorAggregate, SPFolderInterface
{
    use SPPropertiesTrait;

    /**
     * SharePoint Site
     *
     * @access  protected
     * @var     SPSite
     */
    protected $site;

    /**
     * SharePoint Item Count
     *
     * @access  protected
     * @var     array
     */
    protected $itemCount = 0;

    /**
     * SharePoint Items
     *
     * @access  protected
     * @var     array
     */
    protected $items = [];

    /**
     * Relative URL
     *
     * @access  protected
     * @var     string
     */
    protected $relativeUrl;

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (isset($this->items[$offset])) {
            return $this->items[$offset];
        }

        throw new SPInvalidArgumentException('Invalid SharePoint Item GUID');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (! $value instanceof SPItemInterface) {
            throw new SPBadMethodCallException('SharePoint Item expected');
        }

        // Always set the GUID as the array index
        $offset = $value->getGUID();

        $this->items[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function request($url, array $options = [], $method = 'GET', $json = true)
    {
        return $this->site->request($url, $options, $method, $json);
    }

    /**
     * {@inheritdoc}
     */
    public function getSPAccessToken()
    {
        return $this->site->getSPAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getSPFormDigest()
    {
        return $this->site->getSPFormDigest();
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeUrl($path = null)
    {
        return sprintf('%s/%s', rtrim($this->relativeUrl, '/'), ltrim($path, '/'));
    }

    /**
     * {@inheritdoc}
     */
    public function getSPSite()
    {
        return $this->site;
    }
}

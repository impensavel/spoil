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

class SPRecycleBinItemCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * SharePoint Recycle Bin Items
     *
     * @var  array
     */
    protected $items = [];

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

        throw new SPInvalidArgumentException('Invalid SharePoint Recycle Bin Item GUID');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (! $value instanceof SPRecycleBinItem) {
            throw new SPBadMethodCallException('SharePoint Recycle Bin Item expected');
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
     * Create a SharePoint Recycle Bin Item Collection
     *
     * @param   SPSite $site  SharePoint Site
     * @param   array  $extra Extra properties to map
     * @throws  \Impensavel\Spoil\Exception\SPRuntimeException
     * @return  SPRecycleBinItemCollection
     */
    public static function create(SPSite $site, array $extra = [])
    {
        $json = $site->request("_api/web/RecycleBin", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ]);

        $collection = new static();

        foreach ($json['value'] as $item) {
            $collection[$item['Id']] = new SPRecycleBinItem($site, $item, $extra);
        }

        return $collection;
    }
}

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

use Carbon\Carbon;

use Impensavel\Spoil\Exception\SPBadMethodCallException;
use Impensavel\Spoil\Exception\SPInvalidArgumentException;

abstract class SPObject implements SPObjectInterface
{
    /**
     * Property mapper
     *
     * @access  protected
     * @var     array
     */
    protected $mapper = [];

    /**
     * Extra properties
     *
     * @access  protected
     * @var     array
     */
    protected $extra = [];

    /**
     * SharePoint Object constructor
     *
     * @access  public
     * @param   array  $mapper Dot notation property mapper
     * @param   array  $extra  Extra properties to map
     * @return  SPObject
     */
    public function __construct(array $mapper, array $extra = [])
    {
        $this->mapper = array_merge($mapper, $extra);
    }

    /**
     * Get extra properties
     *
     * @access  public
     * @param   string $property Extra property name
     * @throws  SPInvalidArgumentException
     * @return  mixed
     */
    public function getExtra($property = null)
    {
        if ($property === null) {
            return $this->extra;
        }

        if (array_key_exists($property, $this->extra)) {
            return $this->extra[$property];
        }

        throw new SPInvalidArgumentException('Invalid property: '.$property);
    }

    /**
     * Assign a property value
     *
     * @access  protected
     * @param   string $property Property name
     * @param   mixed  $value    Property value
     * @return  void
     */
    protected function assign($property, $value)
    {
        // convert ISO 8601 dates into Carbon objects
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})?$/', $value) === 1) {
            $value = new Carbon($value);
        }

        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            $this->extra[$property] = $value;
        }
    }

    /**
     * Get a value from a JSON array
     *
     * @access  protected
     * @param   array     $json JSON response from the SharePoint REST API
     * @param   string    $path Dot notation path to the value we want to get
     * @return  mixed
     */
    protected function getJsonValue(array $json, $path)
    {
        if (is_string($path)) {
            foreach (explode('->', $path) as $segment) {
                if (! is_array($json) || ! array_key_exists($segment, $json)) {
                    return null;
                }

                $json = $json[$segment];
            }
        }

        return $json;
    }

    /**
     * Hydration handler
     *
     * @access  protected
     * @param   mixed     $data      SPObject / JSON response from the SharePoint REST API
     * @param   bool      $rehydrate Are we rehydrating?
     * @throws  SPBadMethodCallException
     * @return  SPObject
     */
    protected function hydrate($data, $rehydrate = false)
    {
        // hydrate from an SPObject
        if ($data instanceof $this) {
            foreach (get_object_vars($data) as $key => $value) {
                $this->$key = $value;
            }

            return $this;
        }

        // hydrate from an array (JSON)
        if (is_array($data)) {
            foreach ($this->mapper as $property => $path) {
                // make spaces SharePoint compatible
                $path = str_replace(' ', '_x0020_', $path);

                $value = $this->getJsonValue($data, $path);

                if ($value !== null || $rehydrate === false) {
                    $this->assign($property, $value);
                }
            }

            return $this;
        }

        throw new SPBadMethodCallException('Could not hydrate '.get_class($this).' with data from '.gettype($data));
    }
}

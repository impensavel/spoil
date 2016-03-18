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

use Carbon\Carbon;

use Impensavel\Spoil\Exception\SPBadMethodCallException;
use Impensavel\Spoil\Exception\SPInvalidArgumentException;

abstract class SPObject implements SPObjectInterface
{
    /**
     * Payload data
     *
     * @access  protected
     * @var     array
     */
    protected $payload = [];

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
        // Convert ISO 8601 dates into Carbon objects
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
     * Extract a value from an OData response payload
     *
     * @access  protected
     * @param   array     $payload OData response payload
     * @param   string    $path    Path to the value
     * @return  mixed
     */
    protected function extractFromPayload(array $payload, $path)
    {
        if (is_string($path)) {
            foreach (explode('/', $path) as $level) {
                if (is_array($payload) === false || array_key_exists($level, $payload) === false) {
                    return null;
                }

                $payload = $payload[$level];
            }
        }

        return $payload;
    }

    /**
     * Hydration handler
     *
     * @access  protected
     * @param   mixed     $data      SPObject / OData response payload
     * @param   bool      $rehydrate Are we rehydrating?
     * @throws  SPBadMethodCallException
     * @return  SPObject
     */
    protected function hydrate($data, $rehydrate = false)
    {
        // Hydrate from an SPObject
        if ($data instanceof $this) {
            foreach (get_object_vars($data) as $key => $value) {
                $this->$key = $value;
            }

            return $this;
        }

        // Hydrate from an array (JSON)
        if (is_array($data)) {
            // Store payload data
            $this->payload = $data;

            foreach ($this->mapper as $property => $path) {
                // Make spaces SharePoint compatible
                $path = str_replace(' ', '_x0020_', $path);

                $value = $this->extractFromPayload($data, $path);

                if ($value !== null || $rehydrate === false) {
                    $this->assign($property, $value);
                }
            }

            return $this;
        }

        throw new SPBadMethodCallException('Could not hydrate '.get_class($this).' with data from '.gettype($data));
    }

    /**
     * Get the Payload data
     *
     * @access  public
     * @return  array
     */
    public function getPayload()
    {
        return $this->payload;
    }
}

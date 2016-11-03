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

namespace Impensavel\Spoil\Tests;

use GuzzleHttp\Psr7\Response;

class SPTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Create an API mock response
     *
     * @param string $file    API response file name
     * @param int    $status  HTTP status
     * @param array  $headers HTTP headers
     * @return string
     */
    public function createMockResponse($file, $status = 200, $headers = [])
    {
        $body = file_get_contents(sprintf('%s/responses/%s', __DIR__, $file));

        return new Response($status, $headers, $body);
    }
}

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace webservice_mcp;

use webservice_mcp\local\request;
use webservice_mcp\local\server;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/lib.php');
require_once(__DIR__ . '/../classes/local/server.php');

/**
 * Error envelopes must be readable by JSON-RPC clients for every request ID.
 *
 * @package webservice_mcp
 * @copyright 2026 Andreas Giesen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class error_response_test extends \PHPUnit\Framework\TestCase {
    /** Verify string protocol version and exact request-ID type after serialization. */
    public function test_error_envelope_preserves_request_id_and_protocol_version(): void {
        $reflection = new \ReflectionClass(server::class);
        $property = $reflection->getProperty('mcprequest');
        $property->setAccessible(true);
        $generate = $reflection->getMethod('generate_error');
        $generate->setAccessible(true);
        $encode = $reflection->getMethod('safe_json_encode');
        $encode->setAccessible(true);

        foreach ([2, 'conflict-92', '0', 0] as $id) {
            foreach ([new \moodle_exception('err_invalid_request', 'webservice_mcp'), new \TypeError('Private detail')] as $exception) {
                $server = $reflection->newInstanceWithoutConstructor();
                $property->setValue($server, new request(['jsonrpc' => '2.0', 'id' => $id, 'method' => 'tools/call']));
                $result = json_decode($encode->invoke($server, $generate->invoke($server, $exception)), true, 512,
                    JSON_THROW_ON_ERROR);
                $this->assertSame('2.0', $result['jsonrpc']);
                $this->assertSame($id, $result['id']);
                $this->assertSame(-32603, $result['error']['code']);
                $expected = $exception instanceof \moodle_exception ? $exception->getMessage() : 'Internal error';
                $this->assertSame($expected, $result['error']['message']);
                $this->assertArrayNotHasKey('result', $result);
            }
        }
    }

    /** An error before request parsing still needs a valid envelope and null ID. */
    public function test_error_envelope_without_parsed_request(): void {
        $reflection = new \ReflectionClass(server::class);
        $generate = $reflection->getMethod('generate_error');
        $generate->setAccessible(true);
        foreach ([new \Exception('Invalid request'), null] as $exception) {
            $result = $generate->invoke($reflection->newInstanceWithoutConstructor(), $exception);
            $this->assertSame('2.0', $result['jsonrpc']);
            $this->assertNull($result['id']);
        }
    }

    /**
     * Invalid request parameters retain a valid ID before construction finishes.
     */
    public function test_invalid_params_keep_request_id(): void {
        $reflection = new \ReflectionClass(server::class);
        $generate = $reflection->getMethod('generate_error');
        try {
            new request(['jsonrpc' => '2.0', 'id' => '0', 'method' => 'ping', 'params' => 'invalid']);
            $this->fail('Scalar params were accepted.');
        } catch (\webservice_mcp\local\request_exception $exception) {
            $result = $generate->invoke($reflection->newInstanceWithoutConstructor(), $exception);
            $this->assertSame('0', $result['id']);
            $this->assertSame(-32602, $result['error']['code']);
        }
    }
}

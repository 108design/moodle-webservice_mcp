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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

declare(strict_types=1);

namespace webservice_mcp\local;

use JsonException;

/**
 * Immutable and validated MCP JSON-RPC 2.0 request.
 *
 * This class represents a fully validated JSON-RPC request following the
 * Model Context Protocol (MCP) specification. It enforces JSON-RPC 2.0
 * structure, validates required fields, and provides typed access to
 * request properties such as method name, parameters, protocol version,
 * and request ID.
 *
 * All instances are immutable and guaranteed to represent a valid
 * JSON-RPC 2.0 request after construction.
 *
 * @package     webservice_mcp
 * @author      MohammadReza PourMohammad <onbirdev@gmail.com>
 * @copyright   2025 MohammadReza PourMohammad
 * @link        https://onbir.dev
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request {
    /** @var string JSON-RPC protocol version. Always "2.0". */
    public readonly string $jsonrpc;

    /** @var string The requested MCP method name. */
    public readonly string $method;

    /** @var int|string|null Optional request ID. Present for calls expecting a response, null for notifications. */
    public readonly int|string|null $id;

    /** @var array|null Method parameters as an array, or null if not provided. */
    public readonly ?array $params;

    /**
     * Constructor.
     *
     * Builds a validated, immutable MCP request object from a decoded JSON array.
     *
     * @param array $data Decoded JSON representing the JSON-RPC request.
     * @throws request_exception If validation fails or required fields are missing.
     */
    public function __construct(array $data) {
        $this->validate($data);

        $this->jsonrpc = $data['jsonrpc'];
        $this->method = $data['method'];
        $this->id = $data['id'] ?? null;
        $this->params = $data['params'] ?? null;
    }

    /**
     * Validate JSON-RPC 2.0 request structure.
     *
     * @param array $data Decoded JSON input.
     * @throws request_exception If the JSON-RPC structure is invalid.
     */
    protected function validate(array $data): void {
        if (($data['jsonrpc'] ?? null) !== '2.0') {
            throw new request_exception('err_invalid_jsonrpc', -32600);
        }
        if (!is_string($data['method'] ?? null) || $data['method'] === '') {
            throw new request_exception('err_missing_method', -32600);
        }
        if (array_key_exists('id', $data) && !is_int($data['id']) && !is_string($data['id'])) {
            throw new request_exception('err_invalid_id', -32600);
        }
        if (array_key_exists('params', $data) && !is_array($data['params'])) {
            throw new request_exception('err_invalid_params', -32602, $data['id'] ?? null);
        }
    }

    /**
     * Create an MCP request object from raw HTTP input.
     *
     * Reads the raw request body from php://input, decodes the JSON,
     * validates the structure, and returns a fully constructed request object.
     *
     * @return self A validated request instance.
     * @throws request_exception If the body is empty, JSON decoding fails, or the request format is invalid.
     */
    public static function from_raw_input(): self {
        $raw = file_get_contents('php://input');

        if ($raw === false || $raw === '') {
            throw new request_exception('err_empty_request', -32700);
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new request_exception('err_invalid_json', -32700);
        }
        if (!is_array($data) || array_is_list($data)) {
            throw new request_exception('err_invalid_request', -32600);
        }

        return new self($data);
    }
}

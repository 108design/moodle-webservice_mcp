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

use core\exception\moodle_exception;

/**
 * A JSON-RPC request error with an optional validated correlation ID.
 *
 * @package webservice_mcp
 * @copyright 2026 Andreas Giesen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class request_exception extends moodle_exception {
    /**
     * Create a client-facing protocol error.
     *
     * @param string $errorcode Moodle language string key
     * @param int $rpccode JSON-RPC error code
     * @param int|string|null $requestid Validated request ID, if available
     */
    public function __construct(
        string $errorcode,
        public readonly int $rpccode,
        public readonly int|string|null $requestid = null,
    ) {
        parent::__construct($errorcode, 'webservice_mcp');
    }
}

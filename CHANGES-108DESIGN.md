# 108design fork changes

Original project by MohammadReza PourMohammad (onbirdev). Licensed under GNU GPL v3 or later.

## 0.7.1-beta (2026110801)

Tools without a return value now advertise and return JSON null. Empty property maps are JSON objects, and nested required fields appear in the schema. Tool discovery and invocation use the same Moodle service restrictions. Invalid requests return JSON-RPC errors and retain a valid request ID when available.

## 0.7.0-beta (2026110800)

The 17 October 2026 compatibility fix sets the JSON-RPC protocol version to 2.0 on error responses and preserves the request ID separately. The accompanying test covers errors before request parsing and numeric, string and null IDs.

Based on [upstream v0.6.0](https://github.com/onbirdev/moodle-webservice_mcp/tree/v0.6.0). The Moodle component remains webservice_mcp.

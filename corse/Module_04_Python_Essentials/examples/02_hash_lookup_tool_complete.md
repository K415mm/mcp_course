---
status: draft
---

# Example 02 — Hash Lookup Tool: Complete and Tested

> **Example Block 2 of 2 | Module 04: Python Essentials for MCP**

---

## What This Example Adds

Example 01 showed a GET-based API tool (AbuseIPDB). This example shows:
- A **POST-based** API call (MalwareBazaar uses POST)
- Handling **"not found" vs "found"** as a normal business logic case (not an error)
- Returning **nested data** (tags list, signature) cleanly

---

## The Complete File

Save as `d:/mcp_course/practice/hash_lookup.py`:

```python
# hash_lookup.py — Complete hash enrichment tool using MalwareBazaar
# No API key required for MalwareBazaar (free public API)

import re
import requests

# ── Validator ─────────────────────────────────────────────────────
HEX_CHARS = set("0123456789abcdefABCDEF")
VALID_HASH_LENGTHS = {32, 40, 64}  # MD5, SHA1, SHA256


def validate_hash(hash_value: str) -> bool:
    """Return True if hash_value is a valid MD5, SHA1, or SHA256 hex string."""
    # Check length first — fastest check
    if len(hash_value) not in VALID_HASH_LENGTHS:
        return False
    # Check all characters are hexadecimal
    return set(hash_value).issubset(HEX_CHARS)


def detect_hash_type(hash_value: str) -> str:
    """Return the hash algorithm based on length."""
    length_to_type = {32: "MD5", 40: "SHA1", 64: "SHA256"}
    return length_to_type.get(len(hash_value), "unknown")


# ── Main tool function ────────────────────────────────────────────

def lookup_hash(hash_value: str) -> dict:
    """Look up a file hash against the MalwareBazaar threat intelligence database.

    Use when you have a file hash from an alert or malware sample and want to
    check if it is a known threat. Works with MD5, SHA1, and SHA256 hashes.
    No API key required.

    Returns: hash, hash_type, found, file_name, file_type, tags,
             signature (malware family), origin_country, status.

    Read-only. Safe to automate.
    """

    # ── Input validation ──────────────────────────────────────────
    if not hash_value:
        return {"status": "error", "reason": "hash_value cannot be empty"}

    hash_value = hash_value.strip().lower()  # Normalize: lowercase, no spaces

    if not validate_hash(hash_value):
        return {
            "status": "error",
            "reason": f"Invalid hash: '{hash_value}'. "
                      "Expected MD5 (32 chars), SHA1 (40 chars), or SHA256 (64 chars) "
                      "hexadecimal string."
        }

    hash_type = detect_hash_type(hash_value)

    # ── API Call ──────────────────────────────────────────────────
    # MalwareBazaar uses POST with a JSON body, not GET with params.
    # This is different from AbuseIPDB but equally common.
    try:
        response = requests.post(
            "https://mb-api.abuse.ch/api/v1/",

            # json= automatically sets Content-Type: application/json header
            # and serializes the dict to JSON in the request body
            json={
                "query": "get_info",
                "hash": hash_value
            },

            timeout=15   # MalwareBazaar can be slightly slower than AbuseIPDB
        )

        response.raise_for_status()

        raw = response.json()

        # ── Handle "not found" as a valid normal outcome ──────────
        # "hash_not_found" is NOT an error — it just means the hash
        # is not in the MalwareBazaar database. That's actionable info.
        query_status = raw.get("query_status", "")

        if query_status == "hash_not_found":
            return {
                "hash":           hash_value,
                "hash_type":      hash_type,
                "found":          False,
                "file_name":      None,
                "file_type":      None,
                "tags":           [],
                "signature":      "not in MalwareBazaar database",
                "origin_country": None,
                "note":           "Not found. This does not confirm the file is safe — "
                                  "run additional analysis.",
                "status":         "ok"
            }

        if query_status != "ok":
            return {
                "status": "error",
                "reason": f"Unexpected MalwareBazaar response: query_status='{query_status}'"
            }

        # ── Extract from the first result ─────────────────────────
        # MalwareBazaar returns a list under "data" — we take index [0].
        # Use .get() with defaults on every field.
        data_list = raw.get("data", [])

        if not data_list:
            # query_status was "ok" but data is empty — unexpected
            return {"status": "error", "reason": "MalwareBazaar returned ok status but empty data"}

        item = data_list[0]

        return {
            "hash":           hash_value,
            "hash_type":      hash_type,
            "found":          True,
            "file_name":      item.get("file_name", "unknown"),
            "file_type":      item.get("file_type", "unknown"),
            "tags":           item.get("tags", []),              # List — may be empty
            "signature":      item.get("signature", "unknown"),  # Malware family name
            "origin_country": item.get("origin_country", "unknown"),
            "first_seen":     item.get("first_seen", "unknown"),
            "status":         "ok"
        }

    # ── Exception handlers ─────────────────────────────────────────
    except requests.exceptions.Timeout:
        return {"status": "error", "reason": "MalwareBazaar did not respond within 15 seconds"}

    except requests.exceptions.ConnectionError:
        return {"status": "error", "reason": "Could not connect to MalwareBazaar"}

    except requests.exceptions.HTTPError as e:
        return {"status": "error", "reason": f"HTTP error from MalwareBazaar: {e.response.status_code}"}

    except Exception as e:
        return {"status": "error", "reason": f"Unexpected error: {str(e)}"}


# ── Test block ────────────────────────────────────────────────────

if __name__ == "__main__":

    print("=== Test 1: Known malware hash ===")
    # This is the EICAR test file hash — safe, publicly known
    result = lookup_hash("3395856ce81f2b7382dee72602f798b642f14d8")
    print(result)
    print()

    print("=== Test 2: Hash not in database ===")
    result = lookup_hash("a" * 64)  # Random valid SHA256 format — won't be known
    print(result)
    print()

    print("=== Test 3: Invalid hash ===")
    result = lookup_hash("abc123")
    print(result)
    print()

    print("=== Test 4: Empty input ===")
    result = lookup_hash("")
    print(result)
    print()

    print("=== Test 5: Hash with leading/trailing spaces ===")
    result = lookup_hash("  3395856ce81f2b7382dee72602f798b642f14d8  ")
    print(result.get("found"))  # Should be True — we strip spaces
```

---

## Key Differences from the IP Checker

| Aspect | IP Checker (Example 01) | Hash Lookup (This example) |
|---|---|---|
| HTTP method | GET | POST |
| API key | Required (env var) | Not required |
| "Not found" | Always an error | Valid business outcome |
| Return list field | None | `tags: []` (list in dict) |
| Normalization | `hash_value.strip().lower()` | Both — normalize inputs |

---

## Modification Exercises

1. **Add MalwareBazaar URL field:** extract `item.get("url_haus_download", None)` and include it in the result.
2. **Combine with IP checker:** write a function `full_triage(ip: str, hash: str) -> dict` that calls both `enrich_ip` and `lookup_hash`, then returns a combined summary dict.
3. **Tag check:** add a field `is_ransomware: bool` that is `True` if `"ransomware"` appears anywhere in the `tags` list.

---

## What Changes in Module 05

Same as Example 01 — add `@mcp.tool()` and `mcp.run()`. The logic stays identical.

```python
from mcp.server.fastmcp import FastMCP
mcp = FastMCP("Malware Analysis Server")

@mcp.tool()
def lookup_hash(hash_value: str) -> dict:
    # ... same body as above ...
    pass

if __name__ == "__main__":
    mcp.run()   # Replaces the test block
```

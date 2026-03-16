---
status: published
---

# Practical 02 — Multi-Server Client and Agent Loop

> **Practical Block 2 of 3 | Module 06: Building MCP Clients**

---

## Objective

Connect the `MCPClient` to two servers simultaneously, combine their tool lists, and run a simple agent decision loop.

---

## Part A: Connect to Two Servers

```python
# multi_server_client.py
import asyncio
import json
from mcp_client import MCPClient  # from Practical 01


async def main():
    client = MCPClient()

    try:
        # Connect to the CTI server from Module 05
        cti_tools = await client.connect_to_server(
            "cti",
            "d:/mcp_course/cti-mcp-server/server.py"
        )

        # Connect to the malware analysis server (use a simple stub if not built yet)
        # For this practical, we'll create a stub server below
        malware_tools = await client.connect_to_server(
            "malware",
            "d:/mcp_course/malware-stub/stub.py"
        )

        # Combine tool lists from both servers
        all_tools = client.list_all_tools()

        print("=== ALL AVAILABLE TOOLS ===")
        for t in all_tools:
            print(f"  [{t['server_id']}] {t['name']}: {t['description'][:60]}...")

        # Cross-server triage: enrich IP from CTI, compute hash from malware server
        print("\n=== CROSS-SERVER TRIAGE ===")

        ip_result = await client.invoke_tool(
            "cti", "enrich_ip", {"ip_address": "185.220.101.45"}
        )
        print(f"CTI result: {ip_result[:200]}")

        hash_result = await client.invoke_tool(
            "malware", "compute_file_hashes",
            {"file_path": "d:/mcp_course/labs/sample.txt"}
        )
        print(f"Malware result: {hash_result[:200]}")

    finally:
        await client.cleanup()


asyncio.run(main())
```

---

## Part B: Build the Malware Stub Server

```python
# d:/mcp_course/malware-stub/stub.py
# A minimal stub server for testing multi-server client connections
# Run: cd d:/mcp_course/malware-stub && uv init . && uv add "mcp[cli]" && uv run mcp dev stub.py

import hashlib, os
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("Malware Analysis Stub")

ALLOWED = os.path.realpath("d:/mcp_course/labs")


@mcp.tool()
def compute_file_hashes(file_path: str) -> dict:
    """Compute MD5 and SHA256 hashes of a file for threat intelligence lookup.
    Use when you have a suspicious file and need its hash for database lookup.
    Returns: md5, sha256, file_size_bytes, status.
    Read-only. Safe to automate."""

    resolved = os.path.realpath(file_path)
    if not resolved.startswith(ALLOWED):
        return {"status": "error", "reason": "Path outside allowed analysis directory"}
    if not os.path.isfile(resolved):
        return {"status": "error", "reason": f"File not found: {file_path}"}

    try:
        data = open(resolved, "rb").read()
        return {
            "md5":    hashlib.md5(data).hexdigest(),
            "sha256": hashlib.sha256(data).hexdigest(),
            "file_size_bytes": len(data),
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


if __name__ == "__main__":
    mcp.run()
```

Setup the stub:
```powershell
mkdir d:/mcp_course/malware-stub
cd d:/mcp_course/malware-stub
uv init .
uv add "mcp[cli]"
# Copy stub.py into this directory
```

---

## Part C: Add a Simple Triage Decision Loop

```python
# triage_loop.py
import asyncio
import json
from mcp_client import MCPClient


def make_triage_decision(enrichments: dict) -> str:
    """
    Apply simple triage rules based on tool results.
    Returns: 'HIGH', 'MEDIUM', 'LOW', or 'INSUFFICIENT_DATA'
    """
    ip_data = enrichments.get("enrich_ip", {})
    
    if isinstance(ip_data, str):
        # Parse JSON string if needed
        try:
            ip_data = json.loads(ip_data)
        except Exception:
            ip_data = {}

    score = ip_data.get("abuse_score", -1)
    
    if score < 0:
        return "INSUFFICIENT_DATA"
    elif score >= 80:
        return "HIGH"
    elif score >= 40:
        return "MEDIUM"
    else:
        return "LOW"


async def triage_alert(alert: dict):
    """Run enrichment tools and produce a triage verdict."""
    client = MCPClient()

    try:
        tools = await client.connect_to_server(
            "cti",
            "d:/mcp_course/cti-mcp-server/server.py"
        )

        print(f"\n=== TRIAGE ALERT {alert['id']} ===")
        print(f"IOCs: IP={alert.get('ip')}, Hash={alert.get('hash', 'N/A')}")

        enrichments = {}

        # Enrich each IOC
        if alert.get("ip"):
            result = await client.invoke_tool(
                "cti", "enrich_ip", {"ip_address": alert["ip"]}
            )
            enrichments["enrich_ip"] = result
            print(f"IP enrichment: {result[:150]}")

        if alert.get("hash"):
            result = await client.invoke_tool(
                "cti", "enrich_hash", {"hash_value": alert["hash"]}
            )
            enrichments["enrich_hash"] = result
            print(f"Hash enrichment: {result[:150]}")

        # Apply decision rules
        verdict = make_triage_decision(enrichments)
        print(f"\nVERDICT: {verdict}")

        if verdict == "HIGH":
            print("ACTION: Escalate to analyst immediately. Draft containment request.")
        elif verdict == "MEDIUM":
            print("ACTION: Flag for review within 2 hours.")
        else:
            print("ACTION: Log and close. No immediate action required.")

    finally:
        await client.cleanup()


# Test with a simulated alert
TEST_ALERT = {
    "id": "ALT-0099",
    "ip": "185.220.101.45",
    "hash": "3395856ce81f2b7382dee72602f798b642f14d8"
}

asyncio.run(triage_alert(TEST_ALERT))
```

---

## Checklist

- [ ] Multi-server client connects to both CTI and malware stub without errors
- [ ] `list_all_tools()` shows tools from both servers with correct `server_id`
- [ ] Cross-server invocations both return valid results
- [ ] Triage loop prints a verdict and recommended action
- [ ] `cleanup()` runs in the `finally` block — no hanging processes

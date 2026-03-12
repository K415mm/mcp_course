---
status: draft
---

# Example: IOC Enrichment MCP Server

## Scenario

You are building an MCP server that enriches Indicators of Compromise (IOCs) — IPs, domains, and file hashes — using multiple threat intelligence sources. This is the most common "first MCP server" for security teams.

## Architecture

```
SOC AI Agent
  └── MCP Client
        └── ioc-enrichment-server (MCP Server)
              ├── tool: analyze_ip
              ├── tool: analyze_domain
              ├── tool: analyze_hash
              └── resource: supported_sources
```

## Server Implementation

```python
from mcp.server.fastmcp import FastMCP
import httpx

mcp = FastMCP("ioc-enrichment")

# ── Configuration ──────────────────────────
VT_API_KEY = "your-virustotal-api-key"
ABUSE_API_KEY = "your-abuseipdb-api-key"

# ── Tools ──────────────────────────────────

@mcp.tool()
async def analyze_ip(ip: str) -> dict:
    """
    Analyze an IP address across multiple threat intelligence sources.
    Returns reputation data, geolocation, and threat indicators.
    """
    results = {}

    async with httpx.AsyncClient(timeout=10) as client:
        # VirusTotal
        try:
            vt = await client.get(
                f"https://www.virustotal.com/api/v3/ip_addresses/{ip}",
                headers={"x-apikey": VT_API_KEY}
            )
            vt_data = vt.json().get("data", {}).get("attributes", {})
            results["virustotal"] = {
                "malicious": vt_data.get("last_analysis_stats", {}).get("malicious", 0),
                "country": vt_data.get("country", "Unknown"),
                "as_owner": vt_data.get("as_owner", "Unknown")
            }
        except Exception as e:
            results["virustotal"] = {"error": str(e)}

        # AbuseIPDB
        try:
            abuse = await client.get(
                "https://api.abuseipdb.com/api/v2/check",
                params={"ipAddress": ip, "maxAgeInDays": 90},
                headers={"Key": ABUSE_API_KEY, "Accept": "application/json"}
            )
            abuse_data = abuse.json().get("data", {})
            results["abuseipdb"] = {
                "abuse_score": abuse_data.get("abuseConfidenceScore", 0),
                "total_reports": abuse_data.get("totalReports", 0),
                "is_tor": abuse_data.get("isTor", False)
            }
        except Exception as e:
            results["abuseipdb"] = {"error": str(e)}

    # Generate summary
    vt_mal = results.get("virustotal", {}).get("malicious", 0)
    abuse_score = results.get("abuseipdb", {}).get("abuse_score", 0)

    if vt_mal > 5 or abuse_score > 75:
        verdict = "MALICIOUS"
    elif vt_mal > 0 or abuse_score > 25:
        verdict = "SUSPICIOUS"
    else:
        verdict = "CLEAN"

    results["summary"] = {
        "ip": ip,
        "verdict": verdict,
        "confidence": "high" if (vt_mal > 5 and abuse_score > 75) else "medium"
    }

    return results


@mcp.tool()
async def analyze_domain(domain: str) -> dict:
    """
    Analyze a domain name for threat indicators.
    Checks DNS records, WHOIS data, and reputation.
    """
    import socket

    results = {"domain": domain}

    # DNS Resolution
    try:
        ips = socket.gethostbyname_ex(domain)[2]
        results["dns"] = {"resolved_ips": ips}
    except socket.gaierror:
        results["dns"] = {"resolved_ips": [], "error": "DNS resolution failed"}

    # VirusTotal domain check
    async with httpx.AsyncClient(timeout=10) as client:
        try:
            vt = await client.get(
                f"https://www.virustotal.com/api/v3/domains/{domain}",
                headers={"x-apikey": VT_API_KEY}
            )
            vt_data = vt.json().get("data", {}).get("attributes", {})
            stats = vt_data.get("last_analysis_stats", {})
            results["virustotal"] = {
                "malicious": stats.get("malicious", 0),
                "suspicious": stats.get("suspicious", 0),
                "registrar": vt_data.get("registrar", "Unknown"),
                "creation_date": vt_data.get("creation_date", "Unknown")
            }
        except Exception as e:
            results["virustotal"] = {"error": str(e)}

    return results


@mcp.tool()
async def analyze_hash(hash: str) -> dict:
    """
    Analyze a file hash (MD5, SHA-1, or SHA-256) against malware databases.
    Returns detection counts and malware family information.
    """
    results = {"hash": hash, "hash_type": _detect_hash_type(hash)}

    async with httpx.AsyncClient(timeout=10) as client:
        try:
            vt = await client.get(
                f"https://www.virustotal.com/api/v3/files/{hash}",
                headers={"x-apikey": VT_API_KEY}
            )
            if vt.status_code == 200:
                vt_data = vt.json().get("data", {}).get("attributes", {})
                stats = vt_data.get("last_analysis_stats", {})
                results["virustotal"] = {
                    "malicious": stats.get("malicious", 0),
                    "undetected": stats.get("undetected", 0),
                    "file_type": vt_data.get("type_description", "Unknown"),
                    "file_name": vt_data.get("meaningful_name", "Unknown"),
                    "tags": vt_data.get("tags", [])[:5]
                }
            else:
                results["virustotal"] = {"status": "not_found"}
        except Exception as e:
            results["virustotal"] = {"error": str(e)}

    return results


# ── Resources ─────────────────────────────

@mcp.resource("enrichment://sources")
def get_supported_sources() -> str:
    """List all supported threat intelligence sources."""
    return """
    Supported IOC Enrichment Sources:
    - VirusTotal: IP, Domain, Hash analysis
    - AbuseIPDB: IP reputation and abuse reports
    - DNS: Domain resolution
    """


# ── Helpers ───────────────────────────────

def _detect_hash_type(hash_str: str) -> str:
    length = len(hash_str)
    if length == 32: return "MD5"
    elif length == 40: return "SHA-1"
    elif length == 64: return "SHA-256"
    return "Unknown"


if __name__ == "__main__":
    mcp.run()
```

## Usage by the AI Agent

When the SOC agent receives an alert containing the IP `185.220.101.1`, it can autonomously:

1. Call `analyze_ip("185.220.101.1")` to check reputation
2. Receive structured results: verdict, confidence, source details
3. Use the data to make triage decisions

The key insight: the AI agent doesn't need to know *how* each threat intel API works — it just calls the MCP tool with a simple input and gets normalized output.

## Key Takeaways

- An IOC enrichment server is the **ideal first MCP server** for security teams
- Use `@mcp.tool()` decorators to expose functions — FastMCP handles all protocol details
- **Normalize output** across different threat intel sources for consistent agent reasoning
- Include a **summary verdict** (CLEAN/SUSPICIOUS/MALICIOUS) for quick agent decisions
- Handle API errors gracefully — don't crash on a single source failure

---
status: draft
---

# 03 — Real-World MCP Servers for Cyber Defense

> **Theoretical Block 3 of 3 | Module 03: Cyber Defense Foundations for MCP Use**

---

## 3.1 From Theory to Real Tools

The open-source security community has already built MCP servers for specific defensive and offensive security tools. Understanding these existing servers gives you:

1. **Design patterns** — how experienced practitioners structure security tools for AI use
2. **Capability awareness** — what your agent can leverage without you building from scratch
3. **Integration roadmap** — which tools to prioritize connecting in your environment

This block examines four classes of real-world MCP security servers and the design decisions behind them.

---

## 3.2 Class 1: Penetration Testing MCP Servers (Pentest)

### What It Is

Pentest MCP servers wrap offensive security tools — network scanners, vulnerability assessors, exploitation frameworks — exposing them as AI-callable tools. The intended use in a cyber defense context is **controlled red team operations** and **attack path simulation**, not active exploitation of production systems.

### Example Toolset: Metasploit MCP Server

Conceptual tools in a Metasploit MCP server:

```python
@mcp.tool()
def run_nmap_scan(target_host: str, scan_type: str = "basic") -> dict:
    """Run an nmap network scan against a target host.
    [DESTRUCTIVE] REQUIRES HUMAN APPROVAL — only use on authorized targets.
    Scan types: basic (TCP SYN), full (all ports), service (version detection)."""
    ...

@mcp.tool()
def check_known_vulnerabilities(host: str, port: int) -> dict:
    """Check a specific host:port combination against the CVE database.
    Read-only. Safe to automate on authorized hosts."""
    ...

@mcp.tool()
def get_exploit_suggestions(cve_id: str) -> dict:
    """Suggest available Metasploit modules for a given CVE.
    Read-only. Never executes exploits — reference only."""
    ...
```

### Key Design Considerations for Cyber Defense Context

| Design Choice | Reason |
|---|---|
| Scan tools marked `[DESTRUCTIVE]` | Network scans generate detectable traffic and load |
| Exploit suggestion tools are read-only only | Knowledge retrieval ≠ execution |
| Target validation required | Tools must verify the target is in an authorized test scope |
| Audit logging mandatory | Every pentest action must be traceable |

### Real-World Parallel

The `modelcontextprotocol/awesome-mcp-servers` repository on GitHub lists several community-built security scanners that follow this pattern. Many wrap `nmap`, `nessus`, or `openvas` behind an MCP interface.

---

## 3.3 Class 2: Kali Linux Tool Integration

### What It Is

Kali Linux is the de-facto standard distribution for security testing. It includes 600+ pre-installed security tools. MCP servers for Kali Linux wrap these tools — extracting information and running controlled searches — while an AI agent orchestrates the workflow.

### Typical Kali-Integrated MCP Tools

```python
@mcp.tool()
def run_whois(domain: str) -> dict:
    """Retrieve WHOIS registration information for a domain.
    Read-only. Safe to automate."""
    ...

@mcp.tool()
def check_ssl_certificate(domain: str) -> dict:
    """Inspect the SSL/TLS certificate for a domain.
    Read-only. Returns issuer, expiry, SANs."""
    ...

@mcp.tool()
def run_subdomain_enumeration(domain: str, wordlist: str = "small") -> dict:
    """Enumerate subdomains using DNS brute force.
    [DESTRUCTIVE] REQUIRES HUMAN APPROVAL — generates DNS queries."""
    ...

@mcp.tool()
def get_passive_dns(ip_address: str) -> dict:
    """Retrieve historical DNS records for an IP (passive — no active queries).
    Read-only. Uses cached datasets. Safe to automate."""
    ...
```

### Passive vs. Active Tools — The Critical Distinction

In cyber defense workflows, the most important design boundary is **passive vs. active**:

| Type | Description | Examples | Approval Needed? |
|---|---|---|---|
| **Passive (safe)** | Uses cached data, no active queries to target | WHOIS (cached), passive DNS, CVE lookup | No |
| **Semi-active** | Generates observable traffic at very low volume | SSL certificate check, single port probe | Optional |
| **Active (requires approval)** | Generates significant traffic to target | Port scan, subdomain brute force | Yes |

For a SOC defense context (not authorized pentesting), you should almost always use **passive tools only**. The exceptions — when active tools are permitted — require explicit human approval and must be limited to authorized assets.

---

## 3.4 Class 3: REMnux for Malware Analysis

### What It Is

REMnux is a Linux distribution purpose-built for malware analysis. It includes:
- `pecheck`, `pdfid`, `pdfextract` — PE and PDF analysis
- `floss`, `strings` — string extraction from binaries
- `yara`, `yarascan` — pattern matching against ruleset
- `remnux-ida-decompiler`, `ghidra` — disassembly (advanced)
- `fakenet` — simulates network services for safe dynamic analysis

An MCP server wrapping REMnux tools makes static malware analysis AI-orchestrated.

### Design Pattern: The Safety-First REMnux Server

```python
from mcp.server.fastmcp import FastMCP
import subprocess, hashlib, os

mcp = FastMCP("REMnux Malware Analysis")

# Important: all file paths are validated before any operation
ALLOWED_ANALYSIS_DIR = os.environ.get("ANALYSIS_DIR", "/home/analyst/samples")

def validate_path(file_path: str) -> bool:
    """Ensure the file is inside the designated analysis directory."""
    resolved = os.path.realpath(file_path)
    return resolved.startswith(os.path.realpath(ALLOWED_ANALYSIS_DIR))


@mcp.tool()
def run_strings_analysis(file_path: str, min_length: int = 6) -> dict:
    """Run the strings command on a binary file to extract printable text.
    All files must be in the analysis directory. Read-only. Safe to automate."""
    if not validate_path(file_path):
        return {"status": "error", "reason": "File path outside allowed analysis directory"}
    try:
        result = subprocess.run(
            ["strings", "-n", str(min_length), file_path],
            capture_output=True, text=True, timeout=30
        )
        strings_found = result.stdout.strip().split("\n")[:100]  # cap at 100
        return {"file": file_path, "strings": strings_found, "count": len(strings_found), "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def run_yara_scan(file_path: str, ruleset: str = "default") -> dict:
    """Scan a file against YARA rules. Returns matching rule names.
    Safe rulesets: 'default', 'packer', 'webshell'. Read-only. Safe to automate."""
    if not validate_path(file_path):
        return {"status": "error", "reason": "File path outside allowed analysis directory"}
    RULE_PATHS = {
        "default": "/opt/yara-rules/default.yar",
        "packer": "/opt/yara-rules/packers.yar",
        "webshell": "/opt/yara-rules/webshells.yar"
    }
    rule_file = RULE_PATHS.get(ruleset, RULE_PATHS["default"])
    try:
        result = subprocess.run(
            ["yara", "-r", rule_file, file_path],
            capture_output=True, text=True, timeout=60
        )
        matches = [line.split()[0] for line in result.stdout.strip().split("\n") if line]
        return {"file": file_path, "ruleset": ruleset, "matches": matches, "match_count": len(matches), "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}
```

### Key Security Design Patterns Demonstrated

- **Path validation:** every tool verifies the file is inside a designated sandbox directory — prevents path traversal attacks.
- **Output cap:** `[:100]` on strings list prevents the tool from flooding the LLM context with thousands of lines.
- **Subprocess timeout:** `timeout=30` prevents a malicious file from hanging the analysis server.
- **Whitelist for rulesets:** only named, pre-vetted YARA rulesets are accepted — no arbitrary file loads.

---

## 3.5 Class 4: IDA Pro / Disassembly Assistants

### What It Is

IDA Pro and Ghidra are the leading binary disassemblers used for reverse engineering. MCP servers for these tools expose their analysis output — function names, call graphs, disassembled code, decompiled pseudocode — to AI agents that can then interpret the binary's behavior.

### Conceptual Tool Design

```python
@mcp.tool()
def get_function_list(binary_path: str) -> dict:
    """List all functions identified in a binary by IDA Pro analysis.
    Read-only. Returns function names, addresses, and sizes."""
    ...

@mcp.tool()
def get_function_pseudocode(function_name: str) -> dict:
    """Return the decompiled pseudocode for a named function from IDA.
    Read-only. Use to understand what a specific function does.
    Output normalized to remove overly loaded security terms."""
    ...

@mcp.tool()
def get_string_references(search_string: str) -> dict:
    """Find all functions that reference a given string in the binary.
    Read-only. Useful for pivoting from a found URL or API name to the code that uses it."""
    ...
```

### Agent Workflow with Disassembly

The power of disassembly + AI agent is not in reading all the code — it's in targeted pivot analysis:

1. `extract_strings()` → finds `"cmd.exe /c whoami"`.
2. `get_string_references("cmd.exe /c whoami")` → identifies Function_0x4012A0.
3. `get_function_pseudocode("Function_0x4012A0")` → decompiled code shows it's called after a network authentication check.
4. AI agent synthesizes: "This function executes a system reconnaissance command after a network-based authentication event. This is consistent with post-exploitation activity."

---

## 3.6 Choosing Which Servers to Build First

For a beginner cyber defense team deploying MCP for the first time, prioritize in this order:

| Priority | Server Type | Why |
|---|---|---|
| 1 | CTI Enrichment | Highest ROI, all read-only, no risk |
| 2 | SIEM / Log Query | Core investigation data access |
| 3 | Threat Hunting | Semi-automated, analyst in the loop |
| 4 | Network Analysis | Requires good baselines first |
| 5 | Malware Analysis | Requires sandbox infrastructure |
| 6 | Pentest / Active | Only with authorized scope |

---

## Key Takeaways

1. Real MCP security servers exist for Metasploit, Kali, REMnux, IDA — your team can extend or adapt them.
2. Passive tools (no active queries) are always safe to automate. Active tools require approval gates.
3. Path validation is mandatory for any file-handling tool to prevent path traversal attacks.
4. Output capping prevents tools from flooding the LLM context window.
5. Process timeouts prevent malicious files from hanging the analysis server.
6. Start with CTI enrichment servers — maximum ROI, minimum risk.

---

## Further Reading

- [Practical Cyber Defense via MCP.md](file:///d:/mcp_course/Practical%20Cyber%20Defense%20via%20MCP.md)
- [FastMCP_ Building AI-Enabled Python Tools.md](file:///d:/mcp_course/FastMCP_%20Building%20AI-Enabled%20Python%20Tools.md)
- GitHub: `modelcontextprotocol/awesome-mcp-servers` — community server directory
- REMnux Documentation: [docs.remnux.org](https://docs.remnux.org)

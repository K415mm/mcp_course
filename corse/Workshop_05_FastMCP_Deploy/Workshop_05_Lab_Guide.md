---
status: draft
---

# Workshop 5: Build and Deploy a FastMCP Cyber Defense Server (End-to-End)

## Workshop Goal

Go from zero to a fully deployed, tested, and IDE-integrated MCP server — covering the complete development lifecycle: write → test → debug → register → iterate.

## Prerequisites

- All modules 1–7 and Workshops 1–4 reviewed.
- Python environment: `mcp`, `fastmcp`, `requests`, `anthropic`.
- Node.js (for MCP Inspector): `npm`, `npx`.
- Trae AI or Claude Desktop installed.

---

## Lab Overview

This workshop builds a production-ready cyber defense assistant server with all safe design patterns applied. It is the reference implementation for the capstone project.

| Phase | Activity |
|---|---|
| Phase 1 | Plan the server — tools, scope, guardrails |
| Phase 2 | Write the server with all safety patterns |
| Phase 3 | Test with MCP Inspector |
| Phase 4 | Register in AI workspace and run E2E workflow |
| Phase 5 | Add a destructive tool with approval gate |

---

## Phase 1: Plan the Server

Before writing code, define the server specification:

```
Server name: Cyber Defense Assistant

Read-only tools (safe to automate):
  1. hash_file(path)          — compute MD5/SHA1/SHA256
  2. extract_iocs_from_text   — pull IPs, domains, hashes from raw text
  3. check_file_type(path)    — detect PE, PDF, ZIP by magic bytes
  4. summarize_log_file(path) — return line count, first/last timestamp

Destructive tools (require human approval):
  5. quarantine_file(path, reason) — move file to isolated quarantine dir

Outputs:
  - All tools return dict with status: ok or status: error
  - All string outputs normalized (no "suspicious", "malicious", "dangerous")
  - All tool docstrings explicitly state: read-only or [DESTRUCTIVE]

Secrets: none required for this server (no external APIs)
```

---

## Phase 2: Write the Server

Create `d:/mcp_course/servers/defense_assistant.py`:

```python
import os, re, hashlib, shutil, json
from datetime import datetime
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("Cyber Defense Assistant")

QUARANTINE_DIR = os.environ.get("QUARANTINE_DIR", "d:/mcp_course/labs/quarantine")
MAX_FILE_BYTES = 10 * 1024 * 1024  # 10 MB cap

# ── Output normalization ─────────────────────────────────────────────────────

NORMALIZE = {
    "suspicious": "notable",
    "malicious": "flagged by vendor",
    "dangerous": "commonly scrutinized",
    "infected": "flagged",
    "backdoor": "remote access capability",
}

def normalize(text: str) -> str:
    for term, repl in NORMALIZE.items():
        text = text.replace(term, repl)
    return text

# ── Utilities ────────────────────────────────────────────────────────────────

def safe_read_bytes(path: str) -> bytes:
    size = os.path.getsize(path)
    if size > MAX_FILE_BYTES:
        raise ValueError(f"File too large: {size} bytes (max {MAX_FILE_BYTES})")
    with open(path, "rb") as f:
        return f.read()

def log_audit(tool: str, inputs: dict, result_status: str):
    entry = {
        "timestamp": datetime.utcnow().isoformat() + "Z",
        "tool": tool,
        "inputs": inputs,
        "result_status": result_status
    }
    log_path = os.path.join(os.path.dirname(__file__), "audit.log")
    with open(log_path, "a") as f:
        f.write(json.dumps(entry) + "\n")

# ── Tool 1: Hash File ────────────────────────────────────────────────────────

@mcp.tool()
def hash_file(file_path: str) -> dict:
    """Compute MD5, SHA1, and SHA256 hashes for a file at the given path.
    Use as the first step before submitting a hash to threat intelligence lookups.
    Read-only. Safe to automate."""
    try:
        data = safe_read_bytes(file_path)
        result = {
            "md5":    hashlib.md5(data).hexdigest(),
            "sha1":   hashlib.sha1(data).hexdigest(),
            "sha256": hashlib.sha256(data).hexdigest(),
            "size_bytes": len(data),
            "status": "ok"
        }
        log_audit("hash_file", {"file_path": file_path}, "ok")
        return result
    except FileNotFoundError:
        return {"status": "error", "reason": f"File not found: {file_path}"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}

# ── Tool 2: Extract IOCs from Text ───────────────────────────────────────────

@mcp.tool()
def extract_iocs_from_text(text: str) -> dict:
    """Extract all IPv4 addresses, domains, and file hashes from a block of raw text.
    Use when parsing an alert body, email content, or raw log paste.
    Returns categorized lists of all found IOCs.
    Read-only. Safe to automate."""
    try:
        ipv4 = re.findall(
            r"\b(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\b", text
        )
        domains = re.findall(
            r"\b(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}\b", text
        )
        hashes = re.findall(r"\b[a-fA-F0-9]{32}\b|\b[a-fA-F0-9]{40}\b|\b[a-fA-F0-9]{64}\b", text)
        # Remove obvious false positives from domains
        domains = [d for d in domains if "." in d and not re.match(r"^\d+\.\d+$", d)]
        result = {
            "ipv4_addresses": list(set(ipv4)),
            "domains": list(set(domains)),
            "hashes": list(set(hashes)),
            "status": "ok"
        }
        log_audit("extract_iocs_from_text", {"text_length": len(text)}, "ok")
        return result
    except Exception as e:
        return {"status": "error", "reason": str(e)}

# ── Tool 3: Check File Type ──────────────────────────────────────────────────

@mcp.tool()
def check_file_type(file_path: str) -> dict:
    """Detect the type of a file using magic byte signatures.
    Use before selecting an analysis workflow for an unknown file.
    Returns the detected type and the first 16 bytes as hex for reference.
    Read-only. Safe to automate."""
    MAGIC = {
        b"MZ": "Windows PE Executable",
        b"%PDF": "PDF Document",
        b"PK\x03\x04": "ZIP/DOCX/XLSX Archive",
        b"\x7fELF": "ELF Linux Executable",
        b"\xd0\xcf\x11\xe0": "OLE2 Compound File (legacy Office)",
    }
    try:
        with open(file_path, "rb") as f:
            header = f.read(16)
        detected = next(
            (label for magic, label in MAGIC.items() if header[:len(magic)] == magic),
            "Unknown — manual review recommended"
        )
        result = {
            "file_path": file_path,
            "detected_type": detected,
            "header_hex": header.hex(),
            "status": "ok"
        }
        log_audit("check_file_type", {"file_path": file_path}, "ok")
        return result
    except FileNotFoundError:
        return {"status": "error", "reason": f"File not found: {file_path}"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}

# ── Tool 4: Summarize Log File ───────────────────────────────────────────────

@mcp.tool()
def summarize_log_file(file_path: str) -> dict:
    """Summarize a plain-text log file: return total line count, file size,
    and the first and last 3 lines (timestamps and content).
    Use to quickly assess a log file before deciding which query to run.
    Read-only. Safe to automate."""
    try:
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            lines = f.readlines()
        size = os.path.getsize(file_path)
        result = {
            "file_path": file_path,
            "total_lines": len(lines),
            "size_bytes": size,
            "first_3_lines": [l.strip() for l in lines[:3]],
            "last_3_lines":  [l.strip() for l in lines[-3:]],
            "status": "ok"
        }
        log_audit("summarize_log_file", {"file_path": file_path}, "ok")
        return result
    except FileNotFoundError:
        return {"status": "error", "reason": f"File not found: {file_path}"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}

# ── Tool 5: Quarantine File (DESTRUCTIVE) ────────────────────────────────────

@mcp.tool()
def quarantine_file(file_path: str, reason: str, approved_by: str) -> dict:
    """[DESTRUCTIVE] Move a file to the quarantine directory for safe keeping.
    REQUIRES HUMAN APPROVAL — do not call this tool without an explicit analyst instruction.
    Provide the reason for quarantine and the name/ID of the approving analyst.
    This action moves the original file and cannot be undone automatically."""
    try:
        if not reason or len(reason) < 5:
            return {"status": "error", "reason": "Quarantine reason is required (min 5 chars)"}
        if not approved_by:
            return {"status": "error", "reason": "approved_by is required — who authorized this action?"}
        os.makedirs(QUARANTINE_DIR, exist_ok=True)
        filename = os.path.basename(file_path)
        timestamp = datetime.utcnow().strftime("%Y%m%dT%H%M%SZ")
        dest = os.path.join(QUARANTINE_DIR, f"{timestamp}_{filename}")
        shutil.move(file_path, dest)
        result = {
            "status": "quarantined",
            "original_path": file_path,
            "quarantine_path": dest,
            "reason": reason,
            "approved_by": approved_by,
            "timestamp": timestamp
        }
        log_audit("quarantine_file", {"file_path": file_path, "approved_by": approved_by}, "ok")
        return result
    except FileNotFoundError:
        return {"status": "error", "reason": f"File not found: {file_path}"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


if __name__ == "__main__":
    mcp.run()
```

---

## Phase 3: Test with MCP Inspector

```bash
$env:QUARANTINE_DIR="d:/mcp_course/labs/quarantine"
npx @modelcontextprotocol/inspector python d:/mcp_course/servers/defense_assistant.py
```

Run each tool to verify:

| Tool | Test Input | Expected |
|---|---|---|
| `hash_file` | `d:/mcp_course/labs/sample_update.bin` | 3 valid hashes |
| `extract_iocs_from_text` | `"Alert: 185.220.101.45 connected to evil.net hash: a3f9d1..."` | 1 IP, 1 domain, 1 hash |
| `check_file_type` | `d:/mcp_course/labs/sample_update.bin` | Windows PE Executable |
| `summarize_log_file` | any `.txt` file | line count + first/last lines |
| `quarantine_file` | (do not run in Inspector — destructive) | skip |

Verify `audit.log` is created next to the server file after each call.

---

## Phase 4: Register in AI Workspace and Run E2E

Add to your AI workspace alongside `cti_server.py` (Workshop 1).

**E2E test prompt:**

```
We have received a suspicious file at d:/mcp_course/labs/sample_update.bin
and the following alert text:

"Alert: Outbound connection from 192.168.1.55 to 185.220.101.45.
DNS request for update-secure-patch.net observed.
File hash: 3395856ce81f2b7382dee72602f798b642f14d8 attached."

Please:
1. Extract all IOCs from the alert text.
2. Compute the file hashes.
3. Check the file type.
4. Enrich the IP and hash via threat intel.
5. Produce a triage brief with risk level and recommended actions.
```

The agent should chain: `extract_iocs_from_text` → `hash_file` → `check_file_type` → `enrich_ip` (CTI server) → `enrich_hash` (CTI server) → brief.

---

## Phase 5: Approval Gate Exercise

After the triage brief, the agent should recommend quarantine. Respond:

> "Approved. Quarantine the file. Approver: [your name]."

The agent should then call:
```
quarantine_file(
  file_path="d:/mcp_course/labs/sample_update.bin",
  reason="PE file with injection-capable APIs, hash lookup inconclusive, analyst approved",
  approved_by="[your name]"
)
```

Verify in `audit.log`:
```json
{"timestamp": "...", "tool": "quarantine_file", "inputs": {"file_path": "...", "approved_by": "..."}, "result_status": "ok"}
```

---

## Lab Checklist

- [ ] Server starts without errors.
- [ ] All 4 read-only tools pass Inspector tests.
- [ ] `audit.log` created and entries written for each call.
- [ ] Server registered in AI workspace alongside CTI server.
- [ ] Agent correctly chains 5+ tool calls from the E2E test prompt.
- [ ] `quarantine_file` only called after explicit analyst approval in chat.
- [ ] Quarantine directory created with timestamped file.
- [ ] `audit.log` contains quarantine entry with `approved_by` field.

---

## Capstone Readiness Checklist

After completing Workshop 5 you should be able to:

- [ ] Write a FastMCP server with multiple tools from scratch.
- [ ] Apply input validation, error handling, and output normalization.
- [ ] Test any MCP server with the Inspector before connecting an AI.
- [ ] Register servers in Trae AI or Claude Desktop.
- [ ] Build a multi-server AI workflow that chains tools across servers.
- [ ] Design human-in-the-loop gates for destructive tools.
- [ ] Read an audit log and trace every tool call back to its input and approver.

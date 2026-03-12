---
status: draft
---

# 06 — uv: The Modern Python Toolchain for MCP Development

> **Theoretical Block 6 of 6 | Module 04: Python Essentials for MCP**

---

## 6.1 Why uv?

The MCP official documentation — and the majority of MCP developers in the community — use **`uv`** as the default Python toolchain. This is not just a trend: `uv` solves three real problems that traditional `pip` + `venv` creates for MCP projects:

| Problem with `pip` + `venv` | How `uv` solves it |
|---|---|
| Installing packages is slow (seconds per package) | uv is 10–100× faster (written in Rust) |
| `requirements.txt` gets out of sync easily | `uv` manages a `pyproject.toml` with locked versions |
| Running your server on another machine requires manual setup | `uv run` handles the full environment automatically |
| Virtual environment activation differs per OS/shell | `uv run` activates the environment for you — no manual activation |
| Sharing a server config for Claude Desktop requires exact paths | `uv run` path is consistent and easily referenced in JSON config |

From the official MCP quickstart guide:
> "We recommend using `uv` to manage your Python projects... `uv` handles virtual environment creation, dependency management, and running Python scripts — all in one tool."

---

## 6.2 Installing uv

### Windows (PowerShell — Recommended)
```powershell
powershell -ExecutionPolicy ByPass -c "irm https://astral.sh/uv/install.ps1 | iex"
```

After installation, close and reopen your terminal. Verify:
```powershell
uv --version
# Expected: uv 0.5.x (or higher)
```

### macOS / Linux
```bash
curl -LsSf https://astral.sh/uv/install.sh | sh
```

`uv` installs to `~/.local/bin/uv` (or `%USERPROFILE%\.local\bin` on Windows) and adds itself to `PATH` automatically.

---

## 6.3 Creating a New MCP Project with uv

### Step 1: Initialise the project
```powershell
# Navigate to your course folder first
cd d:/mcp_course

# Create a new project called "cti-server"
uv init cti-server
cd cti-server
```

`uv init` creates this structure:
```
cti-server/
├── .python-version   ← pins the Python version (e.g., "3.12")
├── pyproject.toml    ← your project config and dependencies
├── README.md
└── hello.py          ← starter file (you can rename or replace this)
```

### Step 2: Add your dependencies
```powershell
# Add FastMCP (the MCP SDK) and requests for API calls
uv add "mcp[cli]" requests python-dotenv

# This automatically:
# 1. Creates a .venv/ virtual environment (if not already there)
# 2. Installs all packages into it
# 3. Updates pyproject.toml with the dependency list
# 4. Creates uv.lock with exact locked versions
```

Your `pyproject.toml` will now contain:
```toml
[project]
name = "cti-server"
version = "0.1.0"
description = "CTI Enrichment MCP Server"
requires-python = ">=3.10"
dependencies = [
    "mcp[cli]>=1.2.0",
    "requests>=2.31.0",
    "python-dotenv>=1.0.0",
]
```

### Step 3: Create your server file
```powershell
# Create the main server file (rename hello.py or create new)
New-Item server.py   # Windows PowerShell
# or: touch server.py (Linux/Mac)
```

### Step 4: Run your server
```powershell
# uv run handles venv activation automatically — no need to activate manually
uv run server.py

# Or, using the MCP CLI directly:
uv run mcp dev server.py
```

---

## 6.4 Complete Project Structure for a CTI Server

After setup, your project should look like:

```
cti-server/
├── .python-version       ← e.g., "3.12"
├── .env                  ← your API keys (NEVER commit this)
├── .gitignore            ← must include .env and .venv/
├── pyproject.toml        ← dependencies and project metadata
├── uv.lock               ← exact locked versions (commit this to git)
├── README.md
└── server.py             ← your MCP server code
```

### The `.gitignore` file (critical)
```
# .gitignore
.env
.venv/
__pycache__/
*.pyc
```

---

## 6.5 uv vs pip: Command Mapping

If you learned Python with `pip`, here's the direct translation:

| Old way (`pip` + `venv`) | New way (`uv`) |
|---|---|
| `python -m venv .venv` | `uv venv` (or automatic on `uv add`) |
| `source .venv/Scripts/activate` | Not needed — use `uv run` instead |
| `pip install requests` | `uv add requests` |
| `pip install -r requirements.txt` | `uv sync` |
| `python server.py` | `uv run server.py` |
| `python -m mcp dev server.py` | `uv run mcp dev server.py` |

> **Key mindset shift:** With `uv`, you rarely need to "activate" the virtual environment. Just prefix commands with `uv run` and uv handles the rest.

---

## 6.6 The Complete Server File (With uv Context)

Save this as `cti-server/server.py`:

```python
# server.py — CTI Enrichment MCP Server
# Run with: uv run mcp dev server.py

import os
import re
import requests
from dotenv import load_dotenv
from mcp.server.fastmcp import FastMCP

# Load .env file (ABUSEIPDB_KEY, VT_API_KEY, etc.)
load_dotenv()

# FastMCP creates the MCP server instance
mcp = FastMCP("CTI Enrichment Server")

ABUSEIPDB_KEY = os.environ.get("ABUSEIPDB_KEY", "")
IPV4 = re.compile(r"^(\d{1,3}\.){3}\d{1,3}$")


def valid_ip(ip: str) -> bool:
    return bool(IPV4.match(ip)) and all(0 <= int(o) <= 255 for o in ip.split("."))


@mcp.tool()
def enrich_ip(ip_address: str) -> dict:
    """Retrieve AbuseIPDB threat intelligence for an IPv4 address.
    Use when an IP appears in an alert to get abuse score, country, and ISP.
    Returns: abuse_score (0-100), country, isp, total_reports, status.
    Read-only. Safe to automate."""

    if not ABUSEIPDB_KEY:
        return {"status": "error", "reason": "ABUSEIPDB_KEY not set"}
    if not valid_ip(ip_address):
        return {"status": "error", "reason": f"Invalid IPv4: '{ip_address}'"}

    try:
        r = requests.get(
            "https://api.abuseipdb.com/api/v2/check",
            headers={"Key": ABUSEIPDB_KEY, "Accept": "application/json"},
            params={"ipAddress": ip_address, "maxAgeInDays": 90},
            timeout=10
        )
        r.raise_for_status()
        d = r.json().get("data", {})
        return {
            "ip":           d.get("ipAddress", ip_address),
            "abuse_score":  d.get("abuseConfidenceScore", 0),
            "country":      d.get("countryCode", "unknown"),
            "isp":          d.get("isp", "unknown"),
            "total_reports":d.get("totalReports", 0),
            "status":       "ok"
        }
    except requests.exceptions.Timeout:
        return {"status": "error", "reason": "AbuseIPDB timed out"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


# Entry point for uv run and MCP Inspector
if __name__ == "__main__":
    mcp.run()
```

---

## 6.7 Running the Server Three Ways

### Way 1: MCP Inspector (Development and Testing)
```powershell
uv run mcp dev server.py
```
Opens the MCP Inspector in your browser — you can call tools manually, see inputs/outputs, and debug before connecting any AI client.

### Way 2: Direct Python Run (Quick Debug)
```powershell
uv run python server.py
```
Starts the server in stdio mode — used when you want to see raw output.

### Way 3: Register with Claude Desktop (Production)
In your Claude Desktop `claude_desktop_config.json`:
```json
{
  "mcpServers": {
    "cti-server": {
      "command": "uv",
      "args": [
        "--directory",
        "d:\\mcp_course\\cti-server",
        "run",
        "server.py"
      ],
      "env": {
        "ABUSEIPDB_KEY": "your-key-here"
      }
    }
  }
}
```

> **Note:** You can also store the key in `.env` and omit the `"env"` block — uv + dotenv will pick it up automatically. The `"env"` block in the config is an alternative for keys you want Claude Desktop to manage.

---

## 6.8 Useful uv Commands Reference

```powershell
# Create new project
uv init my-mcp-server

# Add a dependency
uv add requests

# Remove a dependency
uv remove requests

# Install all dependencies from pyproject.toml (e.g., after cloning a repo)
uv sync

# Run your server
uv run server.py

# Run MCP Inspector
uv run mcp dev server.py

# Check Python version being used
uv python list

# Pin a specific Python version for the project
uv python pin 3.12

# Show all installed packages in the project
uv pip list
```

---

## 6.9 Quick Setup Checklist

Use this every time you start a new MCP project:

- [ ] `uv init project-name` — create the project
- [ ] `cd project-name` — enter the directory
- [ ] `uv add "mcp[cli]" requests python-dotenv` — install dependencies
- [ ] Create `.env` with your API keys
- [ ] Create `.gitignore` with `.env` and `.venv/`
- [ ] Write your `server.py`
- [ ] `uv run mcp dev server.py` — test in Inspector
- [ ] Register in Claude Desktop config

---

## Key Takeaways

1. `uv` is the recommended toolchain for MCP Python development — used in official docs and the community.
2. `uv init` → `uv add` → `uv run` replaces the old `venv` + `pip` + `source activate` workflow.
3. `uv run server.py` handles environment activation automatically — no manual activation needed.
4. `uv run mcp dev server.py` opens the MCP Inspector — your primary testing tool before Module 5.
5. API keys go in `.env` (loaded by `python-dotenv`) — never in `pyproject.toml` or config files committed to git.
6. `uv.lock` should be committed to git. `.env` and `.venv/` must never be committed.

---

## Further Reading

- [uv Official Docs](https://docs.astral.sh/uv/)
- [MCP Official Quickstart (Python)](https://modelcontextprotocol.io/docs/develop/build-server#set-up-your-environment)
- [FastMCP GitHub](https://github.com/jlowin/fastmcp)

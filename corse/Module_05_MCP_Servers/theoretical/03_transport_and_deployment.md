---
status: draft
---

# 03 — Transport, Deployment, and Claude Desktop Integration

> **Theoretical Block 3 of 4 | Module 05: Building MCP Servers**

---

## 3.1 The Two MCP Transports

An MCP server communicates with its client through a **transport**. FastMCP supports two:

| Transport | Run command | Client connects via | Best for |
|---|---|---|---|
| **STDIO** | `uv run server.py` | Subprocess pipe (stdin/stdout) | Local development, lab servers |
| **Streamable HTTP(SSE)** | `uv run mcp server.py --transport sse` | HTTP GET + POST | Remote servers, production, shared infrastructure |

For this course: **STDIO for all labs**. You will encounter SSE in Module 8 (production deployments).

---

## 3.2 Running Your Server: Three Modes

```powershell
# Mode 1: MCP Inspector (development — visual testing tool)
uv run mcp dev server.py
# Opens browser at http://localhost:5173/
# You can call any tool manually and see raw JSON responses

# Mode 2: Direct stdio run (quick debug — see raw output)
uv run server.py

# Mode 3: Production via Claude Desktop (register in config)
# Edit claude_desktop_config.json and add your server
# Then restart Claude Desktop
```

---

## 3.3 Registering Your Server in Claude Desktop

Find your config file:
- **Windows:** `%APPDATA%\Claude\claude_desktop_config.json`
- **macOS:** `~/Library/Application Support/Claude/claude_desktop_config.json`

Add your server to the `mcpServers` section:

```json
{
  "mcpServers": {
    "cti-server": {
      "command": "uv",
      "args": [
        "--directory",
        "d:\\mcp_course\\cti-mcp-server",
        "run",
        "server.py"
      ]
    }
  }
}
```

**Why `uv` as the command?** Because `uv` handles venv activation automatically. Without it, you'd need to point to the exact Python binary inside `.venv/`.

### Passing API Keys to Claude Desktop

Option A — In the config (simpler, keys visible to Claude Desktop process):
```json
{
  "mcpServers": {
    "cti-server": {
      "command": "uv",
      "args": ["--directory", "d:\\mcp_course\\cti-mcp-server", "run", "server.py"],
      "env": {
        "ABUSEIPDB_KEY": "your-key-here"
      }
    }
  }
}
```

Option B — In a `.env` file in your project directory (recommended — keys not in config file):
```
# d:/mcp_course/cti-mcp-server/.env
ABUSEIPDB_KEY=your-key
VT_API_KEY=your-vt-key
```
`python-dotenv` will auto-load these when the server starts.

After editing the config, **restart Claude Desktop** for changes to take effect.

---

## 3.4 Testing in the MCP Inspector

```powershell
uv run mcp dev server.py
```

The Inspector shows three panels:
1. **Tools** — list all registered tools, call them with custom inputs, see the raw return dict
2. **Resources** — browse and read registered resources by URI
3. **Prompts** — preview and fill in registered prompt templates

**Testing checklist before connecting to Claude Desktop:**
- [ ] All tools appear in the Tools panel
- [ ] Each tool returns `{"status": "ok", ...}` or `{"status": "error", "reason": "..."}` (never a crash)
- [ ] Invalid inputs return a clean error dict (test with `enrich_ip("not-an-ip")`)
- [ ] API key missing returns a clean error dict (unset the env var and test)
- [ ] No `print()` statements cause JSON parse errors in the Inspector output

---

## 3.5 Adding Context (Dependencies) to Your Server

FastMCP provides a `Context` object for accessing server-level features — primarily **logging** inside a tool:

```python
from mcp.server.fastmcp import FastMCP, Context

mcp = FastMCP("CTI Server")


@mcp.tool()
def enrich_ip(ip_address: str, ctx: Context = None) -> dict:
    """Retrieve threat intelligence for an IPv4 address...
    Read-only. Safe to automate."""

    if ctx:
        ctx.info(f"Starting enrich_ip for {ip_address}")  # → MCP server log

    try:
        # ... API call ...
        if ctx:
            ctx.info(f"Got abuse_score={score} for {ip_address}")
        return {"ip": ip_address, "abuse_score": score, "status": "ok"}

    except Exception as e:
        if ctx:
            ctx.error(f"enrich_ip failed: {e}")
        return {"status": "error", "reason": str(e)}
```

`ctx.info()` and `ctx.error()` write to the MCP log channel — visible in the Inspector's log panel and in server stderr. They do **not** go to stdout.

---

## Key Takeaways

1. STDIO transport = stdout/stdin pipe, STDIO servers started by the client as subprocesses.
2. `uv run mcp dev server.py` always first — test in Inspector before registering with Claude Desktop.
3. Claude Desktop config: `"command": "uv"`, `"args": ["--directory", "...", "run", "server.py"]`.
4. API keys go in `.env` (loaded by `python-dotenv`) or in the `"env"` block of the config — never hardcoded.
5. The Inspector's tool tester validates input/output before any LLM interaction.

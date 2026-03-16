---
status: published
---

# Example 02 — SDK vs Raw: Reading JSON-RPC to Debug a Server

> **Example Block 2 of 2 | Module 05: Building MCP Servers**

---

## Scenario

Your CTI server is registered in Claude Desktop but Claude can't see its tools. The MCP Inspector shows "connection failed." This walkthrough shows how to use raw JSON-RPC knowledge to debug the problem.

---

## Step 1 — Run the Server Directly and Watch the Output

```powershell
cd d:/mcp_course/cti-mcp-server
uv run python server.py
```

If the server is healthy, you should see it waiting (cursor blinking, no error). If it exits immediately, there is an import error.

**Common immediate exit causes:**
```
ModuleNotFoundError: No module named 'mcp'
→ Fix: uv add "mcp[cli]"

ModuleNotFoundError: No module named 'dotenv'
→ Fix: uv add python-dotenv

SyntaxError: invalid syntax (line 12)
→ Fix: check the tool function at line 12
```

---

## Step 2 — Send a Raw JSON-RPC Initialize Message

Understanding raw protocol lets you test a STDIO server without any client library:

```bash
# Linux/Mac: pipe a JSON-RPC initialize message to your server
echo '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2024-11-05","capabilities":{},"clientInfo":{"name":"debug","version":"1.0"}}}' | uv run python server.py
```

**Healthy server response:**
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "protocolVersion": "2024-11-05",
    "capabilities": {"tools": {"listChanged": true}},
    "serverInfo": {"name": "CTI Enrichment Server", "version": "1.0.0"}
  }
}
```

**If you see nothing**: the server is consuming the message but not responding — usually because you have a `print()` statement that corrupted the output stream, or a bug in initialization.

**If you see garbage**: a `print()` is polluting stdout before the JSON response.

---

## Step 3 — List Tools via Raw Protocol

```bash
# After initialize, send tools/list (Windows PowerShell — two messages piped together)
@"
{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2024-11-05","capabilities":{},"clientInfo":{"name":"debug","version":"1.0"}}}
{"jsonrpc":"2.0","id":2,"method":"tools/list","params":{}}
"@ | uv run python server.py
```

**Expected output** (two JSON responses):
```json
{"jsonrpc":"2.0","id":1,"result":{"protocolVersion":"2024-11-05",...}}
{"jsonrpc":"2.0","id":2,"result":{"tools":[{"name":"enrich_ip","description":"...","inputSchema":{...}}]}}
```

If `"tools":[]` → your `@mcp.tool()` decorators are not being registered (check imports).
If the tool appears but its `description` is empty → your docstring is malformed.

---

## Step 4 — Call a Tool via Raw Protocol

```json
{"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"enrich_ip","arguments":{"ip_address":"8.8.8.8"}}}
```

Expected response:
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "result": {
    "content": [{"type": "text", "text": "{\"ip\": \"8.8.8.8\", \"abuse_score\": 0, ...}"}]
  }
}
```

The tool result is embedded as a text string inside the `content` array — this is what `result.content[0].text` extracts in the client.

---

## Step 5 — Fix Claude Desktop Config Issues

Most "Claude doesn't see tools" problems are config issues, not code issues:

```json
// ❌ Common mistake: using absolute python path (breaks on venv updates)
{
  "mcpServers": {
    "cti": {
      "command": "d:\\mcp_course\\cti-mcp-server\\.venv\\Scripts\\python.exe",
      "args": ["server.py"]
    }
  }
}

// ✅ Correct: use uv — handles venv automatically
{
  "mcpServers": {
    "cti": {
      "command": "uv",
      "args": ["--directory", "d:\\mcp_course\\cti-mcp-server", "run", "server.py"]
    }
  }
}
```

**Debugging checklist:**
- `--directory` path uses `\\` (Windows) not `/`
- Server file name is exact (case-sensitive on some systems)
- After editing config: **restart Claude Desktop completely** (not just the conversation)
- Check MCP server logs: View → MCP Server Logs in Claude Desktop

---

## Key Learning: Why Raw Protocol Knowledge Matters

| Scenario | What you do with raw protocol knowledge |
|---|---|
| Server doesn't appear in Claude | Send raw initialize → see if server responds at all |
| Tool not showing in Inspector | Send raw tools/list → see if tool registered correctly |
| Tool produces wrong output | Send raw tools/call → compare raw JSON result to expected |
| Can't find config syntax | Read raw JSON-RPC error message to understand what the client sent |

The Bash server from Block 4 was not practical for production — but reading its code taught you exactly what happens in every JSON-RPC exchange. That knowledge now helps you debug.

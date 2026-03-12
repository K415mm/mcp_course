---
status: draft
---

# Module 7: Integrate MCP into AI Workspaces

## Module Goal

Connect your MCP servers to real AI development environments — Trae AI, VS Code, Cursor, and Claude Desktop — and understand the configuration pattern that works across all of them.

## Learning Objectives

1. Explain the universal MCP configuration pattern (command, args, env).
2. Register a local stdio MCP server in Trae AI.
3. Configure MCP servers in VS Code, Cursor, and Claude Desktop.
4. Test and debug MCP connections using available inspection tools.
5. Apply security hygiene to IDE-level MCP integrations.

---

## Theoretical Section

### 7.1 The Universal Configuration Pattern

Despite different products using different config file names, the MCP connection spec is the same across all hosts. Every host needs three things to connect to a local stdio MCP server:

```json
{
  "command": "python",
  "args": ["path/to/server.py"],
  "env": {
    "API_KEY": "${API_KEY}"
  }
}
```

| Field | Purpose | Example |
|---|---|---|
| `command` | Executable to run | `python`, `node`, `uv` |
| `args` | Arguments to pass | `["cti_server.py"]` |
| `env` | Environment variables for secrets | `{ "VT_API_KEY": "${VT_API_KEY}" }` |

For remote servers (Streamable HTTP), replace `command`/`args` with a `url` and optional `headers`:

```json
{
  "url": "https://mcp.myorg.internal/cti",
  "headers": {
    "Authorization": "Bearer ${MCP_TOKEN}"
  }
}
```

**Security rule:** always use `${VAR_NAME}` references — never paste API keys directly into config files that may be committed to version control.

---

### 7.2 How the Host Uses Your Server Once Connected

When the IDE agent starts a session:

1. It spawns your server process (or connects to the remote URL).
2. It sends `tools/list` → receives your tool registry.
3. It injects the tool list into the LLM's context.
4. When you or the LLM asks for a tool to run, the host calls `tools/call` on your server.
5. Results come back as context additions to the ongoing conversation.

This means: **the quality of your tool docstrings directly determines how well the LLM uses your tools in an IDE context.** The IDE does not help the LLM understand your tool beyond what you wrote in the docstring.

---

## Practical Section

### 7.3 Trae AI Integration

Trae AI supports both local stdio servers and remote SSE/Streamable HTTP servers.

#### Adding a Local Server

1. Open Trae settings → **AI** → **MCP Servers**.
2. Click **Add Server** → choose **Stdio**.
3. Fill in the form:

```
Name:    CTI Enrichment
Command: python
Args:    d:/mcp_course/servers/cti_server.py
Env:     VT_API_KEY=<from system env>
```

4. Save and restart the AI agent context.
5. In the Trae chat, ask: *"What tools do you have available?"* — confirm your tools appear.

#### Adding a Remote Server

1. Add Server → choose **Remote / HTTP**.
2. Fill in:

```
Name:    Shared SIEM Connector
URL:     https://mcp.myorg.internal/siem
Headers: Authorization: Bearer <token>
```

#### Testing in Trae

Ask the agent: *"Enrich this IP: 185.220.101.45"*

Expected behavior: the agent recognizes the intent, calls `check_ip_reputation("185.220.101.45")`, and returns the result in conversation.

---

### 7.4 VS Code Integration

VS Code (with GitHub Copilot or similar agent extension) uses a workspace-level `mcp.json` file.

**File location:** `.vscode/mcp.json` in your project root.

```json
{
  "servers": {
    "cti-enrichment": {
      "type": "stdio",
      "command": "python",
      "args": ["${workspaceFolder}/servers/cti_server.py"],
      "env": {
        "VT_API_KEY": "${env:VT_API_KEY}"
      }
    },
    "network-triage": {
      "type": "stdio",
      "command": "python",
      "args": ["${workspaceFolder}/servers/network_server.py"]
    }
  }
}
```

VS Code uses `${workspaceFolder}` for paths and `${env:VAR}` for referencing shell environment variables.

**Reload:** after saving `mcp.json`, trigger **Reload Window** from the command palette. Confirm tools appear in the Copilot Chat tool panel.

---

### 7.5 Cursor Integration

Cursor uses a workspace-level `cursor_mcp.json` or global `~/.cursor/mcp.json`.

**Workspace config** (`.cursor/mcp.json`):

```json
{
  "mcpServers": {
    "cyber-defense": {
      "command": "python",
      "args": ["servers/cti_server.py"],
      "env": {
        "VT_API_KEY": "${VT_API_KEY}"
      }
    }
  }
}
```

After saving, open Cursor Settings → **Features** → **MCP** and toggle the server on. In Cursor chat (Agent mode), type `@cyber-defense` to scope tool calls to your server.

---

### 7.6 Claude Desktop Integration

Claude Desktop uses a global config at:
- Windows: `%APPDATA%\Claude\claude_desktop_config.json`
- macOS: `~/Library/Application Support/Claude/claude_desktop_config.json`

```json
{
  "mcpServers": {
    "cti-enrichment": {
      "command": "python",
      "args": ["D:/mcp_course/servers/cti_server.py"],
      "env": {
        "VT_API_KEY": "your-key-here"
      }
    }
  }
}
```

> **Note:** Claude Desktop currently uses full absolute paths for `args`. Restart Claude Desktop after editing the config. The hammer icon in the chat UI confirms tools are loaded.

---

### 7.7 Debugging MCP Connections

#### MCP Inspector (universal)

```bash
npx @modelcontextprotocol/inspector python servers/cti_server.py
```

Opens a browser UI at `http://localhost:5173` where you can:
- View the full tool list from your server.
- Send test `tools/call` requests manually.
- Inspect raw JSON-RPC messages in both directions.

This is the fastest way to confirm your server works before connecting it to any IDE.

#### Common Problems and Fixes

| Problem | Likely Cause | Fix |
|---|---|---|
| Tools not appearing in IDE | Wrong path in args | Use absolute path; test with inspector first |
| `KeyError` on API key | Env var not passed | Verify env block in config; check `os.environ.get` |
| Tools silently failing | Exception not caught | Wrap all logic in try/except returning error dict |
| LLM not calling your tool | Weak docstring | Rewrite docstring to explicitly say when to call it |
| Remote server auth failure | Missing/expired token | Check header format: `Bearer <token>`, not just token |

---

### 7.8 Security Hygiene for IDE Integrations

- **Never commit config files with keys.** Add `mcp.json`, `cursor_mcp.json`, and `claude_desktop_config.json` to `.gitignore`.
- **Prefer env var references** (`${VT_API_KEY}`) over inline values in all configs.
- **Scope tool permissions.** A tool registered in an IDE runs with the same OS permissions as the IDE process. Do not expose destructive tools in shared/multi-user IDE configs.
- **Review tool lists before granting agent access.** Every tool the LLM can see is a tool it might call. Keep servers tightly scoped.

---

## Example Section

### Full Setup: Trae AI + CTI Server

**Goal:** Trae agent auto-enriches any IP mentioned in analyst chat.

1. Save `cti_server.py` to `d:/mcp_course/servers/`.
2. Set `VT_API_KEY` in your system environment.
3. In Trae: Add stdio server → `python` → `d:/mcp_course/servers/cti_server.py`.
4. In chat: *"This alert shows connections to 185.220.101.45. What do we know about this IP?"*
5. Trae agent calls `check_ip_reputation("185.220.101.45")` → returns structured result → LLM summarizes in conversation.

No extra prompt engineering required — the docstring did the work.

---

## Knowledge Check

1. What three fields are required in every stdio MCP server config block?
2. Why should config files with MCP credentials be in `.gitignore`?
3. What tool can you use to test an MCP server without any IDE?
4. Where is the Claude Desktop MCP config file on Windows?
5. Why does a weak docstring result in the LLM not calling your tool?

---

## Reading List (Module 7 Source Files)

- [Integrating MCP with AI IDEs and Workflows.md](file:///d:/mcp_course/Integrating%20MCP%20with%20AI%20IDEs%20and%20Workflows.md)
- [The MCP Cyber Defense Workshop_ Agentic AI and Tooling Architecture (1).md](file:///d:/mcp_course/The%20MCP%20Cyber%20Defense%20Workshop_%20Agentic%20AI%20and%20Tooling%20Architecture%20(1).md)
- [MCP_Universal_AI_Connectivity (1).pdf](file:///d:/mcp_course/MCP_Universal_AI_Connectivity%20(1).pdf)

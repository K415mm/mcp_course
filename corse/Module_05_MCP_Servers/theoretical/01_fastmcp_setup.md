---
status: draft
---

# 01 — What is an MCP Server? FastMCP Setup with uv

> **Theoretical Block 1 of 4 | Module 05: Building MCP Servers**

---

## 1.1 The MCP Server at One Sentence

An **MCP server** is an independent Python process that exposes functions (tools), data (resources), and prompt templates to any compliant LLM client — without the client needing to know anything about the implementation.

That independence is the key architectural decision: MCP servers are not modules you import. They are processes that communicate via a standard protocol.

---

## 1.2 Why FastMCP?

The MCP Python SDK includes a class called `FastMCP` that handles everything you don't want to write yourself:

| What FastMCP handles | What you would write without it |
|---|---|
| JSON-RPC 2.0 message parsing | Hundreds of lines of protocol boilerplate |
| Tool schema generation | Manually writing JSON Schema from type hints |
| Transport negotiation (STDIO / SSE) | Socket management and handshake code |
| Tool registry | A dict you must manage manually |
| MCP Inspector integration | Custom debug tooling |

You focus on **one thing**: the function body. FastMCP does the rest.

---

## 1.3 Project Setup with uv (The Right Way)

```powershell
# Step 1: Create the project
uv init cti-mcp-server
cd cti-mcp-server

# Step 2: Pin Python version (3.12 recommended for MCP stability)
uv python pin 3.12

# Step 3: Add all dependencies
uv add "mcp[cli]" requests python-dotenv

# Step 4: Create the server file
New-Item server.py

# Step 5: Verify installation
uv run python -c "from mcp.server.fastmcp import FastMCP; print('FastMCP OK')"
```

Your project now looks like:
```
cti-mcp-server/
├── .python-version     ← "3.12"
├── .env                ← API keys (never commit)
├── .gitignore          ← .env, .venv/
├── pyproject.toml      ← dependencies
├── uv.lock             ← exact locked versions (commit this)
├── README.md
└── server.py           ← your MCP server
```

---

## 1.4 The Minimal Working Server

This is the smallest possible MCP server with one tool:

```python
# server.py
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("My First Server")

@mcp.tool()
def ping(message: str) -> dict:
    """Echo a message back to confirm the server is running.
    Use to verify the MCP server is alive and reachable.
    Returns: echo of the input message, status.
    Read-only. Safe to automate."""
    return {"echo": message, "status": "ok"}

if __name__ == "__main__":
    mcp.run()
```

Run and test it:
```powershell
# Launch the MCP Inspector — opens in your browser
uv run mcp dev server.py
```

Click the `ping` tool in the Inspector, type a message, confirm you get an echo. Your server is working.

---

## 1.5 What FastMCP Does When You Decorate a Function

When Python loads this file and hits `@mcp.tool()`, FastMCP:

1. **Reads the type hints** → builds a JSON Schema for the tool's inputs
2. **Reads the docstring** → uses it as the tool description for the LLM
3. **Registers the tool** → adds it to the internal tool registry
4. **Wraps the function** → makes it callable via MCP JSON-RPC

The function itself is unchanged. It still works as a normal Python function.

```python
# This still works (not just as an MCP tool):
result = ping("hello")
print(result)  # {"echo": "hello", "status": "ok"}
```

This means you can test your tool logic as a regular function before adding MCP machinery.

---

## 1.6 The Three Things a FastMCP Server Can Expose

| Type | Decorator | What it does |
|---|---|---|
| **Tool** | `@mcp.tool()` | AI-callable function (the agent decides when to call it) |
| **Resource** | `@mcp.resource("uri://...")` | Read-only data endpoint (like a GET API call) |
| **Prompt** | `@mcp.prompt()` | Pre-written message template the user can invoke |

For this course — and for cyber defense use — **tools are 95% of what you will build**. Resources and prompts are covered in Block 2.

---

## 1.7 Logging: the One Rule You Must Follow

In MCP, your server communicates with the client via `stdout`. If you `print()` to stdout, you corrupt the protocol stream.

```python
# ❌ NEVER — corrupts the MCP STDIO channel
print("Processing request")

# ✅ ALWAYS — writes to stderr, not stdout
import sys
print("Processing request", file=sys.stderr)

# ✅ ALSO OK — logging goes to stderr by default
import logging
logging.basicConfig(level=logging.INFO)
logging.info("Processing request")
```

Using `print()` in an MCP tool is one of the most common beginner bugs. The server will silently malfunction.

---

## Key Takeaways

1. An MCP server is an independent process — not a module you import.
2. FastMCP handles all protocol machinery — you only write the function.
3. `uv init` → `uv add "mcp[cli]"` → `uv run mcp dev server.py` is the complete setup.
4. `@mcp.tool()` reads type hints + docstring → auto-generates the tool schema.
5. Never use `print()` in an MCP server — it corrupts the STDIO stream. Use `logging` or `sys.stderr`.

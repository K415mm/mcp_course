---
status: published
---

# Practical 03 — Knowledge Check and Module Checklist

> **Practical Block 3 of 3 | Module 06: Building MCP Clients**

---

## Section A: Knowledge Check Quiz

**Q1.** What are the four steps of the MCP client lifecycle (in order)?

**Q2.** When should you choose STDIO over SSE transport? Give two specific scenarios for each.

**Q3.** Why does MCP client code use `asyncio`? What would happen if every tool call was synchronous?

**Q4.** What does `AsyncExitStack` do? What happens if you don't call `cleanup()`?

**Q5.** `session.call_tool("enrich_ip", {"ip_address": "1.2.3.4"})` returns a `result` object. Write the exact Python expression to extract the text result from it.

**Q6.** Why does `invoke_tool` use `asyncio.wait_for(..., timeout=30.0)`? What timeout would be better for a file sandbox analysis tool that takes up to 2 minutes?

**Q7.** In `connect_to_server`, the code checks `server_path_or_url.endswith(".py")` to set `command = "python"`. Why is this important — what goes wrong if `command = "uv run python"` instead?

**Q8.** After calling `session.list_tools()`, you get back tool objects with `.name`, `.description`, and `.inputSchema`. What is the `inputSchema` used for?

**Q9.** You have a CTI server and a SIEM server. You call `client.list_all_tools()`. The result mixes tools from both servers. How does the `server_id` field help correctly route a subsequent `invoke_tool()` call?

**Q10.** Write the `asyncio.run()` call pattern that ensures `client.cleanup()` always executes, even if an exception occurs during tool invocation.

---

## Section B: Code Review

Spot all issues in this client code:

```python
import asyncio
from mcp_client import MCPClient

async def run():
    client = MCPClient()
    
    tools = await client.connect_to_server("cti", "server.py")
    
    for i in range(100):
        result = await client.invoke_tool("cti", "enrich_ip", 
                                          {"ip_address": f"192.168.1.{i}"})
        print(result)         # Problem 1?
    
    # Program ends here — no cleanup
```

**Issues to find:**
1. `print(result)` inside a tool — if `result` is used in the same process as an MCP server's STDIO transport, this could cause issues
2. No `try/finally` — if any `invoke_tool` fails, `cleanup()` never runs → orphaned process  
3. No timeout handling — 100 API calls with no rate limiting → likely 429 errors
4. No `asyncio.run()` wrapper shown — the function must be called correctly
5. Hardcoded `"server.py"` path — may not work from different working directories

---

## Section C: Module 06 Self-Assessment

| Skill | Rating /5 |
|---|---|
| Understanding STDIO vs SSE transport selection | /5 |
| Writing `MCPClient.__init__()` from memory | /5 |
| Implementing `connect_to_server()` correctly | /5 |
| Using `asyncio.wait_for()` with correct timeout | /5 |
| Using `try/finally` with `client.cleanup()` | /5 |
| Extracting text from `result.content[0].text` | /5 |
| Connecting to multiple servers simultaneously | /5 |
| Running async code with `asyncio.run()` | /5 |

---

## Section D: Exit Ticket

1. **The asyncio concept that confused me the most was..., and now I understand that...**
2. **The production safety pattern I will add to every client I build is...**
3. **After this module, I could build an agent that...**

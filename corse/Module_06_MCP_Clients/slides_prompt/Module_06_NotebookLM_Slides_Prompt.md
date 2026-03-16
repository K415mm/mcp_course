---
status: published
---

# Module 06 — Google NotebookLM Slides Generation Prompt

## How to Use

1. Open [NotebookLM](https://notebooklm.google.com/) → create notebook: `Module 06 — Building MCP Clients`.
2. Upload all files from `Module_06_MCP_Clients/` as sources.
3. Paste the **master prompt below** into the Studio/Chat panel.
4. Only change the four `[BRACKETED]` fields for each module run.

---

## MASTER SLIDES PROMPT

```
You are a professional instructional designer creating a slide deck for a technical cybersecurity course. Follow the EXACT design system defined below on every slide. This ensures consistent branding across all 8 modules.

═══════════════════════════════════════════════════
COURSE IDENTITY
═══════════════════════════════════════════════════
Course Name: Beginner's Guide to Agentic AI and MCP in Cyber Defense
Audience: Security analysts, SOC engineers, threat hunters (beginner–intermediate)
Tone: Systems thinking focus — "this is how the parts connect." Emphasize async concepts gently; students may not have seen asyncio before. Make it feel achievable.
This is Module: [06]
Module Title: [Building MCP Clients]
Core Topics (4): [Client Architecture and Lifecycle | MCPClient: Connecting and Discovering | Invoking Tools and Cleanup | Agent Loop and Audit Logging]

═══════════════════════════════════════════════════
DESIGN SYSTEM — APPLY TO EVERY SLIDE (DO NOT CHANGE)
═══════════════════════════════════════════════════

COLOR PALETTE:
- Background: Deep navy #0D1B2A (primary), charcoal #1A2332 (section dividers)
- Primary accent: Electric blue #00A8FF
- Secondary accent: Cyan #00E5CC
- Warning/bad examples: Amber #FFB347
- Correct/safe patterns: Emerald #00D68F
- Text primary: White #FFFFFF
- Text secondary: Light grey #B0BEC5
- Code blocks: Dark panel #0A1628, cyan text #00E5CC

TYPOGRAPHY: Inter/Outfit Bold (titles ALL CAPS), Inter/Roboto Regular (body), JetBrains Mono (code)
BRANDING: Top-left every slide: 🛡 MCP-CD | Bottom-right: M06/S## | Bottom-left: module title (small caps, grey)
MAX 5 BULLETS PER SLIDE. Each bullet ≤ 12 words. Code slides = minimal bullets, code block speaks.
Every slide has ONE primary takeaway in a callout box or highlighted text.

═══════════════════════════════════════════════════
SLIDE STRUCTURE — GENERATE IN THIS ORDER
═══════════════════════════════════════════════════

SLIDE 01 — TITLE SLIDE
Title: MODULE 06 — BUILDING MCP CLIENTS
Visual: Two-node diagram — "LLM" node ↔ "MCPClient" ↔ "MCP Server" node with arrows
Sub: "Connect servers. Discover tools. Close the agent loop."
Learning objectives (4):
  - Understand the MCP client lifecycle (Connect → Discover → Invoke → Cleanup)
  - Build the MCPClient class from scratch
  - Choose correctly between STDIO and SSE transport
  - Write a simple autonomous agent loop

SLIDE 02 — WHERE DOES THE CLIENT FIT?
Architecture diagram (center of slide):
  LLM (Think) ↕ tool definitions + results
  MCPClient (Bridge)
  ↕ JSON-RPC 2.0 over STDIO or SSE
  MCP Server A  |  MCP Server B  |  MCP Server C
Below:
  "Claude Desktop = MCP client. VS Code Copilot = MCP client. YOUR Python code = MCP client."
Callout: "Module 05 = you build the server. Module 06 = you build the client. Together = an agent."

SLIDE 03 — THE FOUR-STEP LIFECYCLE
Vertical flow diagram (4 steps with icons):
  1. CONNECT → Start/reach server, negotiate protocol version (10s timeout)
  2. DISCOVER → list_tools() → tool names, descriptions, schemas
  3. INVOKE → call_tool(name, args) → result (30s timeout)
  4. CLEANUP → close session, terminate subprocess (AsyncExitStack)
Arrows connect each step downward
Callout: "These 4 steps happen in EVERY agent run. No exceptions."

SLIDE 04 — STDIO vs SSE: CHOOSING THE RIGHT TRANSPORT
Two-column comparison table:
  | | STDIO | SSE |
  | Location | Same machine | Any machine |
  | Connect by | File path (.py) | URL (http://) |
  | Auth | env vars (process-level) | HTTP headers |
  | Dev use | ✅ Always | Labs only for remote |
  | Production | Local servers | Cloud/shared servers |
Below: Auto-detection code:
  parsed = urlparse(path_or_url)
  is_url = parsed.scheme in ("http", "https")
Callout: "Client auto-detects: URL → SSE, file path → STDIO. You don't manually choose."

SLIDE 05 — WHY asyncio?
Left: Simple explanation:
  - MCP client waits for I/O (network calls)
  - While waiting, it could be doing something else
  - asyncio = do multiple things concurrently on one thread
Right: The pattern students always use:
  async def main():
      client = MCPClient()
      tools = await client.connect_to_server(...)
      result = await client.invoke_tool(...)
      await client.cleanup()
  
  asyncio.run(main())
Callout: "You don't need asyncio internals. Pattern: async def + await + asyncio.run()."
Speaker notes: The key mental model: every 'await' means "start this, continue when ready." asyncio.run() starts and manages the event loop.

SLIDE 06 — MCPClient.__init__: THE THREE STATE VARIABLES
Annotated code block:
  def __init__(self):
      self.sessions = {}          # server_id → active ClientSession
      self._server_tools = {}    # server_id → list of tool dicts (cached)
      self.exit_stack = AsyncExitStack()  # stack of "cleanup when done"
Right: "What each variable does"
  sessions: routes invoke_tool() calls to the right server
  _server_tools: tool list returned after connect_to_server()
  exit_stack: closes ALL transports in reverse order at cleanup()
Callout: "One MCPClient. Multiple servers. Each server gets its own entry in both dicts."

SLIDE 07 — connect_to_server: DETECT TRANSPORT
Code block (STDIO path):
  if server_path_or_url.endswith(".py"):
      command = "python"           # Python server
  elif server_path_or_url.endswith(".sh"):
      command = "bash"             # Bash server
  
  server_params = StdioServerParameters(
      command=command,
      args=[server_path_or_url],
      env=None  # ← inherits .env variables from current process
  )
  transport = await self.exit_stack.enter_async_context(
      stdio_client(server_params)
  )
Callout: "env=None is intentional: the server inherits your .env. API keys flow automatically."

SLIDE 08 — connect_to_server: HANDSHAKE AND DISCOVERY
Code block (after transport established):
  read, write = transport
  session = await self.exit_stack.enter_async_context(
      ClientSession(read, write)
  )
  
  # Protocol handshake (10 second timeout)
  await asyncio.wait_for(session.initialize(), timeout=10.0)
  
  # Tool discovery
  response = await asyncio.wait_for(session.list_tools(), timeout=10.0)
  
  tools = [
      {"name": t.name, "description": t.description, "parameters": t.inputSchema}
      for t in response.tools
  ]
Callout: "t.description = the docstring you wrote in Module 04. The LLM reads it here."
Speaker notes: The 10-second timeout prevents a hung server from blocking your client indefinitely. If initialize doesn't respond in 10 seconds, something is wrong with the server.

SLIDE 09 — AsyncExitStack: WHY IT MATTERS
Left: Without AsyncExitStack (amber, ❌):
  # Server subprocess keeps running after your code exits
  # Python process orphaned in task manager
  # Each run leaks a server process → 10 runs = 10 orphans
Right: With AsyncExitStack (emerald, ✅):
  async def cleanup(self):
      await self.exit_stack.aclose()
      # → closes session
      # → closes transport
      # → terminates server subprocess
      # In correct reverse order
Callout: "Every server you connect to should eventually be disconnected. AsyncExitStack guarantees it."

SLIDE 10 — invoke_tool: CALL AND EXTRACT RESULT
Annotated code block:
  result = await asyncio.wait_for(
      session.call_tool(tool_name, tool_args),
      timeout=30.0   # ← 30 seconds: long for analysis, short enough to catch hangs
  )
  
  # result.content = [ TextContent(text="...") ]
  return result.content[0].text if result.content else ""
Right: "What the 30-second timeout prevents"
  - Malware analysis server hanging on large file
  - External API not responding
  - Server crash leaving socket open
  - Agent waiting forever, no user notification
Callout: "Always use asyncio.wait_for() on every tool call. Never let a server hang forever."

SLIDE 11 — THE try/finally PATTERN
Code block (correct usage):
  async def main():
      client = MCPClient()
      try:
          tools = await client.connect_to_server("cti", "server.py")
          result = await client.invoke_tool("cti", "enrich_ip", {"ip_address": "..."})
          print(result)
      finally:
          await client.cleanup()   # ← ALWAYS runs, even if invoke_tool raises
  
  asyncio.run(main())
Callout: "finally runs after try, even if an exception occurred. Cleanup is guaranteed."

SLIDE 12 — CONNECTING MULTIPLE SERVERS
Code block:
  cti_tools    = await client.connect_to_server("cti",    "path/cti/server.py")
  siem_tools   = await client.connect_to_server("siem",   "path/siem/server.py")
  malware_tools = await client.connect_to_server("malware", "path/malware/server.py")
  
  all_tools = client.list_all_tools()
  # Returns: [{"name": ..., "server_id": "cti"}, {"name": ..., "server_id": "siem"}, ...]
Right:
  - One MCPClient manages all servers
  - list_all_tools() combines everything
  - server_id routes invoke_tool() to right server
  - LLM sees all tools as one unified list
Callout: "The LLM sees one list. The client handles routing. You see none of the complexity."

SLIDE 13 — THE AGENT LOOP PATTERN
Flow diagram:
  CONNECT → (all servers)
  DISCOVER → (all tool lists → combined → pass to LLM)
  LLM DECIDES → (tool name + args from reasoning)
  INVOKE → client.invoke_tool(server_id, name, args)
  RETURN → result → LLM processes
  LOOP → (LLM decides again, or declares DONE)
  CLEANUP → (always)
Callout: "This loop is the foundation of every autonomous SOC agent in this workshop."

SLIDE 14 — AUDIT LOGGING: EVERY CALL LOGGED
Code block (audit log pattern):
  audit_entry = {
      "timestamp": datetime.utcnow().isoformat(),
      "analyst":   "agent",
      "server":    server_id,
      "tool":      tool_name,
      "args":      tool_args,
      "status":    "ok",
      "result_preview": result[:200]
  }
  with open("audit_log.jsonl", "a") as f:
      f.write(json.dumps(audit_entry) + "\n")
Callout: "JSONL = one JSON object per line. This format is directly ingested by most SIEMs."
Speaker notes: The audit log is non-negotiable for production deployments. Every tool call = one log line. The analyst reviews it during shift handover. Never delete audit logs.

SLIDE 15 — THE AUTONOMOUS TRIAGE AGENT (DEMO)
Left: Brief description of Example 02:
  - Alert arrives at 02:00 AM, no analyst present
  - Agent connects to CTI server automatically
  - Enriches IP + file hash in sequence
  - Applies triage rules: verdict HIGH
  - Writes structured brief → saves to file
  - Writes audit log entry
  - NO AUTOMATED ACTIONS TAKEN
Right: Output snippet showing the brief header:
  VERDICT: HIGH
  AUTOMATION LEVEL: Clear
  EVIDENCE (2 indicators):
    • Abuse score 98/100
    • Confirmed Tor exit node
  NO AUTOMATED ACTIONS TAKEN.
  Analyst approval required.
Callout: "The agent enriches and reports. The analyst decides. Act gate = always human."

SLIDE 16 — MODULE SUMMARY
4 takeaways (emerald bullet icons):
  - MCPClient: sessions + _server_tools + exit_stack — 3 state variables, always
  - connect_to_server: detects STDIO vs SSE, handshakes, discovers tools
  - invoke_tool: 30-second timeout, extract result.content[0].text
  - try/finally + cleanup(): non-negotiable — no orphaned server processes
Bonus: "STDIO for local dev. SSE for production. Audit log for every call."
Bottom progress bar: Module 06 of 08 — COMPLETE

═══════════════════════════════════════════════════
GENERATION RULES (SAME FOR ALL MODULES)
═══════════════════════════════════════════════════

Output format per slide:
--- SLIDE [##] ---
TITLE: [ALL CAPS]
LAYOUT: [visual description]
CONTENT: [bullets, table, diagram, or code]
DESIGN_NOTES: [colors, icons, emphasis]
SPEAKER_NOTES: [2–3 sentences — MANDATORY on slides 05, 08, 14]

Never use "suspicious", "dangerous", "malicious" without qualification.
Code blocks: JetBrains Mono, dark panel #0A1628, cyan text, line numbers shown.
GOOD/BAD comparisons: amber for ❌ bad examples, emerald for ✅ good patterns.
All tables: electric blue header row, striped navy/charcoal rows.
After all slides: output SLIDE DECK INDEX (Slide# | Title | Type | Source Block).

SOURCE MATERIAL: Generate ONLY from uploaded files. Write [VERIFY] in speaker notes if uncertain.
```

---

## Reuse Guide

| Module | Title | Topics | Tone Adjustment |
|---|---|---|---|
| 05 | Building MCP Servers | 4 | Hands-on, building-focused |
| **06** | **Building MCP Clients** | **4** | **Systems thinking, async-gentle** |
| 07 | Security and Guardrails | 4 | Security-critical, firm |

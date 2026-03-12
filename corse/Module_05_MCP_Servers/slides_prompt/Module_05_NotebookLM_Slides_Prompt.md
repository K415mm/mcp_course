---
status: draft
---

# Module 05 — Google NotebookLM Slides Generation Prompt

## How to Use

1. Open [NotebookLM](https://notebooklm.google.com/) → create notebook: `Module 05 — Building MCP Servers`.
2. Upload all files from `Module_05_MCP_Servers/` as sources.
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
Tone: Hands-on and building-focused. Assume Python knowledge from Module 04. Every slide should feel like "now you build it."
This is Module: [05]
Module Title: [Building MCP Servers]
Core Topics (4): [FastMCP Setup with uv | Tools, Resources, and Prompts | Transport and Deployment | SDK vs Raw Protocol]

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
BRANDING: Top-left every slide: 🛡 MCP-CD | Bottom-right: M05/S## | Bottom-left: module title (small caps, grey)
MAX 5 BULLETS PER SLIDE. Each bullet ≤ 12 words. Code slides = minimal bullets, code block speaks.
Every slide has ONE primary takeaway in a callout box or highlighted text.

═══════════════════════════════════════════════════
SLIDE STRUCTURE — GENERATE IN THIS ORDER
═══════════════════════════════════════════════════

SLIDE 01 — TITLE SLIDE
Title: MODULE 05 — BUILDING MCP SERVERS
Visual: Server rack icon connected to a neural network node motif
Sub: "3 lines of code. 10 minutes. Your first MCP server in Claude."
Learning objectives (4):
  - Set up an MCP server project with uv
  - Write tools, resources, and prompts using FastMCP
  - Test with the MCP Inspector before connecting any LLM
  - Register your server in Claude Desktop

SLIDE 02 — FROM MODULE 04 TO MODULE 05
Diff-style code block (full width, emerald additions):
  - import os, re, requests
  - from dotenv import load_dotenv
  + from mcp.server.fastmcp import FastMCP
  +
  + mcp = FastMCP("CTI Server")
  +
  + @mcp.tool()
    def enrich_ip(ip_address: str) -> dict:
        ...        # your function body — UNCHANGED
  +
  + if __name__ == "__main__":
  +     mcp.run()
Callout: "3 lines added. Your entire Module 04 function body stays exactly the same."

SLIDE 03 — WHAT IS AN MCP SERVER?
Content:
Left column: "What it IS"
  - An independent Python process
  - Communicates via JSON-RPC 2.0
  - Exposes: Tools, Resources, Prompts
Right column: "What it is NOT"
  - A module you import
  - A web API (unless using SSE transport)
  - A plugin or extension
Callout: "MCP servers run as separate processes. Independence = language-agnostic protocol."

SLIDE 04 — PROJECT SETUP WITH uv (Commands)
Full code slide — exact commands:
  uv init cti-mcp-server
  cd cti-mcp-server
  uv python pin 3.12
  uv add "mcp[cli]" requests python-dotenv
  New-Item server.py
  uv run mcp dev server.py
Below: file tree showing the created structure (.env, pyproject.toml, uv.lock, server.py)
Callout (emerald): "uv python pin 3.12 — stability for MCP async. Run this before uv add."
Speaker notes: Python 3.12 is the recommended version. uv handles the venv automatically — no activate command needed.

SLIDE 05 — THE @mcp.tool() DECORATOR
Large annotated code block:
  @mcp.tool()            ← registers with FastMCP registry
  def enrich_ip(         ← function name = tool name the LLM sees
      ip_address: str    ← type hint → JSON Schema type "string"
  ) -> dict:             ← return type hint
      """..."""           ← docstring → tool description for LLM
      ...
Right: "What FastMCP does automatically"
  - Reads type hints → JSON Schema
  - Reads docstring → tool description
  - Registers in tool registry
  - Wraps for JSON-RPC
Callout: "You write Python. FastMCP builds the MCP protocol layer."

SLIDE 06 — THE THREE CAPABILITIES
Three icon cards (horizontal):
Card 1 (electric blue): Tool — @mcp.tool()
  "AI agent calls this autonomously when it decides to"
  Example: enrich_ip("185.220.101.45")
Card 2 (cyan): Resource — @mcp.resource("uri://...")
  "Agent reads data from a stable URI address"
  Example: threat://triage-policy
Card 3 (light grey): Prompt — @mcp.prompt()
  "User selects this template to start a task"
  Example: ip_alert_triage(ip, alert_id)
Callout: "Tools = 95% of cyber defense servers. Resources and prompts complete the picture."

SLIDE 07 — TOOL NAMING: WHAT THE LLM READS
Two column comparison:
Left (amber, ❌) — Bad tool names:
  check(data)
  process(f)
  run(cmd)
Right (emerald, ✅) — Good tool names:
  enrich_ip(ip_address)
  compute_file_hashes(file_path)
  execute_yara_scan(file_path, ruleset)
Callout: "The LLM reads the function name to decide whether to call it. Name for the agent."

SLIDE 08 — RESOURCES: URI-ADDRESSED DATA
Code block showing:
  @mcp.resource("threat://triage-policy")
  def triage_policy() -> str:
      return """CTI TRIAGE POLICY v2.1
  abuse_score >= 80: Escalate..."""

  @mcp.resource("threat://reports/{report_id}")
  def get_report(report_id: str) -> str:
      ...   # parameterised URI
Right: When to use resources vs tools:
  - Data that changes rarely → Resource
  - Data you READ, not compute → Resource
  - Documents as LLM context → Resource
  - Operations with parameters → Tool

SLIDE 09 — PROMPTS: USER-INVOKED TEMPLATES
Code block:
  @mcp.prompt()
  def ip_alert_triage(ip_address: str, alert_id: str) -> list[PromptMessage]:
      return [PromptMessage(role="user", content=TextContent(
          type="text",
          text=f"Triage alert {alert_id}... Call enrich_ip('{ip_address}')..."
      ))]
Right: What prompts enable:
  - Structured, repeatable investigation starts
  - Consistent agent instructions per task type
  - User-selectable "quick actions" in Claude Desktop
Callout: "Prompts are forms. Fill in the blanks, get a structured investigation."

SLIDE 10 — THE LOGGING RULE (Critical Bug Prevention)
Two code blocks (full width):
Left (amber, ❌):
  print("Processing request")   # ← CORRUPTS STDIO STREAM
Right (emerald, ✅):
  import sys, logging
  logging.basicConfig(stream=sys.stderr)
  logging.info("Processing request")   # ← stderr only
  # OR:
  ctx.info("Processing request")       # ← MCP log channel
Callout in amber: "print() in an MCP server = silent protocol corruption. This is the #1 beginner bug."
Speaker notes: STDIO transport uses stdout for JSON-RPC messages. Any print() output before or between JSON objects breaks the parser in the client.

SLIDE 11 — TRANSPORT: STDIO vs SSE
Two-column comparison table:
  | | STDIO | SSE |
  | Location | Same machine | Any machine |
  | Protocol | stdin/stdout | HTTP/HTTPS |
  | Auth | Process env vars | HTTP headers |
  | Run command | uv run server.py | uv run mcp server.py --transport sse |
  | Best for | Dev, lab servers | Production |
Callout: "For this course: always STDIO. You will see SSE in Module 08 (production)."

SLIDE 12 — CLAUDE DESKTOP REGISTRATION
Code block — the exact config:
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
Callout: "command: uv — not python. uv activates the venv automatically."
Speaker notes: After editing the config, restart Claude Desktop completely — not just the conversation.

SLIDE 13 — INSPECTOR: TEST BEFORE LLM
Screenshot-style mockup of the MCP Inspector:
Left panel: Tools list showing "enrich_ip", "enrich_hash", "enrich_domain"
Center: Input form for enrich_ip with ip_address field
Right: Result panel showing the JSON response
Below: Testing checklist (5 items, green checkboxes):
  ✅ All tools visible
  ✅ Valid input → status: ok
  ✅ Invalid input → status: error (not crash)
  ✅ Key missing → status: error
  ✅ No print() corruption
Callout: "Every tool must pass Inspector before Claude Desktop. No exceptions."

SLIDE 14 — SDK vs RAW PROTOCOL
Two-column comparison (full width):
Left (amber): Bash Raw Protocol — 60 lines for 1 tool, STDIO only, manual JSON Schema, breaks on protocol updates
Right (emerald): Python FastMCP — 20 lines for 3 tools, STDIO + SSE, auto schema, SDK handles updates
Bottom: "Key takeaway from the Bash GitHub server example:
  It shows you WHAT happens in JSON-RPC. It should NEVER be used in production."
Callout: "Understanding raw protocol helps you debug. FastMCP is what you always build with."

SLIDE 15 — JSON-RPC DEBUGGING
Code block showing raw JSON-RPC messages (for debugging):
  # Send to a STDIO server to test directly:
  {"jsonrpc":"2.0","id":1,"method":"initialize","params":{...}}
  →
  {"jsonrpc":"2.0","id":1,"result":{"protocolVersion":"2024-11-05",...}}

  {"jsonrpc":"2.0","id":2,"method":"tools/list","params":{}}
  →
  {"jsonrpc":"2.0","id":2,"result":{"tools":[{"name":"enrich_ip",...}]}}
Callout: "When Inspector fails: pipe raw JSON-RPC and read the server's actual response."

SLIDE 16 — OPERATIONAL READINESS CHECKLIST
Checklist layout (two columns, 8 items):
Code Quality (left):
  ✅ No print() — replaced with logging
  ✅ 4-line docstrings on all tools
  ✅ All tools return status dict
  ✅ File tools use realpath() validation
Infrastructure (right):
  ✅ API keys in .env — never hardcoded
  ✅ .env in .gitignore
  ✅ uv.lock committed to git
  ✅ Inspector tested before Claude Desktop
Callout: "8 checks. All 8 required. No shortcuts."

SLIDE 17 — MODULE SUMMARY
5 takeaways (emerald bullet icons):
  - @mcp.tool() = one decorator to turn a function into an MCP-callable tool
  - FastMCP reads type hints for schema, docstring for description — both mandatory
  - uv init → uv add → uv run mcp dev → Inspector → Claude Desktop config
  - Never print() in a server tool — use logging or ctx
  - Raw protocol knowledge helps debug; FastMCP is always what you build with
Bottom progress bar: Module 05 of 08 — COMPLETE

═══════════════════════════════════════════════════
GENERATION RULES (SAME FOR ALL MODULES)
═══════════════════════════════════════════════════

Output format per slide:
--- SLIDE [##] ---
TITLE: [ALL CAPS]
LAYOUT: [visual description]
CONTENT: [bullets, table, diagram, or code]
DESIGN_NOTES: [colors, icons, emphasis]
SPEAKER_NOTES: [2–3 sentences — MANDATORY on slides 04, 10, 12, 14]

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
| 04 | Python Essentials for MCP | 6 | Encouraging, beginner-friendly |
| **05** | **Building MCP Servers** | **4** | **Hands-on, building-focused** |
| 06 | Building MCP Clients | 4 | Async-focused, systems thinking |

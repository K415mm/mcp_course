---
status: published
---

# Module 04 — Google NotebookLM Slides Generation Prompt

## How to Use

1. Open [NotebookLM](https://notebooklm.google.com/) → create notebook: `Module 04 — Python Essentials for MCP`.
2. Upload all files from `Module_04_Python_Essentials/` as sources.
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
Tone: Encouraging, precise, practical. Assume Python beginner. No unnecessary theory.
This is Module: [04]
Module Title: [Python Essentials for MCP]
Core Topics (6): [Python Basics | Data Structures and JSON | Type Hints and Docstrings | Error Handling and Validation | APIs and Environment Variables | uv: The Modern Python Toolchain]

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
BRANDING: Top-left every slide: 🛡 MCP-CD | Bottom-right: M04/S## | Bottom-left: module title (small caps, grey)
MAX 5 BULLETS PER SLIDE. Each bullet ≤ 12 words. Code slides = minimal bullets, code block speaks.
Every slide has ONE primary takeaway in a callout box or highlighted text.

═══════════════════════════════════════════════════
SLIDE STRUCTURE — GENERATE IN THIS ORDER
═══════════════════════════════════════════════════

SLIDE 01 — TITLE SLIDE
Title: MODULE 04 — PYTHON ESSENTIALS FOR MCP
Visual: Python logo-style snake icon replaced with a circuit board / tool connector motif (not the logo itself)
Sub: "The 20% of Python you need to build 100% of MCP tools"
Learning objectives (6):
  - Write typed, documented Python functions
  - Use dicts and lists to handle API data
  - Apply error handling so tools never crash
  - Validate inputs before calling APIs
  - Read API keys safely from environment variables
  - Set up a professional MCP project with uv

SLIDE 02 — WHAT THIS MODULE IS (AND ISN'T)
Two columns:
Left (amber, ❌): "You do NOT need:"
  - Classes and object-oriented programming
  - Async / await syntax (FastMCP handles it)
  - Web frameworks or routing
  - Deep algorithm knowledge
Right (emerald, ✅): "You DO need:"
  - Functions + type hints
  - Dicts + lists + JSON
  - try/except
  - requests library
  - Environment variables
Takeaway: "This module teaches exactly what you need. Nothing more."

SLIDE 03 — PYTHON FUNCTIONS: THE FOUNDATION
Content:
Left: Simple annotated function (code block):
  def check_ip(ip_address: str) -> dict:
      ...
Labels pointing to: def keyword / function name / parameter / type hint / return type
Right: 3 bullet rules:
  - One function = one specific task
  - Parameters = what goes in
  - Return value = what comes out
Callout: "An MCP tool IS a Python function. Master the function, master the tool."

SLIDE 04 — TYPE HINTS (WHY THEY'RE MANDATORY IN MCP)
Two-column comparison:
Left (amber, ❌) — Without type hints:
  def check(ip):
      ...
Right (emerald, ✅) — With type hints:
  def check(ip_address: str) -> dict:
      ...
Below: mini diagram — FastMCP reads type hints → generates JSON schema → AI agent knows what to call
Callout: "Without type hints: FastMCP can't build the schema. The AI can't use your tool."

SLIDE 05 — THE FOUR TYPES (REFERENCE SLIDE)
Four icon boxes in a 2×2 grid:
  str  — Text: IPs, domains, hashes, file paths, messages
  int  — Integers: scores, counts, ports, thresholds
  bool — True/False: flags, conditions, feature switches
  dict — The MCP return type (always)
Plus two extras in smaller form:
  float — ratios, confidence scores
  list  — collections of IOCs, tags, event records

SLIDE 06 — DICTIONARIES: THE MCP DATA FORMAT
Content:
Left: Code block showing an IP result dict:
  {
    "ip": "185.220.101.45",
    "abuse_score": 98,
    "country": "NL",
    "status": "ok"
  }
Right: Two access patterns:
  data["key"]     — crashes if missing
  data.get("key", default)  — safe
Callout: "Always use .get() on API responses. APIs can change. .get() protects you."

SLIDE 07 — THE STANDARD RESULT DICT PATTERN
Two code blocks side by side:
Left (emerald): Success result:
  return {
    "status": "ok",
    "ip": ip_address,
    "score": 98
  }
Right (amber): Error result:
  return {
    "status": "error",
    "reason": "What went wrong"
  }
Callout: "Every MCP tool returns a dict. Every dict has a 'status' field. No exceptions."

SLIDE 08 — THE 4-PART DOCSTRING (Formula)
Large formula display:
LINE 1: WHAT it does (one sentence — the most important line)
LINE 2: WHEN the agent should call it (triggers)
LINE 3: WHAT it returns (field names)
LINE 4: SAFETY class (Read-only. Safe. OR [DESTRUCTIVE] REQUIRES APPROVAL.)
Below: a complete example docstring in a code block
Callout: "The docstring is the contract between your tool and the AI. Write it first."
Speaker notes: Emphasize that line 4 is the safety classification that governs the agent's behavior.

SLIDE 09 — GOOD vs BAD DOCSTRINGS (Comparison)
Two code blocks (full width):
Left (amber, ❌): "Check an IP."
Right (emerald, ✅): The complete 4-line docstring for enrich_ip
Takeaway: "A vague docstring = an underused tool OR a dangerously overused tool."

SLIDE 10 — DESTRUCTIVE TOOL DOCSTRING (Special Case)
Code block showing the [DESTRUCTIVE] pattern:
  """[DESTRUCTIVE] Move file to quarantine.
  REQUIRES HUMAN APPROVAL — do not call without analyst instruction.
  approved_by must contain the analyst's name.
  This action cannot be automatically reversed."""
Callout in amber: "The word [DESTRUCTIVE] in the docstring signals the AI to pause. This is not optional."

SLIDE 11 — TRY/EXCEPT: THE SAFETY NET
Content:
Left: The bare minimum pattern (code block):
  try:
      result = api_call()
      return {"status": "ok", ...}
  except Exception as e:
      return {"status": "error", "reason": str(e)}
Right: Why it matters:
  - Crash = agent loses context
  - Crash = possible incorrect verdict
  - try/except = always a structured result
Callout: "No MCP tool should ever raise an unhandled exception."
Speaker notes: The agent doesn't see Python exceptions — it sees the return value. A crash robs the agent of the error message.

SLIDE 12 — SPECIFIC EXCEPTION TYPES
Code block showing three except clauses:
  except requests.exceptions.Timeout:
      return {"status": "error", "reason": "Timed out after 10s"}
  except requests.exceptions.HTTPError as e:
      return {"status": "error", "reason": f"HTTP {e.response.status_code}"}
  except Exception as e:
      return {"status": "error", "reason": str(e)}
Right: 3 bullets:
  - Timeout = network problem, retry possible
  - HTTPError = code-specific action (429 = rate limit)
  - Exception = catch-all safety net last

SLIDE 13 — INPUT VALIDATION: VALIDATE BEFORE YOU CALL
Two design patterns (code):
Pattern 1 — IP validation:
  if not validate_ipv4(ip_address):
      return {"status": "error", "reason": "Invalid IPv4 format"}
Pattern 2 — File path security:
  resolved = os.path.realpath(file_path)
  if not resolved.startswith(ALLOWED_DIR):
      return {"status": "error", "reason": "Path traversal blocked"}
Callout in amber: "File path validation prevents an attacker from reading /etc/passwd through your tool."
Speaker notes: Path traversal is a real attack vector for file-handling MCP tools exposed to prompt injection.

SLIDE 14 — ENVIRONMENT VARIABLES (The Secret Rule)
Two code blocks:
Left (amber, ❌):
  API_KEY = "abc123hardcoded"  # NEVER
Right (emerald, ✅):
  API_KEY = os.environ.get("ABUSEIPDB_KEY", "")
Below: .env file example + dotenv load pattern
Callout: "A committed API key is a public API key. Use environment variables. Always."

SLIDE 15 — uv: THE MODERN MCP TOOLCHAIN
Content:
Left: Command comparison table (3 rows):
  | Old (pip + venv)     | New (uv)              |
  | pip install requests | uv add requests       |
  | source .venv/activate| uv run server.py      |
  | python server.py     | uv run mcp dev server.py |
Right: Quick setup sequence (code block):
  uv init cti-server
  cd cti-server
  uv add "mcp[cli]" requests python-dotenv
  uv run mcp dev server.py
Callout (electric blue): "uv is 10-100× faster than pip. It's what the MCP docs recommend."
Speaker notes: uv handles virtualenv creation, package installation, and script execution in one tool. Students will use uv for every server they build from Module 5 onwards.

SLIDE 16 — THE COMPLETE API CALL PATTERN
Full code slide — the complete enrich_ip template:
  1. Check API key configured
  2. Validate input
  3. requests.get() with timeout=10
  4. response.raise_for_status()
  5. .json().get("data", {})
  6. Build result dict with .get() defaults
  7. except Timeout / HTTPError / Exception
Labels on each section pointing to which Module 04 principle it applies

SLIDE 17 — THE IP CHECKER TOOL (Live Demo Reference)
Content:
Left: 5 test cases from examples/01:
  ✅ Test 1: 185.220.101.45 → score 98, HIGH
  ✅ Test 2: "not-an-ip" → error dict (not crash)
  ✅ Test 3: 8.8.8.8 → score 0, LOW
  ✅ Test 4: "" → error dict
  ✅ Test 5: valid IP, days_back=30
Right: callout: "5 tests = every code path validated. Run these before deploying."

SLIDE 18 — WHAT CHANGES IN MODULE 05
Diff-style code block:
  + from mcp.server.fastmcp import FastMCP
  + mcp = FastMCP("CTI Server")
  +
  + @mcp.tool()
    def enrich_ip(ip_address: str) -> dict:
        # Same function body — unchanged
        ...
  -
  - if __name__ == "__main__":
  -     print(enrich_ip("185.220.101.45"))
  + if __name__ == "__main__":
  +     mcp.run()
Callout: "3 lines added, 1 changed. Everything you wrote in Module 04 becomes an MCP tool."

SLIDE 19 — MODULE SUMMARY
6 takeaways (emerald bullet icons):
  - Type hints are mandatory — FastMCP reads them to build the schema
  - Always return a dict with "status": "ok" or "error"
  - Write the docstring first — it's the spec for your tool
  - Wrap every API call in try/except — tools must never crash
  - API keys in environment variables — never in source code
  - Use uv for every MCP project: faster, cleaner, community standard
Bottom progress bar: Module 04 of 08 — COMPLETE

═══════════════════════════════════════════════════
GENERATION RULES (SAME FOR ALL MODULES)
═══════════════════════════════════════════════════

Output format per slide:
--- SLIDE [##] ---
TITLE: [ALL CAPS]
LAYOUT: [visual description]
CONTENT: [bullets, table, diagram, or code]
DESIGN_NOTES: [colors, icons, emphasis]
SPEAKER_NOTES: [2–3 sentences — MANDATORY on slides 08, 11, 13, 15]

Never use "suspicious", "dangerous", "malicious" without qualification.
Code blocks: JetBrains Mono, dark panel #0A1628, cyan text, line numbers shown.
GOOD/BAD comparisons: amber for ❌ bad examples, emerald for ✅ good patterns.
All tables: electric blue header row, striped navy/charcoal rows.
After all slides: output SLIDE DECK INDEX (Slide# | Title | Type | Source Block).

SOURCE MATERIAL: Generate ONLY from uploaded files. Write [VERIFY] in speaker notes if uncertain.
```

---

## Reuse Guide — Changed Fields vs Module 03

| Module | Title | Topic Count | Tone Adjustment |
|---|---|---|---|
| 03 | Cyber Defense Foundations | 3 | Authoritative, operational |
| **04** | **Python Essentials for MCP** | **5** | **Encouraging, beginner-friendly** |
| 05 | Build MCP Servers | 4 | Hands-on, building focus |

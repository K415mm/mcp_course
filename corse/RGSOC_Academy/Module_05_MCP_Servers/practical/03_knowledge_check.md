---
status: published
---

# Practical 03 — Knowledge Check and Module Checklist

> **Practical Block 3 of 3 | Module 05: Building MCP Servers**

---

## Section A: Knowledge Check Quiz

**Q1.** What does `@mcp.tool()` do when Python loads your server file? List the four things FastMCP does automatically.

**Q2.** A tool uses `print("debug info")`. What does this cause in an MCP server running over STDIO transport?

**Q3.** What is the difference between `@mcp.tool()` and `@mcp.resource("uri://...")`? Give a cyber defense example of each.

**Q4.** Write the exact 4 uv commands to create a new MCP server project, add FastMCP, and launch the Inspector.

**Q5.** Your tool function works correctly when you test it as a plain Python function, but breaks when the MCP Inspector calls it with `{"ip_address": "185.1.2.3"}`. What is the most likely cause?

**Q6.** In the Claude Desktop config, what is the `"command"` value and why is it `"uv"` rather than `"python"`?

**Q7.** What is the purpose of the MCP `Context` object? Show the import, the parameter signature, and one example call.

**Q8.** A SIEM MCP server needs to query logs (read-only, returns 10,000 rows) and close an alert (changes state in the SIEM). Classify each operation as Tool or Resource, and Sense or Act. What guardrail applies to the second operation?

**Q9.** What is the purpose of `uv.lock` and should it be committed to git? What about `.env`?

**Q10.** Raw Bash MCP servers (like the GitHub example) have three specific limitations. Name all three.

---

## Section B: Inspector Deep Dive

Run your CTI server in the Inspector:
```powershell
uv run mcp dev server.py
```

For each question, find the answer in the Inspector UI:

1. In the **Tools** panel: what is the exact JSON schema generated for `enrich_ip`'s `ip_address` parameter?
2. In the **Resources** panel: call `threat://triage-policy` — what text does it return?
3. In the **Prompts** panel: fill in `ip_alert_triage` with `ip_address="8.8.8.8"` and `alert_id="ALT-TEST"` — does the generated prompt include the constraint about not blocking without approval?
4. In the **Tools** panel: call `enrich_ip` with `ip_address="not-an-ip"` — does it return a clean `{"status": "error"}` dict or an unhandled exception?

---

## Section C: Module 05 Operational Readiness Checklist

Before the server is production-ready:

**Code Quality:**
- [ ] No `print()` in any tool function — replaced with `logging.info()` or `ctx.info()`
- [ ] All tools have 4-line docstrings (what / when / returns / safety)
- [ ] All tools return `{"status": "ok"/"error"}` — never raise unhandled exceptions
- [ ] All file-handling tools have `os.path.realpath()` path validation

**Infrastructure:**
- [ ] API keys in `.env` or Claude Desktop `"env"` block — never hardcoded
- [ ] `.env` and `.venv/` in `.gitignore`
- [ ] `uv.lock` committed to git (reproducible deployments)
- [ ] Server tested in MCP Inspector before connecting to any LLM client

**Safety:**
- [ ] Act tools have `[DESTRUCTIVE]` and `REQUIRES HUMAN APPROVAL` in docstring
- [ ] All act tools log to an audit log (tool name, input, timestamp, outcome)
- [ ] Rate limit errors (HTTP 429) return actionable error messages

---

## Section D: Exit Ticket

1. **The biggest difference between writing a Python function in Module 04 and an MCP tool in Module 05 is...**
2. **The testing step I would never skip before registering a server with Claude Desktop is...**
3. **The guard I will put in every single tool I write, no matter what, is...**

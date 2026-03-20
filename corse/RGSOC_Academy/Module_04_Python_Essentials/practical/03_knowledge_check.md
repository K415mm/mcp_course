---
status: published
---

# Practical 03 — Knowledge Check and Module Checklist

> **Practical Block 3 of 3 | Module 04: Python Essentials for MCP**

---

## Section A: Knowledge Check Quiz

Answer each question briefly without looking at your notes.

**Q1.** Python is dynamically typed. Why are type hints still required for MCP tools even though Python doesn't enforce them at runtime?

**Q2.** What is the difference between `data["key"]` and `data.get("key", "default")`? Which should you use in MCP tools and why?

**Q3.** Write the standard MCP error return dict (both fields). What does the `"status"` field enable on the agent side?

**Q4.** Why should a tool's docstring NOT include implementation details like "this calls the AbuseIPDB v2 API using a GET request to..."?

**Q5.** What does `[DESTRUCTIVE] REQUIRES HUMAN APPROVAL` in a docstring tell the AI agent?

**Q6.** If `os.environ.get("MY_KEY", "")` returns an empty string, what should your tool do? Write the exact 2 lines of code.

**Q7.** A tool receives `ip_address = "999.999.999.999"`. What should happen before any API call is made?

**Q8.** Why should API keys be loaded once at module level (outside the function) rather than inside the function on every call?

**Q9.** A requests call raises `requests.exceptions.Timeout`. What should your `except` clause return?

**Q10.** Complete the sentence: "A `.env` file must always be added to `.gitignore` because..."

**Q11 (uv).** Write the exact 4 commands you run to create a new MCP project called `threat-server` using `uv`, add `mcp[cli]` and `requests` as dependencies, and launch the MCP Inspector. No pip, no manual venv activation.

---

## Section B: Code Review Exercise

Spot all the problems in this tool. List each issue and provide the fix:

```python
def check_ip(ip):
    api_key = "abc123secretkey"         # Problem 1?
    
    response = requests.get(
        "https://api.abuseipdb.com/api/v2/check",
        headers={"Key": api_key},
        params={"ipAddress": ip}        # Problem 2?
    )
    
    data = response.json()
    score = data["data"]["abuseConfidenceScore"]  # Problem 3?
    
    if score > 80:
        verdict = "This IP is dangerous and malicious!"   # Problem 4?
    
    print(f"Score: {score}, Verdict: {verdict}")   # Problem 5?
```

There are at least 5 distinct issues. Find them all.

**Issues to find:** hardcoded API key, no timeout, unguarded key access with `[]`, loaded language in output, returns nothing (no `return` / no `dict` result).

---

## Section C: Module 04 Self-Assessment

Rate your confidence 1 (low) – 5 (high):

| Skill | My Rating |
|---|---|
| Writing Python functions with type hints | /5 |
| Using `.get()` safely on nested dicts | /5 |
| Writing a 4-part MCP docstring | /5 |
| Wrapping API calls in try/except | /5 |
| Validating IP addresses and file paths | /5 |
| Setting and reading environment variables | /5 |
| Using `requests` to call a REST API | /5 |
| Setting up a uv project (`uv init`, `uv add`, `uv run`) | /5 |

Items rated 3 or below: revisit the corresponding theoretical block before Module 05.

---

## Section D: Module 04 Exit Ticket

1. **The Python concept I was least confident about entering this module that now makes sense is...**
2. **Before starting Module 05, I still need to practice...**
3. **The one habit this module taught me that I will apply in every tool I write is...**

---

## Section E: uv Hands-On Validation

Do this before closing the module. It takes less than 5 minutes.

```powershell
# Step 1: Verify uv is installed
uv --version

# Step 2: Create your practice project
uv init my-cti-server
cd my-cti-server

# Step 3: Add dependencies
uv add "mcp[cli]" requests python-dotenv

# Step 4: Check what was installed
uv pip list

# Step 5: Create your server file and test run
# (copy the enrich_ip function from example 01)
New-Item server.py
# Paste your code into server.py

# Step 6: Launch the MCP Inspector
uv run mcp dev server.py
```

If the MCP Inspector opens in your browser and shows your `enrich_ip` tool in the tool list — **Module 04 is complete and you are ready for Module 05.**

If you see an error:
- `uv: command not found` → re-install uv (Block 6, Step 6.2)
- `No module named 'mcp'` → run `uv add "mcp[cli]"` again
- `ABUSEIPDB_KEY not set` → create `.env` with your key and re-run

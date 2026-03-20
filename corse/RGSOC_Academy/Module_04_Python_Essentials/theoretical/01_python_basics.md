---
status: published
---

# 01 — Python Basics: Variables, Functions, and Types

> **Theoretical Block 1 of 5 | Module 04: Python Essentials for MCP**

---

## A Note Before We Start

This module does not teach all of Python. It teaches the **specific Python you need** to build MCP cyber defense tools. If you can write a function that accepts inputs, processes data, and returns a result — you can build an MCP server.

You do not need to know:
- ❌ Object-oriented programming (classes, inheritance)
- ❌ Decorators in depth (you only need `@mcp.tool()` — we explain it once)
- ❌ Async programming (FastMCP handles this for you)
- ❌ Web frameworks (FastMCP is not a web framework)

You **do** need to know:
- ✅ Variables and data types
- ✅ Functions with parameters and return values
- ✅ Type hints (Python's way of labeling data types)
- ✅ Dictionaries and JSON (the core data format of MCP)
- ✅ Error handling (so your tools never crash an agent)
- ✅ Calling APIs (how your tools fetch threat intelligence)

Let's go.

---

## 1.1 Variables — Naming Your Data

A **variable** stores a value and gives it a name so you can use it later.

```python
# Storing an IP address
ip_address = "185.220.101.45"

# Storing a number
abuse_score = 98

# Storing a yes/no flag
is_tor_exit_node = True
```

Rules for variable names:
- Use lowercase with underscores: `file_hash`, not `FileHash` or `filehash`
- Be descriptive: `abuse_score` is better than `s`
- No spaces. No special characters except `_`

---

## 1.2 Data Types — The Building Blocks

Python has four types you will use constantly in MCP tools:

### `str` — Text (String)

```python
domain = "evil-phish.net"
sha256 = "a3f9d1b2c4e5f6a7b8c9d0e1f2a3b4c5"
file_path = "C:/Users/jsmith/Downloads/invoice.pdf"
```

Strings go inside quotes (single `'` or double `"` — both work). Strings are for: IP addresses, domains, hashes, file paths, messages.

### `int` — Whole Numbers

```python
port_number = 443
days_old = 3
report_count = 47
```

Use for counts, scores, port numbers, thresholds.

### `bool` — True or False

```python
is_malicious = False
requires_approval = True
```

Use for flags, conditions, and switch-like fields in your tool output.

### `float` — Decimal Numbers

```python
confidence_score = 0.87
risk_ratio = 4.2
```

Use for confidence percentages, ratios, and scored metrics.

---

## 1.3 Functions — Reusable Blocks of Logic

A **function** is a named block of code that does one specific thing. You define it once; you (or the AI agent) can call it as many times as needed.

```python
def greet_analyst(name):
    message = "Welcome, " + name
    return message
```

Breaking it down:
- `def` — tells Python "I'm about to define a function"
- `greet_analyst` — the function's name
- `(name)` — the **parameter**: input the function needs to do its job
- `return message` — what the function gives back when it's done

**Calling the function:**
```python
result = greet_analyst("Sarah")
print(result)   # Output: Welcome, Sarah
```

---

## 1.4 Multiple Parameters

A function can accept multiple inputs:

```python
def check_ip_risk(ip_address, abuse_score, country):
    if abuse_score > 80:
        return ip_address + " is high-risk (" + country + ")"
    else:
        return ip_address + " appears clean"
```

```python
check_ip_risk("185.220.101.45", 98, "Netherlands")
# Returns: "185.220.101.45 is high-risk (Netherlands)"
```

---

## 1.5 Type Hints — Labeling Your Parameters

**Type hints** are labels that tell Python (and FastMCP!) what kind of data each parameter should be. You add them with a colon after the parameter name:

```python
def check_ip_risk(ip_address: str, abuse_score: int, country: str) -> str:
    if abuse_score > 80:
        return ip_address + " is high-risk (" + country + ")"
    else:
        return ip_address + " appears clean"
```

New parts:
- `ip_address: str` — this parameter must be a string
- `abuse_score: int` — this parameter must be an integer
- `-> str` — this function returns a string

**Why type hints matter for MCP:**

FastMCP reads your type hints automatically and builds the JSON schema that tells the AI agent:
- What inputs your tool requires
- What type each input must be
- What the output type will be

Without type hints → FastMCP can't generate a proper schema → the AI can't reliably call your tool.

> **Rule:** Every MCP tool function must have type hints on ALL parameters and on the return type.

---

## 1.6 Default Parameter Values

Sometimes you want a parameter to have a sensible default — the caller can override it if needed, but doesn't have to:

```python
def lookup_ip(ip_address: str, max_age_days: int = 90) -> str:
    """Look up threat reports for an IP in the last N days."""
    # max_age_days defaults to 90 if not provided
    return f"Looking up {ip_address} for the last {max_age_days} days"
```

```python
lookup_ip("1.2.3.4")           # Uses default: 90 days
lookup_ip("1.2.3.4", 30)       # Overrides: 30 days
```

---

## 1.7 The `if` Statement — Making Decisions

Your tools need to make decisions based on data:

```python
def classify_risk(abuse_score: int) -> str:
    if abuse_score >= 80:
        return "HIGH"
    elif abuse_score >= 40:
        return "MEDIUM"
    else:
        return "LOW"
```

```python
classify_risk(98)   # Returns: "HIGH"
classify_risk(55)   # Returns: "MEDIUM"
classify_risk(10)   # Returns: "LOW"
```

**The `elif`** (else-if) lets you check multiple conditions in order. Python checks them top-to-bottom and stops at the first `True`.

---

## 1.8 `print()` — Your Debugging Friend

While building tools, use `print()` to check what's happening inside your function:

```python
def check_domain(domain: str) -> str:
    print(f"Checking domain: {domain}")   # Debug line
    result = "flagged" if "secure-update" in domain else "clean"
    print(f"Result: {result}")            # Debug line
    return result
```

The `f"..."` is an **f-string** — it lets you embed variables directly in text using `{}`:

```python
name = "Alice"
score = 98
message = f"Analyst {name} reviewed IP with score {score}"
# message = "Analyst Alice reviewed IP with score 98"
```

F-strings are the cleanest way to build return messages in your MCP tools.

---

## 1.9 Putting It Together: Your First Proto-Tool

Before adding `@mcp.tool()`, let's write a complete function the MCP way:

```python
def enrich_ip_simple(ip_address: str, abuse_score: int, country: str) -> str:
    """
    Summarize an IP address risk based on its abuse score and country.
    Returns a one-line risk summary.
    """
    risk = "HIGH" if abuse_score >= 80 else "MEDIUM" if abuse_score >= 40 else "LOW"
    return f"IP {ip_address} ({country}): risk={risk}, score={abuse_score}/100"


# Test it
print(enrich_ip_simple("185.220.101.45", 98, "NL"))
# Output: IP 185.220.101.45 (NL): risk=HIGH, score=98/100
```

This function has:
- ✅ Type hints on all parameters
- ✅ A return type hint
- ✅ A docstring explaining what it does
- ✅ A return value (not just a print)
- ✅ Logic that makes a decision

In Module 5, we add `@mcp.tool()` above this and it becomes an AI-callable tool. That's it.

---

## Key Takeaways

1. Variables store named data. Use lowercase with underscores.
2. The four types you'll use most: `str`, `int`, `bool`, `float`.
3. Functions take inputs (parameters) and return outputs. Define once, use many times.
4. Type hints are mandatory for MCP tools — FastMCP reads them to build the tool schema.
5. `if/elif/else` lets your tool make decisions based on data.
6. F-strings (`f"text {variable}"`) are the cleanest way to build tool output messages.
7. A well-typed, documented function is 90% of an MCP tool. The decorator comes later.

---

## Try It Yourself

Open a Python file or the Python REPL (`python` in your terminal) and try:

1. Define a function called `classify_domain_age(age_days: int) -> str` that returns `"new"` if under 7 days, `"recent"` if under 30, and `"established"` if 30 or older.
2. Test it with: `classify_domain_age(2)`, `classify_domain_age(15)`, `classify_domain_age(365)`.
3. Add a default parameter: `classify_domain_age(age_days: int = 0) -> str` and test calling it with no arguments.

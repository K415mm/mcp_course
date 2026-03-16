---
status: published
---

# 04 — Core Agent Characteristics and Orchestration Patterns

> **Theoretical Block 4 of 5 | Module 01: Agentic AI Foundations**

---

## 4.1 Revisiting the Five Characteristics in Depth

The five core agent characteristics introduced in Block 1 become more nuanced when examined in the context of real deployments. This block examines each characteristic in detail and explores how multi-agent systems extend them.

---

### 4.1.1 Autonomy — The Spectrum

Autonomy is not a binary switch. It exists on a spectrum from fully supervised to fully autonomous:

```
SUPERVISED ◄─────────────────────────────────► AUTONOMOUS

  Human       Human      Human approves    Human monitors    Human has
  drives      approves   destructive       dashboards only   no role
  every step  each step  actions only
```

In cyber defense, the appropriate position on this spectrum depends on the complexity domain (covered in Block 5). Most production SOC agents operate in the middle of this spectrum: autonomous for data gathering, supervised for containment.

---

### 4.1.2 Perception — Tool Quality is Everything

An agent's perception is only as good as the tools it has access to. A tool with:
- **A weak docstring** → LLM doesn't know when to use it → data never gathered.
- **Unvalidated inputs** → Bad data enters the Sense phase → garbage-in reasoning.
- **Raw, unnormalized output** → Emotional language ("suspicious!", "dangerous!") biases LLM judgment.

> **Design rule:** The Sense phase is where most agent failures originate. Two-thirds of agent quality engineering effort should be on tool quality, not on prompt engineering.

---

### 4.1.3 Reasoning — Chain-of-Thought and Tool Use

Modern LLMs use **chain-of-thought (CoT)** reasoning: before choosing a tool or producing a final answer, they generate an internal reasoning trace. For agents, this trace shapes which tool gets called next.

CoT can be:
- **Implicit:** generated internally and not visible to the user (most chat APIs).
- **Explicit / Visible:** the reasoning trace is returned as a separate field (o3, Gemini Thinking).

**Why it matters for security:** visible CoT allows audit of the agent's reasoning, not just its actions. When an agent recommends blocking an IP, you can read exactly why it reached that conclusion — enabling analyst review of the reasoning chain, not just the verdict.

---

### 4.1.4 Action — Idempotency and Rollback

Act-phase tools should be designed for safety:

**Idempotency:** calling the same tool twice has the same effect as calling it once.
- Blocking an already-blocked IP: no additional effect ✅
- Creating a duplicate ticket: two tickets created ❌ — not idempotent, needs a guard.

**Rollback capability:** destructive tools should have corresponding undo tools where possible:
- `block_ip` ↔ `unblock_ip`
- `quarantine_file` ↔ `restore_from_quarantine`
- `isolate_endpoint` ↔ `reconnect_endpoint`

This gives human reviewers the ability to reverse agent decisions quickly.

---

### 4.1.5 Adaptation — Feedback Loops

Adaptation in current production agents happens at two levels:

1. **Within-session:** the agent updates its intermediate conclusions as new Sense data arrives. If the first enrichment returns "no known threat," but the second enrichment reveals the IP is a TOR exit node, the agent adapts its risk assessment.

2. **Cross-session (long-term learning):** via human feedback collected systematically. Analysts rate verdicts; the feedback is used to fine-tune the model or update tool selection heuristics over time.

---

## 4.2 Orchestration: Single Agents vs. Multi-Agent Systems

### Single Agent
One LLM, one tool loop, one task. Simpler, more auditable, lower risk.

```
[User] → [Agent (LLM + tools)] → [Result]
```

Appropriate for:
- Well-scoped tasks (triage one alert, enrich one IOC).
- Environments where auditability is paramount.
- Learners beginning with agent development.

### Multi-Agent Systems
Multiple agents coordinated by an orchestrator. Each agent specializes in a domain.

```
[User] → [Orchestrator Agent (Planner)]
               │
        ┌──────┴──────┐──────────────┐
        ▼             ▼              ▼
[CTI Agent]  [Network Agent]  [Malware Agent]
   │               │                │
   └───────────────┴────────────────┘
                   │
         [Orchestrator synthesizes]
                   │
              [Final Brief]
```

Appropriate for:
- Complex investigations requiring domain expertise from multiple specialties.
- High-volume environments where sub-tasks run in parallel.
- Advanced deployments (Module 6 covers client-server multi-agent patterns).

**Risks of multi-agent systems:**
- **Context fragmentation:** sub-agents may work with incomplete information.
- **Coordination errors:** orchestrator may delegate incorrectly or fail to synthesize sub-outputs.
- **Compounded hallucinations:** errors from one agent are passed as ground truth to the next.

> **Rule:** Start with single agents. Add multi-agent coordination only when single-agent scope is genuinely insufficient.

---

## 4.3 Tools as the Agent's Senses and Limbs

Tools are the bridge between the LLM's text world and the real world. There are three categories:

| Category | Examples | Safe to Automate? |
|---|---|---|
| **Information tools** | Lookup, query, parse, hash | Always yes |
| **Transformation tools** | Summarize, classify, extract | Yes |
| **State-change tools** | Block, isolate, quarantine, create | Human approval required |

The JSON schema of each tool — generated automatically by FastMCP from type hints and docstrings — defines the formal contract between the LLM and the tool. A well-formed schema means the LLM:
- Knows what the tool does (description).
- Knows what inputs it requires (schema).
- Can validate whether its planned call will succeed before making it.

---

## 4.4 The Tool Selection Problem

How does the LLM decide which tool to call? It compares the current context (goal + history + current data) against the list of available tools and their descriptions. The selection is based on the statistical likelihood that a tool description matches the current need.

**Failure modes:**
- **Wrong tool selected:** descriptions too vague — LLM picks the closest match, not the right match.
- **Tool skipped:** description unclear — LLM doesn't recognize the tool is needed.
- **Tool called with wrong arguments:** schema not strict enough — LLM passes an IP where a domain is expected.

**Mitigation:**
- Tool names should be self-explanatory (`enrich_ip` not `check_thing`).
- Docstrings should explicitly say *when* to call the tool.
- Schemas should use strict types and enums where possible.

---

## Key Takeaways

1. Autonomy is a spectrum — position on it should be determined by complexity domain.
2. Perception quality is the biggest determinant of agent quality — invest in tool design.
3. Chain-of-thought reasoning enables audit of agent decisions, not just outputs.
4. Act-phase tools must be idempotent and have rollback counterparts where possible.
5. Start with single-agent architectures; add multi-agent coordination only when necessary.
6. Tool selection quality depends entirely on description and schema quality.

---

## Discussion Questions

1. Why might a multi-agent system be *more* dangerous than a single-agent system for high-stakes SOC decisions?
2. Design two versions of a tool docstring for `block_ip`: one that causes the LLM to call it too often (over-blocking) and one that is appropriately calibrated.
3. What is the difference between adaptation within a session and adaptation across sessions? Which is more relevant for a SOC agent?

---

## Further Reading

- [The Anatomy of Agentic Systems_ Mechanics and Orchestration.md](file:///d:/mcp_course/corse/Module_01_Agentic_AI_Foundations/The%20Anatomy%20of%20Agentic%20Systems_%20Mechanics%20and%20Orchestration.md) — Tool Use, Multi-Agent Coordination sections

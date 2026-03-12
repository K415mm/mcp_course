---
status: draft
---

# 03 — Memory, Goals, and State in Agentic Systems

> **Theoretical Block 3 of 5 | Module 01: Agentic AI Foundations**

---

## 3.1 The Statelessness Problem

By default, an LLM has no memory. Every time you send it a message, it processes only what is in the current request. It does not remember your last conversation, the tool results from three steps ago, or the goal it was given at the start of the session.

This creates an immediate problem for agents: if an agent is helping triage a multi-step alert investigation but can't remember step 1 by step 5, it cannot maintain coherent reasoning.

**The solution:** the agentic framework acts as the agent's external memory. It is responsible for:
1. Storing the conversation history (all prior messages, tool calls, and results).
2. Injecting that history into every new LLM call as context.
3. Managing what to include and what to trim when the history grows too long.

---

## 3.2 The Context Window as Working Memory

The **context window** is the total amount of text an LLM can process in a single call. Modern frontier models (GPT-4, Claude Sonnet, Gemini Pro) have context windows of 100,000–200,000 tokens — roughly 75,000–150,000 words.

For an agent, the context window is its working memory. Everything the agent "knows" during a session must fit inside this window. The framework injects:

```
[System Prompt]        ← defines the agent's role, rules, tools
[Conversation History] ← all prior turns, tool calls, and results
[Current User Request] ← the latest instruction
[Available Tool List]  ← names, descriptions, schemas of all tools
```

The LLM processes all of this simultaneously and produces its next output.

**Impact on security agents:**

| Context grows because | Problem |
|---|---|
| Many enrichment tool results | History fills the window → oldest context pushed out |
| Long log file contents returned | Single tool result can consume most of the window |
| Multi-step investigation | Each step adds to history; late steps lose early context |

**Mitigation patterns:**
- Return structured JSON dicts (compact) not raw log text from tools.
- Summarize intermediate results before passing them back to the LLM.
- Use retrieval-augmented memory for very long investigations.

---

## 3.3 Memory Types in Agentic Systems

Beyond the context window, sophisticated agents implement additional memory mechanisms:

### Episodic Memory (Short-term, Session-scoped)
The standard conversation history. Contains every message, tool call, and result from the current session. Cleared when the session ends. Implemented automatically by frameworks like AutoGen, LangChain, and MCP clients.

### Semantic Memory (Long-term, Persistent)
Stored facts and knowledge that persist across sessions. Implemented via vector databases (RAG — Retrieval-Augmented Generation). The agent can retrieve relevant knowledge chunks using similarity search.

> **SOC application:** Store past incident reports in a vector DB. When a new alert arrives, retrieve the 3 most similar past incidents. The agent reasons over historical context without needing to fit all past cases in the context window.

### Procedural Memory (Playbooks as Memory)
Tool definitions themselves act as procedural memory — they encode expert knowledge about *how* to do things. A well-written tool docstring ("run this tool when you see an IP in an alert to check its abuse history") teaches the agent when and how to invoke procedures.

### Task-Centric Memory (Experimental)
Some frameworks (e.g., AutoGen) implement fast-path memory that stores intermediate task states separately from conversation history. Useful for long multi-task workflows where the agent needs to track sub-goals independently.

---

## 3.4 Goals, Sub-goals, and Planning

An agent's goal is set by the user's prompt. But complex security tasks require the agent to decompose the top-level goal into sub-goals:

**Top-level goal:** "Triage this phishing alert and tell me if we need to take action."

**Sub-goals the agent must generate internally:**
1. Extract IOCs from the alert (IP, domain, hash).
2. Enrich each IOC with threat intelligence.
3. Determine the Cynefin domain for the resulting evidence picture.
4. If Clear domain: recommend automated action.
5. If Complicated: produce analyst brief with recommended action pending approval.
6. If Complex/Chaotic: flag for human review only.

The LLM generates this sub-goal decomposition implicitly through chain-of-thought reasoning — it produces an internal planning trace as part of its output before deciding which tool to call next.

**Reasoning models** (o3, Gemini Thinking) run an explicit internal planning loop before producing a final answer. They are better at multi-step decomposition but slower and more expensive.

---

## 3.5 State Persistence and Safety

In long-running security investigations, the agent must track state across many steps. Critical state items for SOC agents:

| State Item | Why It Matters |
|---|---|
| IOCs already enriched | Avoid duplicate API calls |
| Hosts already investigated | Prevent re-running the same queries |
| Actions already taken | Never block the same IP twice or quarantine an already-quarantined file |
| Approval status | Remember which destructive tools have been approved by a human |
| Confidence level per IOC | Accumulate evidence before making a recommendation |

**Safety implication:** Without state tracking, an agent can loop — repeatedly calling the same tool, escalating severity incorrectly, or taking actions it already took. Always implement:
- Idempotency in tool design (calling a tool twice has the same effect as calling it once).
- State tracking in the framework or explicitly in the system prompt ("tools you have already called: ...").

---

## 3.6 Evaluation: How Do You Know the Agent Is Working Correctly?

Agent evaluation is a non-trivial problem. Unlike deterministic code, the agent's behavior is probabilistic. Testing strategies:

### Human-in-the-Loop Feedback
Analysts review agent outputs and flag incorrect verdicts. Over time, patterns of errors indicate whether the agent's reasoning (Think phase) is sound or its tool outputs (Sense phase) are misleading it.

### Automated Metrics
- **Task success rate:** did the agent produce a valid verdict for all test alerts?
- **Hallucination rate:** did the agent claim enrichment data it did not actually receive from a tool?
- **Tool selection accuracy:** did the agent call the correct tools in the correct order?
- **Latency:** how many tokens / tool calls per alert?

### Industry Benchmarks
- **AgentBench:** general agent task success evaluation.
- **ToolBench:** evaluation of external tool usage accuracy.
- **WebArena / Mind2Web:** web navigation and interaction tasks.

> **For security agents:** create an evaluation set of realistic historical alerts with known ground-truth verdicts. Measure the agent's verdict accuracy and tool selection quality against this set before deploying in production.

---

## Key Takeaways

1. LLMs are stateless by default — frameworks provide memory by injecting conversation history.
2. The context window is the agent's working memory; everything must fit inside it.
3. Three memory types: episodic (session), semantic (long-term/RAG), procedural (tool knowledge).
4. Goals decompose into sub-goals implicitly through LLM chain-of-thought reasoning.
5. State tracking prevents loops, duplicate actions, and safety regressions.
6. Agent evaluation requires both human feedback and automated metrics.

---

## Discussion Questions

1. Why would a tool that returns 10,000 lines of raw log text be dangerous for a production agent?
2. Describe how semantic memory (RAG) could be used to make a SOC agent better at a specific customer's environment over time.
3. What happens if an agent successfully blocks an IP, loses that state (context window trims early history), and then reasons about whether to block the same IP again?

---

## Further Reading

- [The Anatomy of Agentic Systems_ Mechanics and Orchestration.md](file:///d:/mcp_course/corse/Module_01_Agentic_AI_Foundations/The%20Anatomy%20of%20Agentic%20Systems_%20Mechanics%20and%20Orchestration.md) — Memory in AutoGen section

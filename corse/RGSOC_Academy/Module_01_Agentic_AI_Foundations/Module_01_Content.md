---
status: published
---

# Module 1: Agentic AI Foundations for Cyber Defense

## Module Goal

Build a clear mental model of what an AI agent is, how it differs from a chatbot, and how the Sense‑Think‑Act loop maps to real SOC workflows.

## Learning Objectives

1. Define an AI agent using core characteristics such as autonomy and perception.
2. Distinguish agents from chatbots using concrete security examples.
3. Explain the Sense‑Think‑Act loop and how MCP enables Sense and Act.
4. Apply the decision-complexity‑informed autonomy matrix to security operations.

## Theoretical Section

### What Is an AI Agent?

- An AI agent perceives its environment, reasons about goals, and takes actions to achieve them.
- Autonomy is graded: agents can operate independently but still require supervision for high‑risk actions.
- Chatbots respond to prompts; agents execute multi‑step workflows and tool calls.

### Core Characteristics of Agents

- Autonomy, perception, reasoning and planning, action, and learning/adaptation.
- Tools act as the agent’s external capabilities, enabling action beyond text.
- Orchestration coordinates memory, tools, and the model across steps.

### The Agent Loop: Sense‑Think‑Act

- Sense: tools gather data from the environment.
- Think: the model reasons using context and goals.
- Act: tools execute changes or actions in the environment.

### Memory, Goals, and Evaluation

- Context windows and memory systems preserve goals and state across long tasks.
- Evaluation uses human feedback, automated metrics, and benchmarks to control errors.

### Safe Autonomy with the decision-complexity Lens

- Clear: autonomous execution is safe.
- Complicated: assisted analysis with human validation.
- Complex: hypothesis generation only.
- Chaotic: data triage only.

## Practical Section

### SOC Mapping Exercise

- Map alerts to Sense‑Think‑Act steps.
- Identify which steps can be automated safely.

### Autonomy Guardrails

- Define actions that must be human‑approved.
- Separate data collection from containment actions.

### Operational Checklist

- What must be sensed before action?
- What evidence is required to act?
- What is the maximum safe action in each domain?

## Example Section

### Phishing Alert Walkthrough

- Sense: ingest email headers, URLs, attachment hashes.
- Think: correlate sender reputation and campaign indicators.
- Act: quarantine the email and notify the user only if the alert is in the Clear domain.

### decision-complexity Decision

- Clear: known phishing kit indicators → automate quarantine.
- Complicated: mixed indicators → escalate for analyst validation.
- Complex/Chaotic: unfamiliar campaign → generate hypotheses only.

## Knowledge Check

1. List the core characteristics of an AI agent.
2. Explain why autonomy must be limited in the Complex domain.
3. Describe how MCP contributes to Sense and Act.

## Reading List (Module 1 Source Files)

- [Agentic AI Integration and the automation-safety framework for SOC Operations (1).md](file:///d:/mcp_course/corse/Module_01_Agentic_AI_Foundations/Agentic%20AI%20Integration%20and%20the%20Cynefin%20Framework%20for%20SOC%20Operations%20(1).md)
- [The Anatomy of Agentic Systems_ Mechanics and Orchestration.md](file:///d:/mcp_course/corse/Module_01_Agentic_AI_Foundations/The%20Anatomy%20of%20Agentic%20Systems_%20Mechanics%20and%20Orchestration.md)
- [9781806116478.pdf](file:///d:/mcp_course/9781806116478.pdf)
- [Strategic_Agentic_Autonomy_(2).pdf](file:///d:/mcp_course/Strategic_Agentic_Autonomy_(2).pdf)

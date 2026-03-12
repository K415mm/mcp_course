---
status: draft
---

# Module 2: MCP Fundamentals and Architecture

## Module Goal

Explain how MCP works as a universal adapter for tools and why it matters for safe, scalable cyber defense automation.

## Learning Objectives

1. Describe MCP’s role in connecting AI agents to tools and data.
2. Identify core MCP components and their responsibilities.
3. Explain how MCP supports safe, observable actions in security workflows.

## Theoretical Section

### Why MCP Exists

- Tool integrations used to require custom connectors per AI framework.
- MCP provides a standardized, reusable interface for tools and context.

### Core Architecture

- Host: the AI application coordinating clients.
- Client: maintains a dedicated connection to one server.
- Server: exposes tools, resources, and prompts.
- Data layer: JSON‑RPC protocol and lifecycle.
- Transport layer: stdio or Streamable HTTP for local and remote use.【modelcontextprotocol.io/docs/learn/architecture】

### Transport Basics

- Stdio: local subprocess communication over stdin/stdout.
- Streamable HTTP: remote servers using HTTP and optional SSE streaming.
- Security expectations include origin validation and authentication for remote servers.【modelcontextprotocol.io/specification/2025-03-26/basic/transports】

## Practical Section

### MCP in Cyber Defense Workflows

- Use MCP to standardize access to CTI, SIEM, EDR, and malware analysis tools.
- Separate read‑only tools (enrichment, search) from destructive actions (block, isolate).

### Guardrails for Safe Automation

- Scope tools to least privilege and explicit action boundaries.
- Require human approval for containment actions in Complicated or Complex domains.

### Implementation Patterns

- Local development: stdio servers for fast iteration.
- Shared services: Streamable HTTP servers with auth and logging.

## Example Section

### Example: IOC Enrichment Server

- Inputs: IP, domain, hash.
- Tool calls: DNS lookup, WHOIS, reputation checks.
- Output: normalized enrichment summary and confidence notes.

### Example: Alert Triage Flow

- Sense: query SIEM for related events via MCP tools.
- Think: correlate indicators and determine domain risk.
- Act: only trigger low‑risk actions automatically.

## Knowledge Check

1. What is the difference between an MCP host and server?
2. Why is transport choice important for security tools?
3. Name two ways to reduce risk in MCP tool design.

## Reading List (Module 2 Source Files)

- [The Evolution and Architecture of Generative AI Agents.md](file:///d:/mcp_course/corse/Module_02_MCP_Fundamentals/The%20Evolution%20and%20Architecture%20of%20Generative%20AI%20Agents.md)
- [The Universal Framework for Model Context Protocol Integration.md](file:///d:/mcp_course/corse/Module_02_MCP_Fundamentals/The%20Universal%20Framework%20for%20Model%20Context%20Protocol%20Integration.md)
- [MCP_Universal_AI_Connectivity (1).pdf](file:///d:/mcp_course/corse/Module_02_MCP_Fundamentals/MCP_Universal_AI_Connectivity%20(1).pdf)
- [The_MCP_Universal_Plug.pdf](file:///d:/mcp_course/corse/Module_02_MCP_Fundamentals/The_MCP_Universal_Plug.pdf)

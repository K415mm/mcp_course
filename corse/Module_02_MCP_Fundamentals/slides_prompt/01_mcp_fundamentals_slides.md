---
status: draft
---

# Module 02 Slides: MCP Fundamentals and Architecture

## Slide 1 — Title
**MCP Fundamentals & Architecture**
Module 02 | Agentic AI & MCP for Cyber Defense

---

## Slide 2 — The Problem
**Before MCP: Integration Chaos**
- Every AI framework needed custom connectors per tool
- Tool providers maintained N integrations for N frameworks
- Brittle, fragmented, unmaintainable ecosystem
- Same problem as pre-USB peripherals: every device needed its own cable

---

## Slide 3 — The Solution
**MCP: A Universal Protocol**
- Introduced by Anthropic
- "Write Once, Run Everywhere" for AI tools
- Build one MCP server → works with any compatible AI
- Language-agnostic: Python, TypeScript, Go, Bash, etc.

---

## Slide 4 — Architecture Overview
**Host → Client → Server**
```
          MCP HOST (AI App)
         ┌──────────────────┐
         │ Client 1 ──▶ Server A (CTI)
         │ Client 2 ──▶ Server B (SIEM)
         │ Client 3 ──▶ Server C (EDR)
         └──────────────────┘
```
- **Host:** The AI application
- **Client:** 1:1 connection to a Server
- **Server:** Exposes Tools + Resources + Prompts

---

## Slide 5 — Server Primitives
| Primitive | Type | Example |
|-----------|------|---------|
| **Tools** | Actions | `scan_ip`, `block_domain` |
| **Resources** | Data | Config files, threat feeds |
| **Prompts** | Templates | Triage workflow prompts |

---

## Slide 6 — Two Communication Layers
**Data Layer (WHAT)**
- JSON-RPC 2.0 messages
- Requests, Responses, Notifications

**Transport Layer (HOW)**
- stdio → Local servers (fast, same machine)
- Streamable HTTP → Remote servers (network, auth, TLS)

Same messages work over any transport!

---

## Slide 7 — Local vs Remote
| | stdio (Local) | HTTP (Remote) |
|-|--------------|---------------|
| Speed | Very fast | Network-dependent |
| Auth | OS-level | Tokens, OAuth |
| Use | Dev, local tools | Prod, shared services |
| Risk | Runs with your perms | Standard web security |

---

## Slide 8 — Cyber Defense Application
**The Golden Rule:**
> Enrich automatically. Contain with approval.

- 🟢 Read tools (CTI, SIEM queries) → Auto
- 🔴 Write tools (block, isolate) → Human approval

---

## Slide 9 — Impact
**Alert Triage: Before vs After MCP**
| Manual | MCP-Powered |
|--------|-------------|
| 43 min | ~2 min |
| Serial enrichment | Parallel MCP calls |
| Copy-paste IOCs | Automatic correlation |
95% time reduction!

---

## Slide 10 — Key Takeaways
1. MCP = Universal protocol for AI-tool integration
2. Host → Client → Server architecture
3. Data Layer (JSON-RPC) + Transport Layer (stdio/HTTP)
4. Separate read from write tools
5. Use official SDKs (FastMCP)
6. Always: least privilege + audit logging + fail safe

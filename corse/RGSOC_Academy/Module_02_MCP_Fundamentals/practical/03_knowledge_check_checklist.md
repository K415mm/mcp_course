---
status: published
---

# Module 02 Knowledge Check

## Instructions

Answer the following questions to verify your understanding of MCP Fundamentals. Try to answer from memory before reviewing the lessons.

---

## Questions

### 1. MCP Architecture

**What is the difference between an MCP Host, Client, and Server?**

> **Expected answer:** The **Host** is the AI application (e.g., Claude Desktop), the **Client** is a connector embedded inside the Host that maintains a 1:1 connection to a single Server, and the **Server** is an independent program that exposes Tools, Resources, and Prompts to the AI.

---

### 2. The Problem MCP Solves

**What integration problem existed before MCP, and how does MCP solve it?**

> **Expected answer:** Before MCP, every AI framework required custom, hardcoded connectors for each tool, creating a fragmented, brittle ecosystem. MCP solves this by providing a universal, standardized protocol — "Write Once, Run Everywhere" — so developers build one server and it works with any MCP-compatible AI system.

---

### 3. Communication Layers

**Name and describe the two communication layers in MCP.**

> **Expected answer:** The **Data Layer** handles message content using JSON-RPC 2.0 (requests, responses, notifications). The **Transport Layer** handles how messages physically travel — via **stdio** for local servers or **Streamable HTTP** for remote servers.

---

### 4. Transport Choice and Security

**Why is transport choice important for security tools?**

> **Expected answer:** Local servers (stdio) run with the user's permissions, which is fast but risky with untrusted code. Remote servers (HTTP) work across networks but require authentication, encryption (TLS), and access control. The choice affects security posture, performance, and deployment model.

---

### 5. Risk Reduction

**Name two ways to reduce risk in MCP tool design.**

> **Expected answer:** Any two of: (1) Apply **least privilege** — scope tools to minimum permissions, (2) **Separate read from write** tools, (3) Require **human approval** for containment/destructive actions, (4) **Validate all inputs**, (5) **Audit log** every tool invocation.

---

### 6. MCP Primitives

**What are the three core primitives that an MCP Server can expose?**

> **Expected answer:** **Tools** (executable functions the AI can invoke), **Resources** (contextual data the AI can read), and **Prompts** (reusable prompt templates).

---

### 7. Practical Application

**In a SOC workflow, which types of MCP tools should be automated vs. require human approval?**

> **Expected answer:** **Read-only/enrichment** tools (IOC lookup, SIEM queries, reputation checks) can be automated. **Write/containment** tools (block IP, isolate host, disable account) should require human approval.

---

## Scoring

| Score | Level |
|-------|-------|
| 7/7 | 🏆 Excellent — Ready for Module 03 |
| 5-6/7 | ✅ Good — Review the gaps, then proceed |
| 3-4/7 | ⚠️ Fair — Re-read the theoretical lessons |
| 0-2/7 | 🔄 Needs review — Start from Lesson 01 |

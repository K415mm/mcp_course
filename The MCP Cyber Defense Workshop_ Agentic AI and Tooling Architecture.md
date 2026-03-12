# Course Title: Cyber Defense with MCP and Agentic AI

## Course Overview

This course builds a full, beginner-to-practitioner path for using the Model Context Protocol (MCP) in cyber defense. It starts with core theory (agentic AI, MCP architecture, cyber defense foundations), moves into Python fundamentals and hands-on MCP server/client development, and finishes with integrations, policy boundaries, and practical workshops for CTI, threat hunting, network analysis, and malware analysis.

## Target Audience

Security analysts, SOC engineers, threat hunters, and developers who want to operationalize agentic AI in cyber defense workflows.

## Prerequisites

Basic command-line usage and introductory cybersecurity concepts. No prior MCP experience required.

## Learning Outcomes

By the end, learners can:

1. Explain the agentic AI loop and how MCP enables Sense and Act.
2. Build MCP servers and clients in Python using FastMCP.
3. Integrate MCP tools into Trae AI and other MCP hosts.
4. Apply MCP safely within cyber defense boundaries.
5. Deploy MCP workflows for CTI, threat hunting, network analysis, and malware analysis.

## Delivery Plan

- Format: 8 modules + 4 workshops + capstone
- Duration: 4–6 weeks (flexible pacing)
- Artifacts: slides, labs, reference checklists, and demo scripts

## Module 1: Agentic AI Foundations for Cyber Defense

### 1.1 What Makes an AI Agent “Agentic”

- Sense-Think-Act loop and why tools matter.
- Autonomy vs assistance in security operations.
- Where agentic AI fails without guardrails.

### 1.2 Sense-Think-Act in Security Workflows

- Sense: telemetry collection, logs, endpoint signals.
- Think: correlation, hypothesis generation, and prioritization.
- Act: containment, enrichment, and automated remediation.

## Module 2: MCP Fundamentals and Architecture

### 2.1 Why MCP Exists

- The connector problem in AI tooling.
- MCP as a universal adapter for tools and resources.

### 2.2 MCP Building Blocks

- Hosts, clients, servers, tools, resources.
- JSON-RPC messaging model.
- Transports: stdio, SSE, Streamable HTTP.

### 2.3 Security Boundaries

- Least privilege tools.
- Environment variable secrets and isolation.
- Tool output normalization and prompt injection defense.

## Module 3: Cyber Defense Foundations for MCP Use

### 3.1 SOC Workflow Mapping

- Detection, triage, investigation, containment, and recovery.
- Where MCP adds leverage in each phase.

### 3.2 Core Workstreams

- CTI: sources, normalization, enrichment.
- Threat hunting: hypotheses, queries, signals.
- Network analysis: flow and packet workflows.
- Malware analysis: static and dynamic stages.

## Module 4: Python Essentials for MCP and Agentic AI

### 4.1 Python Basics (Minimal but Practical)

- Functions, types, and data structures.
- Reading files and handling JSON data.
- Error handling with clear user-facing messages.

### 4.2 Agent-Friendly Python

- Type hints for tool schema.
- Docstrings for tool descriptions.
- Input validation for safe automation.

## Module 5: Build MCP Servers with Python

### 5.1 FastMCP Fundamentals

- Initializing a server and adding tools.
- Tool schema generation from type hints.
- Returning structured outputs.

### 5.2 Example Server: CTI Enrichment

- Input: IP, domain, or hash.
- Output: enrichment summary and IOC indicators.
- Safe defaults and output normalization.

### 5.3 Example Server: Network Triage

- Input: pcap or flow files.
- Output: suspicious domains, high-risk IPs, anomalies.

## Module 6: Build MCP Clients with Python

### 6.1 Client Responsibilities

- Tool discovery and invocation.
- Request/response flow with JSON-RPC.
- Retry and timeouts for long-running tasks.

### 6.2 Example Client: Incident Triage Assistant

- Reads alerts, calls enrichment tools, outputs a brief.
- Produces analyst-ready summaries with citations.

## Module 7: Integrate MCP into AI Workspaces

### 7.1 Trae AI Integration

- Local stdio MCP servers for quick iteration.
- Remote SSE/HTTP MCP servers for shared services.
- Minimal configuration pattern: command, args, env.

### 7.2 Google Nati Gravity Integration

- Assumption: Google Nati Gravity acts as an MCP host client.
- Integration pattern mirrors Trae: register the MCP server, define transport, and pass auth via environment variables.
- Use the same tool schema to keep behavior consistent across hosts.

## Module 8: Where and Where Not to Use MCP in Cyber Defense

### 8.1 The Autonomy Matrix

- Clear domain: safe for autonomous playbooks.
- Complicated domain: assisted analysis with human validation.
- Complex domain: hypothesis generation only.
- Chaotic domain: data triage only.

### 8.2 Guardrails

- Human-in-the-loop checkpoints for high-impact actions.
- Tool scope constraints and rate limits.
- Output normalization to avoid LLM bias.

## Workshop 1: CTI Automation with MCP

- Ingest CTI feeds and normalize indicators.
- Enrich IOCs with DNS, WHOIS, and reputation data.
- Produce analyst-ready intelligence briefs.

## Workshop 2: Threat Hunting with MCP

- Build hypothesis templates.
- Automate log queries across sources.
- Summarize findings with supporting evidence.

## Workshop 3: Network Analysis with MCP

- Parse pcap files with safety controls.
- Identify suspicious protocols and domains.
- Create a network risk summary report.

## Workshop 4: Malware Analysis with MCP

- Safe triage and static analysis workflow.
- Behavioral summaries from sandbox outputs.
- IOC extraction with neutral language normalization.

## Tool-Specific MCP Integrations

- Kali Linux automation for scanning and recon.
- REMnux for malware analysis pipelines.
- IDA Pro for reverse engineering assistance.
- SIEM or EDR connectors for alert enrichment.

## Capstone Project

Build a full MCP-enabled cyber defense assistant:

- MCP server with CTI, network triage, and malware analysis tools.
- MCP client that produces incident briefs.
- Integration into Trae AI for interactive workflows.

## Assessment

- Quizzes per module.
- Lab checklists per workshop.
- Final capstone demo and report.

Here is a comprehensive outline and content guide for **Workshop 1** of your beginner's course on using the Model Context Protocol (MCP) for Cyber Defense, based entirely on the provided sources.  
*(Note: The provided sources do not contain any information regarding "Google Antigravity tools." This specific topic is outside the provided materials, and you may need to independently verify or research how MCP integrates with it. The rest of your requested topics are covered in detail below.)*

# Course Title: Beginner's Guide to Agentic AI and MCP in Cyber Defense

## Workshop 1: Agentic AI, MCP Architecture, and Cyber Defense Tooling

### Part 1: Introduction to Agentic AI

To understand MCP, beginners must first understand what an AI agent is. Unlike traditional chatbots that simply answer questions, AI agents are autonomous systems that perceive their environment, reason about goals, and take actions to achieve them 1, 2\.  
**The core of an agent is the "Agent Loop", which consists of three phases:**

* **Sensing:** The agent gathers data from its environment. In an AI framework, this is triggered when the LLM requests a "tool call" to read data from an API, a database, or a physical sensor 3, 4\.  
* **Thinking:** All reasoning, planning, and decision-making are handled by the Large Language Model (LLM). The framework sends the gathered context to the LLM, which decides what to do next 5\.  
* **Acting:** The agent executes a tool that impacts the outside world, such as running a script, modifying a file, or running a cybersecurity scan, and sends the result back to the LLM 6, 7\.

### Part 2: MCP Architecture Overview

Before MCP, developers had to build custom, hardcoded connectors every time they wanted an AI agent to interact with a new external tool 8, 9\. The **Model Context Protocol (MCP)** solves this by providing a universal standard for interactions between AI models and external services 10\.  
**Key Components of MCP:**

* **MCP Hosts & Clients:** The AI application (like an IDE or chat interface) acts as the host, embedding an MCP Client. The client is responsible for dynamically discovering and invoking tools 11, 12\.  
* **MCP Servers:** These are lightweight programs that expose specific tools (functions) and resources to the AI. A single AI client can connect to multiple MCP servers simultaneously 11, 13\.  
* **Transports:** MCP communicates using JSON-RPC 2.0 messages 14\. It uses standard input/output (stdio) for secure, local connections, and Server-Sent Events (SSE) or Streamable HTTP for remote servers 15-17.

### Part 3: Using MCP with Python

Because MCP is language-agnostic, you can build tools using Python, TypeScript, or even Bash 18, 19\.  
For Python developers, building an MCP server is incredibly simple using the official MCP Python SDK and the FastMCP class 20\.

* **Decorator-Based Registration:** You can turn any standard Python function into an AI-accessible tool simply by adding the @mcp.tool() decorator above the function 21\.  
* **Automatic Handling:** FastMCP automatically handles all the complex JSON-RPC message parsing, protocol version negotiation, and type inference from your Python docstrings and type hints 22\.  
* **Execution:** You just initialize the server (e.g., mcp \= FastMCP("My Tool")) and call mcp.run() to start listening for AI requests 23, 24\.

### Part 4: Integrating MCP with AI IDEs and Tools

You can easily connect your custom MCP servers to popular developer tools:

* **Trae AI:** In the Trae IDE, agents act as MCP clients that can make requests to your MCP servers 25\. Trae supports both local stdio servers and remote SSE/Streamable HTTP servers, standardizing how the AI reads tool definitions and execution results 26, 27\.  
* **VS Code, Cursor, and Claude Desktop:** You can connect your MCP servers to these platforms by adding a simple JSON configuration to their settings (e.g., mcp.json or claude\_desktop\_config.json). You simply define the command to run (like python), the arguments, and any necessary environment variables 28-30.

### Part 5: Practical Cyber Defense via MCP

In the final part of the workshop, you will showcase how MCP is actively used in the cybersecurity domain using real-world open-source MCP servers:  
**1\. Penetration Testing (Pentest MCP Server)**This server allows AI agents to perform autonomous penetration testing on Linux distributions (like Kali or Parrot) over SSH 31, 32\.

* **Persistent Sessions:** It uses tmux to maintain persistent sessions, meaning the AI can run long network scans or reverse shells without the session crashing if the network drops 32, 33\.  
* **Interactive Tools:** It allows the AI to send input to interactive console tools like msfconsole (Metasploit) or interact with reverse shells 34, 35\.

**2\. Kali Linux Automation (awsome\_kali\_MCPServers)**A dedicated server designed to expose Kali Linux tools to AI clients like Claude or VS Code via Docker containers 36, 37\.

* The AI can autonomously run basic\_scan or stealth\_scan using **Nmap** 37, 38\.  
* It exposes binary analysis tools like **nm** and **objdump** for symbol and file header analysis 39\.  
* It allows real-time network traffic capture and PCAP analysis using **Wireshark/tshark** 40\.

**3\. Malware Analysis (REMnux MCP Server)**This server connects AI assistants to the REMnux malware analysis toolkit, operating safely via Docker exec, SSH, or local execution 41\.

* **Expert Workflows:** It encodes domain expertise by automatically recommending the right tools based on the detected file type and safely extracting Indicators of Compromise (IOCs) 42, 43\.  
* **Mitigating AI Bias:** Raw analysis tools often flag benign capabilities as "suspicious." This MCP server translates tool outputs into neutral language to prevent the LLM from hallucinating or jumping to false conclusions about malicious intent 44-46.

**4\. Reverse Engineering (IDA Pro MCP Servers)**By installing an MCP server plugin into IDA Pro, you can turn your AI into a reverse engineering assistant 47-49.

* The AI can query IDA Pro to get byte data, disassembly, and decompiled pseudocode 49, 50\.  
* It allows the AI to list functions, find cross-references, and even execute batch rename operations or patch assembly instructions directly in the IDA Pro environment 51-53.


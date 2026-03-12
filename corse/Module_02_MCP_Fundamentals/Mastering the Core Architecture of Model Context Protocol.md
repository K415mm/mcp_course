---
status: draft
---



### 📝Course Module: MCP Architecture Core and Core Components

## 1\. The Motivation Behind MCP

Before diving into how the Model Context Protocol (MCP) works, you must understand the problem it was built to solve.  
Historically, if a developer wanted their AI agent to interact with an external tool, database, or proprietary API, they had to design and write custom, hardcoded connectors from scratch 1\. Because every AI framework was different, tool providers had to build multiple integration layers just to support different AI systems 2\.  
This created a brittle, fragmented ecosystem and a maintenance nightmare for developers 2, 3\.  
Introduced by Anthropic, **MCP solves this by providing a universal, shared protocol that standardizes how tools and resources are exposed to AI models** 3, 4\. It embodies the principle of "Write once, Run everywhere" 4\. A developer builds an MCP server once, and suddenly any MCP-compatible AI system can dynamically discover and use those tools 3\.

## 2\. The Core Architecture Components

MCP operates on a straightforward client-server architecture 5\. To understand how an agentic AI system interacts with MCP, you need to know three main actors:

* **MCP Hosts:** This is the agentic AI application or framework itself (like the AI-6 framework) where the AI agent "lives" 5, 6\.  
* **MCP Clients:** The client is a component embedded directly inside the MCP Host 5, 7\. Its specific job is to reach out to servers, discover what tools are available, and invoke those tools when the AI asks for them 7\. *Note: Because the protocol is complex and constantly evolving, it is highly recommended to use existing client libraries rather than building an MCP client from scratch* 8\.  
* **MCP Servers:** These are independent programs running as separate processes that expose specific capabilities to the AI 6, 9\. An MCP server provides **Tools** (functions the AI can execute) and **Resources** (contextual information or data the AI can read) 6\.

A single AI host can embed a client that connects to multiple different MCP servers simultaneously, instantly giving the AI a massive, composable toolset 5, 9\.

## 3\. The Two Communication Layers

When the MCP Client and MCP Server talk to each other, they use a protocol divided into two distinct layers:

1. **The Data Layer:** This layer handles the actual content of the conversation 5\. It uses JSON-RPC 2.0 messages to manage the connection life cycle and exchange the core primitives, which include the tools, resources, prompts, and notifications 5, 10\.  
2. **The Transport Layer:** This layer handles the physical mechanics of *how* the messages travel from the client to the server, including connection establishment, message framing, and authorization 5\.

## 4\. Local vs. Remote Servers

Because agentic AI systems need to perform a wide variety of tasks securely, MCP clients can connect to servers located in different places using different transport methods 5, 11:

* **Local MCP Servers:** These servers run on the exact same physical machine as the AI agent 11\. Because they are local, the MCP client communicates with them using standard input/output (stdio) streams 11\. This is incredibly fast, but requires caution if you are downloading third-party servers, as they will run with your local user permissions 12\.  
* **Remote MCP Servers:** These servers run on a completely different machine, often in the cloud 11\. To talk to a remote server, the MCP client uses network-based transports like Server-Sent Events (SSE) or Streamable HTTP 11\. Remote servers carry the same networking and authentication concerns as any standard web API 5\.

Regardless of whether the server is local or remote, the sequence of messages remains identical, ensuring the AI agent always experiences a smooth, predictable interaction 10\.  

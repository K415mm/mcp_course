---
status: draft
---

**Understanding MCP Servers**An MCP (Model Context Protocol) server is an independent program designed to expose tools (invokable functions) and resources (contextual information) to LLM-based systems using the MCP standard 1\. By adhering to this protocol, any compliant LLM host application can discover, invoke, and consume the server's capabilities in a predictable and transport-agnostic manner 1\.  
Unlike framework-specific tools (such as AI-6's native tools) that are discovered and imported as modules directly into the system, MCP servers run as independent processes 2\. They operate in two primary modes:

* **Local MCP servers:** Run on the same machine as the MCP client and communicate using standard input/output (STDIO) streams 3, 4\.  
* **Remote MCP servers:** Run on a different machine, such as in the cloud, and communicate using Streamable HTTP transport (Server-Sent Events) 3, 4\.

**Building MCP Servers**Building MCP servers demonstrates the flexibility of the protocol, as they can be written in any programming language 5, 6\. To illustrate this, the source material explores the creation of two distinct MCP servers: a Python-based Filesystem server using an official SDK, and a Bash-based GitHub server built from scratch to demonstrate the raw protocol 5, 6\.  
**1\. The Filesystem MCP Server**The Filesystem MCP server is implemented in Python and leverages the official MCP Python SDK 7\. This approach is highly efficient because the SDK's FastMCP class handles all the heavy lifting, such as registering tools and managing the underlying MCP transport layers 7\.  
Key characteristics of building this server include:

* **Decorator-based implementation:** The server contains multiple tools within a single module. Each tool is a simple Python function marked with the @mcp.tool() decorator, which tells the server to expose it as an MCP tool 8\.  
* **Command-line execution:** Functions such as ls, cat, pwd, mkdir, and cp are defined. They take an args string, parse it using shlex.split, and invoke the underlying system commands using the sh library 8-10.  
* **Simplicity:** By relying on the FastMCP class, the developer does not need to manually handle JSON-RPC messages. The official SDK abstracts the complexity away, making Python-based MCP servers very simple to implement 11\.

**2\. The GitHub MCP Server**The GitHub MCP server is intentionally built using a Bash script 6\. This serves two educational purposes: it proves that MCP servers can be written in any language, and it provides a close look at the low-level JSON-RPC 2.0 messages that make up the protocol, since no SDK is used to hide the complexity 6\.  
Building an MCP server from scratch requires directly handling the protocol's lifecycle and routing JSON-RPC messages 6, 12\. The Bash script runs an infinite loop waiting for client messages and responds based on the "method" field 13:

* **initialize:** The server receives the initial connection request and must respond with its supported protocol version and server information 14\.  
* **notifications/initialized:** The client confirms initialization succeeded. No response from the server is needed 15\.  
* **tools/list:** The server responds with a JSON array of the tools it supports. In this case, it exposes a single tool named "gh" and provides the input schema (requiring an "args" string) so the LLM knows how to use it 15, 16\.  
* **tools/call:** When the client requests a tool execution, the server verifies the tool name is "gh". It then executes the GitHub CLI command (gh $args 2\>&1), captures the standard output, escapes it into proper JSON, and sends the result back to the client 16, 17\. Unrecognized tools or methods trigger properly formatted JSON-RPC error responses 18, 19\.

**Key Takeaway on Building Servers:**While building the GitHub server in Bash reveals the inner workings of the protocol, it is labor-intensive, difficult to maintain if the protocol evolves, and limited to STDIO transport (meaning it cannot be used as a remote server) 19\. Therefore, **it is strongly recommended to use official MCP SDKs** (like the Python SDK used for the Filesystem server) when building your own MCP servers for production 19\.  

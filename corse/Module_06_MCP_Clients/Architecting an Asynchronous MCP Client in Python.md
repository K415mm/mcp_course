---
status: draft
---

Building an MCP client involves creating the component that interacts with MCP servers to discover and invoke tools. While it is possible to build the raw JSON-RPC messaging from scratch, it is highly recommended to use an official library (such as the MCP Python SDK) because the protocol is complex and continually evolving 1\.  
Here is a detailed breakdown of how to build an asynchronous MCP client, focusing on the MCPClient class, connecting to servers, and invoking tools.

### 1\. The MCPClient Class

The MCPClient class is designed to handle asynchronous operations and manage multiple connections to various MCP servers simultaneously. It lives in the mcp\_client.py module 2\.  
**Imports and Setup:**The client relies on asyncio for async operations and AsyncExitStack for safely cleaning up connections. From the official mcp package, it imports session management (ClientSession) and transports for both local (stdio\_client) and remote (sse\_client) servers 2, 3\.  
**Constructor:**The constructor initializes dictionaries to track active sessions and the tools provided by each server, alongside the exit stack for cleanup 3\.  
import asyncio  
from contextlib import AsyncExitStack  
from urllib.parse import urlparse  
from mcp import ClientSession, StdioServerParameters  
from mcp.client.stdio import stdio\_client  
from mcp.client.sse import sse\_client

class MCPClient:  
    """Standalone MCP client for connecting to and interacting with MCP servers."""  
    def \_\_init\_\_(self):  
        self.sessions: dict\[str, ClientSession\] \= {}  
        self.\_server\_tools: dict\[str, list\[dict\]\] \= {}  
        self.exit\_stack \= AsyncExitStack()

### 2\. Connecting to an MCP Server

The connect\_to\_server async method is responsible for establishing a connection, negotiating the protocol, and fetching the available tools. It takes a unique server\_id and a server\_path\_or\_url 4\.  
**Local vs. Remote Transport:**The client first checks if the connection is a URL (for remote servers) or a file path (for local servers) 5\.

* **Remote Servers:** It uses the Server-Sent Events (SSE) transport client (sse\_client) to establish the connection 6\.  
* **Local Servers:** It checks the file extension to determine the command (python for .py, bash for .sh, node for .js) and sets up an STDIO transport using StdioServerParameters and stdio\_client 7, 8\.

**Initialization and Tool Discovery:**Once the transport is established, the client creates a ClientSession, initializes it, and calls session.list\_tools() to discover what the server can do 9\. It uses a 10-second timeout to prevent hanging during initialization 10\. The discovered tools and the active session are then cached 10\.  
    async def connect\_to\_server(self, server\_id: str, server\_path\_or\_url: str) \-\> list\[dict\]:  
        if server\_id in self.sessions:  
            return self.\_server\_tools.get(server\_id, \[\])

        parsed \= urlparse(server\_path\_or\_url)  
        is\_url \= parsed.scheme in ('http', 'https')

        if is\_url:  
            \# Connect via HTTP/SSE for remote servers  
            transport \= await self.exit\_stack.enter\_async\_context(sse\_client(server\_path\_or\_url))  
        else:  
            \# Connect via STDIO for local scripts  
            if server\_path\_or\_url.endswith('.py'):  
                command \= "python"  
            elif server\_path\_or\_url.endswith('.sh'):  
                command \= "bash"  
            elif server\_path\_or\_url.endswith('.js'):  
                command \= "node"  
            else:  
                raise ValueError(f"Unsupported server type: {server\_path\_or\_url}")  
              
            server\_params \= StdioServerParameters(command=command, args=\[server\_path\_or\_url\], env=None)  
            transport \= await self.exit\_stack.enter\_async\_context(stdio\_client(server\_params))

        read, write \= transport  
        session \= await self.exit\_stack.enter\_async\_context(ClientSession(read, write))

        \# Initialize session and fetch tools  
        await asyncio.wait\_for(session.initialize(), timeout=10.0)  
        response \= await asyncio.wait\_for(session.list\_tools(), timeout=10.0)  
          
        tools \= \[{  
            "name": tool.name,  
            "description": tool.description,  
            "parameters": tool.inputSchema  
        } for tool in response.tools\]

        \# Cache session and tools  
        self.sessions\[server\_id\] \= session  
        self.\_server\_tools\[server\_id\] \= tools  
        return tools

### 3\. Invoking an MCP Tool

Once the agentic system decides to use a tool, it calls the invoke\_tool method. This method requires the server\_id, the tool\_name, and a dictionary of tool\_args 11\.  
**Execution and Error Handling:**The method retrieves the active session from the cache and raises an error if it doesn't exist 12, 13\. It then calls session.call\_tool(tool\_name, tool\_args), wrapping the call in a 30-second timeout. This timeout acts as a crucial guardrail to prevent the AI system from hanging indefinitely if a remote server or local script becomes unresponsive 13\. If successful, it extracts the text result and returns it to the LLM 14\.  
    async def invoke\_tool(self, server\_id: str, tool\_name: str, tool\_args: dict) \-\> str:  
        """Invoke a specific tool on the specified MCP server."""  
        session \= self.sessions.get(server\_id)  
        if not session:  
            raise RuntimeError(f"No active session for server '{server\_id}'. Connect to server first.")

        try:  
            \# Add 30-second timeout to prevent hanging on slow servers  
            result \= await asyncio.wait\_for(  
                session.call\_tool(tool\_name, tool\_args),  
                timeout=30.0    
            )  
            return result.content.text if result.content else ""  
              
        except asyncio.TimeoutError:  
            raise RuntimeError(f"Tool invocation timed out for {server\_id}:{tool\_name} after 30 seconds")  
        except Exception as e:  
            raise

### Cleanup and Utilities

A robust MCP client must also manage the lifecycle of its connections. The MCPClient class typically includes additional helper methods such as disconnect\_server() to remove specific sessions and a cleanup() method that calls self.exit\_stack.aclose() to cleanly tear down all active transports and sessions when the application shuts down 15, 16\.  

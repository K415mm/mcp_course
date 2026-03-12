Here is the full, beginner-friendly course module for **Part 3: Using MCP with Python**, continuing our focus on practical mechanics over heavy theory.  
As before, I have included the AI slide generator prompts at the top so you can easily create your visual deck.

### 📝 Prompts to Generate Your Slide Deck

*Copy and paste these prompts into an AI image generator or presentation maker to create your visuals.*

* **Slide 1 (Title):** Generate a presentation slide title: "Building MCP Servers with Python." Include a sleek, modern illustration of the Python logo glowing and connecting to a robot brain.  
* **Slide 2 (The Hard Way vs. The Easy Way):** Create a split-screen diagram. Left side: "Manual Setup" showing a stressed developer surrounded by complex boxes labeled "JSON-RPC," "Schema Generation," and "Message Parsing." Right side: "FastMCP" showing a relaxed developer with a single glowing magic wand pointing to a server. Whiteboard style.  
* **Slide 3 (The Magic Wand):** Create a slide showing a clean snippet of Python code. Highlight a piece of text hovering above the code that says "@mcp.tool()". Add a visual of a robot translating standard Python code into a universal language. Modern tech aesthetic.  
* **Slide 4 (Type Hints & Docstrings):** Create an infographic showing how an AI reads code. Show a standard Python code comment (docstring) turning into a thought bubble for an AI robot. Title: "How the AI Understands Your Code."

# Course Module: Part 3 \- Using MCP with Python

## 1\. Why Python for MCP?

Because the Model Context Protocol (MCP) communicates using standard JSON messages over standard transports, it is entirely **language-agnostic** 1, 2\. You could build an MCP server using TypeScript, Go, or even Bash 1, 3\.  
However, Python is the undisputed king of cybersecurity and AI. Most open-source cyber tools, network scanners, and data analysis libraries are written in Python. Therefore, learning to expose Python functions to AI via MCP is a superpower for a cyber defender.

## 2\. The Secret Weapon: FastMCP

If you wanted to build an MCP server from scratch, you would have to write hundreds of lines of complex code just to handle the communication. You would need to manually parse JSON-RPC messages, negotiate protocol versions, generate tool schemas, and handle request/response errors 4\.  
Fortunately, you don't have to do any of that.  
The official MCP Python SDK includes a class called **FastMCP**. FastMCP handles all the complex protocol details in the background 5\. It acts as a wrapper that automatically translates your standard Python code into the universal MCP language that AI agents understand 5\.

## 3\. The Magic Wand: @mcp.tool()

So, how do you actually turn a regular Python script into a tool an AI can use? You use a Python feature called a **decorator**.  
With FastMCP, you simply write a normal Python function to do a cybersecurity task (like checking an IP address or isolating a machine). Then, you place the @mcp.tool() decorator right above it 6\.  
Here is a practical example of a simple cyber defense tool:  
from mcp.server.fastmcp import FastMCP

\# 1\. Initialize the server  
mcp \= FastMCP("Cyber Defense Server")

\# 2\. Add the magic wand decorator  
@mcp.tool()  
def block\_suspicious\_ip(ip\_address: str) \-\> str:  
    """Blocks a malicious IP address on the local firewall."""  
      
    \# (Imagine firewall blocking code goes here)  
    return f"Successfully blocked the IP: {ip\_address}"  
That is it\! You do not need to write complex JSON schemas to explain the tool to the AI.

## 4\. How the AI Learns About the Tool (Automatic Translation)

You might be wondering: *If I just write normal Python code, how does the AI know what the tool does and what information it needs to provide?*  
FastMCP uses **Automatic Type Inference** 5\. It looks at two specific things in your Python code and translates them for the AI:

1. **Type Hints:** In the code above, ip\_address: str tells FastMCP that the tool requires a string of text. FastMCP automatically builds a rule that forces the AI to provide a string.  
2. **Docstrings:** The text enclosed in triple quotes ("""Blocks a malicious IP...""") is called a docstring. FastMCP automatically extracts this exact text and sends it to the AI agent as the tool's official description 5\.

When the LLM reads that description, it thinks: *"Ah\! If the user ever asks me to stop a bad IP, this is the tool I need to use\!"*

## 5\. Starting the Engine

Once you have written your functions and added your @mcp.tool() decorators, you just need a single line of code at the bottom of your script to start listening for the AI's requests 7\.  
if \_\_name\_\_ \== '\_\_main\_\_':  
    mcp.run()  
When you execute this script, it becomes an active MCP Server 7\. You can now point an AI Client (like the Trae IDE, Claude Desktop, or a custom script) to this server. The AI will instantly discover the block\_suspicious\_ip tool, understand how to use it, and be able to trigger it autonomously 8\.  

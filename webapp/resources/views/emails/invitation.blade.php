<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>You're Invited to RAISEGUARD Academy</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { background-color: #050a14; font-family: 'Segoe UI', Arial, sans-serif; color: #c9d6e3; }
  .email-wrapper { max-width: 620px; margin: 0 auto; padding: 32px 16px; }

  /* Header */
  .header { background: linear-gradient(135deg, #050a14 0%, #0d1b2e 50%, #061526 100%);
    border: 1px solid rgba(0,212,255,0.15); border-radius: 16px 16px 0 0;
    padding: 40px 40px 30px; text-align: center; position: relative; overflow: hidden; }
  .header::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
    background: radial-gradient(ellipse at center, rgba(0,212,255,0.06) 0%, transparent 60%); }
  .logo-badge { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 24px; }
  .logo-icon { width: 48px; height: 48px; background: linear-gradient(135deg, #00d4ff, #0066cc);
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 900; color: #000; }
  .logo-text { font-size: 20px; font-weight: 700; color: #fff; letter-spacing: 2px; }
  .header h1 { font-size: 28px; font-weight: 800; color: #ffffff; line-height: 1.2; margin-bottom: 8px; }
  .header h1 span { color: #00d4ff; }
  .header p { font-size: 15px; color: #7a9bb5; margin-top: 8px; }

  /* Cinematic divider */
  .divider { height: 2px; background: linear-gradient(90deg, transparent, #00d4ff, #0066cc, transparent); }

  /* Body */
  .body { background: #081121; border: 1px solid rgba(0,212,255,0.1); border-top: none; padding: 40px; }

  .greeting { font-size: 16px; color: #a0b8c8; line-height: 1.7; margin-bottom: 28px; }
  .greeting strong { color: #00d4ff; }

  /* Feature cards */
  .features { display: grid; gap: 14px; margin: 28px 0; }
  .feature { background: rgba(0,212,255,0.04); border: 1px solid rgba(0,212,255,0.12);
    border-radius: 10px; padding: 16px 18px; display: flex; align-items: flex-start; gap: 14px; }
  .feature-icon { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
  .feature-title { font-size: 14px; font-weight: 700; color: #e0eaf5; margin-bottom: 3px; }
  .feature-desc { font-size: 13px; color: #7a9bb5; line-height: 1.5; }

  /* Certificate / gift highlight */
  .highlight-box { background: linear-gradient(135deg, rgba(251,191,36,0.06), rgba(245,158,11,0.03));
    border: 1px solid rgba(251,191,36,0.25); border-radius: 12px; padding: 20px 24px;
    margin: 28px 0; text-align: center; }
  .highlight-box .icon { font-size: 36px; margin-bottom: 10px; }
  .highlight-box h3 { font-size: 17px; font-weight: 700; color: #fbbf24; margin-bottom: 6px; }
  .highlight-box p { font-size: 13px; color: #9ca3af; line-height: 1.6; }

  /* CTA Button */
  .cta-wrap { text-align: center; margin: 36px 0; }
  .cta-btn { display: inline-block; background: linear-gradient(135deg, #00d4ff, #0066cc);
    color: #000; font-size: 16px; font-weight: 800; text-decoration: none;
    padding: 16px 44px; border-radius: 50px; letter-spacing: 0.5px;
    box-shadow: 0 8px 32px rgba(0,212,255,0.3); }

  /* Module list */
  .module-list { margin: 20px 0; }
  .module-item { display: flex; align-items: center; gap: 10px; padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 13px; color: #8fa8bd; }
  .module-item:last-child { border-bottom: none; }
  .module-num { background: linear-gradient(135deg, #00d4ff, #0066cc); color: #000;
    font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 20px; flex-shrink: 0; }

  /* Footer */
  .footer { background: #040c18; border: 1px solid rgba(0,212,255,0.08); border-top: none;
    border-radius: 0 0 16px 16px; padding: 24px 40px; text-align: center; }
  .footer p { font-size: 12px; color: #4a6070; line-height: 1.6; }
  .footer a { color: #00d4ff; text-decoration: none; }
  .expire-notice { margin-top: 20px; padding: 12px; background: rgba(239,68,68,0.06);
    border: 1px solid rgba(239,68,68,0.15); border-radius: 8px;
    font-size: 12px; color: #9ca3af; }
</style>
</head>
<body>
<div class="email-wrapper">

  <!-- Header -->
  <div class="header">
    <div class="logo-badge">
      <div class="logo-icon">R</div>
      <span class="logo-text">RAISEGUARD</span>
    </div>
    <h1>You're Invited to<br><span>RAISEGUARD Academy</span></h1>
    <p>AI-Powered Cyber Defence Training</p>
  </div>
  <div class="divider"></div>

  <!-- Body -->
  <div class="body">
    <p class="greeting">
      {{ $invitation->name ? 'Hi ' . $invitation->name . ',' : 'Hello,' }}<br><br>
      You have been personally invited to join <strong>RAISEGUARD Academy</strong> — the
      hands-on cybersecurity AI course that teaches you how to build, deploy, and govern
      autonomous MCP agents for real-world SOC operations.
    </p>

    <!-- Feature cards -->
    <div class="features">
      <div class="feature">
        <div class="feature-icon">🤖</div>
        <div>
          <div class="feature-title">8 Comprehensive Modules</div>
          <div class="feature-desc">From Agentic AI fundamentals to deploying production MCP servers and clients — complete hands-on training.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">🛡️</div>
        <div>
          <div class="feature-title">5 Live SOC Workshops</div>
          <div class="feature-desc">Real CTI automation, threat hunting, network analysis, and malware analysis exercises using Jupyter Notebooks.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">🧑‍💻</div>
        <div>
          <div class="feature-title">Project-Based Learning</div>
          <div class="feature-desc">Build working tools, not just theory. Every module ends with a hands-on lab you keep forever.</div>
        </div>
      </div>
    </div>

    <!-- Certificate / Gift highlight -->
    <div class="highlight-box">
      <div class="icon">🏆</div>
      <h3>Certificate of Completion + Exclusive Gift</h3>
      <p>Students who complete the full programme receive a <strong style="color:#fbbf24;">RAISEGUARD Academy Certificate of Achievement</strong> in AI-Powered Cyber Defence, plus an exclusive physical or digital gift as a reward for your commitment.</p>
    </div>

    <!-- Module list -->
    <p style="font-size:13px; color:#7a9bb5; margin-bottom:12px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">What You Will Learn</p>
    <div class="module-list">
      @foreach([
        ['M01','Agentic AI Foundations'],['M02','MCP Protocol & Architecture'],
        ['M03','Cyber Defence & SOC Workflows'],['M04','Python for MCP Development'],
        ['M05','Building MCP Servers with FastMCP'],['M06','Building Autonomous MCP Clients'],
        ['M07','End-to-End SOC Integrations'],['M08','Policy, Governance & Guardrails'],
      ] as [$num,$title])
      <div class="module-item">
        <span class="module-num">{{ $num }}</span>
        <span>{{ $title }}</span>
      </div>
      @endforeach
    </div>

    <!-- CTA -->
    <div class="cta-wrap">
      <a href="{{ $inviteUrl }}" class="cta-btn">🚀 Accept Your Invitation</a>
    </div>

    @if($expiresAt)
    <div class="expire-notice">
      ⏰ This invitation expires on <strong>{{ $expiresAt }}</strong>. Please register before it expires.
    </div>
    @endif
  </div>

  <!-- Footer -->
  <div class="footer">
    <p>
      You received this invitation because someone from RAISEGUARD Academy chose you personally for this opportunity.<br>
      If you did not expect this email, you can safely ignore it.<br><br>
      © {{ date('Y') }} RAISEGUARD Academy · <a href="{{ url('/') }}">tunai.cloud</a>
    </p>
    <p style="margin-top:10px; font-size:11px; color:#2d3f50;">
      If the button doesn't work, copy this link: <a href="{{ $inviteUrl }}" style="color:#00d4ff; word-break:break-all;">{{ $inviteUrl }}</a>
    </p>
  </div>

</div>
</body>
</html>

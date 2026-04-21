<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>You're Invited to Carthage Shield</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { 
    background-color: #f4f5f7; /* Light silver/gray background */
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
    color: #1e293b; /* Dark text for readability */
    -webkit-font-smoothing: antialiased;
  }
  .email-wrapper { max-width: 600px; margin: 0 auto; padding: 40px 20px; }

  /* Main Minimalist Panel */
  .hud-panel {
    background: #ffffff; /* Clean white card */
    border: 1px solid #e2e8f0;
    padding: 40px 40px;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  }

  /* Header */
  .brand-logo { text-align: center; margin-bottom: 35px; }
  .brand-logo img { max-width: 120px; height: auto; }

  h1 { font-size: 24px; font-weight: 700; color: #b8860b; /* Elegant Dark Gold */ line-height: 1.3; margin-bottom: 8px; text-align: center; }
  .subtitle { font-size: 13px; color: #b8860b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 35px; text-align: center; font-weight: 600; }

  /* Body Content */
  .greeting { font-size: 15px; color: #334155; line-height: 1.6; margin-bottom: 25px; }
  .greeting strong { color: #000000; font-weight: 600; }

  /* Module List */
  .module-list { margin: 35px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; padding: 25px 0; }
  .module-list-title { font-size: 12px; color: #b8860b; text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 20px; font-weight: 700; text-align: center; }
  .module-item { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; font-size: 14px; color: #475569; }
  .module-item:last-child { margin-bottom: 0; }
  .module-badge { background: #f8fafc; border: 1px solid #e2e8f0; color: #000000; font-size: 11px; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-weight: 600; }
  
  /* Highlight Box */
  .highlight-box { background: #fafafa; border-left: 3px solid #b8860b; padding: 20px; margin: 30px 0; border-radius: 0 8px 8px 0; display: flex; gap: 15px; }
  .highlight-icon { font-size: 20px; }
  .highlight-title { font-size: 14px; font-weight: 700; color: #000000; margin-bottom: 6px; }
  .highlight-text { font-size: 13px; color: #475569; line-height: 1.6; }

  /* CTA Button */
  .cta-wrap { margin: 40px 0 20px; text-align: center; }
  .cta-btn { display: inline-block; background: #000000; color: #ffffff; font-size: 14px; font-weight: 600;
    text-decoration: none; padding: 14px 35px; border-radius: 6px; text-transform: uppercase; letter-spacing: 1px; transition: all 0.2s; }

  /* Footer */
  .footer { margin-top: 30px; padding: 25px; background: #0b1117; border-radius: 12px; font-size: 12px; line-height: 1.8; text-align: center; }
  .footer p { margin-bottom: 10px; }
  .footer p:last-child { margin-bottom: 0; }
  .footer a { text-decoration: none; }
  .expire-notice { margin-top: 25px; padding: 15px; background: #fff1f2; border: 1px solid #ffe4e6; border-radius: 6px; font-size: 12px; color: #e11d48; text-align: center; }
</style>
</head>
<body>
<div class="email-wrapper">
  
  <div class="hud-panel">
    <div class="brand-logo">
      <img src="https://cyberhero.defensy.io/cs-assets/game_logo.png" alt="Carthage Shield Logo">
    </div>

    <h1>You're Invited to Join</h1>
    <div class="subtitle">Cyber Breach Tabletop Simulation</div>

    <p class="greeting">
      {{ $invitation->name ? 'Hi ' . $invitation->name . ',' : 'Hello,' }}<br><br>
      You have been granted exclusive access to the <strong>Carthage Shield</strong> platform. Prepare to face the Phantom Grid attack in our highly immersive, real-time cyber breach exercise.
    </p>

    <div class="module-list">
      <div class="module-list-title">Exercise Briefing</div>
      @foreach([
        ['01','Real-time SOC Environment Simulation'],
        ['02','Dynamic Threat Injects (Phantom Grid)'],
        ['03','Team-Based Incident Response'],
        ['04','Executive Reporting & Action Plans'],
      ] as [$num,$title])
      <div class="module-item">
        <span class="module-badge">{{ $num }}</span>
        <span>{{ $title }}</span>
      </div>
      @endforeach
    </div>

    <div class="highlight-box">
      <div class="highlight-icon">🎯</div>
      <div>
        <div class="highlight-title">Mission Objective</div>
        <div class="highlight-text">Analyze incoming threats, collaborate with your team, and make critical decisions to defend the infrastructure against advanced persistent threats.</div>
      </div>
    </div>

    <div class="cta-wrap">
      <a href="{{ $inviteUrl }}" class="cta-btn">Initialize Connection</a>
    </div>

    @if($expiresAt)
    <div class="expire-notice">
      <strong>SYSTEM ALERT:</strong> This token expires on {{ $expiresAt }}
    </div>
    @endif
  </div>

  <div class="footer">
    <p>
      <span style="color: gold;">This is an automated system message from</span> 
      <span style="color: red;">defensy</span><br>
      <span style="color: white;">access granted via</span> 
      <span style="color: magenta;">cyberhero platform defensy 2026.</span>
    </p>
    <p style="font-size:11px; word-break:break-all;">
      <span style="color: white;">Backup link:</span> <a href="{{ $inviteUrl }}" style="color: gold;">{{ $inviteUrl }}</a>
    </p>
  </div>

</div>
</body>
</html>
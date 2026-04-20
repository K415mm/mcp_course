<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>You're Invited to Carthage Shield</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { 
    background-color: #0b1117; 
    background-image: url('{{ $message->embed(public_path("hud/img/cover/cover-raiseguard.png")) }}');
    background-size: cover;
    background-position: center;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
    color: #a4b2c1; 
  }
  .email-wrapper { max-width: 600px; margin: 0 auto; padding: 40px 20px; }

  /* HUD Panel */
  .hud-panel {
    background: rgba(0, 0, 0, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.08);
    position: relative;
    padding: 35px 40px;
    border-radius: 4px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
  }

  /* HUD Corner Brackets */
  .hud-bracket { position: absolute; width: 10px; height: 10px; border-color: rgba(255, 255, 255, 0.25); border-style: solid; }
  .bracket-tl { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
  .bracket-tr { top: -1px; right: -1px; border-width: 2px 2px 0 0; }
  .bracket-bl { bottom: -1px; left: -1px; border-width: 0 0 2px 2px; }
  .bracket-br { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

  /* Header */
  .brand-logo { display: inline-flex; align-items: center; gap: 12px; margin-bottom: 30px; }
  .brand-img { width: 38px; height: 38px; background: rgba(4, 236, 240, 0.15); border-radius: 6px;
    display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; color: #04ecf0; }
  .brand-text { font-size: 18px; font-weight: 700; color: #04ecf0; letter-spacing: 1px; }

  h1 { font-size: 24px; font-weight: 700; color: #ffffff; line-height: 1.3; margin-bottom: 8px; }
  .subtitle { font-size: 14px; color: #6c7e93; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 30px; }

  /* Body Content */
  .greeting { font-size: 15px; color: #cbd5e1; line-height: 1.6; margin-bottom: 25px; }
  .greeting strong { color: #ffffff; }

  /* Module List */
  .module-list { margin: 30px 0; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 20px; }
  .module-list-title { font-size: 12px; color: #04ecf0; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; font-weight: 600; }
  .module-item { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; font-size: 13px; color: #94a3b8; }
  .module-badge { background: rgba(4, 236, 240, 0.1); color: #04ecf0; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-family: monospace; }
  
  /* Highlight Box */
  .highlight-box { background: rgba(255, 193, 7, 0.05); border: 1px solid rgba(255, 193, 7, 0.2);
    border-radius: 4px; padding: 18px 20px; margin: 30px 0; display: flex; gap: 15px; }
  .highlight-icon { font-size: 24px; }
  .highlight-title { font-size: 14px; font-weight: 700; color: #ffc107; margin-bottom: 4px; }
  .highlight-text { font-size: 13px; color: #94a3b8; line-height: 1.5; }

  /* CTA Button */
  .cta-wrap { margin: 35px 0 20px; }
  .cta-btn { display: inline-block; background: #04ecf0; color: #000000; font-size: 14px; font-weight: 700;
    text-decoration: none; padding: 14px 35px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px; }

  /* Footer */
  .footer { margin-top: 30px; font-size: 12px; color: #475569; line-height: 1.6; text-align: center; }
  .footer a { color: #04ecf0; text-decoration: none; }
  .expire-notice { margin-top: 20px; padding: 12px; background: rgba(239, 68, 68, 0.05);
    border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 4px; font-size: 12px; color: #cbd5e1; }
</style>
</head>
<body>
<div class="email-wrapper">
  
  <div class="hud-panel">
    <!-- Corner brackets -->
    <div class="hud-bracket bracket-tl"></div>
    <div class="hud-bracket bracket-tr"></div>
    <div class="hud-bracket bracket-bl"></div>
    <div class="hud-bracket bracket-br"></div>

    <div class="brand-logo">
      <div class="brand-img">C</div>
      <div class="brand-text">CARTHAGE SHIELD</div>
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
      SYSTEM ALERT: This token expires on <strong>{{ $expiresAt }}</strong>
    </div>
    @endif
  </div>

  <div class="footer">
    <p>This is an automated system message from Carthage Shield Command.<br>
       Access granted via <a href="{{ url('/') }}">tunai.cloud</a> HUD.</p>
    <p style="margin-top:10px; font-size:11px; word-break:break-all;">
      Backup link: <a href="{{ $inviteUrl }}">{{ $inviteUrl }}</a>
    </p>
  </div>

</div>
</body>
</html>

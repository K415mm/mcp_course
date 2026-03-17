<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Module Complete — RAISEGUARD Academy</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { 
    background-color: #0b1117; 
    background-image: url('{{ $message->embed(public_path("hud/img/cover/cover-raiseguard.png")) }}');
    background-size: cover;
    background-position: center;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; 
    color:#a4b2c1; 
  }
  .wrapper { max-width: 600px; margin: 0 auto; padding: 40px 20px; }

  /* HUD Panel */
  .hud-panel {
    background: rgba(0, 0, 0, 0.85); border: 1px solid rgba(255, 255, 255, 0.08);
    position: relative; padding: 40px 40px; border-radius: 4px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center;
  }

  /* HUD Brackets */
  .hud-bracket { position: absolute; width: 10px; height: 10px; border-color: rgba(4, 236, 240, 0.4); border-style: solid; }
  .bracket-tl { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
  .bracket-tr { top: -1px; right: -1px; border-width: 2px 2px 0 0; }
  .bracket-bl { bottom: -1px; left: -1px; border-width: 0 0 2px 2px; }
  .bracket-br { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

  /* Header */
  .brand-text { font-size: 13px; font-weight: 700; color: #04ecf0; letter-spacing: 2px; margin-bottom: 30px; }
  
  /* Badge display */
  .badge-display { width: 100px; height: 100px; margin: 0 auto 25px; border: 1px solid rgba(4, 236, 240, 0.3);
    background: rgba(4, 236, 240, 0.05); border-radius: 8px; display: flex; align-items: center; justify-content: center;
    font-size: 40px; position: relative; }
  .badge-tag { position: absolute; bottom: -8px; background: #04ecf0; color: #000;
    font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 2px; font-family: monospace; }

  h1 { font-size: 22px; font-weight: 700; color: #fff; margin-bottom: 8px; }
  .subtitle { font-size: 14px; color: #6c7e93; margin-bottom: 30px; }

  /* Progress Bar */
  .progress-wrap { margin: 30px 0; text-align: left; }
  .progress-head { display: flex; justify-content: space-between; font-size: 12px; color: #04ecf0;
    text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; font-weight: 600; }
  .progress-track { background: rgba(255,255,255,0.05); height: 6px; border-radius: 3px; overflow: hidden; }
  .progress-fill { background: #04ecf0; height: 100%; box-shadow: 0 0 10px rgba(4,236,240,0.5); }

  /* Content */
  .message { font-size: 15px; color: #cbd5e1; line-height: 1.6; text-align: left; margin-bottom: 30px; }

  /* Next Step Box */
  .next-box { background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.15);
    border-radius: 4px; padding: 20px; text-align: left; margin-bottom: 30px; }
  .next-header { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
  .next-title { font-size: 15px; font-weight: 700; color: #fff; }

  /* CTA */
  .cta-btn { display: inline-block; background: #04ecf0; color: #000; font-size: 14px; font-weight: 700;
    text-decoration: none; padding: 14px 35px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px; }

  /* Footer */
  .footer { margin-top: 30px; font-size: 12px; color: #475569; line-height: 1.6; text-align: center; }
  .footer a { color: #04ecf0; text-decoration: none; }
</style>
</head>
<body>
<div class="wrapper">

  <div class="hud-panel">
    <div class="hud-bracket bracket-tl"></div>
    <div class="hud-bracket bracket-tr"></div>
    <div class="hud-bracket bracket-bl"></div>
    <div class="hud-bracket bracket-br"></div>

    <div class="brand-text">RAISEGUARD ACADEMY</div>
    
    <div class="badge-display">
      🎖️
      <div class="badge-tag">M{{ $moduleNum }}</div>
    </div>

    <h1>Module Complete</h1>
    <div class="subtitle">{{ $module['title'] }}</div>

    <div class="progress-wrap">
      <div class="progress-head">
        <span>System Progress</span>
        <span>{{ intval($moduleNum)/8*100 }}%</span>
      </div>
      <div class="progress-track">
        <div class="progress-fill" style="width:{{ intval($moduleNum)/8*100 }}%;"></div>
      </div>
    </div>

    <div class="message">
      Operation successful, <strong>{{ $user->name }}</strong>.<br><br>
      You have successfully completed the <strong>{{ $module['title'] }}</strong> training module and earned the <em>{{ $badgeLabel }}</em> achievement badge. Your HUD transcript has been updated.
    </div>

    @if($nextModule)
    <div class="next-box">
      <div class="next-header">▶ Ready for next sequence</div>
      <div class="next-title">{{ $nextModule['title'] }}</div>
    </div>
    @endif

    <a href="{{ $continueUrl }}" class="cta-btn">Resume Training</a>
  </div>

  <div class="footer">
    <p>Automated progression alert from RAISEGUARD Academy.<br>
       <a href="{{ url('/') }}">tunai.cloud</a></p>
  </div>

</div>
</body>
</html>

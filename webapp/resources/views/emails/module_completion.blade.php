<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Module Complete — RAISEGUARD Academy</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#050a14; font-family:'Segoe UI',Arial,sans-serif; color:#c9d6e3; }
  .wrapper { max-width: 600px; margin: 0 auto; padding: 32px 16px; }

  /* Header */
  .header { background: linear-gradient(135deg,#071628,#091f3a,#0a1e30);
    border:1px solid rgba(0,212,255,.15); border-radius:16px 16px 0 0;
    padding:36px 40px 28px; text-align:center; }
  .logo { font-size:13px; font-weight:700; color:#00d4ff; letter-spacing:3px;
    text-transform:uppercase; margin-bottom:24px; }
  .badge-wrap { margin: 0 auto 24px; width:120px; height:120px; position:relative; }
  .badge-circle { width:120px; height:120px; border-radius:50%;
    background:linear-gradient(135deg,rgba(0,212,255,.15),rgba(0,102,204,.1));
    border:3px solid rgba(0,212,255,.4); display:flex; align-items:center;
    justify-content:center; font-size:48px; box-shadow:0 0 40px rgba(0,212,255,.2); }
  .module-num-badge { position:absolute; bottom:-6px; right:-6px;
    background:linear-gradient(135deg,#00d4ff,#0066cc); color:#000; font-weight:800;
    font-size:11px; padding:3px 10px; border-radius:20px; }
  .header h1 { font-size:24px; font-weight:800; color:#fff; line-height:1.3; margin-bottom:6px; }
  .header h1 span { color:#00d4ff; }
  .header p { font-size:14px; color:#7a9bb5; }

  .divider { height:2px; background:linear-gradient(90deg,transparent,#00d4ff,#0066cc,transparent); }

  /* Body */
  .body { background:#081121; border:1px solid rgba(0,212,255,.1); border-top:none; padding:36px 40px; }

  .congrats { font-size:16px; color:#a0b8c8; line-height:1.7; margin-bottom:24px; }
  .congrats strong { color:#00d4ff; }

  /* Progress bar */
  .progress-section { margin: 24px 0; }
  .progress-label { font-size:12px; color:#7a9bb5; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;
    display:flex; justify-content:space-between; }
  .progress-bar-bg { background:rgba(255,255,255,.06); border-radius:50px; height:8px; overflow:hidden; }
  .progress-bar-fill { height:100%; border-radius:50px;
    background:linear-gradient(90deg,#00d4ff,#0066cc); }

  /* Achievement box */
  .achievement { background:rgba(0,212,255,.04); border:1px solid rgba(0,212,255,.15);
    border-radius:12px; padding:20px 24px; margin:24px 0; text-align:center; }
  .achievement h3 { font-size:16px; font-weight:700; color:#e0eaf5; margin-bottom:6px; }
  .achievement p { font-size:13px; color:#7a9bb5; }
  .badge-label { display:inline-block; margin-top:12px; background:linear-gradient(135deg,#00d4ff20,#0066cc20);
    border:1px solid rgba(0,212,255,.3); border-radius:8px; padding:6px 18px;
    font-size:12px; font-weight:700; color:#00d4ff; letter-spacing:1px; }

  /* Next steps */
  .next-section { margin:28px 0; }
  .next-section h3 { font-size:14px; font-weight:700; color:#fff; margin-bottom:12px;
    text-transform:uppercase; letter-spacing:1px; }
  .next-card { background:linear-gradient(135deg,rgba(0,212,255,.06),rgba(0,102,204,.04));
    border:1px solid rgba(0,212,255,.15); border-radius:10px; padding:16px 20px;
    display:flex; align-items:center; gap:14px; }
  .next-arrow { font-size:24px; }
  .next-card-title { font-size:14px; font-weight:700; color:#e0eaf5; }
  .next-card-sub { font-size:12px; color:#7a9bb5; margin-top:3px; }

  /* CTA */
  .cta-wrap { text-align:center; margin:32px 0; }
  .cta-btn { display:inline-block; background:linear-gradient(135deg,#00d4ff,#0066cc);
    color:#000; font-size:15px; font-weight:800; text-decoration:none;
    padding:14px 40px; border-radius:50px; box-shadow:0 8px 28px rgba(0,212,255,.25); }

  /* Certificate teaser */
  .cert-box { background:linear-gradient(135deg,rgba(251,191,36,.06),transparent);
    border:1px solid rgba(251,191,36,.2); border-radius:10px;
    padding:16px 20px; margin:24px 0; display:flex; align-items:center; gap:16px; }
  .cert-box .icon { font-size:28px; flex-shrink:0; }
  .cert-box p { font-size:13px; color:#9ca3af; line-height:1.5; }
  .cert-box strong { color:#fbbf24; }

  /* Footer */
  .footer { background:#040c18; border:1px solid rgba(0,212,255,.08); border-top:none;
    border-radius:0 0 16px 16px; padding:24px 40px; text-align:center; }
  .footer p { font-size:12px; color:#3a5060; line-height:1.7; }
  .footer a { color:#00d4ff; text-decoration:none; }
</style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <div class="logo">RAISEGUARD ACADEMY</div>
    <div class="badge-wrap">
      <div class="badge-circle">🏅</div>
      <span class="module-num-badge">M{{ $moduleNum }}</span>
    </div>
    <h1>Module Complete!<br><span>{{ $module['title'] }}</span></h1>
    <p>Achievement unlocked — keep going!</p>
  </div>
  <div class="divider"></div>

  <div class="body">
    <p class="congrats">
      Hi <strong>{{ $user->name }}</strong>,<br><br>
      Congratulations! You have successfully completed <strong>{{ $module['title'] }}</strong>.
      This is a significant milestone in your journey to becoming an AI-powered cyber defence
      professional. You're building real, deployable skills that matter.
    </p>

    <!-- Progress bar -->
    <div class="progress-section">
      <div class="progress-label">
        <span>Course Progress</span>
        <span style="color:#00d4ff;">Module {{ $moduleNum }}/08 Complete</span>
      </div>
      <div class="progress-bar-bg">
        <div class="progress-bar-fill" style="width:{{ intval($moduleNum)/8*100 }}%;"></div>
      </div>
    </div>

    <!-- Achievement -->
    <div class="achievement">
      <h3>🏆 Achievement Unlocked</h3>
      <p>You have earned the badge for completing this module:</p>
      <div class="badge-label">{{ $badgeLabel }}</div>
    </div>

    <!-- Next module suggestion -->
    @if($nextModule)
    <div class="next-section">
      <h3>▶ Your Next Step</h3>
      <div class="next-card">
        <div class="next-arrow">🚀</div>
        <div>
          <div class="next-card-title">Continue to: {{ $nextModule['title'] }}</div>
          <div class="next-card-sub">Keep your momentum — the next module builds directly on what you just learned.</div>
        </div>
      </div>
    </div>
    @endif

    <!-- CTA -->
    <div class="cta-wrap">
      <a href="{{ $continueUrl }}" class="cta-btn">Continue Learning →</a>
    </div>

    <!-- Certificate teaser -->
    <div class="cert-box">
      <div class="icon">🎓</div>
      <p>Complete all 8 modules and 5 workshops to earn your <strong>RAISEGUARD Academy Certificate of Achievement</strong> in AI-Powered Cyber Defence — plus an exclusive gift for graduates! You're {{ intval($moduleNum)/8*100 }}% of the way there.</p>
    </div>
  </div>

  <div class="footer">
    <p>
      © {{ date('Y') }} RAISEGUARD Academy · <a href="{{ url('/') }}">tunai.cloud</a><br>
      You received this email because you are enrolled in RAISEGUARD Academy.
    </p>
  </div>

</div>
</body>
</html>

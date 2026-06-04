<!DOCTYPE html>
@php
    $isEn = true;
    $useThree = config('course.neptune_threejs', true);
@endphp
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $scenario['title'] ?? 'NEPTUNE STRIKE' }} — Grand Screen</title>

{{-- HUD theme assets (same as app layout) --}}
<link href="{{ asset('hud/css/vendor.min.css') }}" rel="stylesheet">
<link href="{{ asset('hud/css/app.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/three@0.162.0/build/three.min.js"></script>

<style>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   NEPTUNE STRIKE â€” GRAND SCREEN DESIGN SYSTEM
   Palette: Cyan #00ffcc | Blue #00aaff | Deep Navy #000810
   â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */

/* â”€â”€ CSS Tokens â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
:root {
    --cs-gold:    #00ffcc;
    --cs-gold2:   #00e5b4;
    --cs-red:     #00aaff;
    --cs-red2:    #0077dd;
    --cs-dark:    #000810;
    --cs-dark2:   rgba(0,5,12,.94);
    --cs-border:  rgba(0,255,204,.2);
    --cs-glow-g:  rgba(0,255,204,.3);
    --cs-glow-r:  rgba(0,170,255,.3);
}

/* â”€â”€ Atmosphere transitions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
body { transition: background 2s ease; }
body.atmo-tension  { background: #070800 !important; }
body.atmo-crisis   { background: #0d0303 !important; }
body.atmo-hacked   { background: #000    !important; }
body.atmo-victory  { background: #030c05 !important; }
body.scanlines::after {
    content: '';
    position: fixed; inset: 0;
    background: repeating-linear-gradient(0deg, rgba(0,0,0,.12) 0px, rgba(0,0,0,.12) 1px, transparent 1px, transparent 4px);
    pointer-events: none; z-index: 999;
}

/* â”€â”€ Layout â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.cs-layout {
    display: grid;
    grid-template-rows: 88px 1fr 220px;
    height: 100vh;
    padding: 12px 12px 12px;
    gap: 10px;
    position: relative; z-index: 1;
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   HEADER â€” Teal/Blue HUD Bar
   â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.cs-header {
    position: relative;
    display: flex; align-items: center;
    height: 88px;
    /* Deep navy + teal gradient */
    background: linear-gradient(90deg,
        rgba(0,3,12,.98) 0%,
        rgba(0,10,24,.97) 30%,
        rgba(0,16,32,.97) 50%,
        rgba(0,10,24,.97) 70%,
        rgba(0,3,12,.98) 100%
    );
    /* Cyan top border, blue bottom accent */
    border-top: 2px solid var(--cs-gold);
    border-bottom: 1px solid var(--cs-red);
    border-left: 1px solid var(--cs-border);
    border-right: 1px solid var(--cs-border);
    border-radius: 10px;
    backdrop-filter: blur(12px);
    box-shadow:
        0 0 40px rgba(0,170,255,.15),
        inset 0 1px 0 rgba(0,255,204,.12),
        inset 0 -1px 0 rgba(0,170,255,.15);
    overflow: visible;
    padding: 0 28px;
}

/* Subtle inner teal shimmer line across top */
.cs-header::before {
    content: '';
    position: absolute; top: 0; left: 10%; right: 10%; height: 1px;
    background: linear-gradient(90deg, transparent, var(--cs-gold2), transparent);
    opacity: .4;
}

/* â”€â”€ Left block: title + phase bar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.cs-left {
    display: flex; flex-direction: column; justify-content: center; gap: 5px;
    flex: 1; min-width: 0;
}
.logo-txt {
    font-family: 'Space Mono', monospace;
    font-weight: 700; font-size: 1.3rem; letter-spacing: 4px;
    /* Cyan gradient text */
    background: linear-gradient(90deg, var(--cs-gold) 0%, var(--cs-gold2) 50%, var(--cs-gold) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: none;
}
.logo-txt .shield-word {
    background: linear-gradient(90deg, var(--cs-red) 0%, #22d3f0 50%, var(--cs-red) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.scenario-sub {
    font-size: .72rem;
    color: rgba(0,255,204,.45);
    font-family: 'Space Mono', monospace;
    letter-spacing: 1px;
}

/* â”€â”€ Phase bar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.phase-bar { display: flex; align-items: center; gap: 5px; margin-top: 2px; }
.ph-seg {
    width: 28px; height: 4px; border-radius: 2px;
    background: rgba(0,255,204,.12); transition: all .5s;
}
.ph-seg.done  { background: rgba(0,255,204,.5); opacity: .8; }
.ph-seg.active {
    background: var(--cs-gold);
    animation: segPulse 2s infinite;
    box-shadow: 0 0 8px var(--cs-gold);
}
@keyframes segPulse { 0%,100% { opacity:1 } 50% { opacity:.4 } }
.phase-label {
    font-family: 'Space Mono', monospace;
    font-size: .68rem; color: var(--cs-gold);
    letter-spacing: 2px; margin-left: 6px; opacity: .8;
}

/* â”€â”€ CENTER MEDALLION â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.cs-medallion {
    position: absolute;
    left: 50%; transform: translateX(-50%);
    /* Protrudes above AND below the header */
    top: -18px;
    width: 124px; height: 124px;
    z-index: 20;
    /* Horizontal badge shape behind the circle */
    display: flex; align-items: center; justify-content: center;
}
/* The wide horizontal gold band */
.cs-medallion::before {
    content: '';
    position: absolute;
    left: 50%; transform: translateX(-50%);
    top: 50%; transform: translate(-50%, -50%);
    width: 220px; height: 44px;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(0,255,204,.12) 15%,
        rgba(0,255,204,.25) 50%,
        rgba(0,255,204,.12) 85%,
        transparent 100%
    );
    border-top: 1px solid rgba(0,255,204,.3);
    border-bottom: 1px solid rgba(0,255,204,.3);
    border-radius: 4px;
}
/* Corner decorations */
.cs-medallion::after {
    content: 'âœ¦';
    position: absolute;
    font-size: .9rem;
    color: var(--cs-gold);
    opacity: .5;
    animation: cornerSpin 8s linear infinite;
    top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(0deg) translateX(72px);
}
@keyframes cornerSpin {
    from { transform: translate(-50%,-50%) rotate(0deg) translateX(72px); }
    to   { transform: translate(-50%,-50%) rotate(360deg) translateX(72px); }
}
.cs-medal-img {
    width: 110px; height: 110px;
    object-fit: contain;
    position: relative; z-index: 2;
    filter:
        drop-shadow(0 0 18px rgba(0,255,204,.7))
        drop-shadow(0 0 6px rgba(0,170,255,.5));
    animation: medalFloat 4s ease-in-out infinite;
}
@keyframes medalFloat {
    0%,100% { transform: translateY(0);    filter: drop-shadow(0 0 18px rgba(0,255,204,.7)) drop-shadow(0 0 6px rgba(0,170,255,.5)); }
    50%      { transform: translateY(-4px); filter: drop-shadow(0 0 26px rgba(0,255,204,.9)) drop-shadow(0 0 10px rgba(0,170,255,.6)); }
}

/* â”€â”€ Right block: status + timer â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.cs-right {
    display: flex; align-items: center; gap: 18px;
    flex-shrink: 0;
}
.status-badge {
    font-family: 'Space Mono', monospace;
    font-size: .6rem; padding: 4px 12px;
    border-radius: 99px; letter-spacing: 2px;
    border: 1px solid rgba(0,255,204,.25);
    color: rgba(0,255,204,.5);
}
.status-badge.running {
    border-color: #2dc653; color: #2dc653;
    background: rgba(45,198,83,.08);
    box-shadow: 0 0 8px rgba(45,198,83,.2);
}
.clock-sm {
    font-family: 'Space Mono', monospace;
    font-size: .78rem; color: rgba(0,255,204,.4);
    margin-top: 2px;
}
.timer-big {
    font-family: 'Space Mono', monospace;
    font-size: 2.8rem; font-weight: 700;
    /* Teal gradient timer */
    background: linear-gradient(180deg, var(--cs-gold2) 0%, var(--cs-gold) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1; transition: all .5s;
    filter: drop-shadow(0 0 12px rgba(0,255,204,.5));
}
.timer-big.warn {
    background: linear-gradient(180deg, #fcd34d 0%, #f59e0b 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 0 12px rgba(245,158,11,.6));
}
.timer-big.danger {
    background: linear-gradient(180deg, #f87171 0%, #ef4444 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 0 16px rgba(239,68,68,.7));
    animation: tPulse .5s infinite;
}
@keyframes tPulse { 0%,100% { opacity:1 } 50% { opacity:.3 } }

/* â”€â”€ Teams grid â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.dashboard-main {
    display: grid;
    grid-template-columns: minmax(220px, 1.1fr) minmax(0, 4.6fr) minmax(220px, 1.1fr);
    gap: 16px;
    min-height: 0;
    padding-top: 10px;
}
.team-rail {
    display: grid;
    grid-template-rows: repeat(3, minmax(0, 1fr));
    gap: 16px;
    min-height: 0;
}
.center-stage {
    min-height: 0;
    display: flex;
}
.hero-board {
    position: relative;
    width: 100%;
    min-height: 0;
    border: 2px solid transparent;
    border-radius: 14px;
    background:
        linear-gradient(180deg, rgba(0,5,16,.96) 0%, rgba(0,3,10,.96) 100%);
    background-clip: padding-box;
    padding: 14px 14px 12px;
    box-shadow:
        inset 0 0 32px rgba(0,255,204,.15),
        0 0 28px rgba(0,255,204,.2);
    display: flex;
    flex-direction: column;
}
.hero-board::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 16px;
    z-index: -1;
    background: linear-gradient(45deg, var(--cs-gold), transparent, var(--cs-gold2), transparent, var(--cs-gold));
    background-size: 300% 300%;
    animation: tealBorderShift 4s linear infinite;
}
@keyframes tealBorderShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.hero-stage-label {
    font-family: 'Space Mono', monospace;
    font-size: .62rem;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: var(--cs-gold);
    margin-bottom: 10px;
    text-shadow: 0 0 8px rgba(0,255,204,.4);
}
.hero-media-stage {
    border: 1px solid rgba(0, 255, 204, .25);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #000;
    position: relative;
    cursor: grab;
}
.hero-media-stage {
    flex: 1;
    min-height: 0;
}
.hero-media-stage img,
.hero-media-stage video {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: #000;
}
.media-stage-empty {
    font-family: 'Space Mono', monospace;
    font-size: 1.2rem;
    color: rgba(255,255,255,.5);
}
.hero-quiz-panel {
    margin-top: 12px;
    border: 2px solid rgba(120, 214, 255, .88);
    border-radius: 10px;
    background:
        linear-gradient(180deg, rgba(235,241,245,.98) 0%, rgba(217,226,233,.95) 100%);
    color: #1195df;
    padding: 14px 18px 12px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
}
.hero-quiz-title {
    font-size: 1.15rem;
    font-weight: 800;
    margin-bottom: 10px;
    line-height: 1.25;
}
.hero-quiz-choices {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 14px;
    font-size: .94rem;
    font-weight: 700;
}
.hero-quiz-choice {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(17,149,223,.09);
    border: 1px solid rgba(17,149,223,.18);
    line-height: 1.2;
}
.hero-quiz-choice.empty {
    opacity: .7;
}
.right-stage {
    display: block;
    min-height: 0;
}
.right-stage .team-rail {
    height: 100%;
}

/* === CARD DESIGN === same language as header: dark + gold border */
.team-card {
    background:
        radial-gradient(circle at 50% 0%, rgba(0,30,60,.16) 0%, rgba(0,0,0,0) 48%),
        linear-gradient(180deg, rgba(0,5,12,.98) 0%, rgba(0,8,20,.96) 50%, rgba(0,5,14,.98) 100%);
    border-top: 2px solid rgba(0,255,204,.75);
    border-bottom: 2px solid rgba(0,255,204,.3);
    border-left: 1px solid var(--cs-border);
    border-right: 1px solid var(--cs-border);
    border-radius: 12px;
    padding: 14px 12px 12px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: border-color .4s, box-shadow .4s, transform .25s ease;
    backdrop-filter: blur(12px);
    box-shadow:
        0 0 20px rgba(0,255,204,.08),
        inset 0 1px 0 rgba(0,255,204,.16),
        inset 0 -1px 0 rgba(0,255,204,.08);
    min-height: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
.team-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(255,255,255,.04) 0%, rgba(255,255,255,0) 32%),
        linear-gradient(90deg, rgba(0,255,204,0) 0%, rgba(0,255,204,.08) 50%, rgba(0,255,204,0) 100%);
    pointer-events: none;
}
.team-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 18px;
    right: 18px;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0,255,204,.85), transparent);
    pointer-events: none;
}
.team-card:hover {
    transform: translateY(-2px);
}
.team-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    margin-bottom: 4px;
}
.team-logo-wrap {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 50% 35%, rgba(0,255,204,.18) 0%, rgba(0,0,0,0) 68%);
    box-shadow: inset 0 0 0 1px rgba(0,255,204,.14);
}
.team-copy {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
}
.team-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 8px;
    flex: 1;
}
.team-stat-box {
    background: rgba(0,255,204,.04);
    border: 1px solid rgba(0,255,204,.12);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px 4px 6px;
    position: relative;
}
.team-score-box {
    background: linear-gradient(180deg, rgba(121,17,17,.18) 0%, rgba(42,8,8,.1) 100%);
    border-color: rgba(0,255,204,.2);
}
.team-score-label,
.team-badge-label {
    font-family: 'Space Mono', monospace;
    font-size: .52rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(0,255,204,.52);
    position: absolute;
    top: 6px;
    width: 100%;
    text-align: center;
}
.t-score {
    font-family: 'Space Mono', monospace;
    font-size: 3.2rem; font-weight: 800;
    color: #ffd772;
    background: linear-gradient(180deg, var(--cs-gold2) 0%, var(--cs-gold) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: .9;
    transition: all .4s;
    display: block;
    text-shadow: 0 0 24px rgba(0,255,204,.28);
    margin-top: 10px;
}
.t-badge {
    margin-top: 14px;
    min-height: 40px;
    display: flex; align-items: center; justify-content: center;
}
.team-footer {
    margin-top: auto;
    padding-top: 6px;
}
.team-footer .t-online {
    margin-top: 0;
}
.team-footer .t-online-text {
    color: rgba(255,255,255,.72);
}
.team-footer .t-online-count {
    color: #2dc653;
    font-weight: 700;
}

/* â”€â”€ Score flash animation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@keyframes scoreFlash {
    0%   { transform: scale(1); }
    20%  { transform: scale(1.22); color: #fff;
           text-shadow: 0 0 20px var(--cs-gold2), 0 0 60px var(--cs-gold); }
    60%  { transform: scale(1.06); }
    100% { transform: scale(1); }
}
.score-pop { animation: scoreFlash .65s cubic-bezier(.36,.07,.19,.97); }

/* â”€â”€ Badge unlock â€” energetic spinâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@keyframes badgeBlast {
    0%   { transform: scale(0) rotate(-30deg);  opacity: 0; filter: brightness(5) blur(4px); }
    25%  { transform: scale(1.6) rotate(15deg); opacity: 1; filter: brightness(3) drop-shadow(0 0 30px gold); }
    45%  { transform: scale(.85) rotate(-8deg); filter: brightness(2) drop-shadow(0 0 18px gold); }
    65%  { transform: scale(1.15) rotate(4deg); }
    80%  { transform: scale(.96) rotate(-1deg); }
    100% { transform: scale(1)   rotate(0deg);  filter: drop-shadow(0 0 8px rgba(0,255,204,.7)); }
}
.badge-unlocked { animation: badgeBlast 1s cubic-bezier(.34,1.56,.64,1) forwards; }

/* â”€â”€ Card gold glow on score â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@keyframes cardGlow {
    0%,100% { box-shadow: none; border-color: rgba(0,255,204,.25); }
    40%     {
        box-shadow: 0 0 28px rgba(0,255,204,.5), 0 0 60px rgba(0,255,204,.2);
        border-color: rgba(0,255,204,.7);
    }
}
.score-glow { animation: cardGlow 1s ease; }

/* â•â• DOMINATION OVERLAY â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
/* Full-screen flash when a team gets points */
.dom-overlay {
    display: none;
    position: fixed; inset: 0; z-index: 800;
    align-items: center; justify-content: center;
    flex-direction: column; text-align: center;
    background: radial-gradient(ellipse at center,
        rgba(16,6,2,.97) 0%, rgba(0,0,0,.98) 100%
    );
    pointer-events: none;
}
.dom-overlay.show { display: flex; animation: domIn .22s ease; }
@keyframes domIn {
    from { opacity: 0; transform: scale(.92); }
    to   { opacity: 1; transform: scale(1); }
}
.dom-badge-img {
    width: 220px; height: 220px; display: flex; align-items: center; justify-content: center;
    animation: domBadgePop .5s cubic-bezier(.34,1.56,.64,1) both;
    filter: drop-shadow(0 0 40px rgba(0,255,204,.9));
}
.dom-badge-img img { width: 100%; height: 100%; object-fit: contain; }
.dom-badge-img span { font-size: 10rem; }
@keyframes domBadgePop {
    from { transform: scale(0) rotate(-15deg); opacity: 0; }
    to   { transform: scale(1.1) rotate(0deg);   opacity: 1; }
}
.dom-team-icon { font-size: 4rem; margin-bottom: 8px; }
.dom-name {
    font-family: 'Space Mono', monospace;
    font-size: 3.5rem; font-weight: 700; letter-spacing: 6px;
    background: linear-gradient(90deg, var(--cs-gold) 0%, var(--cs-gold2) 50%, var(--cs-gold) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    text-transform: uppercase;
    filter: drop-shadow(0 0 20px rgba(0,255,204,.8));
}
.dom-delta {
    font-family: 'Space Mono', monospace;
    font-size: 4rem; font-weight: 700; color: #2dc653;
    filter: drop-shadow(0 0 20px rgba(45,198,83,.8));
    animation: domDelta .6s .25s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes domDelta {
    from { transform: translateY(30px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.dom-label {
    font-family: 'Space Mono', monospace;
    font-size: .75rem; letter-spacing: 6px;
    color: rgba(0,255,204,.5);
    margin-top: 6px;
    animation: domDelta .5s .4s both;
}
/* Corner ornaments */
.dom-corner {
    position: absolute;
    font-size: 1.5rem; color: rgba(0,255,204,.35);
}
.dom-corner.tl { top: 20px; left: 24px; }
.dom-corner.tr { top: 20px; right: 24px; transform: scaleX(-1); }
.dom-corner.bl { bottom: 20px; left: 24px; transform: scaleY(-1); }
.dom-corner.br { bottom: 20px; right: 24px; transform: scale(-1); }

/* Badge domination (bigger flash for badge unlock) */
.dom-overlay.badge-dom .dom-delta { color: var(--cs-gold2); }
.dom-overlay.badge-dom { background: radial-gradient(ellipse at center, rgba(35,18,0,.97) 0%, rgba(0,0,0,.99) 100%); }

.t-icon-img { display: block; margin: 0 auto; width: 34px; height: 34px; object-fit: contain; filter: drop-shadow(0 0 12px rgba(0,255,204,.2)); }
.t-icon { font-size: 1.6rem; display: block; line-height: 1; color: var(--tc); text-shadow: 0 0 16px rgba(0,255,204,.16); }
.t-name { font-size: .96rem; font-weight: 900; letter-spacing: .8px; color: #fff; line-height: 1.02; text-transform: uppercase; }
.t-role {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 18px;
    padding: 1px 7px;
    border-radius: 999px;
    background: rgba(0,255,204,.08);
    border: 1px solid rgba(0,255,204,.12);
    font-size: .54rem;
    color: rgba(0,255,204,.68);
    font-family: 'Space Mono',monospace;
    letter-spacing: .8px;
}
.t-delta {
    display: none; position: absolute;
    top: 50%; right: 10px; transform: translateY(-50%);
    font-family: 'Space Mono', monospace; font-size: 1.15rem; font-weight: 700;
    animation: deltaUp .9s ease forwards; pointer-events: none;
}
@keyframes deltaUp {
    0%   { opacity: 1; transform: translateY(-50%); }
    100% { opacity: 0; transform: translateY(-140%); }
}
/* Badge image container â€” bigger + centered */
.t-badge img {
    width: 40px; height: 40px; object-fit: contain;
    filter: drop-shadow(0 0 15px rgba(0,255,204,.48));
    transition: filter .5s, transform .3s;
}
.t-badge span {
    font-size: 1.8rem; filter: drop-shadow(0 0 12px var(--cs-gold));
}
.t-badge-nm {
    font-family: 'Space Mono', monospace; font-size: .5rem; font-weight: bold;
    color: var(--cs-gold); margin-top: 2px;
    letter-spacing: .9px; text-transform: uppercase;
    line-height: 1.25;
}
.t-online {
    font-family: 'Space Mono', monospace; font-size: .56rem;
    color: rgba(255,255,255,.32); margin-top: 4px;
    display: flex; align-items: center; gap: 4px; justify-content: center;
}
.dot-on { width: 6px; height: 6px; border-radius: 50%; background: #2dc653; display: inline-block; animation: dotPulse 2s infinite; box-shadow: 0 0 8px rgba(45,198,83,.65); }
@keyframes dotPulse { 0%,100% { opacity:1 } 50% { opacity:.25 } }

/* â”€â”€ Bottom widgets â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.widgets-row { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr 1fr; gap: 10px; }
@media (max-width: 1400px) { .widgets-row { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 980px) { .widgets-row { grid-template-columns: 1fr; } }
@media (max-width: 1320px) {
    .dashboard-main {
        grid-template-columns: minmax(180px, .9fr) minmax(0, 3.8fr) minmax(210px, 1.1fr);
    }
}
@media (max-width: 1160px) {
    .dashboard-main {
        grid-template-columns: 1fr;
    }
    .team-rail {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        grid-template-rows: none;
    }
}

.widget {
    background: rgba(13,27,46,.8);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 10px; padding: 12px 14px; overflow: hidden;
}
.widget-hdr {
    font-family: 'Space Mono', monospace;
    font-size: .65rem; letter-spacing: 3px; color: rgba(255,255,255,.4);
    text-transform: uppercase; margin-bottom: 8px;
    padding-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,.06);
    display: flex; align-items: center; gap: 6px;
}
.widget-hdr i { color: var(--bs-theme); font-size: .8rem; }

.feed-list { display: flex; flex-direction: column; gap: 4px; max-height: 160px; overflow-y: auto; }
.feed-item {
    padding: 6px 9px; border-radius: 5px;
    border-left: 3px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.03);
    font-size: .72rem; line-height: 1.4;
}
.feed-item.info    { border-color: var(--bs-theme); }
.feed-item.warn    { border-color: #f59e0b; }
.feed-item.alert   { border-color: #ef4444; background: rgba(239,68,68,.06); }
.feed-item.success { border-color: #2dc653; }
.fi-ts { font-family: 'Space Mono', monospace; font-size: .6rem; color: rgba(255,255,255,.3); margin-bottom: 2px; }

/* Vote bars */
.vote-bars { display: flex; flex-direction: column; gap: 7px; }
.vote-bar-row { display: flex; align-items: center; gap: 8px; }
.vb-lbl { display:flex; align-items:flex-start; gap:8px; min-width:0; max-width:none; flex:1; }
.vb-key { font-family: 'Space Mono', monospace; font-size: 1.1rem; font-weight: 700; min-width:26px; text-align:center; }
.vb-text { font-size: .78rem; color: rgba(255,255,255,.9); white-space: normal; overflow: visible; text-overflow: unset; word-break: break-word; }
.vb-track {
    flex: 1; height: 18px;
    background: rgba(255,255,255,.07); border-radius: 4px; overflow: hidden;
}
.vb-fill {
    height: 100%; border-radius: 4px;
    transition: width .5s ease;
    background: var(--bs-theme);
}
.vb-count { font-family: 'Space Mono', monospace; font-size: .75rem; min-width:64px; text-align: right; }
.vote-q {
    font-family: 'Space Mono', monospace;
    font-size: .78rem;
    color: rgba(255,255,255,.78);
    margin-bottom: 8px;
    line-height: 1.35;
    white-space: normal;
    word-break: break-word;
}

/* â•â• ATMOSPHERE SYSTEM â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
/* Each mode tints the whole dashboard */
body { transition: background 2.5s ease; }

/* âœ” CALM (default) â€” gold/dark */
body.atmo-calm {
    --atmo-accent: var(--cs-gold);
    --atmo-card-border: rgba(0,255,204,.25);
}
/* âš  TENSION â€” amber amber */
body.atmo-tension {
    --atmo-accent: #f59e0b;
    --atmo-card-border: rgba(245,158,11,.3);
    background: radial-gradient(ellipse at 50% 0%, rgba(40,20,0,.8) 0%, rgba(5,3,0,.98) 100%) !important;
}
body.atmo-tension .cs-header {
    border-top-color: #f59e0b !important;
    box-shadow: 0 0 60px rgba(245,158,11,.15), inset 0 1px 0 rgba(245,158,11,.2) !important;
}
body.atmo-tension .team-card {
    border-color: rgba(245,158,11,.2);
}

/* ðŸš¨ CRISIS â€” deep red pulsing */
body.atmo-crisis {
    --atmo-accent: #ef4444;
    --atmo-card-border: rgba(239,68,68,.35);
    background: radial-gradient(ellipse at 50% 0%, rgba(60,6,6,.85) 0%, rgba(8,2,2,.99) 100%) !important;
    animation: crisisBodyPulse 2.5s ease-in-out infinite;
}
@keyframes crisisBodyPulse {
    0%,100% { filter: brightness(1); }
    50%      { filter: brightness(1.04) saturate(1.3); }
}
body.atmo-crisis .cs-header {
    border-top-color: #ef4444 !important;
    border-bottom-color: rgba(239,68,68,.5) !important;
    box-shadow: 0 0 80px rgba(239,68,68,.2), inset 0 1px 0 rgba(239,68,68,.25) !important;
    animation: crisisHeaderPulse 2s ease-in-out infinite;
}
@keyframes crisisHeaderPulse {
    0%,100% { box-shadow: 0 0 80px rgba(239,68,68,.2), inset 0 1px 0 rgba(239,68,68,.25); }
    50%      { box-shadow: 0 0 120px rgba(239,68,68,.35), inset 0 1px 0 rgba(239,68,68,.4); }
}
body.atmo-crisis .t-score {
    background: linear-gradient(180deg, #f87171 0%, #ef4444 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
body.atmo-crisis .team-card {
    border-color: rgba(239,68,68,.3);
    animation: crisisCardPulse 3s ease-in-out infinite;
}
body.atmo-crisis .logo-txt {
    background: linear-gradient(90deg, #ef4444 0%, #f87171 50%, #ef4444 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
@keyframes crisisCardPulse {
    0%,100% { box-shadow: none; }
    50%      { box-shadow: inset 0 0 20px rgba(239,68,68,.08), 0 0 10px rgba(239,68,68,.1); }
}
body.atmo-crisis.scanlines::after {
    content: '';
    position: fixed; inset: 0;
    background: repeating-linear-gradient(0deg,
        rgba(239,68,68,.05) 0px, rgba(239,68,68,.05) 1px,
        transparent 1px, transparent 4px
    );
    pointer-events: none; z-index: 999;
    animation: scanMove 8s linear infinite;
}
@keyframes scanMove { from { background-position: 0 0; } to { background-position: 0 100px; } }

/* ðŸ‘¾ HACKED â€” glitch green matrix */
body.atmo-hacked {
    --atmo-accent: #00ff88;
    --atmo-card-border: rgba(0,255,136,.25);
    background: radial-gradient(ellipse at 50% 50%, rgba(0,30,12,.95) 0%, rgba(0,4,2,.99) 100%) !important;
}
body.atmo-hacked .cs-header {
    border-top-color: #00ff88 !important;
    box-shadow: 0 0 60px rgba(0,255,136,.15) !important;
    animation: glitchHeader .1s steps(1) infinite;
}
@keyframes glitchHeader {
    0%,100% { transform: none; }
    33%     { transform: translateX(-1px); }
    66%     { transform: translateX(1px); }
}
body.atmo-hacked .t-score {
    background: linear-gradient(180deg, #00ff88 0%, #00cc66 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
body.atmo-hacked .team-card { border-color: rgba(0,255,136,.2); }
body.atmo-hacked.scanlines::after {
    content: '';
    position: fixed; inset: 0;
    background: repeating-linear-gradient(0deg,
        rgba(0,255,136,.04) 0px, rgba(0,255,136,.04) 1px,
        transparent 1px, transparent 3px
    );
    pointer-events: none; z-index: 999;
    animation: scanMove 4s linear infinite;
}

/* ðŸ† VICTORY â€” all gold everything */
body.atmo-victory {
    background: radial-gradient(ellipse at 50% 0%, rgba(50,30,0,.9) 0%, rgba(8,5,0,.98) 100%) !important;
    animation: victoryPulse 3s ease-in-out infinite;
}
@keyframes victoryPulse {
    0%,100% { filter: brightness(1); }
    50%      { filter: brightness(1.08) saturate(1.2); }
}
body.atmo-victory .cs-header {
    border-top-color: var(--cs-gold2) !important;
    box-shadow: 0 0 80px rgba(0,255,204,.3), inset 0 1px 0 rgba(240,192,96,.4) !important;
}
body.atmo-victory .team-card { border-color: rgba(0,255,204,.4); }

/* ðŸŒ NEUTRAL */
body.atmo-neutral { }

/* â”€â”€ Screen shake (crisis/hacked alert) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@keyframes screenShake {
    0%,100% { transform: none; }
    10%     { transform: translate(-3px,-2px) rotate(-.3deg); }
    20%     { transform: translate(3px, 2px) rotate(.3deg); }
    30%     { transform: translate(-2px, 3px); }
    40%     { transform: translate(2px,-3px) rotate(-.2deg); }
    50%     { transform: translate(-1px, 1px); }
    60%     { transform: translate(1px,-1px) rotate(.15deg); }
    70%     { transform: translate(-2px, 2px); }
    80%     { transform: translate(2px,-2px); }
    90%     { transform: translate(-1px,-1px) rotate(.1deg); }
}
.screen-shake { animation: screenShake .55s ease; }
.phantom-ov {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.95); z-index: 900;
    align-items: center; justify-content: center; flex-direction: column;
    text-align: center; padding: 24px;
}
.phantom-ov.show { display: flex; animation: phIn .4s ease; }
@keyframes phIn { from { opacity: 0 } to { opacity: 1 } }
.ph-label { font-family: 'Space Mono', monospace; font-size: .7rem; letter-spacing: 6px; color: rgba(239,68,68,.5); margin-bottom: 12px; }
.ph-skull { font-size: 7rem; animation: bob 2s infinite; }
@keyframes bob { 0%,100% { transform: scale(1) } 50% { transform: scale(1.06) } }
.ph-msg {
    font-family: 'Space Mono', monospace;
    font-size: 1.6rem; color: #ef4444; max-width: 680px;
    line-height: 1.5; text-shadow: 0 0 30px rgba(239,68,68,.5);
    position: relative;
}
.ph-msg::before, .ph-msg::after {
    content: attr(data-txt); position: absolute; top: 0; left: 0; right: 0;
}
.ph-msg::before { color: var(--bs-theme); clip-path: polygon(0 0,100% 0,100% 33%,0 33%); transform: translate(-3px,0); animation: gA .8s infinite; }
.ph-msg::after  { color: #ef4444; clip-path: polygon(0 67%,100% 67%,100% 100%,0 100%); transform: translate(3px,0); animation: gB .8s infinite; }
@keyframes gA { 0%,100% { transform: translate(-3px,0) } 50% { transform: translate(3px,-1px) } }
@keyframes gB { 0%,100% { transform: translate(3px,0) } 50% { transform: translate(-3px,1px) } }
.ph-dismiss { font-family: 'Space Mono', monospace; font-size: .65rem; color: rgba(239,68,68,.35); letter-spacing: 4px; margin-top: 22px; animation: blink 2s infinite; }
@keyframes blink { 0%,100% { opacity:.3 } 50% { opacity:1 } }

/* â”€â”€ ENDGAME overlay â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.endgame-ov {
    display: none; position: fixed; inset: 0;
    background: radial-gradient(circle at 50% 40%, rgba(10, 25, 40, 0.98) 0%, rgba(0, 0, 0, 1) 100%);
    z-index: 950;
    align-items: center; justify-content: center; flex-direction: column;
}
body.atmo-crisis .endgame-ov { background: radial-gradient(circle at 50% 40%, rgba(40, 10, 10, 0.98) 0%, rgba(0, 0, 0, 1) 100%); }
.endgame-ov.show { display: flex; animation: endIn .8s cubic-bezier(0.2, 0.8, 0.2, 1); }
@keyframes endIn { from { opacity: 0; transform: scale(.9) } to { opacity: 1; transform: scale(1) } }
.eg-label { font-family: 'Space Mono', monospace; font-size: 1rem; letter-spacing: 12px; color: #f59e0b; margin-bottom: 10px; text-transform: uppercase; animation: floatLabel 3s ease-in-out infinite; }
@keyframes floatLabel { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
body.atmo-crisis .eg-label { color: #ef4444; }
.eg-title { font-size: 4rem; font-weight: 900; margin-bottom: 60px; color: #fff; text-shadow: 0 0 30px rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 2px; }
body.atmo-crisis .eg-title { color: #ef4444; text-shadow: 0 0 40px rgba(239,68,68,.6); }

.podium { display: flex; align-items: flex-end; gap: 24px; justify-content: center; margin-bottom: 20px; }
.podium-slot { text-align: center; width: 240px; display: flex; flex-direction: column; align-items: center; }
.pod-bar {
    width: 100%;
    border-radius: 16px 16px 0 0;
    display: flex; align-items: center; justify-content: flex-start; flex-direction: column;
    padding: 24px 14px 14px;
    backdrop-filter: blur(10px);
    position: relative;
    overflow: visible;
    box-shadow: 0 -10px 40px rgba(0,0,0,0.5);
}
.pod-bar::before { content:''; position:absolute; inset:0; border-radius: 16px 16px 0 0; background: linear-gradient(180deg, rgba(255,255,255,0.1), transparent); pointer-events:none; }

.p1 { z-index: 3; animation: floatWinner 4s ease-in-out infinite; }
@keyframes floatWinner { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.p1 .pod-bar { border: 2px solid #fbbf24; background: linear-gradient(180deg, rgba(245,158,11,.4) 0%, rgba(245,158,11,.05) 100%); min-height: 420px; box-shadow: 0 0 60px rgba(245,158,11,.3); }
.p2 { z-index: 2; animation: floatWinner 4s ease-in-out infinite 1s; }
.p2 .pod-bar { border: 2px solid #94a3b8; background: linear-gradient(180deg, rgba(148,163,184,.3) 0%, rgba(148,163,184,.05) 100%); min-height: 330px; box-shadow: 0 0 40px rgba(148,163,184,.2); }
.p3 { z-index: 1; animation: floatWinner 4s ease-in-out infinite 2s; }
.p3 .pod-bar { border: 2px solid #b45309; background: linear-gradient(180deg, rgba(180,83,9,.3) 0%, rgba(180,83,9,.05) 100%); min-height: 270px; box-shadow: 0 0 40px rgba(180,83,9,.2); }

.pod-icon { font-size: 3.5rem; margin-bottom: 10px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5)); }
.pod-icon img { width: 80px !important; height: 80px !important; object-fit: contain; }
.pod-name {
    font-size: 1.3rem; font-weight: 900; margin-bottom: 6px;
    letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.8);
    word-break: break-word; white-space: normal; line-height: 1.2;
    width: 100%; text-align: center;
}
.pod-score { font-family: 'Space Mono', monospace; font-size: 2.8rem; color: #fff; font-weight: 700; text-shadow: 0 0 20px rgba(255,255,255,0.5); margin-bottom: 12px; }
.p2 .pod-score, .p3 .pod-score { font-size: 2.2rem; }
.pod-badge { margin-top: auto; padding-top: 8px; }
.pod-badge img { width: 110px !important; height: 110px !important; object-fit: contain; filter: drop-shadow(0 0 20px rgba(255,255,255,0.3)) !important; transition: transform 0.3s; }
.p1 .pod-badge img { width: 140px !important; height: 140px !important; object-fit: contain; filter: drop-shadow(0 0 30px rgba(245,158,11,0.6)) !important; animation: pulseBadge 2s infinite; }
@keyframes pulseBadge { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }

.pod-base {
    width: 100%;
    background: rgba(255,255,255,.1); padding: 14px 10px;
    border-radius: 0 0 16px 16px;
    font-family: 'Space Mono', monospace; font-size: 1.2rem; color: #fff; font-weight: 800; letter-spacing: 2px;
    box-shadow: inset 0 2px 0 rgba(255,255,255,0.2);
}

.others-row { display: flex; gap: 20px; margin-top: 40px; justify-content: center; flex-wrap: wrap; max-width: 1000px; }
.other-tile {
    text-align: center; padding: 18px 28px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 12px;
    backdrop-filter: blur(5px);
    transition: transform 0.2s, background 0.2s;
    min-width: 180px;
}
.other-tile:hover { transform: translateY(-3px); background: rgba(255,255,255,.1); }
.ot-rank { font-family: 'Space Mono', monospace; font-size: 0.9rem; color: rgba(255,255,255,.5); letter-spacing: 1px; margin-bottom: 8px; }
.ot-name { font-size: 1.3rem; font-weight: 700; margin-bottom: 5px; }
.ot-score { font-family: 'Space Mono', monospace; font-size: 1.6rem; color: var(--bs-theme); font-weight: bold; }

#confettiCanvas { position: fixed; inset: 0; pointer-events: none; z-index: 960; }

::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); }

/* ── Neptune Strike Theme Overrides ────────────────────── */
body {
    --cs-gold:    #00ffcc;
    --cs-gold2:   #00ffcc;
    --cs-red:     #00aaff;
    --cs-red2:    #00aaff;
    --cs-dark:    #000c14;
    --cs-dark2:   rgba(0,12,20,.92);
    --cs-border:  rgba(0,255,204,.25);
    --cs-glow-g:  rgba(0,255,204,.35);
    --cs-glow-r:  rgba(0,170,255,.45);
}
body.scenario-neptune_strike .logo-txt .shield-word {
    background: linear-gradient(90deg, var(--cs-red) 0%, #33ccff 50%, var(--cs-red) 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
}
body.scenario-neptune_strike .team-card {
    background:
        radial-gradient(circle at 50% 0%, rgba(0,255,204,.1) 0%, rgba(0,0,0,0) 48%),
        linear-gradient(180deg, rgba(0,8,16,.98) 0%, rgba(0,16,32,.96) 50%, rgba(0,8,16,.98) 100%) !important;
}
body.scenario-neptune_strike .widget {
    background: rgba(0,15,30,.8) !important;
}
body.scenario-neptune_strike .hero-board {
    background: linear-gradient(180deg, rgba(0,10,20,.96) 0%, rgba(0,5,10,.96) 100%) !important;
    box-shadow:
        inset 0 0 32px rgba(0,255,204,.15),
        0 0 28px rgba(0,255,204,.2) !important;
}
.team-score-box {
    background: linear-gradient(180deg, rgba(0,170,255,.12) 0%, rgba(0,40,80,.05) 100%) !important;
}
.cs-medallion::before {
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(0,255,204,.12) 15%,
        rgba(0,255,204,.25) 50%,
        rgba(0,255,204,.12) 85%,
        transparent 100%
    ) !important;
    border-top: 1px solid rgba(0,255,204,.3) !important;
    border-bottom: 1px solid rgba(0,255,204,.3) !important;
}
.cs-header {
    background: linear-gradient(90deg,
        rgba(0,8,16,.98) 0%,
        rgba(0,16,32,.97) 30%,
        rgba(0,24,48,.97) 50%,
        rgba(0,16,32,.97) 70%,
        rgba(0,8,16,.98) 100%
    ) !important;
    box-shadow:
        0 0 40px rgba(0,255,204,.2),
        inset 0 1px 0 rgba(0,255,204,.15),
        inset 0 -1px 0 rgba(0,170,255,.2) !important;
}
/* --- Scene Info Modal Overlay --- */
.scene-info-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 8, 16, 0.85);
    backdrop-filter: blur(8px);
    z-index: 15;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}
.scene-info-overlay.show {
    opacity: 1;
    pointer-events: auto;
}
.scene-info-box {
    background: rgba(0, 16, 32, 0.95);
    border: 2px solid var(--cs-gold);
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(0, 255, 204, 0.3);
    max-width: 600px;
    width: 100%;
    padding: 24px;
    position: relative;
    font-family: 'Share Tech Mono', monospace;
    color: rgba(255, 255, 255, 0.9);
}
.scene-info-box .close-btn {
    position: absolute;
    top: 12px;
    right: 16px;
    background: transparent;
    border: none;
    color: var(--cs-gold);
    font-size: 1.5rem;
    cursor: pointer;
    line-height: 1;
    transition: transform 0.2s, color 0.2s;
}
.scene-info-box .close-btn:hover {
    color: #fff;
    transform: scale(1.2);
}
.scene-info-title {
    font-family: 'Orbitron', sans-serif;
    font-weight: 700;
    font-size: 1.4rem;
    color: var(--cs-gold);
    border-bottom: 1px solid rgba(0, 255, 204, 0.3);
    padding-bottom: 8px;
    margin-bottom: 16px;
    letter-spacing: 1px;
}
.scene-info-sub {
    font-size: 0.9rem;
    color: var(--cs-red);
    margin-bottom: 16px;
    letter-spacing: 1px;
}
.scene-info-desc {
    font-family: 'Space Mono', monospace;
    font-size: 0.85rem;
    line-height: 1.6;
    margin-bottom: 20px;
}
.scene-info-footer {
    text-align: right;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.4);
}
.info-hint {
    position: absolute;
    bottom: 12px;
    right: 12px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 10px;
    color: rgba(0, 255, 204, 0.55);
    background: rgba(0, 8, 16, 0.7);
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid rgba(0, 255, 204, 0.25);
    pointer-events: none;
    z-index: 5;
    animation: blinkGlow 2s infinite ease-in-out;
}
@keyframes blinkGlow {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 0.9; }
}
</style>
</head>
<body>
<div class="app-cover"></div>

{{-- PHANTOM overlay --}}
<div class="phantom-ov" id="phantomOv" onclick="dismissPhantom()">
    <div class="ph-label">INTERCEPTED MESSAGE — {{ $scenario['attacker_name'] ?? 'PHANTOM GRID' }}</div>
    <div class="ph-skull">{{ $scenario['attacker_icon'] ?? '☠️' }}</div>
    <div class="ph-msg" id="phMsg" data-txt=""></div>
    <div class="ph-dismiss">CLICK TO CLOSE</div>
</div>

{{-- ENDGAME overlay --}}
<div class="endgame-ov" id="endgameOv">
    <canvas id="confettiCanvas"></canvas>
    <div class="eg-label">END OF EXERCISE — {{ $scenario['attacker_name'] ?? 'PHANTOM GRID' }}</div>
    <div class="eg-title">{{ $scenario['title'] ?? 'NEPTUNE STRIKE' }}</div>
    <div class="podium" id="podiumEl"></div>
    <div class="others-row" id="othersEl"></div>
</div>

{{-- DOMINATION OVERLAY (score/badge flash) --}}
<div class="dom-overlay" id="domOverlay">
    <span class="dom-corner tl">❖</span>
    <span class="dom-corner tr">❖</span>
    <span class="dom-corner bl">❖</span>
    <span class="dom-corner br">❖</span>
    <div class="dom-badge-img" id="domBadge" style="display:none"></div>
    <div class="dom-team-icon" id="domIcon"></div>
    <div class="dom-name" id="domName"></div>
    <div class="dom-delta" id="domDelta"></div>
    <div class="dom-label" id="domLabel">POINTS AWARDED</div>
</div>

{{-- MAIN LAYOUT --}}
<div class="cs-layout">

    {{-- ══ HEADER ══ --}}
    <div class="cs-header">

        {{-- LEFT: Title + Phase bar --}}
        <div class="cs-left">
            <div class="logo-txt">
                NEPTUNE <span class="shield-word">STRIKE</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="phase-bar">
                    @foreach($scenario['phases'] as $p)
                    <div class="ph-seg" id="ph-seg-{{ $p['index'] }}"></div>
                    @endforeach
                    <span class="phase-label" id="phaseLabel">{{ $scenario['phases'][0]['name'] ?? '—' }}</span>
                </div>
            </div>
            <div class="scenario-sub">{{ $scenario['title'] ?? '' }} &mdash; {{ $session->name }}</div>
        </div>

        {{-- CENTER: Protruding game medallion (absolute positioned) --}}
        <div class="cs-medallion">
            <div class="cs-medal-img d-flex align-items-center justify-content-center" style="width:110px;height:110px;border-radius:50%;background:rgba(0,255,204,0.1);border:2px solid #00ffcc;box-shadow:0 0 20px rgba(0,255,204,0.4)">
                    <span style="font-size:3.5rem;color:#00ffcc;text-shadow:0 0 15px #00ffcc">⚓</span>
                </div>
        </div>

        {{-- RIGHT: Status + clock + big timer --}}
        <div class="cs-right">
            <div class="d-flex flex-column align-items-end gap-1">
                <div class="status-badge" id="statusBadge">AWAITING</div>
                <div class="clock-sm" id="clockSm">--:--:--</div>
            </div>
            <div class="timer-big" id="mainTimer">--:--</div>
        </div>

    </div>

    {{-- MAIN STAGE --}}
    <div class="dashboard-main">
        <div class="team-rail" id="leftTeamRail"></div>

        <div class="center-stage">
            <div class="hero-board">
                <div class="hero-stage-label">Phase Briefing</div>
                <div class="hero-media-stage" id="mainMediaStage">
                    <div class="media-stage-empty">MEDIA</div>
                </div>
                <div class="hero-quiz-panel">
                    <div class="hero-quiz-title" id="mainQuizQuestion">Quiz question</div>
                    <div class="hero-quiz-choices" id="mainQuizChoices">
                        <span class="hero-quiz-choice empty">Answers will appear here</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="right-stage">
            <div class="team-rail" id="rightTeamRail"></div>
        </div>
    </div>

    {{-- BOTTOM WIDGETS --}}
    <div class="widgets-row">
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-megaphone"></i>Announcements</div>
            <div class="feed-list" id="announceLog"></div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-hand-thumbs-up"></i>Strategic Vote</div>
            <div id="voteWidget">
                <div class="vote-q fst-italic">No vote in progress</div>
            </div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-patch-question"></i>Quiz Question</div>
            <div id="quizWidget">
                <div class="vote-q fst-italic">No question in progress</div>
            </div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-lightning-charge"></i>Active Injects</div>
            <div class="feed-list" id="injectLog"></div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-collection-play"></i>Phase Media & Quiz</div>
            <div class="feed-list" id="phaseMediaQuiz"></div>
        </div>
    </div>

</div>

{{-- HUD JS (theme, Bootstrap) --}}
<script src="{{ asset('hud/js/vendor.min.js') }}"></script>
<script src="{{ asset('hud/js/app.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<script>
const SESSION_CODE = '{{ $session->code }}';
const TOTAL_PHASES = {{ count($scenario['phases']) }};
const SCENARIO_KEY = '{{ $scenario['key'] }}';
const IS_EN = true;
const USE_THREE = {{ var_export($useThree, true) }};
document.body.classList.add('scenario-' + SCENARIO_KEY);

let lastBcId = 0, lastInjectId = 0, lastAtmo = '', endgameFired = false;
let prevScores = {};
let latestSessionPhaseIdx = null;

// ── Three.js Cinematic Engine ─────────────────────────────────────────────
let threeRenderer = null, threeScene = null, threeCamera = null;
let threeAnimId = null, threeContainer = null;
let currentThreeScene = null;
let threeSceneObjects = {}; // holds objects per scene for cleanup
let mouseX = 0, mouseY = 0;
let threeW = 800, threeH = 450;

const HUD_BY_SCENE = {
  ocean:  {lat:"43°17'N",lon:"005°22'E",time:'06:42:00',vtms:'NOMINAL',scada:'NOMINAL',ais:'ACTIVE',threat:'LOW',apt:'MONITORING',marsec:'BRAVO',pct:5},
  port:   {lat:"43°18'N",lon:"005°21'E",time:'06:42:33',vtms:'OFFLINE',scada:'COMPROMISED',ais:'DISRUPTED',threat:'CRITICAL',apt:'73% MATCH',marsec:'CHARLIE',pct:80},
  cable:  {lat:"43°09'N",lon:"005°55'E",time:'07:17:12',vtms:'DEGRADED',scada:'NOMINAL',ais:'DARK',threat:'HIGH',apt:'APT-POSEIDON',marsec:'CHARLIE',pct:50},
  hack:   {lat:'--',lon:'--',time:'07:57:44',vtms:'OFFLINE',scada:'COMPROMISED',ais:'SPOOFED',threat:'EXTREME',apt:'CONFIRMED',marsec:'DELTA',pct:98},
  command:{lat:"48°52'N",lon:"002°21'E",time:'09:12:00',vtms:'RESTORING',scada:'ISOLATED',ais:'MONITORED',threat:'MEDIUM',apt:'ATTRIBUTED',marsec:'CHARLIE',pct:45}
};

const G = { cinScene: 'ocean', sceneIdx: 0, frame: 0, t: 0 };

function setScene(sc) {
  G.cinScene = sc;
  const el = document.getElementById('scene-title');
  if (el) {
    el.classList.remove('on');
    const scLabels = { ocean:'PHASE I · INITIAL DETECTION', port:'PHASE I-II · ATTACK ACTIVE', cable:'PHASE II · HYBRID THREAT', hack:'PHASE III · ESCALATION', command:'PHASE IV · STRATEGIC RESPONSE' };
    const scSubs = { ocean:'JUNE 9 2026 · 06:42 LOCAL · SITUATION NOMINALE', port:'T+00:00 · SYSTEM FAILURE ACTIVE', cable:'MV SILVER HORIZON · ROV DETECTED', hack:'T+01:15 · MULTI-VECTOR ATTACK ACTIVE', command:'CRISIS COORDINATION CELL ACTIVATED' };
    const stPh = document.getElementById('st-ph');
    const stH = document.getElementById('st-h');
    const stS = document.getElementById('st-s');
    if (stPh) stPh.textContent = scLabels[sc] || '';
    if (stH) stH.textContent = sc.toUpperCase();
    if (stS) stS.textContent = scSubs[sc] || '';
    setTimeout(() => { el.classList.add('on'); setTimeout(() => el.classList.remove('on'), 3500); }, 50);
  }
  if (threeRenderer) buildThreeScene(sc);
  updateHUD();
}

function updateHUD() {
  const d = HUD_BY_SCENE[G.cinScene];
  if(!d) return;
  const setH=(id,v)=>{const el=document.getElementById(id);if(el)el.textContent=v;};
  setH('h-lat',d.lat); setH('h-lon',d.lon); setH('h-time',d.time); setH('h-vtms',d.vtms);
  setH('h-scada',d.scada); setH('h-ais',d.ais); setH('h-threat',d.threat); setH('h-apt',d.apt);
  setH('h-marsec',d.marsec);
  const el=document.getElementById('ts-fill');if(el)el.style.width=d.pct+'%';
}

// ── 2D Canvas Fallback Scene Engine ─────────────────────────────────────────
let bgCv = null, mainCv = null, bgCtx = null, ctx = null, W = 800, H = 450;
const stars = Array.from({length:60},()=>({x:Math.random(),y:Math.random()*.44,r:Math.random()*.85+.2,a:Math.random()*.7+.3,t:Math.random()*6.28}));
const ships = [
  {name:'MV OLYMPIA',x:.28,y:.62,spd:.00008,dir:1,col:'#1a4060',alert:false,lbl:'IMO CLASS 3',type:'cargo'},
  {name:'MV ADRIATIC STAR',x:.63,y:.55,spd:.00006,dir:-1,col:'#1a3050',alert:true,lbl:'80,000T CRUDE',type:'tanker'},
  {name:'MV SILVER HORIZON',x:.52,y:.71,spd:.00005,dir:1,col:'#102030',alert:false,lbl:'AIS DARK',type:'susp'},
];
const cables=[
  {pts:[[.05,.82],[.2,.76],[.4,.8],[.6,.74],[.8,.78],[.95,.81]],name:'SEA-ME-WE 5'},
  {pts:[[.1,.86],[.3,.84],[.5,.88],[.7,.85],[.9,.83]],name:'MEDEX-3'},
];
const cmdNodes=[
  {lbl:'ANSSI\nCERT',x:.5,y:.38,col:'#00ffcc',ring:true},
  {lbl:'MARINE\nNATIONALE',x:.2,y:.56,col:'#00aaff'},
  {lbl:'PORT\nMARSEILLE',x:.78,y:.56,col:'#ffaa00'},
  {lbl:'SGDSN',x:.35,y:.73,col:'#ff6688'},
  {lbl:'EUNAVFOR\nNATO',x:.65,y:.73,col:'#aa88ff'},
  {lbl:'ENISA',x:.12,y:.38,col:'#44ddaa'},
  {lbl:'IMO',x:.88,y:.38,col:'#ffdd44'},
];
const cmdLinks=[[0,1],[0,2],[0,3],[0,4],[0,5],[0,6],[1,3],[2,4],[5,6]];

function initCanvas() {
  bgCv = document.getElementById('bg-cv');
  mainCv = document.getElementById('main-cv');
  const container = document.getElementById('neptuneCanvasContainer');
  if (!bgCv || !mainCv || !container) return;
  
  W = container.clientWidth || 800;
  H = container.clientHeight || 450;
  bgCv.width = mainCv.width = W;
  bgCv.height = mainCv.height = H;
  
  bgCtx = bgCv.getContext('2d');
  ctx = mainCv.getContext('2d');
  
  const resizeObserver = new ResizeObserver(entries => {
    for (let entry of entries) {
      W = entry.contentRect.width;
      H = entry.contentRect.height;
      if (bgCv && mainCv) {
        bgCv.width = mainCv.width = W;
        bgCv.height = mainCv.height = H;
      }
    }
  });
  resizeObserver.observe(container);
}

function renderLoop() {
  if (!animationLoopRunning) return;
  G.t += .016; G.frame++;
  drawBG(); drawScene(); updateHUD();
  requestAnimationFrame(renderLoop);
}

function drawBG() {
  if (!bgCtx) return;
  bgCtx.clearRect(0,0,W,H);
  const sc = G.cinScene;
  if(sc==='ocean'||sc==='port'||sc==='cable') {
    const sh = H*.42;
    const sg = bgCtx.createLinearGradient(0,0,0,sh);
    sg.addColorStop(0,'#000810'); sg.addColorStop(.5,'#001525'); sg.addColorStop(1,'#002035');
    bgCtx.fillStyle=sg; bgCtx.fillRect(0,0,W,sh);
    stars.forEach(s=>{s.t+=.007;const a=s.a*(.7+.3*Math.sin(s.t));bgCtx.beginPath();bgCtx.arc(s.x*W,s.y*sh,s.r,0,Math.PI*2);bgCtx.fillStyle=`rgba(255,255,255,${a})`;bgCtx.fill();});
    bgCtx.beginPath();bgCtx.arc(W*.86,sh*.22,10,0,Math.PI*2);bgCtx.fillStyle='rgba(215,225,255,.78)';bgCtx.fill();
    bgCtx.beginPath();bgCtx.arc(W*.86+4,sh*.22-2,9,0,Math.PI*2);bgCtx.fillStyle='#000810';bgCtx.fill();
    const hg=bgCtx.createLinearGradient(0,sh-10,0,sh+15);hg.addColorStop(0,'rgba(0,180,120,.07)');hg.addColorStop(1,'transparent');bgCtx.fillStyle=hg;bgCtx.fillRect(0,sh-10,W,25);
    const seaG=bgCtx.createLinearGradient(0,sh,0,H);seaG.addColorStop(0,'#002d50');seaG.addColorStop(.3,'#001c35');seaG.addColorStop(1,'#000d1a');bgCtx.fillStyle=seaG;bgCtx.fillRect(0,sh,W,H-sh);
    for(let w=0;w<5;w++){const wy=sh+(H-sh)*(w/5)+Math.sin(G.t*.4+w)*2;bgCtx.beginPath();bgCtx.moveTo(0,wy);for(let x=0;x<=W;x+=8)bgCtx.lineTo(x,wy+Math.sin(x*.02+G.t*.5+w*.8)*3);bgCtx.strokeStyle=`rgba(0,255,204,${.025+w*.012})`;bgCtx.lineWidth=1;bgCtx.stroke();}
  } else if(sc==='hack') {
    bgCtx.fillStyle='#000004';bgCtx.fillRect(0,0,W,H);
    bgCtx.font='10px Share Tech Mono';
    for(let c=0;c<Math.floor(W/12);c++){for(let r=0;r<3;r++){const y=((G.frame*2+c*20+r*40)%(H+40))-20;bgCtx.fillStyle=`rgba(255,30,50,${.02+Math.random()*.03})`;bgCtx.fillText('01ABCDEF'[Math.floor(Math.random()*8)],c*12,y);}}
  } else if(sc==='command') {
    const g=bgCtx.createRadialGradient(W/2,H/2,0,W/2,H/2,W*.7);g.addColorStop(0,'#000d18');g.addColorStop(1,'#000004');bgCtx.fillStyle=g;bgCtx.fillRect(0,0,W,H);
    bgCtx.strokeStyle='rgba(0,255,204,.04)';bgCtx.lineWidth=.5;
    for(let x=0;x<W;x+=30){bgCtx.beginPath();bgCtx.moveTo(x,0);bgCtx.lineTo(x,H);bgCtx.stroke();}
    for(let y=0;y<H;y+=30){bgCtx.beginPath();bgCtx.moveTo(0,y);bgCtx.lineTo(W,y);bgCtx.stroke();}
  }
}

function drawShip(x,y,ship,flip=false) {
  if (!ctx) return;
  const sc=(ship.type==='tanker'?1.25:ship.type==='susp'?.78:1) * 0.9;
  ctx.save();ctx.translate(x,y);if(flip)ctx.scale(-1,1);ctx.scale(sc,sc);
  ctx.beginPath();ctx.moveTo(-48,0);ctx.quadraticCurveTo(-53,8,-38,10);ctx.lineTo(38,10);ctx.quadraticCurveTo(53,8,56,0);ctx.quadraticCurveTo(53,-2,38,-3);ctx.lineTo(-38,-3);ctx.closePath();
  ctx.fillStyle=ship.col;ctx.fill();ctx.strokeStyle=ship.type==='susp'?'rgba(255,100,0,.45)':'rgba(0,200,180,.18)';ctx.lineWidth=.7;ctx.stroke();
  ctx.fillStyle='#0d2030';ctx.fillRect(-4,-15,22,12);ctx.fillRect(1,-24,13,10);ctx.fillRect(4,-32,7,9);
  if(ship.type!=='susp'){ctx.beginPath();ctx.arc(56,-2,2,0,Math.PI*2);ctx.fillStyle=`rgba(255,240,150,${.65+.3*Math.sin(G.frame*.08)})`;ctx.fill();}
  if(ship.alert){ctx.beginPath();ctx.arc(0,-24,3+2*Math.sin(G.frame*.12),0,Math.PI*2);ctx.strokeStyle=`rgba(255,50,80,${.45+.4*Math.sin(G.frame*.12)})`;ctx.lineWidth=1;ctx.stroke();}
  ctx.restore();
  ctx.font='9px Share Tech Mono';ctx.textAlign='center';
  ctx.fillStyle=ship.type==='susp'?'rgba(255,140,0,.55)':'rgba(0,255,204,.4)';ctx.fillText(ship.name,x,y-19*sc-5);
}

function drawScene() {
  if (!ctx) return;
  ctx.clearRect(0,0,W,H);
  const sc=G.cinScene, sh=H*.42;
  if(sc==='ocean'||sc==='port'||sc==='cable') {
    if(sc==='ocean'){
      ships.forEach((s,i)=>{s.x+=s.spd*s.dir;if(s.x>1.1)s.x=-.1;if(s.x<-.1)s.x=1.1;drawShip(s.x*W,sh+(H-sh)*(.14+i*.22),s,s.dir<0);});
      ctx.fillStyle='#0a1a28';ctx.fillRect(W*.05-2,sh-16,4,16);
      ctx.beginPath();ctx.arc(W*.05,sh-16,3,0,Math.PI*2);ctx.fillStyle=`rgba(255,240,100,${.45+.45*Math.sin(G.frame*.05)})`;ctx.fill();
      ctx.save();ctx.translate(W*.05,sh-16);ctx.rotate(G.frame*.012);const bg2=ctx.createLinearGradient(0,0,W*.22,0);bg2.addColorStop(0,'rgba(255,240,100,.1)');bg2.addColorStop(1,'transparent');ctx.beginPath();ctx.moveTo(0,0);ctx.lineTo(W*.22,-H*.05);ctx.lineTo(W*.22,H*.05);ctx.fillStyle=bg2;ctx.fill();ctx.restore();
    }
    if(sc==='port'){
      ctx.fillStyle='#0a1520';ctx.fillRect(0,sh,W*.42,H-sh);ctx.fillStyle='#0d1e2e';ctx.fillRect(0,sh,W*.42,3);
      [W*.08,W*.18,W*.29].forEach((cx,ci)=>{
        ctx.fillStyle='#0a1820';ctx.fillRect(cx-2,sh-32,4,32);
        ctx.beginPath();ctx.moveTo(cx,sh-32);ctx.lineTo(cx+26,sh-41);ctx.strokeStyle=`rgba(255,50,80,${.45+.4*Math.sin(G.frame*.12+ci)})`;ctx.lineWidth=1;ctx.stroke();
        ctx.beginPath();ctx.moveTo(cx+26,sh-41);ctx.lineTo(cx+26,sh-13);ctx.strokeStyle=`rgba(255,50,80,${.35+.3*Math.sin(G.frame*.1+ci)})`;ctx.lineWidth=0.7;ctx.stroke();
      });
      for(let r=0;r<3;r++)for(let c=0;c<7;c++){ctx.fillStyle=['#1a3040','#0f2030','#162535','#0a1a28'][(r+c)%4];ctx.fillRect(2+c*25,sh+8+r*12,24,10);ctx.strokeStyle='rgba(0,100,150,.2)';ctx.lineWidth=.2;ctx.strokeRect(2+c*25,sh+8+r*12,24,10);}
      ctx.fillStyle='#3a0810';ctx.fillRect(W*.05,sh+8,24,10);ctx.strokeStyle=`rgba(255,50,80,${.35+.35*Math.sin(G.frame*.1)})`;ctx.lineWidth=.4;ctx.strokeRect(W*.05,sh+8,24,10);
      drawShip(W*.62,sh+(H-sh)*.36,ships[0]);
      ctx.fillStyle=`rgba(255,20,40,${.015+.012*Math.sin(G.frame*.08)})`;ctx.fillRect(0,sh,W*.42,H-sh);
    }
    if(sc==='cable'){
      const fg=ctx.createLinearGradient(0,H*.68,0,H);fg.addColorStop(0,'#001020');fg.addColorStop(1,'#000508');ctx.fillStyle=fg;ctx.fillRect(0,H*.68,W,H*.32);
      cables.forEach((cable,ci)=>{
        const pts=cable.pts.map(p=>[p[0]*W,p[1]*H]);
        ctx.beginPath();ctx.moveTo(pts[0][0],pts[0][1]);
        for(let i=1;i<pts.length;i++){const mx=(pts[i-1][0]+pts[i][0])/2,my=(pts[i-1][1]+pts[i][1])/2;ctx.quadraticCurveTo(pts[i-1][0],pts[i-1][1],mx,my);}
        ctx.lineTo(pts[pts.length-1][0],pts[pts.length-1][1]);ctx.strokeStyle=`rgba(0,255,204,${.22+.08*Math.sin(G.frame*.05+ci)})`;ctx.lineWidth=1;ctx.stroke();
        const pp=(G.frame*.007+ci*.45)%1,pi2=Math.floor(pp*(pts.length-1));
        if(pi2<pts.length-1){const f=pp*(pts.length-1)-pi2,px2=pts[pi2][0]+(pts[pi2+1][0]-pts[pi2][0])*f,py2=pts[pi2][1]+(pts[pi2+1][1]-pts[pi2][1])*f;ctx.beginPath();ctx.arc(px2,py2,1.5,0,Math.PI*2);ctx.fillStyle='#00ffcc';ctx.fill();}
      });
      drawShip(W*.54,H*.44,ships[2]);
      const rx=W*.54+Math.sin(G.frame*.016)*11,ry=H*.77;
      ctx.save();ctx.translate(rx,ry);ctx.fillStyle='#0a1520';ctx.fillRect(-10,-4,20,8);ctx.strokeStyle='rgba(255,150,0,.6)';ctx.lineWidth=.4;ctx.strokeRect(-10,-4,20,8);ctx.beginPath();ctx.arc(10,0,1.1,0,Math.PI*2);ctx.fillStyle=`rgba(255,200,0,${.5+.4*Math.sin(G.frame*.2)})`;ctx.fill();ctx.beginPath();ctx.moveTo(0,-4);ctx.lineTo(0,-(H*.3));ctx.strokeStyle='rgba(180,140,60,.22)';ctx.lineWidth=.4;ctx.stroke();ctx.restore();
    }
  } else if(sc==='hack') {
    const panels=[
      {t:'VTMS ACCESS',bl:true},
      {t:'SCADA BREACH',bl:false},
      {t:'AIS SPOOFER',bl:true},
      {t:'C2 BEACON',bl:false},
      {t:'APT-POSEIDON',bl:true},
      {t:'NETWORK MAP',bl:false},
    ];
    const cols=3,panW=(W-10)/cols,panH=(H-30)/2;
    panels.forEach((p,i)=>{
      const c=i%cols,r=Math.floor(i/cols),px=5+c*panW,py=15+r*panH*.72,pw=panW-3,ph=panH*.66;
      const ba=p.bl?.48+.48*Math.sin(G.frame*.12+i):1;
      ctx.fillStyle='rgba(20,5,8,.86)';ctx.fillRect(px,py,pw,ph);ctx.strokeStyle=`rgba(255,50,80,${ba*.5})`;ctx.lineWidth=.4;ctx.strokeRect(px,py,pw,ph);
      ctx.font='bold 9px Share Tech Mono';ctx.fillStyle=`rgba(255,${p.bl?'80':'130'},100,${ba})`;ctx.textAlign='left';ctx.fillText('> '+p.t,px+4,py+10);
    });
  } else if(sc==='command') {
    cmdLinks.forEach(([a2,b])=>{
      const na=cmdNodes[a2],nb=cmdNodes[b];const ax=na.x*W,ay=na.y*H,bx=nb.x*W,by=nb.y*H;
      const pr=((G.frame*.006+a2*.2)%1);const px2=ax+(bx-ax)*pr,py2=ay+(by-ay)*pr;
      ctx.strokeStyle='rgba(0,200,160,.09)';ctx.lineWidth=.4;ctx.beginPath();ctx.moveTo(ax,ay);ctx.lineTo(bx,by);ctx.stroke();
      ctx.beginPath();ctx.arc(px2,py2,1,0,Math.PI*2);ctx.fillStyle=`rgba(0,255,200,${.3+.3*Math.sin(G.frame*.1+a2)})`;ctx.fill();
    });
    cmdNodes.forEach((nd,ni)=>{
      const nx=nd.x*W,ny=nd.y*H,ic=ni===0,r=ic?22:15;
      const h=nd.col ? parseInt(nd.col.slice(1),16) : 0,cr=(h>>16)&255,cg=(h>>8)&255,cb=h&255;
      ctx.beginPath();ctx.arc(nx,ny,r,0,Math.PI*2);ctx.fillStyle=`rgba(${cr},${cg},${cb},.09)`;ctx.fill();ctx.strokeStyle=`rgba(${cr},${cg},${cb},.6)`;ctx.lineWidth=ic?0.8:0.6;ctx.stroke();
      const lines=nd.lbl.split('\n');ctx.font='7px Share Tech Mono';ctx.textAlign='center';
      lines.forEach((ln,li)=>{ctx.fillStyle=nd.col;ctx.fillText(ln,nx,ny-(lines.length-1)*3+li*6);});
    });
  }
}

// ── Scene Information and Briefings ────────────────────────────────────────
const SCENE_DESCRIPTIONS = {
  ocean: {
    title: 'PHASE I · INITIAL DETECTION',
    sub: 'SITUATION NOMINALE · MARITIME ROUTINE',
    desc: 'During routine maritime monitoring, VTMIS registers telemetry anomalies in the Mediterranean. A suspicious cargo vessel, the MV Silver Horizon, goes dark by turning off its AIS transponder, triggering security alert protocols for hybrid crisis response.',
    desc_fr: 'Lors de la surveillance maritime de routine, le VTMIS enregistre des anomalies de télémétrie en Méditerranée. Un cargo suspect, le MV Silver Horizon, éteint son transpondeur AIS, déclenchant les protocoles d\'alerte de sécurité.'
  },
  cable: {
    title: 'PHASE II · HYBRID THREAT',
    sub: 'MV SILVER HORIZON · ROV DETECTED',
    desc: 'Underwater acoustic sensors and patrolling assets report an unidentified Remotely Operated Vehicle (ROV) operating in close proximity to critical submarine communication cables (e.g. SEA-ME-WE 5). Reconnaissance operations indicate active physical threat preparation.',
    desc_fr: 'Les capteurs acoustiques sous-marins et les patrouilleurs signalent un véhicule sous-marin télécommandé (ROV) non identifié opérant à proximité immédiate de câbles de communication sous-marins critiques. Les opérations de reconnaissance indiquent une préparation active de menace physique.'
  },
  port: {
    title: 'PHASE I-II · ATTACK ACTIVE',
    sub: 'T+00:00 · SYSTEM FAILURE ACTIVE',
    desc: 'A coordinated cyber-physical disruption hits the Marseille maritime terminal. The SCADA systems governing cargo crane operations are locked, causing terminal gridlock. General communication failures and system lockouts lock down port operations.',
    desc_fr: 'Une perturbation cyber-physique coordonnée frappe le terminal maritime de Marseille. Les systèmes SCADA régissant le fonctionnement des portiques de fret sont verrouillés, provoquant un blocage du terminal.'
  },
  hack: {
    title: 'PHASE III · ESCALATION',
    sub: 'T+01:15 · MULTI-VECTOR ATTACK ACTIVE',
    desc: 'The threat actor escalates operations into a full-scale digital offensive. Propagation of malware and active C2 (Command & Control) beacons are identified across the maritime operational technology (OT) network, targeting industrial control logic.',
    desc_fr: 'L\'attaquant intensifie ses opérations en une offensive numérique à grande échelle. La propagation de logiciels malveillants et de balises C2 actives est identifiée sur le réseau OT maritime, ciblant la logique de contrôle industriel.'
  },
  command: {
    title: 'PHASE IV · STRATEGIC RESPONSE',
    sub: 'CRISIS COORDINATION CELL ACTIVATED',
    desc: 'The Carthage Shield joint command and crisis response center is fully operational. Authorities coordinate with national cyber agency ANSSI and allied commands to isolate affected systems, route communications, and initialize attribution operations.',
    desc_fr: 'Le commandement interarmées Carthage Shield et le centre de réponse aux crises sont pleinement opérationnels. Les autorités se coordonnent avec l\'ANSSI et les commandements alliés pour isoler les systèmes affectés et lancer les opérations d\'attribution.'
  }
};

function openSceneInfo() {
  const info = SCENE_DESCRIPTIONS[G.cinScene];
  if (!info) return;
  document.getElementById('infoTitle').textContent = info.title;
  document.getElementById('infoSub').textContent = info.sub;
  document.getElementById('infoDesc').innerHTML = `
      <p style="margin-bottom: 12px; color: #fff; font-size: 0.9rem;">${info.desc}</p>
      <p style="margin-bottom: 0; color: rgba(200,255,240,0.65); font-style: italic; font-size: 0.82rem; border-top: 1px solid rgba(0,255,204,0.15); padding-top: 10px;">${info.desc_fr}</p>
  `;
  document.getElementById('sceneInfoOverlay').classList.add('show');
}

function closeSceneInfo(event) {
  if (event) {
    event.stopPropagation();
  }
  document.getElementById('sceneInfoOverlay').classList.remove('show');
}

// ── Three.js Initialisation ────────────────────────────────────────────────
function initThree(container) {
  threeContainer = container;
  threeW = container.clientWidth || 800;
  threeH = container.clientHeight || 450;
  threeRenderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
  threeRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  threeRenderer.setSize(threeW, threeH);
  threeRenderer.shadowMap.enabled = true;
  container.appendChild(threeRenderer.domElement);
  threeRenderer.domElement.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;z-index:1;';
  threeCamera = new THREE.PerspectiveCamera(60, threeW / threeH, 0.1, 2000);
  const ro = new ResizeObserver(e => {
    const r = e[0].contentRect;
    threeW = r.width; threeH = r.height;
    threeRenderer.setSize(threeW, threeH);
    threeCamera.aspect = threeW / threeH;
    threeCamera.updateProjectionMatrix();
  });
  ro.observe(container);
  container.addEventListener('mousemove', e => {
    const rect = container.getBoundingClientRect();
    mouseX = ((e.clientX - rect.left) / threeW - 0.5) * 2;
    mouseY = -((e.clientY - rect.top) / threeH - 0.5) * 2;
  });
  buildThreeScene(G.cinScene);
}

function clearThreeScene() {
  if (threeAnimId) { cancelAnimationFrame(threeAnimId); threeAnimId = null; }
  if (threeScene) {
    threeScene.traverse(obj => {
      if (obj.geometry) obj.geometry.dispose();
      if (obj.material) { if (Array.isArray(obj.material)) obj.material.forEach(m => m.dispose()); else obj.material.dispose(); }
    });
  }
  threeScene = null;
  threeSceneObjects = {};
}

function buildThreeScene(sc) {
  clearThreeScene();
  threeScene = new THREE.Scene();
  currentThreeScene = sc;
  if (sc === 'ocean') buildOceanScene();
  else if (sc === 'cable') buildCableScene();
  else if (sc === 'port') buildPortScene();
  else if (sc === 'hack') buildHackScene();
  else if (sc === 'command') buildCommandScene();
  threeLoop();
}

function threeLoop() {
  threeAnimId = requestAnimationFrame(threeLoop);
  G.t += 0.016; G.frame++;
  if (threeSceneObjects.animate) threeSceneObjects.animate(G.t, G.frame);
  if (threeCamera) {
    threeCamera.position.x += (mouseX * 2 - threeCamera.position.x) * 0.02;
    threeCamera.position.y += (mouseY * 1 - threeCamera.position.y) * 0.02;
    threeCamera.lookAt(threeSceneObjects.lookAt || new THREE.Vector3(0, 0, 0));
  }
  if (threeRenderer && threeScene && threeCamera) threeRenderer.render(threeScene, threeCamera);
  updateHUD();
}

// ══ SCENE 1 — OCEAN ══════════════════════════════════════════════════════
function buildOceanScene() {
  threeScene.background = new THREE.Color(0x000c1a);
  threeScene.fog = new THREE.Fog(0x000c1a, 60, 200);
  threeCamera.position.set(0, 18, 55);
  threeSceneObjects.lookAt = new THREE.Vector3(0, 0, 0);
  const starGeo = new THREE.BufferGeometry();
  const sv = []; for (let i = 0; i < 800; i++) sv.push((Math.random()-.5)*300, Math.random()*60+8, (Math.random()-.5)*300);
  starGeo.setAttribute('position', new THREE.Float32BufferAttribute(sv, 3));
  threeScene.add(new THREE.Points(starGeo, new THREE.PointsMaterial({ color:0xffffff, size:0.25, transparent:true, opacity:0.7 })));
  const moon = new THREE.Mesh(new THREE.SphereGeometry(2.5,16,16), new THREE.MeshBasicMaterial({color:0xd7e1ff}));
  moon.position.set(35,35,-60); threeScene.add(moon);
  moon.add(new THREE.Mesh(new THREE.SphereGeometry(3.8,16,16), new THREE.MeshBasicMaterial({color:0x8899cc,transparent:true,opacity:0.18,side:THREE.BackSide})));
  const wSeg=100, waterGeo=new THREE.PlaneGeometry(200,200,wSeg,wSeg);
  waterGeo.rotateX(-Math.PI/2);
  const wv = waterGeo.attributes.position, wd=[];
  for (let i=0;i<wv.count;i++) wd.push({ox:wv.getX(i),oz:wv.getZ(i),phase:Math.random()*Math.PI*2});
  const waterMat = new THREE.MeshPhongMaterial({color:0x002d50,emissive:0x001520,specular:0x00ffcc,shininess:80,transparent:true,opacity:0.92,side:THREE.DoubleSide});
  const water = new THREE.Mesh(waterGeo, waterMat);
  threeScene.add(water);
  threeScene.add(new THREE.AmbientLight(0x001a2e, 1.2));
  const ml = new THREE.DirectionalLight(0x4466aa, 0.8); ml.position.set(35,35,-60); threeScene.add(ml);
  const tl = new THREE.PointLight(0x00ffcc, 0.4, 80); tl.position.set(0,5,0); threeScene.add(tl);
  function mkShip(x,z,col,sc2) {
    const g=new THREE.Group();
    g.add(Object.assign(new THREE.Mesh(new THREE.BoxGeometry(12*sc2,1.2*sc2,3.5*sc2),new THREE.MeshPhongMaterial({color:col})), {position:{x:0,y:0.6,z:0}}));
    const br=new THREE.Mesh(new THREE.BoxGeometry(3*sc2,2*sc2,2.8*sc2),new THREE.MeshPhongMaterial({color:0x0d2030}));
    br.position.set(-2*sc2,1.8*sc2,0); g.add(br);
    const nl=new THREE.Mesh(new THREE.SphereGeometry(0.18*sc2,6,6),new THREE.MeshBasicMaterial({color:0xffee88}));
    nl.position.set(5.8*sc2,1*sc2,0); g.add(nl);
    g.position.set(x,0.5,z); threeScene.add(g); return g;
  }
  const s1=mkShip(-18,8,0x1a4060,1), s2=mkShip(12,-5,0x1a3050,1.3), s3=mkShip(-5,18,0x102030,0.85);
  const lt=new THREE.Mesh(new THREE.CylinderGeometry(0.5,0.8,8,8),new THREE.MeshPhongMaterial({color:0x1a3040}));
  lt.position.set(-40,4,-15); threeScene.add(lt);
  const ltl=new THREE.SpotLight(0xffee88,2,120,Math.PI/8,0.5); ltl.position.set(-40,12,-15); threeScene.add(ltl);
  const rings=[]; for(let i=0;i<3;i++){const r=new THREE.Mesh(new THREE.RingGeometry(.5+i*1.2,.7+i*1.2,32),new THREE.MeshBasicMaterial({color:0x00ffcc,transparent:true,opacity:.3-i*.08,side:THREE.DoubleSide}));r.rotation.x=-Math.PI/2;r.position.set(12,1.8,-5);threeScene.add(r);rings.push({m:r,o:i*.5});}
  threeSceneObjects.animate = (t) => {
    const pos=waterGeo.attributes.position;
    for(let i=0;i<wd.length;i++){const d=wd[i];pos.setY(i,Math.sin(d.ox*.08+t*.8+d.phase)*1.2+Math.cos(d.oz*.06+t*.5)*.8);}
    pos.needsUpdate=true; waterGeo.computeVertexNormals();
    s1.position.x+=.005; s1.position.y=.5+Math.sin(t*.7)*.2; if(s1.position.x>50)s1.position.x=-50;
    s2.position.x-=.004; s2.position.y=.5+Math.sin(t*.6+1)*.25; if(s2.position.x<-50)s2.position.x=50;
    s3.position.z+=.003; s3.position.y=.5+Math.sin(t*.5+2)*.15;
    ltl.target.position.set(-40+Math.cos(t*.5)*60,0,-15+Math.sin(t*.5)*60); ltl.target.updateMatrixWorld();
    rings.forEach((r,i)=>{r.m.material.opacity=Math.max(0,.35*(.5+.5*Math.sin(t*1.5-i*.8)));const s=1+.15*Math.sin(t*1.2-i*.6);r.m.scale.set(s,s,s);});
  };
}

// ══ SCENE 2 — CABLE (Underwater) ═════════════════════════════════════════
function buildCableScene() {
  threeScene.background=new THREE.Color(0x000508);
  threeScene.fog=new THREE.FogExp2(0x000508,.025);
  threeCamera.position.set(0,0,50);
  threeSceneObjects.lookAt=new THREE.Vector3(0,-5,0);
  threeScene.add(new THREE.AmbientLight(0x001020,1.5));
  const tp=new THREE.PointLight(0x00ffcc,1.5,120); tp.position.set(0,10,20); threeScene.add(tp);
  const dp=new THREE.PointLight(0x0033aa,.8,80); dp.position.set(-20,-10,0); threeScene.add(dp);
  const flG=new THREE.PlaneGeometry(200,200,40,40); flG.rotateX(-Math.PI/2);
  const fv=flG.attributes.position; for(let i=0;i<fv.count;i++) fv.setY(i,(Math.random()-.5)*2.5);
  flG.computeVertexNormals();
  const fl=new THREE.Mesh(flG,new THREE.MeshPhongMaterial({color:0x001015})); fl.position.y=-22; threeScene.add(fl);
  const cPaths=[
    [{x:-40,y:-8,z:0},{x:-20,y:-12,z:3},{x:0,y:-10,z:-2},{x:20,y:-13,z:1},{x:40,y:-9,z:-1}],
    [{x:-35,y:-14,z:-5},{x:-10,y:-16,z:-3},{x:15,y:-14,z:-6},{x:40,y:-15,z:-4}]
  ];
  const cObjs=[];
  cPaths.forEach((pts,ci)=>{
    const curve=new THREE.CatmullRomCurve3(pts.map(p=>new THREE.Vector3(p.x,p.y,p.z)));
    const tube=new THREE.Mesh(new THREE.TubeGeometry(curve,80,.18,8,false),new THREE.MeshPhongMaterial({color:0x00ffcc,emissive:0x004433,transparent:true,opacity:.7}));
    threeScene.add(tube);
    const pulse=new THREE.Mesh(new THREE.SphereGeometry(.5,8,8),new THREE.MeshBasicMaterial({color:0x00ffcc}));
    threeScene.add(pulse);
    cObjs.push({tube,pulse,curve,ci,pct:ci*.4%1});
  });
  const rov=new THREE.Group();
  rov.add(new THREE.Mesh(new THREE.BoxGeometry(4,1.5,2.5),new THREE.MeshPhongMaterial({color:0x223344,emissive:0x111a22})));
  const rl=new THREE.SpotLight(0xffee88,3,30,Math.PI/6,.6); rl.position.set(2.5,-.3,0); rov.add(rl);
  rov.position.set(5,-8,8); threeScene.add(rov);
  const bGeo=new THREE.BufferGeometry();
  const bv=[]; for(let i=0;i<200;i++) bv.push((Math.random()-.5)*60,Math.random()*30-20,(Math.random()-.5)*30);
  bGeo.setAttribute('position',new THREE.Float32BufferAttribute(bv,3));
  const bubbles=new THREE.Points(bGeo,new THREE.PointsMaterial({color:0x00aaff,size:.2,transparent:true,opacity:.5}));
  threeScene.add(bubbles);
  threeSceneObjects.animate=(t)=>{
    cObjs.forEach(c=>{c.pct=(c.pct+.003+c.ci*.001)%1;const pt=c.curve.getPoint(c.pct);c.pulse.position.copy(pt);c.tube.material.opacity=.5+.2*Math.sin(t*1.5+c.ci);c.tube.material.emissiveIntensity=.3+.2*Math.sin(t*2+c.ci*1.3);});
    rov.position.x=5+Math.sin(t*.4)*12; rov.position.y=-8+Math.sin(t*.3)*3; rov.position.z=8+Math.cos(t*.25)*5; rov.rotation.z=Math.sin(t*.5)*.1;
    const bPos=bGeo.attributes.position; for(let i=0;i<bPos.count;i++){const y=bPos.getY(i)+.04;bPos.setY(i,y>15?-20:y);} bPos.needsUpdate=true;
    dp.intensity=.5+.3*Math.sin(t*.7); tp.position.x=Math.sin(t*.3)*15;
  };
}

// ══ SCENE 3 — PORT ═══════════════════════════════════════════════════════
function buildPortScene() {
  threeScene.background=new THREE.Color(0x050810);
  threeScene.fog=new THREE.Fog(0x050810,60,180);
  threeCamera.position.set(0,28,65);
  threeSceneObjects.lookAt=new THREE.Vector3(0,0,0);
  threeScene.add(new THREE.AmbientLight(0x0a0f18,2));
  const ra=new THREE.PointLight(0xff1020,3,60); ra.position.set(0,20,10); threeScene.add(ra);
  const w=new THREE.Mesh(new THREE.PlaneGeometry(200,100,1,1).rotateX(-Math.PI/2),new THREE.MeshPhongMaterial({color:0x001520,emissive:0x000508}));
  w.position.set(20,-.5,10); threeScene.add(w);
  const dock=new THREE.Mesh(new THREE.BoxGeometry(70,1.5,35),new THREE.MeshPhongMaterial({color:0x0a1520}));
  dock.position.set(-15,-.5,2); threeScene.add(dock);
  const cCols=[0x1a3040,0x0f2030,0x162535,0x0a1a28,0x3a0810]; const conts=[];
  for(let r=0;r<3;r++) for(let c=0;c<8;c++){const m=new THREE.Mesh(new THREE.BoxGeometry(5.5,2.5,2.5),new THREE.MeshPhongMaterial({color:cCols[(r+c)%cCols.length],emissive:new THREE.Color(cCols[(r+c)%cCols.length]).multiplyScalar(.3)}));m.position.set(-28+c*6,1+r*2.6,-6);threeScene.add(m);conts.push(m);}
  conts[2].material.color.setHex(0x3a0810); conts[2].material.emissive.setHex(0x661010);
  const fl2=new THREE.PointLight(0xff2010,4,20); fl2.position.set(-16,5,-6); threeScene.add(fl2);
  function mkCrane(x,z){const g=new THREE.Group();const pole=new THREE.Mesh(new THREE.BoxGeometry(.6,20,.6),new THREE.MeshPhongMaterial({color:0x0a1820}));pole.position.y=10;g.add(pole);const arm=new THREE.Mesh(new THREE.BoxGeometry(14,.5,.5),new THREE.MeshPhongMaterial({color:0x0a1820}));arm.position.set(5,20,0);g.add(arm);const led=new THREE.Mesh(new THREE.SphereGeometry(.3,8,8),new THREE.MeshBasicMaterial({color:0xff2020}));led.position.set(12,20.5,0);g.add(led);g.position.set(x,0,z);threeScene.add(g);return {g,led};}
  const cranes=[mkCrane(-25,-5),mkCrane(-12,-5),mkCrane(2,-5)];
  const ship=new THREE.Group(); const hull=new THREE.Mesh(new THREE.BoxGeometry(22,2.5,6),new THREE.MeshPhongMaterial({color:0x1a4060}));ship.add(hull);const bridge=new THREE.Mesh(new THREE.BoxGeometry(5,4.5,5),new THREE.MeshPhongMaterial({color:0x0d2030}));bridge.position.set(-5,3.5,0);ship.add(bridge);ship.position.set(25,.8,5);threeScene.add(ship);
  const strobes=[]; [[-25,12,-8],[0,12,-8],[25,12,-8]].forEach(([x,y,z])=>{const s=new THREE.Mesh(new THREE.SphereGeometry(.5,8,8),new THREE.MeshBasicMaterial({color:0xff3030}));s.position.set(x,y,z);threeScene.add(s);const sl=new THREE.PointLight(0xff1010,2,25);sl.position.set(x,y,z);threeScene.add(sl);strobes.push({m:s,l:sl});});
  threeSceneObjects.animate=(t)=>{
    strobes.forEach((s,i)=>{const on=Math.sin(t*4+i*1.2)>0;s.m.material.opacity=on?1:.05;s.l.intensity=on?3:0;});
    fl2.intensity=3+2*Math.sin(t*7); ra.intensity=1.5+1.5*Math.sin(t*3); ra.position.x=Math.sin(t*.5)*15;
    ship.position.y=.8+Math.sin(t*.6)*.3; ship.rotation.z=Math.sin(t*.4)*.01;
  };
}

// ══ SCENE 4 — HACK (Cyberspace) ══════════════════════════════════════════
function buildHackScene() {
  threeScene.background=new THREE.Color(0x000004);
  threeCamera.position.set(0,0,55);
  threeSceneObjects.lookAt=new THREE.Vector3(0,0,0);
  threeScene.add(new THREE.AmbientLight(0x110008,2));
  const rc=new THREE.PointLight(0xff1030,3,80); rc.position.set(0,0,20); threeScene.add(rc);
  const nData=[{x:0,y:0,z:0,col:0xff2040,sz:2.2,core:true},{x:-14,y:6,z:-5,col:0xff3030,sz:1.4},{x:12,y:-5,z:-8,col:0xff1050,sz:1.2},{x:-8,y:-10,z:-3,col:0xcc0040,sz:1.5},{x:16,y:7,z:-6,col:0xff4020,sz:1.3},{x:-16,y:-4,z:-10,col:0xdd2060,sz:1.1},{x:0,y:13,z:-7,col:0x882020,sz:1},{x:8,y:-12,z:-5,col:0xaa1030,sz:.9}];
  const nMesh=[];
  nData.forEach((nd,i)=>{
    const m=new THREE.Mesh(new THREE.IcosahedronGeometry(nd.sz,nd.core?2:1),new THREE.MeshPhongMaterial({color:nd.col,emissive:new THREE.Color(nd.col).multiplyScalar(.4),transparent:true,opacity:.85,shininess:120}));
    m.position.set(nd.x,nd.y,nd.z); threeScene.add(m);
    m.add(new THREE.Mesh(new THREE.IcosahedronGeometry(nd.sz,nd.core?2:1).clone(),new THREE.MeshBasicMaterial({color:nd.col,wireframe:true,transparent:true,opacity:.2})));
    const ring=new THREE.Mesh(new THREE.RingGeometry(nd.sz*1.4,nd.sz*1.8,32),new THREE.MeshBasicMaterial({color:nd.col,transparent:true,opacity:.25,side:THREE.DoubleSide}));ring.rotation.x=Math.PI/2;m.add(ring);
    m.add(new THREE.PointLight(nd.col,.6,20));
    nMesh.push({m,ring,nd});
  });
  const edges=[[0,1],[0,2],[0,3],[0,4],[0,5],[1,6],[2,4],[3,5],[4,6],[5,7],[3,7]]; const eObjs=[];
  edges.forEach(([a,b])=>{
    const na=nData[a],nb=nData[b];
    const line=new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(na.x,na.y,na.z),new THREE.Vector3(nb.x,nb.y,nb.z)]),new THREE.LineBasicMaterial({color:0xff1030,transparent:true,opacity:.25}));
    threeScene.add(line);
    const pulse=new THREE.Mesh(new THREE.SphereGeometry(.22,6,6),new THREE.MeshBasicMaterial({color:0xff4060}));threeScene.add(pulse);
    eObjs.push({pts:[new THREE.Vector3(na.x,na.y,na.z),new THREE.Vector3(nb.x,nb.y,nb.z)],pulse,pct:Math.random(),line});
  });
  const rGeo=new THREE.BufferGeometry(); const rv=[]; for(let i=0;i<600;i++) rv.push((Math.random()-.5)*80,(Math.random()-.5)*60,(Math.random()-.5)*40);
  rGeo.setAttribute('position',new THREE.Float32BufferAttribute(rv,3));
  const rain=new THREE.Points(rGeo,new THREE.PointsMaterial({color:0xff1030,size:.15,transparent:true,opacity:.5}));
  threeScene.add(rain);
  threeSceneObjects.animate=(t)=>{
    nMesh.forEach(({m,ring},i)=>{m.rotation.x=t*.4+i;m.rotation.y=t*.6+i*.5;m.material.opacity=.85+.15*Math.sin(t*3+i*1.1);ring.material.opacity=.15+.2*Math.sin(t*2+i);});
    eObjs.forEach(e=>{e.pct=(e.pct+.008)%1;const pos=e.pts[0].clone().lerp(e.pts[1],e.pct);e.pulse.position.copy(pos);e.line.material.opacity=.15+.15*Math.sin(t*2+e.pct*6);});
    const rp=rGeo.attributes.position;for(let i=0;i<rp.count;i++){const y=rp.getY(i)-.3;rp.setY(i,y<-30?30:y);}rp.needsUpdate=true;
    rc.intensity=2+2*Math.sin(t*5); rc.position.x=Math.sin(t*.8)*8; rc.position.y=Math.cos(t*.6)*5;
    threeCamera.position.z=55+Math.sin(t*8)*.3;
  };
}

// ══ SCENE 5 — COMMAND (Globe) ════════════════════════════════════════════
function buildCommandScene() {
  threeScene.background=new THREE.Color(0x000d18);
  threeScene.fog=new THREE.Fog(0x000d18,80,250);
  threeCamera.position.set(0,10,65);
  threeSceneObjects.lookAt=new THREE.Vector3(0,0,0);
  threeScene.add(new THREE.AmbientLight(0x001020,1.8));
  const tk=new THREE.DirectionalLight(0x00ffcc,.6); tk.position.set(20,30,20); threeScene.add(tk);
  const br=new THREE.PointLight(0x0044ff,1.5,100); br.position.set(-30,0,-20); threeScene.add(br);
  const grid=new THREE.GridHelper(120,40,0x00ffcc,0x002233); grid.position.y=-18; grid.material.transparent=true; grid.material.opacity=.3; threeScene.add(grid);
  const globe=new THREE.Mesh(new THREE.SphereGeometry(14,48,48),new THREE.MeshPhongMaterial({color:0x001528,emissive:0x000c14,specular:0x00ffcc,shininess:60,transparent:true,opacity:.9}));
  threeScene.add(globe);
  globe.add(new THREE.Mesh(new THREE.SphereGeometry(14.1,24,24),new THREE.MeshBasicMaterial({color:0x00ffcc,wireframe:true,transparent:true,opacity:.08})));
  threeScene.add(new THREE.Mesh(new THREE.SphereGeometry(15.5,32,32),new THREE.MeshBasicMaterial({color:0x004488,transparent:true,opacity:.12,side:THREE.BackSide})));
  function ll2v(lat,lon,r){const phi=(90-lat)*(Math.PI/180),theta=(lon+180)*(Math.PI/180);return new THREE.Vector3(-r*Math.sin(phi)*Math.cos(theta),r*Math.cos(phi),r*Math.sin(phi)*Math.sin(theta));}
  const gPts=[{lat:43,lon:5,col:0x00ffcc,sz:.6},{lat:48,lon:2,col:0xffaa00,sz:.5},{lat:51,lon:-.1,col:0x4488ff,sz:.45},{lat:52,lon:13,col:0xaa88ff,sz:.45},{lat:41,lon:29,col:0x44ddaa,sz:.4},{lat:36,lon:10,col:0xffdd44,sz:.4},{lat:40,lon:-3.7,col:0xff6688,sz:.4},{lat:45,lon:9,col:0x88ffcc,sz:.38}];
  const gNodes=[];
  gPts.forEach(pt=>{
    const pos=ll2v(pt.lat,pt.lon,14.3);
    const n=new THREE.Mesh(new THREE.SphereGeometry(pt.sz,10,10),new THREE.MeshBasicMaterial({color:pt.col}));
    n.position.copy(pos); globe.add(n);
    const ring=new THREE.Mesh(new THREE.RingGeometry(pt.sz*1.5,pt.sz*2.2,16),new THREE.MeshBasicMaterial({color:pt.col,transparent:true,opacity:.4,side:THREE.DoubleSide}));
    ring.lookAt(pos.clone().multiplyScalar(2)); ring.position.copy(pos); globe.add(ring);
    const pl=new THREE.PointLight(pt.col,.4,8); pl.position.copy(pos); globe.add(pl);
    gNodes.push({n,ring,pl});
  });
  const conns=[[0,1],[0,5],[1,2],[1,3],[1,6],[0,4],[2,3],[0,7]]; const arcOs=[];
  conns.forEach(([ai,bi])=>{
    const pa=ll2v(gPts[ai].lat,gPts[ai].lon,14.8),pb=ll2v(gPts[bi].lat,gPts[bi].lon,14.8);
    const mid=pa.clone().add(pb).multiplyScalar(.5).normalize().multiplyScalar(18);
    const curve=new THREE.QuadraticBezierCurve3(pa,mid,pb);
    const arc=new THREE.Line(new THREE.BufferGeometry().setFromPoints(curve.getPoints(40)),new THREE.LineBasicMaterial({color:0x00ffcc,transparent:true,opacity:.35}));
    threeScene.add(arc);
    const pulse=new THREE.Mesh(new THREE.SphereGeometry(.25,8,8),new THREE.MeshBasicMaterial({color:0x00ffcc}));threeScene.add(pulse);
    arcOs.push({curve,pulse,pct:Math.random(),arc});
  });
  const s2g=new THREE.BufferGeometry(); const s2v=[]; for(let i=0;i<1200;i++) s2v.push((Math.random()-.5)*400,(Math.random()-.5)*400,(Math.random()-.5)*400);
  s2g.setAttribute('position',new THREE.Float32BufferAttribute(s2v,3)); threeScene.add(new THREE.Points(s2g,new THREE.PointsMaterial({color:0xffffff,size:.3,transparent:true,opacity:.5})));
  const hr=new THREE.Mesh(new THREE.TorusGeometry(20,.15,8,80),new THREE.MeshBasicMaterial({color:0x00ffcc,transparent:true,opacity:.3})); hr.rotation.x=Math.PI/4; threeScene.add(hr);
  const hr2=hr.clone(); hr2.rotation.x=-Math.PI/4; hr2.rotation.z=Math.PI/3; threeScene.add(hr2);
  threeSceneObjects.animate=(t)=>{
    globe.rotation.y=t*.08;
    gNodes.forEach(({ring},i)=>{ring.material.opacity=.2+.3*Math.abs(Math.sin(t*1.5+i*.7));const s=1+.2*Math.sin(t*2+i);ring.scale.set(s,s,s);});
    arcOs.forEach(e=>{e.pct=(e.pct+.006)%1;e.pulse.position.copy(e.curve.getPoint(e.pct));e.arc.material.opacity=.2+.2*Math.sin(t*1.5+e.pct*5);});
    hr.rotation.z=t*.15; hr2.rotation.y=t*.12; hr.material.opacity=.2+.1*Math.sin(t*2); hr2.material.opacity=.15+.1*Math.cos(t*2.5);
    br.intensity=1+.5*Math.sin(t*.5); tk.intensity=.4+.2*Math.sin(t*.7);
  };
}

// ── Zoom & Pan Controls for Main Media ──
let mediaScale = 1;
let mediaX = 0;
let mediaY = 0;
let isDraggingMedia = false;
let startDragX = 0, startDragY = 0;
let lastRenderedMediaStr = '';

document.addEventListener('DOMContentLoaded', () => {
    const stage = document.getElementById('mainMediaStage');
    if(!stage) return;

    stage.addEventListener('wheel', (e) => {
        e.preventDefault();
        const media = stage.querySelector('img, video');
        if(!media) return;

        const zoomIntensity = 0.1;
        if(e.deltaY < 0) {
            mediaScale += zoomIntensity;
        } else {
            mediaScale -= zoomIntensity;
        }
        mediaScale = Math.max(0.5, Math.min(mediaScale, 5));
        updateMediaTransform(media);
    });

    stage.addEventListener('mousedown', (e) => {
        const media = stage.querySelector('img, video');
        if(!media) return;
        isDraggingMedia = true;
        startDragX = e.clientX - mediaX;
        startDragY = e.clientY - mediaY;
        stage.style.cursor = 'grabbing';
    });

    window.addEventListener('mouseup', () => {
        isDraggingMedia = false;
        if(stage) stage.style.cursor = 'grab';
    });

    window.addEventListener('mousemove', (e) => {
        if(!isDraggingMedia) return;
        const media = stage.querySelector('img, video');
        if(!media) return;
        
        mediaX = e.clientX - startDragX;
        mediaY = e.clientY - startDragY;
        updateMediaTransform(media);
    });
});

function updateMediaTransform(el) {
    el.style.transform = `translate(${mediaX}px, ${mediaY}px) scale(${mediaScale})`;
    el.style.transition = 'none';
}

function resetMediaTransform() {
    mediaScale = 1;
    mediaX = 0;
    mediaY = 0;
}
// ────────────────────────────────────────────────────────

// ── Clock ───────────────────────────────────────────────
setInterval(() => {
    const n = new Date();
    document.getElementById('clockSm').textContent =
        [n.getHours(), n.getMinutes(), n.getSeconds()].map(x => String(x).padStart(2,'0')).join(':');
}, 1000);

// ── Poll ────────────────────────────────────────────────
async function poll() {
    try {
        const d = await fetch(`/neptune/${SESSION_CODE}/api/state`).then(r => r.json());
        updateTimer(d.timer, d.session);
        updatePhase(d.session);
        updateAtmo(d.session.atmosphere);
        updateTeams(d.teams);
        handleActivity(d.broadcasts, d.injects);
        handleVote(d.vote);
        handleQuiz(d.quiz);
        handlePhaseContent(d.phaseContent);
        if (d.session.status === 'finished' && !endgameFired) fireEndgame(d.teams, d.session);
    } catch(e) {}
}

function handlePhaseContent(content) {
    const root = document.getElementById('phaseMediaQuiz');
    if (!root) return;
    const data = content || {};
    let media = Array.isArray(data.media) ? data.media : [];
    
    // Only show media if it's injected by moderator or if it's the national map
    media = media.filter(m => m && (m.isLive || (m.title && m.title.includes('Carte des Opérations'))));
    
    const questions = Array.isArray(data.questions) ? data.questions : [];
    const messages = Array.isArray(data.messages) ? data.messages : [];

    if (!media.length && !questions.length && !messages.length) {
        root.innerHTML = `<div class="feed-item">No phase content</div>`;
        renderMainMedia(null);
        return;
    }

    const mediaHtml = media.slice(0, 2).map(m => `<div class="feed-item"><div class="fi-ts">${(m.type || 'image').toUpperCase()}</div>${m.title || 'Media'}<div style="font-size:.65rem;opacity:.65">${m.caption || ''}</div></div>`).join('');
    const qHtml = questions.slice(0, 2).map(q => `<div class="feed-item"><div class="fi-ts">QUIZ ${(q.type || 'single_choice').toUpperCase()}</div>${q.question || 'Question'}</div>`).join('');
    const mHtml = messages.slice(0, 1).map(m => `<div class="feed-item ${m.type || 'info'}"><div class="fi-ts">MESSAGE</div>${m.content || ''}</div>`).join('');
    root.innerHTML = `${mediaHtml}${mHtml}${qHtml}`;
    renderMainMedia({ ...data, media });
}
setInterval(poll, 1000); poll();

function renderMainMedia(content) {
    renderMediaStage(document.getElementById('mainMediaStage'), content, 'MEDIA');
}

function renderMediaStage(stage, content, emptyLabel = 'MEDIA') {
    if (!stage) return;
    
    // We only reset transform if it's the main stage
    if(stage.id === 'mainMediaStage') resetMediaTransform();

    let media = Array.isArray(content?.media) ? content.media : [];
    media = media.filter(m => m && (m.isLive || (m.title && m.title.includes('Carte des Opérations'))));
    
    const preferred = media.find(m => m.isLive) || media[0] || null;
    
    if (SCENARIO_KEY === 'neptune_strike' && !preferred) {
        lastRenderedMediaStr = 'canvas';
        if (!document.getElementById('neptuneCanvasContainer')) {
            if (USE_THREE) {
                stage.innerHTML = `
                    <div id="neptuneCanvasContainer" class="position-relative overflow-hidden w-100 h-100 rounded" style="background:#000810; cursor:pointer;" onclick="openSceneInfo()">
                        <div id="alert-ov" style="position:absolute;inset:0;pointer-events:none;opacity:0;transition:opacity .1s;z-index:3;"></div>
                        <div class="scanlines" style="position:absolute;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,.04) 3px,rgba(0,0,0,.04) 4px);pointer-events:none;z-index:6;"></div>
                        <div class="vignette" style="position:absolute;inset:0;background:radial-gradient(ellipse at center,transparent 38%,rgba(0,0,0,.72) 100%);pointer-events:none;z-index:6;"></div>
                        <div id="hud" style="position:absolute;inset:0;pointer-events:none;z-index:7;font-family:'Share Tech Mono',monospace;font-size:12px;color:rgba(0,255,204,0.65);padding:12px;line-height:1.2">
                            <div class="d-flex justify-content-between">
                                <div>LAT: <span id="h-lat" class="hv">--</span> | LON: <span id="h-lon" class="hv">--</span></div>
                                <div>TIME: <span id="h-time" class="hv">--</span></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>VTMS: <span id="h-vtms" class="hv">--</span></div>
                                <div>SCADA: <span id="h-scada" class="hv">--</span></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>AIS: <span id="h-ais" class="hv">--</span></div>
                                <div>APT: <span id="h-apt" class="hv">--</span></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>THREAT: <span id="h-threat" class="hv">--</span></div>
                                <div>MARSEC: <span id="h-marsec" class="hv">--</span></div>
                            </div>
                            <div style="position:absolute;bottom:0;left:0;right:0;height:4px;background:#030c14;">
                                <div id="ts-fill" style="height:100%;background:linear-gradient(90deg,#00ffcc,#ffaa00,#ff3355);transition:width .4s;width:0%;"></div>
                            </div>
                        </div>
                        <div id="scene-title" class="position-absolute text-center text-white" style="top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;z-index:8;opacity:0;transition:opacity .5s;">
                            <div id="st-ph" style="font-family:'Share Tech Mono',monospace;font-size:12px;color:#00ffcc;letter-spacing:2px;margin-bottom:4px;"></div>
                            <div id="st-h" style="font-family:'Orbitron',monospace;font-weight:700;font-size:16px;letter-spacing:1px;text-shadow:0 0 20px rgba(0,255,204,0.5);text-transform:uppercase;"></div>
                            <div id="st-s" style="font-family:'Share Tech Mono',monospace;font-size:10px;color:rgba(255,255,255,0.5);letter-spacing:1px;"></div>
                        </div>
                        <div class="info-hint">[ CLICK SCENE FOR INFO ]</div>
                        <div id="sceneInfoOverlay" class="scene-info-overlay" onclick="closeSceneInfo(event)">
                            <div class="scene-info-box" onclick="event.stopPropagation()">
                                <button class="close-btn" onclick="closeSceneInfo(event)">&times;</button>
                                <div class="scene-info-title" id="infoTitle">--</div>
                                <div class="scene-info-sub" id="infoSub">--</div>
                                <div class="scene-info-desc" id="infoDesc">--</div>
                                <div class="scene-info-footer">CARTHAGE SHIELD SECUR-OPS · SITUATION REPORT</div>
                            </div>
                        </div>
                    </div>
                `;
                const container = document.getElementById('neptuneCanvasContainer');
                initThree(container);
            } else {
                stage.innerHTML = `
                    <div id="neptuneCanvasContainer" class="position-relative overflow-hidden w-100 h-100 rounded" style="background:#000; cursor:pointer;" onclick="openSceneInfo()">
                        <canvas id="bg-cv" style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none;"></canvas>
                        <canvas id="main-cv" style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none;"></canvas>
                        <div id="alert-ov" style="position:absolute;inset:0;pointer-events:none;opacity:0;transition:opacity .1s;z-index:3;"></div>
                        <div class="scanlines" style="position:absolute;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,.04) 3px,rgba(0,0,0,.04) 4px);pointer-events:none;z-index:6;"></div>
                        <div class="vignette" style="position:absolute;inset:0;background:radial-gradient(ellipse at center,transparent 38%,rgba(0,0,0,.72) 100%);pointer-events:none;z-index:6;"></div>
                        <div id="hud" style="position:absolute;inset:0;pointer-events:none;z-index:7;font-family:'Share Tech Mono',monospace;font-size:12px;color:rgba(0,255,204,0.65);padding:12px;line-height:1.2">
                            <div class="d-flex justify-content-between">
                                <div>LAT: <span id="h-lat" class="hv">--</span> | LON: <span id="h-lon" class="hv">--</span></div>
                                <div>TIME: <span id="h-time" class="hv">--</span></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>VTMS: <span id="h-vtms" class="hv">--</span></div>
                                <div>SCADA: <span id="h-scada" class="hv">--</span></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>AIS: <span id="h-ais" class="hv">--</span></div>
                                <div>APT: <span id="h-apt" class="hv">--</span></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>THREAT: <span id="h-threat" class="hv">--</span></div>
                                <div>MARSEC: <span id="h-marsec" class="hv">--</span></div>
                            </div>
                            <div style="position:absolute;bottom:0;left:0;right:0;height:4px;background:#030c14;">
                                <div id="ts-fill" style="height:100%;background:linear-gradient(90deg,#00ffcc,#ffaa00,#ff3355);transition:width .4s;width:0%;"></div>
                            </div>
                        </div>
                        <div id="scene-title" class="position-absolute text-center text-white" style="top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;z-index:8;opacity:0;transition:opacity .5s;">
                            <div id="st-ph" style="font-family:'Share Tech Mono',monospace;font-size:12px;color:#00ffcc;letter-spacing:2px;margin-bottom:4px;"></div>
                            <div id="st-h" style="font-family:'Orbitron',monospace;font-weight:700;font-size:16px;letter-spacing:1px;text-shadow:0 0 20px rgba(0,255,204,0.5);text-transform:uppercase;"></div>
                            <div id="st-s" style="font-family:'Share Tech Mono',monospace;font-size:10px;color:rgba(255,255,255,0.5);letter-spacing:1px;"></div>
                        </div>
                        <div class="info-hint">[ CLICK SCENE FOR INFO ]</div>
                        <div id="sceneInfoOverlay" class="scene-info-overlay" onclick="closeSceneInfo(event)">
                            <div class="scene-info-box" onclick="event.stopPropagation()">
                                <button class="close-btn" onclick="closeSceneInfo(event)">&times;</button>
                                <div class="scene-info-title" id="infoTitle">--</div>
                                <div class="scene-info-sub" id="infoSub">--</div>
                                <div class="scene-info-desc" id="infoDesc">--</div>
                                <div class="scene-info-footer">CARTHAGE SHIELD SECUR-OPS · SITUATION REPORT</div>
                            </div>
                        </div>
                    </div>
                `;
                initCanvas();
                if (!animationLoopRunning) {
                    animationLoopRunning = true;
                    renderLoop();
                }
            }
        }
        const sceneOrder = ['ocean', 'cable', 'port', 'hack', 'command'];
        const targetScene = sceneOrder[latestSessionPhaseIdx ?? 0] || 'ocean';
        if (targetScene !== G.cinScene) {
            setScene(targetScene);
        }
        return;
    }
    
    if (!preferred || !preferred.url) {
        lastRenderedMediaStr = 'empty';
        stage.innerHTML = `<div class="media-stage-empty">${emptyLabel}</div>`;
        return;
    }

    const mediaStr = `${preferred.id || ''}:${preferred.url || ''}:${preferred.title || ''}:${preferred.autoplay || ''}:${preferred.loop || ''}:${preferred.muted || ''}`;
    if (mediaStr === lastRenderedMediaStr) {
        return;
    }
    lastRenderedMediaStr = mediaStr;

    if ((preferred.type || 'image') === 'video') {
        stage.innerHTML = `<video src="${preferred.url}" ${preferred.autoplay ? 'autoplay' : ''} ${preferred.loop ? 'loop' : ''} ${preferred.muted !== false ? 'muted' : ''} playsinline controls></video>`;
        return;
    }
    stage.innerHTML = `<img src="${preferred.url}" alt="${preferred.title || 'media'}" style="pointer-events: none;">`;
}

// ── Timer ───────────────────────────────────────────────
function updateTimer(timer, session) {
    let secs;
    if (timer.isRunning && timer.endsAt)
        secs = Math.max(0, Math.round((new Date(timer.endsAt) - Date.now()) / 1000));
    else
        secs = timer.remainingSeconds ?? 0;

    const el = document.getElementById('mainTimer');
    const sb = document.getElementById('statusBadge');
    el.textContent = fmt(secs);
    el.className = 'timer-big' + (secs <= 60 ? ' danger' : secs <= 180 ? ' warn' : '');

    if (timer.isRunning) {
        sb.textContent = 'RUNNING'; sb.className = 'status-badge running';
    } else {
        sb.textContent = session.status === 'finished' ? ('FINISHED') : ('AWAITING');
        sb.className = 'status-badge';
    }
}

// ── Phase ───────────────────────────────────────────────
function updatePhase(session) {
    const idx = session.currentPhaseIndex;
    latestSessionPhaseIdx = idx;
    document.getElementById('phaseLabel').textContent = session.currentPhase?.name ?? '—';
    for (let i = 0; i < TOTAL_PHASES; i++) {
        const el = document.getElementById('ph-seg-' + i);
        if (!el) continue;
        el.className = 'ph-seg' + (i < idx ? ' done' : i === idx ? ' active' : '');
    }
}

// ── Atmosphere ──────────────────────────────────────────
function updateAtmo(mode) {
    if (mode === lastAtmo) return;
    lastAtmo = mode;
    document.body.className = '';
    if (mode && mode !== 'calm' && mode !== 'neutral') document.body.classList.add('atmo-' + mode);
    if (mode === 'crisis' || mode === 'hacked') document.body.classList.add('scanlines');
    addFeed(mode === 'crisis' ? 'alert' : mode === 'victory' ? 'success' : 'info',
        IS_EN ? `ATMOSPHERE → ${mode.toUpperCase()}` : `ATMOSPHÈRE → ${mode.toUpperCase()}`);
        
    if (mode === 'crisis') playLocalSound('crisis');
    else if (mode === 'tension') playLocalSound('tension');
    else if (mode === 'hacked') playLocalSound('hacked');
}

// ── Teams ───────────────────────────────────────────────
function badgeImgHtml(badge) {
    if (badge.image) {
        return `<img src="${badge.image}" alt="${badge.name}" style="max-height:60px; max-width:60px; width:auto; height:auto;" onerror="this.replaceWith(document.createTextNode('${badge.icon}'))">`;
    }
    return `<span style="font-size:2rem">${badge.icon}</span>`;
}

function triggerAnim(el, cls, dur = 900) {
    el.classList.remove(cls);
    void el.offsetWidth; // reflow
    el.classList.add(cls);
    setTimeout(() => el.classList.remove(cls), dur);
}

function fireDomination(t, delta, isBadge) {
    const ov = document.getElementById('domOverlay');
    const bImg = document.getElementById('domBadge');
    const dDelta = document.getElementById('domDelta');
    
    document.getElementById('domIcon').innerHTML = t.logoPath ? `<img src="${t.logoPath}" style="width:72px;height:72px;object-fit:contain">` : `<span style="font-size:3.5rem">${t.icon}</span>`;
    document.getElementById('domName').textContent = t.name;
    document.getElementById('domLabel').textContent = isBadge ? ("NEW BADGE UNLOCKED!") : ("POINTS AWARDED");
    
    if (isBadge) {
        ov.classList.add('badge-dom');
        bImg.style.display = 'flex';
        bImg.innerHTML = badgeImgHtml(t.badge);
        dDelta.style.display = 'none';
        
        playTone(659, .6, 'square', 0.4);
        setTimeout(() => playTone(880, 1.2, 'square', 0.4), 100);
    } else {
        ov.classList.remove('badge-dom');
        bImg.style.display = 'none';
        dDelta.textContent = delta > 0 ? '+' + delta : delta;
        dDelta.style.display = 'block';
        
        playTone(440, .2, 'square', .2);
        setTimeout(() => playTone(554, .3, 'square', .2), 150);
        setTimeout(() => playTone(659, .6, 'square', .25), 300);
    }
    
    ov.classList.add('show');
    clearTimeout(ov.domTimer);
    ov.domTimer = setTimeout(() => ov.classList.remove('show'), 2000);
}

const prevBadge = {}; // track badge tier per team

function updateTeams(teams) {
    if (!teams) return;
    const leftRail = document.getElementById('leftTeamRail');
    const rightRail = document.getElementById('rightTeamRail');
    const leftCount = Math.ceil(teams.length / 2);

    teams.forEach((t, index) => {
        let el = document.getElementById('tc-' + t.id);
        if (!el) {
            el = document.createElement('div');
            el.id = 'tc-' + t.id;
            el.className = 'team-card card module-card border-0';
            el.style.cssText = `--tc:${t.color};`;
            el.innerHTML = `
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="team-header">
                    <div class="team-logo-wrap">
                        ${t.logoPath ? `<img src="${t.logoPath}" class="t-icon-img">` : `<span class="t-icon">${t.icon}</span>`}
                    </div>
                    <div class="team-copy">
                        <div class="t-name" id="tn-${t.id}" style="color: var(--tc)">${t.name}</div>
                        <div class="t-role" id="trl-${t.id}">${t.roleLabel}</div>
                    </div>
                </div>
                
                <div class="team-stats-grid">
                    <div class="team-stat-box">
                        <div class="team-badge-label">Badge</div>
                        <div class="t-badge" id="tb-${t.id}">${badgeImgHtml(t.badge)}</div>
                        <div class="t-badge-nm" id="tbn-${t.id}">${t.badge.name}</div>
                    </div>
                    
                    <div class="team-stat-box team-score-box">
                        <div class="team-score-label" id="tsl-${t.id}">${t.isScored ? 'Score' : 'Status'}</div>
                        <div class="t-score" id="ts-${t.id}">${t.isScored ? t.score : 'MENTOR'}</div>
                        <div class="t-delta" id="td-${t.id}"></div>
                    </div>
                </div>

                <div class="team-footer">
                    <div class="t-online">
                        <span class="dot-on"></span>
                        <span class="t-online-text">Online</span>
                        <span class="t-online-count" id="ton-${t.id}">${t.onlineCount}</span>
                        <span class="ms-1" style="color:rgba(255,255,255,.6)">/</span>
                        <span class="ms-1" id="tpc-${t.id}" style="color:rgba(255,255,255,.6);font-weight:700">${t.playerCount}</span>
                    </div>
                </div>`;
            prevScores[t.id] = t.score;
            prevBadge[t.id]  = t.badge.name;
        } else {
            const prev      = prevScores[t.id] ?? t.score;
            const prevBdg   = prevBadge[t.id]  ?? t.badge.name;
            const scoreEl   = document.getElementById('ts-' + t.id);
            const badgeEl   = document.getElementById('tb-' + t.id);

            // â”€â”€ Score changed â”€â”€
            if (t.isScored && t.score !== prev) {
                const delta = t.score - prev;
                const dEl   = document.getElementById('td-' + t.id);
                dEl.textContent = (delta > 0 ? '+' : '') + delta;
                dEl.style.color = delta > 0 ? '#2dc653' : '#ef4444';
                dEl.style.display = 'block';
                dEl.style.animation = 'none'; void dEl.offsetWidth;
                dEl.style.animation = 'deltaUp .9s ease forwards';
                setTimeout(() => dEl.style.display = 'none', 920);
                prevScores[t.id] = t.score;

                if (delta > 0) {
                    // Score flash on the number
                    triggerAnim(scoreEl, 'score-pop', 750);
                    // Card glow
                    triggerAnim(el, 'score-glow', 900);
                    // Domination overlay
                    fireDomination(t, delta, false);
                } else {
                    playTone(220, .18, 'sine', .12);
                }
            }
            scoreEl.textContent = t.isScored ? t.score : 'MENTOR';
            document.getElementById('tsl-' + t.id).textContent = t.isScored ? 'Score' : 'Status';
            document.getElementById('tn-' + t.id).textContent = t.name;
            document.getElementById('trl-' + t.id).textContent = t.roleLabel;

            // â”€â”€ Badge tier changed (unlock animation!) â”€â”€
            if (t.badge.name !== prevBdg) {
                prevBadge[t.id] = t.badge.name;
                badgeEl.innerHTML = badgeImgHtml(t.badge);
                // Apply unlock animation to the img/span inside
                const img = badgeEl.querySelector('img') || badgeEl.querySelector('span');
                if (img) { img.classList.add('badge-unlocked'); }
                // Log in feed
                addFeed('success', IS_EN ? `ðŸ… ${t.name} â€” New badge: <strong>${t.badge.name}</strong>` : `ðŸ… ${t.name} â€” Nouveau badge : <strong>${t.badge.name}</strong>`);
                // Domination overlay for badge
                fireDomination(t, 0, true);
            } else {
                // Just update normally (no animation)
                badgeEl.innerHTML = badgeImgHtml(t.badge);
            }

            document.getElementById('tbn-' + t.id).textContent = t.badge.name;
            document.getElementById('ton-' + t.id).textContent = t.onlineCount;
            const tpcEl = document.getElementById('tpc-' + t.id);
            if (tpcEl) tpcEl.textContent = t.playerCount;
        }

        const targetRail = index < leftCount ? leftRail : rightRail;
        if (targetRail && el.parentElement !== targetRail) {
            targetRail.appendChild(el);
        }
    });
}
// â”€â”€ Activity feed â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function handleActivity(broadcasts, injects) {
    if (broadcasts?.length) {
        const b = broadcasts[0];
        if (b.id > lastBcId) {
            lastBcId = b.id;
            if (b.isPhantom) showPhantom(b.message);
            else addFeed(b.type, b.message);
        }
    }
    if (injects?.length) {
        const inj = injects[0];
        if (inj.id > lastInjectId) {
            lastInjectId = inj.id;
            const d = document.createElement('div');
            d.className = 'feed-item alert';
            d.innerHTML = `<div class="fi-ts">${inj.tag}</div>${inj.content}`;
            const log = document.getElementById('injectLog');
            if (log) log.insertBefore(d, log.firstChild);
            
            // Unified feed: also push inject to the announcements feed (announceLog)
            addFeed('alert', `<strong>${inj.tag}:</strong> ${inj.content}`);

            playTone(220, .3, 'square', .15);
        }
    }
}

function addFeed(type, msg) {
    const fc = document.getElementById('announceLog');
    const n = new Date();
    const ts = [n.getHours(), n.getMinutes()].map(x => String(x).padStart(2,'0')).join(':');
    const d = document.createElement('div');
    d.className = `feed-item ${type}`;
    d.innerHTML = `<div class="fi-ts">${ts}</div>${msg}`;
    fc.insertBefore(d, fc.firstChild);
    while (fc.children.length > 15) fc.removeChild(fc.lastChild);
}

// â”€â”€ Vote â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
let lastVoteId = null, lastVoteOpen = null;
function handleVote(vote) {
    const el = document.getElementById('voteWidget');
    if (!vote) {
        el.innerHTML = `<div class="vote-q fst-italic" style="opacity:.5">${'No vote in progress'}</div>`;
        lastVoteId  = null;
        lastVoteOpen = null;
        return;
    }

    const isSecretOpen = !!(vote.isSecret && (vote.is_open ?? vote.isOpen));
    const tallyData = vote.tally || {};
    const hasVisibleTally = Object.keys(tallyData).length > 0;
    const total = Object.values(tallyData).reduce((a,b) => a+b, 0) || 1;
    const isOpen = (vote.is_open ?? vote.isOpen ?? true) === true;
    const winner = !isOpen ? Object.entries(tallyData).sort((a,b)=>b[1]-a[1])[0]?.[0] : null;

    // Detect vote just closed â†’ flash announcement
    if (lastVoteId === vote.id && lastVoteOpen === true && !isOpen && winner) {
        addFeed('success', IS_EN ? `ðŸ—³ï¸ Vote closed â€” National choice: <strong>${winner}</strong>` : `ðŸ—³ï¸ Vote fermÃ© â€” Choix national: <strong>${winner}</strong>`);
        playTone(523, .4, 'triangle', .3);
    }
    lastVoteId   = vote.id;
    lastVoteOpen = isOpen;

    const barsHtml = (vote.options||[]).map(o => {
        const count  = tallyData[o.key] ?? 0;
        const pct    = Math.round(count / total * 100);
        const isWin  = winner === o.key;
        const safeColor = o.color || 'var(--bs-theme)';
        const safeLabel = o.label || `Option ${o.key}`;
        return `<div class="vote-bar-row" style="${isWin ? 'opacity:1' : (winner ? 'opacity:.55' : '')}">
            <span class="vb-lbl">
                <span class="vb-key" style="color:${safeColor}">${isWin ? 'ðŸ†' : o.key}</span>
                <span class="vb-text" title="${safeLabel}">${safeLabel}</span>
            </span>
            <div class="vb-track"><div class="vb-fill" style="width:${pct}%;background:${safeColor};${isWin ? `box-shadow:0 0 6px ${safeColor}` : ''}"></div></div>
            <span class="vb-count">${count} <span style="font-size:.65rem;opacity:.6">(${pct}%)</span></span>
        </div>`;
    }).join('');

    if (isSecretOpen && !hasVisibleTally) {
        el.innerHTML = `
            <div class="vote-q">${vote.question ?? 'Vote stratégique en cours'} 🔒</div>
            <div style="font-size:.8rem;opacity:.7;text-align:center;padding:10px 0">Vote secret en cours. Les resultats seront visibles a la cloture.</div>`;
        return;
    }

    el.innerHTML = `
        <div class="vote-q">${vote.question ?? 'Vote stratégique en cours'}${vote.isSecret ? ' 🔒' : ''}</div>
        <div class="vote-bars">${barsHtml}</div>
        ${winner ? `<div style="text-align:center;margin-top:8px;font-family:'Space Mono',monospace;font-size:.8rem;color:#22c55e;font-weight:700">
            ✅ RÉSULTAT FINAL : ${winner}
        </div>` : `<div style="font-size:.72rem;opacity:.4;text-align:center;margin-top:4px">${total} vote${total>1?'s':''} reçus</div>`}`;
}

function handleQuiz(quiz) {
    const el = document.getElementById('quizWidget');
    const mainQuestion = document.getElementById('mainQuizQuestion');
    const mainChoices = document.getElementById('mainQuizChoices');
    if (!el) return;
    if (!quiz) {
        el.innerHTML = `<div class="vote-q fst-italic" style="opacity:.5">No question in progress</div>`;
        if (mainQuestion) mainQuestion.textContent = 'Quiz question';
        if (mainChoices) mainChoices.innerHTML = `<span class="hero-quiz-choice empty">Answers will appear here</span>`;
        return;
    }

    const optionsHtml = (quiz.options || []).map(o => `
        <div class="vote-bar-row">
            <span class="vb-lbl"><span class="vb-key" style="color:${o.color || '#60a5fa'}">${o.key}</span><span class="vb-text">${o.label}</span></span>
        </div>
    `).join('');

    const resultHtml = (quiz.results || []).slice(0, 6).map(r => `
        <div style="font-size:.72rem;opacity:.85">${r.teamName}: ${r.answerKey || 'â€”'} => <strong>${r.awardedPoints} pts</strong></div>
    `).join('');

    el.innerHTML = `
        <div class="vote-q">${quiz.question ?? ('Quiz Question')}</div>
        <div style="font-size:.72rem;opacity:.65;margin-bottom:8px">Type: ${(quiz.type || 'single_choice').replace('_',' ')} Â· ${'Answers:'} ${quiz.answerCount || 0}</div>
        <div class="vote-bars">${optionsHtml}</div>
        ${resultHtml ? `<div style="margin-top:8px">${resultHtml}</div>` : ''}
    `;
    if (mainQuestion) {
        mainQuestion.textContent = (quiz.question || 'Quiz question').trim();
    }
    if (mainChoices) {
        const heroChoices = (quiz.options || []).length
            ? (quiz.options || []).map(o => `<span class="hero-quiz-choice">${o.key}. ${o.label}</span>`).join('')
            : `<span class="hero-quiz-choice empty">Waiting for answer choices</span>`;
        mainChoices.innerHTML = heroChoices;
    }
}

// â”€â”€ Phantom â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function showPhantom(msg) {
    const el = document.getElementById('phMsg');
    el.textContent = msg; el.dataset.txt = msg;
    document.getElementById('phantomOv').classList.add('show');
    playLocalSound('tension');
    setTimeout(dismissPhantom, 12000);
}
function dismissPhantom() {
    document.getElementById('phantomOv').classList.remove('show');
}

// â”€â”€ Endgame â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function fireEndgame(teams, session) {
    endgameFired = true;
    const rankable = [...teams].filter(t => t.showInRanking !== false);
    const sorted = rankable.sort((a,b) => b.score - a.score);
    const top3 = sorted.slice(0, 3);
    const rest = sorted.slice(3);

    // Podium order: silver, gold, bronze
    const order = [1, 0, 2];
    const classes = ['p2', 'p1', 'p3'];
    const ranks = IS_EN ? ['🥈 2ND', '🥇 1ST', '🥉 3RD'] : ['🥈 2ÈME', '🥇 1ÈRE', '🥉 3ÈME'];

    document.getElementById('podiumEl').innerHTML = order.map((ri, ci) => {
        const t = top3[ri]; if (!t) return '';
        return `<div class="podium-slot ${classes[ci]}">
            <div class="pod-bar">
                <div class="pod-icon">${t.logoPath ? `<img src="${t.logoPath}" alt="logo">` : t.icon}</div>
                <div class="pod-name">${t.name}</div>
                <div class="pod-score">${t.score}</div>
                <div class="pod-badge" style="font-size: 2.5rem;">${t.badge.image
                    ? `<img src="${t.badge.image}" alt="${t.badge.name}" style="max-height:80px; width:auto;">`
                    : t.badge.icon}</div>
                <div class="pod-badge-name" style="font-size: 0.8rem; margin-top: 5px;">${t.badge.name}</div>
            </div>
            <div class="pod-base">${ranks[ci]}</div>
        </div>`;
    }).join('');

    if (rest.length) {
        document.getElementById('othersEl').innerHTML = rest.map((t,i) =>
            `<div class="other-tile">
                <div class="ot-rank">${i+4}${'TH'}</div>
                <div class="ot-name">${t.icon} ${t.name}</div>
                <div class="ot-score">${t.score} pts</div>
            </div>`).join('');
    }

    document.getElementById('endgameOv').classList.add('show');
    launchConfetti();
    playLocalSound('victory');
}

// â”€â”€ Confetti â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function launchConfetti() {
    const canvas = document.getElementById('confettiCanvas');
    canvas.width = window.innerWidth; canvas.height = window.innerHeight;
    const ctx = canvas.getContext('2d');
    const parts = Array.from({length:300}, () => ({
        x: Math.random() * canvas.width, y: -20 - Math.random() * 1000,
        w: 6 + Math.random() * 10, h: 10 + Math.random() * 20,
        vx: (Math.random()-.5)*5, vy: 3 + Math.random()*6,
        r: Math.random()*Math.PI*2, vr: (Math.random()-.5)*.25,
        c: ['#f59e0b','#fbbf24','#10b981','#3b82f6','#8b5cf6','#ef4444','#ec4899','#f43f5e']
            [Math.floor(Math.random()*8)],
    }));
    (function loop() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        parts.forEach(p => {
            p.x += p.vx; p.y += p.vy; p.r += p.vr; p.vy += .03;
            if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
            ctx.save(); ctx.translate(p.x,p.y); ctx.rotate(p.r);
            ctx.fillStyle = p.c; ctx.fillRect(-p.w/2,-p.h/2,p.w,p.h);
            ctx.restore();
        });
        if (parts.some(p => p.y < canvas.height)) requestAnimationFrame(loop);
    })();
}

// â”€â”€ Audio â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
let audioCtx = null;
function getAudio() { if (!audioCtx) audioCtx = new (window.AudioContext||window.webkitAudioContext)(); return audioCtx; }
function playTone(f, d, type='sine', vol=.2) {
    try {
        const c=getAudio(), o=c.createOscillator(), g=c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type=type; o.frequency.value=f;
        g.gain.setValueAtTime(vol,c.currentTime);
        g.gain.exponentialRampToValueAtTime(.001, c.currentTime+d);
        o.start(c.currentTime); o.stop(c.currentTime+d);
    } catch(e) {}
}
function playLocalSound(t) {
    if (t === 'tension') {
        const c=getAudio(),o=c.createOscillator(),g=c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type='sawtooth'; o.frequency.value=55;
        g.gain.setValueAtTime(0,c.currentTime);
        g.gain.linearRampToValueAtTime(.18, c.currentTime+.6);
        g.gain.linearRampToValueAtTime(0, c.currentTime+3);
        o.start(c.currentTime); o.stop(c.currentTime+3);
    }
    if (t === 'crisis') {
        const c=getAudio(),o=c.createOscillator(),g=c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type='square'; o.frequency.value=80; // deep bass alarm
        g.gain.setValueAtTime(.3,c.currentTime);
        o.frequency.exponentialRampToValueAtTime(120, c.currentTime+1);
        g.gain.linearRampToValueAtTime(0, c.currentTime+2);
        o.start(c.currentTime); o.stop(c.currentTime+2);
    }
    if (t === 'hacked') {
        const c=getAudio(),o=c.createOscillator(),g=c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type='sawtooth'; o.frequency.value=400; // chaotic
        g.gain.setValueAtTime(.2,c.currentTime);
        for(let i=0; i<10; i++) {
           o.frequency.setValueAtTime(400 + Math.random()*800, c.currentTime + i*.1);
        }
        g.gain.linearRampToValueAtTime(0, c.currentTime+1);
        o.start(c.currentTime); o.stop(c.currentTime+1);
    }
    if (t === 'victory') {
        [523,659,784,1047,1319].forEach((f,i) => setTimeout(() => playTone(f,.3,'triangle',.28), i*120));
    }
}

function fmt(s) {
    return `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
}
</script>
</body>
</html>



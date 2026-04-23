<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $scenario['title'] ?? 'CARTHAGE SHIELD' }} — Grand Écran</title>

{{-- HUD theme assets (same as app layout) --}}
<link href="{{ asset('hud/css/vendor.min.css') }}" rel="stylesheet">
<link href="{{ asset('hud/css/app.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* ═══════════════════════════════════════════════════════════
   CARTHAGE SHIELD — HUD DESIGN SYSTEM
   Palette: Crimson #c0152a | Gold #c9a050 | Deep #0d1117
   ═══════════════════════════════════════════════════════════ */

/* ── CSS Tokens ───────────────────────────────────────────── */
:root {
    --cs-gold:    #c9a050;
    --cs-gold2:   #f0c060;
    --cs-red:     #c0152a;
    --cs-red2:    #9b0e20;
    --cs-dark:    #0d1117;
    --cs-dark2:   rgba(10,5,5,.92);
    --cs-border:  rgba(201,160,80,.25);
    --cs-glow-g:  rgba(201,160,80,.35);
    --cs-glow-r:  rgba(192,21,42,.45);
}

/* ── Atmosphere transitions ───────────────────────────────── */
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

/* ── Layout ─────────────────────────────────────────────────────── */
.cs-layout {
    display: grid;
    grid-template-rows: 88px 1fr 220px;
    height: 100vh;
    padding: 12px 12px 12px;
    gap: 10px;
    position: relative; z-index: 1;
}

/* ═══════════════════════════════════════════════════
   HEADER — Gold/Crimson HUD Bar with center medallion
   ═══════════════════════════════════════════════════ */
.cs-header {
    position: relative;
    display: flex; align-items: center;
    height: 88px;
    /* Deep dark + gold/red gradient */
    background: linear-gradient(90deg,
        rgba(10,4,4,.98) 0%,
        rgba(24,8,8,.97) 30%,
        rgba(35,12,8,.97) 50%,
        rgba(24,8,8,.97) 70%,
        rgba(10,4,4,.98) 100%
    );
    /* Gold top border, red bottom accent */
    border-top: 2px solid var(--cs-gold);
    border-bottom: 1px solid var(--cs-red);
    border-left: 1px solid var(--cs-border);
    border-right: 1px solid var(--cs-border);
    border-radius: 10px;
    backdrop-filter: blur(12px);
    box-shadow:
        0 0 40px rgba(192,21,42,.2),
        inset 0 1px 0 rgba(201,160,80,.15),
        inset 0 -1px 0 rgba(192,21,42,.2);
    overflow: visible;  /* allow medallion to overflow */
    padding: 0 28px;
}

/* Subtle inner gold shimmer line across top */
.cs-header::before {
    content: '';
    position: absolute; top: 0; left: 10%; right: 10%; height: 1px;
    background: linear-gradient(90deg, transparent, var(--cs-gold2), transparent);
    opacity: .5;
}

/* ── Left block: title + phase bar ───────────────────── */
.cs-left {
    display: flex; flex-direction: column; justify-content: center; gap: 5px;
    flex: 1; min-width: 0;
}
.logo-txt {
    font-family: 'Space Mono', monospace;
    font-weight: 700; font-size: 1.3rem; letter-spacing: 4px;
    /* Gold gradient text */
    background: linear-gradient(90deg, var(--cs-gold) 0%, var(--cs-gold2) 50%, var(--cs-gold) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: none;
}
.logo-txt .shield-word {
    background: linear-gradient(90deg, var(--cs-red) 0%, #e83352 50%, var(--cs-red) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.scenario-sub {
    font-size: .72rem;
    color: rgba(201,160,80,.55);
    font-family: 'Space Mono', monospace;
    letter-spacing: 1px;
}

/* ── Phase bar ───────────────────────────────────────── */
.phase-bar { display: flex; align-items: center; gap: 5px; margin-top: 2px; }
.ph-seg {
    width: 28px; height: 4px; border-radius: 2px;
    background: rgba(201,160,80,.12); transition: all .5s;
}
.ph-seg.done  { background: rgba(201,160,80,.5); opacity: .8; }
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

/* ── CENTER MEDALLION ────────────────────────────────── */
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
        rgba(201,160,80,.12) 15%,
        rgba(201,160,80,.25) 50%,
        rgba(201,160,80,.12) 85%,
        transparent 100%
    );
    border-top: 1px solid rgba(201,160,80,.3);
    border-bottom: 1px solid rgba(201,160,80,.3);
    border-radius: 4px;
}
/* Corner decorations */
.cs-medallion::after {
    content: '✦';
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
        drop-shadow(0 0 18px rgba(201,160,80,.7))
        drop-shadow(0 0 6px rgba(192,21,42,.5));
    animation: medalFloat 4s ease-in-out infinite;
}
@keyframes medalFloat {
    0%,100% { transform: translateY(0);    filter: drop-shadow(0 0 18px rgba(201,160,80,.7)) drop-shadow(0 0 6px rgba(192,21,42,.5)); }
    50%      { transform: translateY(-4px); filter: drop-shadow(0 0 26px rgba(201,160,80,.9)) drop-shadow(0 0 10px rgba(192,21,42,.6)); }
}

/* ── Right block: status + timer ─────────────────────── */
.cs-right {
    display: flex; align-items: center; gap: 18px;
    flex-shrink: 0;
}
.status-badge {
    font-family: 'Space Mono', monospace;
    font-size: .6rem; padding: 4px 12px;
    border-radius: 99px; letter-spacing: 2px;
    border: 1px solid rgba(201,160,80,.25);
    color: rgba(201,160,80,.5);
}
.status-badge.running {
    border-color: #2dc653; color: #2dc653;
    background: rgba(45,198,83,.08);
    box-shadow: 0 0 8px rgba(45,198,83,.2);
}
.clock-sm {
    font-family: 'Space Mono', monospace;
    font-size: .78rem; color: rgba(201,160,80,.4);
    margin-top: 2px;
}
.timer-big {
    font-family: 'Space Mono', monospace;
    font-size: 2.8rem; font-weight: 700;
    /* Gold gradient timer */
    background: linear-gradient(180deg, var(--cs-gold2) 0%, var(--cs-gold) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1; transition: all .5s;
    filter: drop-shadow(0 0 12px rgba(201,160,80,.5));
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

/* ── Teams grid ─────────────────────────────────────────────────── */
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
        linear-gradient(180deg, rgba(20,5,5,.96) 0%, rgba(10,3,3,.96) 100%);
    background-clip: padding-box;
    padding: 14px 14px 12px;
    box-shadow:
        inset 0 0 32px rgba(201,160,80,.15),
        0 0 28px rgba(201,160,80,.2);
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
    animation: goldBorderShift 4s linear infinite;
}
@keyframes goldBorderShift {
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
    text-shadow: 0 0 8px rgba(201,160,80,.4);
}
.hero-media-stage {
    border: 1px solid rgba(201, 160, 80, .4);
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
        radial-gradient(circle at 50% 0%, rgba(113,15,24,.16) 0%, rgba(0,0,0,0) 48%),
        linear-gradient(180deg, rgba(15,5,6,.98) 0%, rgba(32,8,10,.96) 50%, rgba(16,5,7,.98) 100%);
    border-top: 2px solid rgba(201,160,80,.75);
    border-bottom: 2px solid rgba(201,160,80,.3);
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
        0 0 20px rgba(201,160,80,.08),
        inset 0 1px 0 rgba(201,160,80,.16),
        inset 0 -1px 0 rgba(201,160,80,.08);
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
        linear-gradient(90deg, rgba(201,160,80,0) 0%, rgba(201,160,80,.08) 50%, rgba(201,160,80,0) 100%);
    pointer-events: none;
}
.team-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 18px;
    right: 18px;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(201,160,80,.85), transparent);
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
    background: radial-gradient(circle at 50% 35%, rgba(201,160,80,.18) 0%, rgba(0,0,0,0) 68%);
    box-shadow: inset 0 0 0 1px rgba(201,160,80,.14);
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
    background: rgba(201,160,80,.04);
    border: 1px solid rgba(201,160,80,.12);
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
    border-color: rgba(201,160,80,.2);
}
.team-score-label,
.team-badge-label {
    font-family: 'Space Mono', monospace;
    font-size: .52rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(201,160,80,.52);
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
    text-shadow: 0 0 24px rgba(201,160,80,.28);
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

/* ── Score flash animation ──────────────── */
@keyframes scoreFlash {
    0%   { transform: scale(1); }
    20%  { transform: scale(1.22); color: #fff;
           text-shadow: 0 0 20px var(--cs-gold2), 0 0 60px var(--cs-gold); }
    60%  { transform: scale(1.06); }
    100% { transform: scale(1); }
}
.score-pop { animation: scoreFlash .65s cubic-bezier(.36,.07,.19,.97); }

/* ── Badge unlock — energetic spin───────────── */
@keyframes badgeBlast {
    0%   { transform: scale(0) rotate(-30deg);  opacity: 0; filter: brightness(5) blur(4px); }
    25%  { transform: scale(1.6) rotate(15deg); opacity: 1; filter: brightness(3) drop-shadow(0 0 30px gold); }
    45%  { transform: scale(.85) rotate(-8deg); filter: brightness(2) drop-shadow(0 0 18px gold); }
    65%  { transform: scale(1.15) rotate(4deg); }
    80%  { transform: scale(.96) rotate(-1deg); }
    100% { transform: scale(1)   rotate(0deg);  filter: drop-shadow(0 0 8px rgba(201,160,80,.7)); }
}
.badge-unlocked { animation: badgeBlast 1s cubic-bezier(.34,1.56,.64,1) forwards; }

/* ── Card gold glow on score ────────────── */
@keyframes cardGlow {
    0%,100% { box-shadow: none; border-color: rgba(201,160,80,.25); }
    40%     {
        box-shadow: 0 0 28px rgba(201,160,80,.5), 0 0 60px rgba(201,160,80,.2);
        border-color: rgba(201,160,80,.7);
    }
}
.score-glow { animation: cardGlow 1s ease; }

/* ══ DOMINATION OVERLAY ═══════════════════════════════ */
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
    filter: drop-shadow(0 0 40px rgba(201,160,80,.9));
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
    filter: drop-shadow(0 0 20px rgba(201,160,80,.8));
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
    color: rgba(201,160,80,.5);
    margin-top: 6px;
    animation: domDelta .5s .4s both;
}
/* Corner ornaments */
.dom-corner {
    position: absolute;
    font-size: 1.5rem; color: rgba(201,160,80,.35);
}
.dom-corner.tl { top: 20px; left: 24px; }
.dom-corner.tr { top: 20px; right: 24px; transform: scaleX(-1); }
.dom-corner.bl { bottom: 20px; left: 24px; transform: scaleY(-1); }
.dom-corner.br { bottom: 20px; right: 24px; transform: scale(-1); }

/* Badge domination (bigger flash for badge unlock) */
.dom-overlay.badge-dom .dom-delta { color: var(--cs-gold2); }
.dom-overlay.badge-dom { background: radial-gradient(ellipse at center, rgba(35,18,0,.97) 0%, rgba(0,0,0,.99) 100%); }

.t-icon-img { display: block; margin: 0 auto; width: 34px; height: 34px; object-fit: contain; filter: drop-shadow(0 0 12px rgba(201,160,80,.2)); }
.t-icon { font-size: 1.6rem; display: block; line-height: 1; color: var(--tc); text-shadow: 0 0 16px rgba(201,160,80,.16); }
.t-name { font-size: .96rem; font-weight: 900; letter-spacing: .8px; color: #fff; line-height: 1.02; text-transform: uppercase; }
.t-role {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 18px;
    padding: 1px 7px;
    border-radius: 999px;
    background: rgba(201,160,80,.08);
    border: 1px solid rgba(201,160,80,.12);
    font-size: .54rem;
    color: rgba(201,160,80,.68);
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
/* Badge image container — bigger + centered */
.t-badge img {
    width: 40px; height: 40px; object-fit: contain;
    filter: drop-shadow(0 0 15px rgba(201,160,80,.48));
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

/* ── Bottom widgets ─────────────────────────────────────────────── */
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

/* ══ ATMOSPHERE SYSTEM ═══════════════════════════════════════════ */
/* Each mode tints the whole dashboard */
body { transition: background 2.5s ease; }

/* ✔ CALM (default) — gold/dark */
body.atmo-calm {
    --atmo-accent: var(--cs-gold);
    --atmo-card-border: rgba(201,160,80,.25);
}
/* ⚠ TENSION — amber amber */
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

/* 🚨 CRISIS — deep red pulsing */
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

/* 👾 HACKED — glitch green matrix */
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

/* 🏆 VICTORY — all gold everything */
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
    box-shadow: 0 0 80px rgba(201,160,80,.3), inset 0 1px 0 rgba(240,192,96,.4) !important;
}
body.atmo-victory .team-card { border-color: rgba(201,160,80,.4); }

/* 🌍 NEUTRAL */
body.atmo-neutral { }

/* ── Screen shake (crisis/hacked alert) ──────────────── */
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

/* ── ENDGAME overlay ─────────────────────────────────────────────── */
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

.podium { display: flex; align-items: flex-end; gap: 30px; justify-content: center; margin-bottom: 20px; }
.podium-slot { text-align: center; width: 220px; display: flex; flex-direction: column; align-items: center; }
.pod-bar {
    width: 100%;
    border-radius: 16px 16px 0 0;
    display: flex; align-items: center; justify-content: flex-start; flex-direction: column;
    padding: 30px 15px;
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
    box-shadow: 0 -10px 40px rgba(0,0,0,0.5);
}
.pod-bar::before { content:''; position:absolute; inset:0; background: linear-gradient(180deg, rgba(255,255,255,0.1), transparent); pointer-events:none; }

.p1 { z-index: 3; animation: floatWinner 4s ease-in-out infinite; }
@keyframes floatWinner { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.p1 .pod-bar { border: 2px solid #fbbf24; background: linear-gradient(180deg, rgba(245,158,11,.4) 0%, rgba(245,158,11,.05) 100%); height: 380px; box-shadow: 0 0 60px rgba(245,158,11,.3); }
.p2 { z-index: 2; animation: floatWinner 4s ease-in-out infinite 1s; }
.p2 .pod-bar { border: 2px solid #94a3b8; background: linear-gradient(180deg, rgba(148,163,184,.3) 0%, rgba(148,163,184,.05) 100%); height: 280px; box-shadow: 0 0 40px rgba(148,163,184,.2); }
.p3 { z-index: 1; animation: floatWinner 4s ease-in-out infinite 2s; }
.p3 .pod-bar { border: 2px solid #b45309; background: linear-gradient(180deg, rgba(180,83,9,.3) 0%, rgba(180,83,9,.05) 100%); height: 220px; box-shadow: 0 0 40px rgba(180,83,9,.2); }

.pod-icon { font-size: 3.5rem; margin-bottom: 10px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5)); }
.pod-icon img { width: 70px !important; height: 70px !important; object-fit: contain; }
.pod-name { font-size: 1.6rem; font-weight: 900; margin-bottom: 5px; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
.pod-score { font-family: 'Space Mono', monospace; font-size: 2.8rem; color: #fff; font-weight: 700; text-shadow: 0 0 20px rgba(255,255,255,0.5); margin-bottom: 15px; }
.pod-badge { margin-top: auto; }
.pod-badge img { width: 110px !important; height: 110px !important; object-fit: contain; filter: drop-shadow(0 0 20px rgba(255,255,255,0.3)) !important; transition: transform 0.3s; }
.p1 .pod-badge img { width: 140px !important; height: 140px !important; object-fit: contain; filter: drop-shadow(0 0 30px rgba(245,158,11,0.6)) !important; animation: pulseBadge 2s infinite; }
@keyframes pulseBadge { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }

.pod-base {
    width: 100%;
    background: rgba(255,255,255,.1); padding: 12px;
    border-radius: 0 0 16px 16px;
    font-family: 'Space Mono', monospace; font-size: 1.1rem; color: #fff; font-weight: 700; letter-spacing: 2px;
    box-shadow: inset 0 2px 0 rgba(255,255,255,0.2);
}

.others-row { display: flex; gap: 20px; margin-top: 40px; justify-content: center; flex-wrap: wrap; max-width: 900px; }
.other-tile {
    text-align: center; padding: 15px 25px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 12px;
    backdrop-filter: blur(5px);
    transition: transform 0.2s, background 0.2s;
    min-width: 180px;
}
.other-tile:hover { transform: translateY(-3px); background: rgba(255,255,255,.1); }
.ot-rank { font-family: 'Space Mono', monospace; font-size: 0.8rem; color: rgba(255,255,255,.5); letter-spacing: 1px; margin-bottom: 5px; }
.ot-name { font-size: 1.2rem; font-weight: 700; margin-bottom: 5px; }
.ot-score { font-family: 'Space Mono', monospace; font-size: 1.4rem; color: var(--bs-theme); font-weight: bold; }

#confettiCanvas { position: fixed; inset: 0; pointer-events: none; z-index: 960; }

::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); }
</style>
</head>
<body>
<div class="app-cover"></div>

{{-- PHANTOM overlay --}}
<div class="phantom-ov" id="phantomOv" onclick="dismissPhantom()">
    <div class="ph-label">MESSAGE INTERCEPTÉ — {{ $scenario['attacker_name'] ?? 'PHANTOM GRID' }}</div>
    <div class="ph-skull">{{ $scenario['attacker_icon'] ?? '☠️' }}</div>
    <div class="ph-msg" id="phMsg" data-txt=""></div>
    <div class="ph-dismiss">CLIQUER POUR FERMER</div>
</div>

{{-- ENDGAME overlay --}}
<div class="endgame-ov" id="endgameOv">
    <canvas id="confettiCanvas"></canvas>
    <div class="eg-label">FIN DE L'EXERCICE — {{ $scenario['attacker_name'] ?? 'PHANTOM GRID' }}</div>
    <div class="eg-title">{{ $scenario['title'] ?? 'CARTHAGE SHIELD' }}</div>
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
    <div class="dom-label" id="domLabel">POINTS ATTRIBUÉS</div>
</div>

{{-- MAIN LAYOUT --}}
<div class="cs-layout">

    {{-- ══ HEADER ══ --}}
    <div class="cs-header">

        {{-- LEFT: Title + Phase bar --}}
        <div class="cs-left">
            <div class="logo-txt">CARTHAGE <span class="shield-word">SHIELD</span></div>
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
            <img src="/cs-assets/game_logo.png" class="cs-medal-img" alt="Carthage Shield">
        </div>

        {{-- RIGHT: Status + clock + big timer --}}
        <div class="cs-right">
            <div class="d-flex flex-column align-items-end gap-1">
                <div class="status-badge" id="statusBadge">EN ATTENTE</div>
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
            <div class="widget-hdr"><i class="bi bi-hand-thumbs-up"></i>Vote Stratégique</div>
            <div id="voteWidget">
                <div class="vote-q fst-italic">Aucun vote en cours</div>
            </div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-patch-question"></i>Question Quiz</div>
            <div id="quizWidget">
                <div class="vote-q fst-italic">Aucune question en cours</div>
            </div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-lightning-charge"></i>Injections Actives</div>
            <div class="feed-list" id="injectLog"></div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-collection-play"></i>Media & Quiz de Phase</div>
            <div class="feed-list" id="phaseMediaQuiz"></div>
        </div>
    </div>

</div>

{{-- HUD JS (theme, Bootstrap) --}}
<script src="{{ asset('hud/js/vendor.min.js') }}"></script>
<script src="{{ asset('hud/js/app.min.js') }}"></script>

<script>
const SESSION_CODE = '{{ $session->code }}';
const TOTAL_PHASES = {{ count($scenario['phases']) }};

let lastBcId = 0, lastInjectId = 0, lastAtmo = '', endgameFired = false;
let prevScores = {};

// ── Zoom & Pan Controls for Main Media ────────────────────────────
let mediaScale = 1;
let mediaX = 0;
let mediaY = 0;
let isDraggingMedia = false;
let startDragX = 0, startDragY = 0;

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
// ──────────────────────────────────────────────────────────────────

// ── Clock ─────────────────────────────────────────────────────────
setInterval(() => {
    const n = new Date();
    document.getElementById('clockSm').textContent =
        [n.getHours(), n.getMinutes(), n.getSeconds()].map(x => String(x).padStart(2,'0')).join(':');
}, 1000);

// ── Poll ──────────────────────────────────────────────────────────
async function poll() {
    try {
        const d = await fetch(`/cs/${SESSION_CODE}/api/state`).then(r => r.json());
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
        root.innerHTML = '<div class="feed-item">Aucun contenu de phase</div>';
        renderMainMedia({ ...data, media });
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
    if (!preferred || !preferred.url) {
        stage.innerHTML = `<div class="media-stage-empty">${emptyLabel}</div>`;
        return;
    }

    if ((preferred.type || 'image') === 'video') {
        stage.innerHTML = `<video src="${preferred.url}" ${preferred.autoplay ? 'autoplay' : ''} ${preferred.loop ? 'loop' : ''} ${preferred.muted !== false ? 'muted' : ''} playsinline controls></video>`;
        return;
    }
    stage.innerHTML = `<img src="${preferred.url}" alt="${preferred.title || 'media'}" style="pointer-events: none;">`;
}

// ── Timer ─────────────────────────────────────────────────────────
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
        sb.textContent = 'EN COURS'; sb.className = 'status-badge running';
    } else {
        sb.textContent = session.status === 'finished' ? 'TERMINÉ' : 'EN ATTENTE';
        sb.className = 'status-badge';
    }
}

// ── Phase ─────────────────────────────────────────────────────────
function updatePhase(session) {
    const idx = session.currentPhaseIndex;
    document.getElementById('phaseLabel').textContent = session.currentPhase?.name ?? '—';
    for (let i = 0; i < TOTAL_PHASES; i++) {
        const el = document.getElementById('ph-seg-' + i);
        if (!el) continue;
        el.className = 'ph-seg' + (i < idx ? ' done' : i === idx ? ' active' : '');
    }
}

// ── Atmosphere ────────────────────────────────────────────────────
function updateAtmo(mode) {
    if (mode === lastAtmo) return;
    lastAtmo = mode;
    document.body.className = '';
    if (mode && mode !== 'calm' && mode !== 'neutral') document.body.classList.add('atmo-' + mode);
    if (mode === 'crisis' || mode === 'hacked') document.body.classList.add('scanlines');
    addFeed(mode === 'crisis' ? 'alert' : mode === 'victory' ? 'success' : 'info',
        `ATMOSPHÈRE → ${mode.toUpperCase()}`);
        
    if (mode === 'crisis') playLocalSound('crisis');
    else if (mode === 'tension') playLocalSound('tension');
    else if (mode === 'hacked') playLocalSound('hacked');
}

// ── Teams ─────────────────────────────────────────────────────────
function badgeImgHtml(badge) {
    if (badge.image) {
        return `<img src="${badge.image}" alt="${badge.name}" onerror="this.replaceWith(document.createTextNode('${badge.icon}'))">`;
    }
    return `<span>${badge.icon}</span>`;
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
    document.getElementById('domLabel').textContent = isBadge ? "NOUVEAU BADGE DÉVERROUILLÉ !" : "POINTS ATTRIBUÉS";
    
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

            // ── Score changed ──
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

            // ── Badge tier changed (unlock animation!) ──
            if (t.badge.name !== prevBdg) {
                prevBadge[t.id] = t.badge.name;
                badgeEl.innerHTML = badgeImgHtml(t.badge);
                // Apply unlock animation to the img/span inside
                const img = badgeEl.querySelector('img') || badgeEl.querySelector('span');
                if (img) { img.classList.add('badge-unlocked'); }
                // Log in feed
                addFeed('success', `🏅 ${t.name} — Nouveau badge : <strong>${t.badge.name}</strong>`);
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
// ── Activity feed ─────────────────────────────────────────────────
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
            log.insertBefore(d, log.firstChild);
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

// ── Vote ──────────────────────────────────────────────────────────
let lastVoteId = null, lastVoteOpen = null;
function handleVote(vote) {
    const el = document.getElementById('voteWidget');
    if (!vote) {
        el.innerHTML = '<div class="vote-q fst-italic" style="opacity:.5">Aucun vote en cours</div>';
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

    // Detect vote just closed → flash announcement
    if (lastVoteId === vote.id && lastVoteOpen === true && !isOpen && winner) {
        addFeed('success', `🗳️ Vote fermé — Choix national: <strong>${winner}</strong>`);
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
                <span class="vb-key" style="color:${safeColor}">${isWin ? '🏆' : o.key}</span>
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
        el.innerHTML = '<div class="vote-q fst-italic" style="opacity:.5">Aucune question en cours</div>';
        if (mainQuestion) mainQuestion.textContent = 'Quiz question';
        if (mainChoices) mainChoices.innerHTML = '<span class="hero-quiz-choice empty">Answers will appear here</span>';
        return;
    }

    const optionsHtml = (quiz.options || []).map(o => `
        <div class="vote-bar-row">
            <span class="vb-lbl"><span class="vb-key" style="color:${o.color || '#60a5fa'}">${o.key}</span><span class="vb-text">${o.label}</span></span>
        </div>
    `).join('');

    const resultHtml = (quiz.results || []).slice(0, 6).map(r => `
        <div style="font-size:.72rem;opacity:.85">${r.teamName}: ${r.answerKey || '—'} => <strong>${r.awardedPoints} pts</strong></div>
    `).join('');

    el.innerHTML = `
        <div class="vote-q">${quiz.question ?? 'Question Quiz'}</div>
        <div style="font-size:.72rem;opacity:.65;margin-bottom:8px">Type: ${(quiz.type || 'single_choice').replace('_',' ')} · Réponses: ${quiz.answerCount || 0}</div>
        <div class="vote-bars">${optionsHtml}</div>
        ${resultHtml ? `<div style="margin-top:8px">${resultHtml}</div>` : ''}
    `;
    if (mainQuestion) {
        mainQuestion.textContent = (quiz.question || 'Quiz question').trim();
    }
    if (mainChoices) {
        const heroChoices = (quiz.options || []).length
            ? (quiz.options || []).map(o => `<span class="hero-quiz-choice">${o.key}. ${o.label}</span>`).join('')
            : '<span class="hero-quiz-choice empty">Waiting for answer choices</span>';
        mainChoices.innerHTML = heroChoices;
    }
}

// ── Phantom ───────────────────────────────────────────────────────
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

// ── Endgame ───────────────────────────────────────────────────────
function fireEndgame(teams, session) {
    endgameFired = true;
    const rankable = [...teams].filter(t => t.showInRanking !== false);
    const sorted = rankable.sort((a,b) => b.score - a.score);
    const top3 = sorted.slice(0, 3);
    const rest = sorted.slice(3);

    // Podium order: silver, gold, bronze
    const order = [1, 0, 2];
    const classes = ['p2', 'p1', 'p3'];
    const ranks = ['🥇 1ÈRE', '🥈 2ÈME', '🥉 3ÈME'];

    document.getElementById('podiumEl').innerHTML = order.map((ri, ci) => {
        const t = top3[ri]; if (!t) return '';
        return `<div class="podium-slot ${classes[ci]}">
            <div class="pod-bar">
                <div class="pod-icon">${t.logoPath ? `<img src="${t.logoPath}" alt="logo">` : t.icon}</div>
                <div class="pod-name">${t.name}</div>
                <div class="pod-score">${t.score}</div>
                <div class="pod-badge">${t.badge.image
                    ? `<img src="${t.badge.image}" alt="${t.badge.name}">`
                    : t.badge.icon}</div>
            </div>
            <div class="pod-base">${ranks[ri]}</div>
        </div>`;
    }).join('');

    if (rest.length) {
        document.getElementById('othersEl').innerHTML = rest.map((t,i) =>
            `<div class="other-tile">
                <div class="ot-rank">${i+4}ÈME</div>
                <div class="ot-name">${t.icon} ${t.name}</div>
                <div class="ot-score">${t.score} pts</div>
            </div>`).join('');
    }

    document.getElementById('endgameOv').classList.add('show');
    launchConfetti();
    playLocalSound('victory');
}

// ── Confetti ──────────────────────────────────────────────────────
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

// ── Audio ─────────────────────────────────────────────────────────
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

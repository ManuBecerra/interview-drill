<?php
/* Template Name: Interview Drill AI Safe */
if($_SERVER['REQUEST_METHOD']==='POST' && (($_POST['action'] ?? '')==='transcribe')){
  header('Content-Type: application/json; charset=utf-8');
  $apiKey=trim($_POST['openai_key'] ?? '');
  $lang=trim($_POST['lang'] ?? '');
  if($apiKey===''){
    http_response_code(400);
    echo json_encode(['error'=>'Missing OpenAI API key']);
    exit;
  }
  if(empty($_FILES['audio']['tmp_name']) || !is_uploaded_file($_FILES['audio']['tmp_name'])){
    http_response_code(400);
    echo json_encode(['error'=>'Missing audio upload']);
    exit;
  }
  $ch=curl_init('https://api.openai.com/v1/audio/transcriptions');
  $payload=[
    'model'=>'gpt-4o-mini-transcribe',
    'file'=>new CURLFile($_FILES['audio']['tmp_name'], $_FILES['audio']['type'] ?: 'audio/webm', $_FILES['audio']['name'] ?: 'answer.webm'),
  ];
  if($lang==='en' || $lang==='de'){
    $payload['language']=$lang;
  }
  curl_setopt_array($ch,[
    CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>$payload,
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HTTPHEADER=>[
      'Authorization: Bearer '.$apiKey,
    ],
  ]);
  $raw=curl_exec($ch);
  $status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
  $err=curl_error($ch);
  curl_close($ch);
  if($raw===false){
    http_response_code(500);
    echo json_encode(['error'=>$err ?: 'OpenAI transcription failed']);
    exit;
  }
  http_response_code($status?:200);
  echo $raw;
  exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Interview Drill — Manuel Becerra</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@400;500;600&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0e0e0e;--bg2:#141414;--bg3:#1a1a1a;
  --surface:#242424;--surface2:#2e2e2e;
  --ink:#f5f5f0;--ink-dim:#999990;--ink-ghost:#555550;
  --accent:#e8ff00;--accent-dim:rgba(232,255,0,0.1);--accent-hover:#d4eb00;
  --border:rgba(245,245,240,0.08);--border-med:rgba(245,245,240,0.14);--border-strong:rgba(245,245,240,0.24);
  --danger:#e05050;--danger-bg:rgba(224,80,80,0.1);
  --warn:#d49040;--warn-bg:rgba(212,144,64,0.1);
  --success:#50a070;--success-bg:rgba(80,160,112,0.1);
  /* used by timers/vol-bars/rings; previously missing causing CSS errors in console */
  --sage:#6fcf97;
  --gold:#c8a840;--gold-bg:rgba(200,168,64,0.1);
  --r:8px;--rl:12px;
}
html{font-size:16px;-webkit-text-size-adjust:100%}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100dvh;line-height:1.6}
.shell{max-width:680px;margin:0 auto;padding:0 1.5rem 5rem}

/* NAV */
nav{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 0;border-bottom:1px solid var(--border);margin-bottom:3rem}
.nav-logo{font-family:'Shippori Mincho',serif;font-size:16px;font-weight:600;letter-spacing:0.03em;color:var(--ink);text-decoration:none;display:flex;align-items:center;gap:3px}
.nav-dot{color:var(--accent)}
.nav-right{display:flex;align-items:center;gap:14px}
.nav-tag{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.1em;text-transform:uppercase}
.streak-pill{display:none;align-items:center;gap:4px;background:var(--gold-bg);border:1px solid rgba(200,168,64,0.2);border-radius:20px;padding:3px 10px;font-family:'DM Mono',monospace;font-size:11px;color:var(--gold);letter-spacing:0.04em}

/* PHASES */
.phase{display:none}.phase.active{display:block}

/* HERO */
.hero{padding:3rem 0 2.5rem;border-bottom:1px solid var(--border);margin-bottom:2.5rem}
.hero-eyebrow{font-family:'DM Mono',monospace;font-size:11px;color:var(--ink-ghost);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
.eyebrow-dot{width:6px;height:6px;border-radius:50%;background:var(--accent);display:inline-block}
.hero-title{font-family:'Shippori Mincho',serif;font-size:clamp(30px,5vw,50px);font-weight:600;line-height:1.1;margin-bottom:1.25rem;color:var(--ink)}
.hero-title .acc{color:var(--accent)}
.hero-desc{font-size:15px;color:var(--ink-dim);line-height:1.7;max-width:540px;margin-bottom:2rem}
.hero-stats{display:flex;gap:2.5rem;flex-wrap:wrap;margin-top:1.5rem}
.hero-stat .hsv{font-family:'Shippori Mincho',serif;font-size:28px;font-weight:600;color:var(--accent);line-height:1;margin-bottom:3px}
.hero-stat .hsl{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.06em;text-transform:uppercase}

/* TREND CARD */
.trend-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:2rem}
.trend-hdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.trend-lbl{font-size:13px;color:var(--ink-dim);font-weight:500}
.trend-best{font-family:'DM Mono',monospace;font-size:11px;color:var(--ink-ghost)}
.trend-bars{display:flex;align-items:flex-end;gap:8px;height:56px}
.tb-wrap{display:flex;flex-direction:column;align-items:center;gap:5px;flex:1}
.tb{width:100%;border-radius:3px 3px 0 0;min-height:3px;background:var(--surface2);transition:height 0.5s cubic-bezier(0.4,0,0.2,1)}
.tb.a{background:var(--accent)}.tb.w{background:var(--warn)}.tb.d{background:var(--danger)}.tb.e{opacity:0.3}
.tb-date{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);text-align:center}
.trend-empty{font-size:13px;color:var(--ink-ghost);text-align:center;padding:16px 0}

/* SECTION LABEL */
.slabel{display:block;font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:10px}

/* CARD */
.card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1rem}
.card-success{background:var(--success-bg);border:1px solid rgba(80,160,112,0.2);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1rem}
.card-accent{background:var(--accent-dim);border:1px solid rgba(232,255,0,0.15);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1rem}

/* MODE GRID */
.mode-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:1.5rem}
.mode-card{border:1px solid var(--border-med);border-radius:var(--rl);padding:1rem 1.125rem;cursor:pointer;background:var(--bg3);transition:border-color 0.15s,background 0.15s;-webkit-tap-highlight-color:transparent}
.mode-card:hover{border-color:var(--border-strong);background:var(--surface)}
.mode-card.selected{border:1.5px solid var(--accent);background:var(--accent-dim)}
.mode-card .mct{font-size:14px;font-weight:500;color:var(--ink);margin-bottom:3px}
.mode-card .mcd{font-size:12px;color:var(--ink-ghost);line-height:1.4}

/* INPUTS */
input[type=text],input[type=password],textarea{width:100%;font-family:'DM Sans',sans-serif;font-size:16px;color:var(--ink);background:var(--surface);border:1px solid var(--border-med);border-radius:var(--r);padding:10px 14px;outline:none;transition:border-color 0.15s;-webkit-appearance:none}
input::placeholder,textarea::placeholder{color:var(--ink-ghost)}
input:focus,textarea:focus{border-color:var(--accent)}
textarea{resize:vertical;min-height:100px;line-height:1.6}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;padding:11px 20px;border-radius:var(--r);border:1px solid var(--border-med);background:transparent;color:var(--ink);cursor:pointer;transition:all 0.15s;-webkit-tap-highlight-color:transparent;touch-action:manipulation;min-height:44px}
.btn:hover{background:var(--surface2);border-color:var(--border-strong)}.btn:active{transform:scale(0.97)}
.btn-primary{background:var(--accent);color:#000;border-color:transparent;font-weight:600}.btn-primary:hover{background:var(--accent-hover)}
.btn-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:1.5rem;align-items:center}
.btn-ghost{background:none;border:none;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--ink-ghost);cursor:pointer;text-decoration:underline;-webkit-tap-highlight-color:transparent}

/* PROGRESS */
.prog-track{height:2px;background:var(--surface2);border-radius:1px;margin-bottom:2rem}
.prog-bar{height:100%;background:var(--accent);border-radius:1px;transition:width 0.4s ease}

/* QUESTION */
.q-meta{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:12px}
.q-text{font-family:'Shippori Mincho',serif;font-size:clamp(18px,3vw,23px);font-weight:600;line-height:1.4;margin-bottom:1.5rem;color:var(--ink)}
.cpar-row{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1.25rem}
.cpar-pill{font-family:'DM Mono',monospace;font-size:10px;letter-spacing:0.04em;padding:3px 9px;border-radius:20px;background:var(--surface);color:var(--ink-ghost);border:1px solid var(--border)}
.cpar-pill:first-child{border-color:rgba(232,255,0,0.25);color:rgba(232,255,0,0.75)}

/* TIMER + REC */
.timer-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.timer-num{font-family:'DM Mono',monospace;font-size:24px;font-weight:500;color:var(--ink);font-variant-numeric:tabular-nums}
.timer-num.warn{color:var(--warn)}.timer-num.danger{color:var(--danger)}
.timer-track{height:3px;background:var(--surface2);border-radius:2px;margin-bottom:1rem;overflow:hidden}
.timer-fill{height:100%;background:var(--accent);border-radius:2px;transition:width 1s linear,background 0.5s}
.rec-box{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1rem;transition:border-color 0.3s}
.rec-box.live{border-color:rgba(224,80,80,0.4)}
.rec-status-row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink-dim);margin-bottom:12px}
.rec-dot{width:7px;height:7px;border-radius:50%;background:var(--ink-ghost);flex-shrink:0}
.rec-dot.on{background:var(--danger);animation:blink 1s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0.3}}
.vol-bars{display:flex;align-items:flex-end;gap:3px;height:22px;margin-bottom:10px}
.vb{width:3px;border-radius:2px;background:var(--surface2);min-height:3px;transition:height 0.08s}
.txlive{font-size:14px;color:var(--ink-ghost);line-height:1.7;min-height:60px;font-style:italic}
.txlive.has-text{font-style:normal;color:var(--ink)}

/* SCORE RING */
.score-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1.5rem;display:flex;gap:1.5rem;align-items:center;margin-bottom:1rem}
.rbg{fill:none;stroke:var(--surface2);stroke-width:8}
.rfg{fill:none;stroke-width:8;stroke-linecap:round;transform:rotate(-90deg);transform-origin:50% 50%;transition:stroke-dashoffset 1s cubic-bezier(0.4,0,0.2,1),stroke 0.5s}
.rnum{font-family:'Shippori Mincho',serif;font-size:20px;font-weight:600;fill:var(--ink);dominant-baseline:central;text-anchor:middle;font-variant-numeric:tabular-nums}
.rsub{font-family:'DM Mono',monospace;font-size:9px;letter-spacing:0.06em;fill:var(--ink-ghost);dominant-baseline:central;text-anchor:middle}
.dims{flex:1;display:grid;grid-template-columns:1fr 1fr;gap:8px}
.dim{background:var(--surface);border-radius:var(--r);padding:8px 10px}
.dim .dl{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:3px}
.dim .dv{font-size:16px;font-weight:500;color:var(--ink);font-variant-numeric:tabular-nums}
.dim.good .dv{color:var(--accent)}.dim.warn .dv{color:var(--warn)}.dim.bad .dv{color:var(--danger)}

/* TAGS */
.tag-row{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
.tag{font-size:11px;padding:3px 9px;border-radius:20px;background:var(--danger-bg);color:var(--danger);border:1px solid rgba(224,80,80,0.2)}
.tag.good{background:var(--success-bg);color:var(--success);border-color:rgba(80,160,112,0.2)}
.tag.accent{background:var(--accent-dim);color:var(--accent);border-color:rgba(232,255,0,0.2)}

/* FEEDBACK */
.fb-body{font-size:14px;line-height:1.8;color:var(--ink-dim);white-space:pre-wrap}
.fb-body strong{color:var(--ink);font-weight:500}

/* SELF RATE */
.rate-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:1.5rem}
.rate-cell{border:1px solid var(--border-med);border-radius:var(--r);padding:10px 12px;background:var(--bg3)}
.rate-cell .rl{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:6px}
.stars{display:flex;gap:4px}
.star{font-size:18px;cursor:pointer;color:var(--surface2);transition:color 0.1s;-webkit-tap-highlight-color:transparent}
.star.on{color:var(--accent)}

/* API */
.api-row{display:flex;gap:8px;align-items:center}.api-row input{flex:1}
.api-eye{background:none;border:1px solid var(--border-med);border-radius:var(--r);font-size:14px;cursor:pointer;padding:0 10px;height:42px;color:var(--ink-ghost);transition:background 0.15s}
.api-eye:hover{background:var(--surface)}

/* LOADING */
.dots{display:flex;gap:5px;align-items:center;padding:1.5rem 0}
.dots span{width:5px;height:5px;border-radius:50%;background:var(--ink-ghost);animation:bounce 1.2s infinite}
.dots span:nth-child(2){animation-delay:0.2s}.dots span:nth-child(3){animation-delay:0.4s}
@keyframes bounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-5px)}}
.dots-msg{font-size:14px;color:var(--ink-ghost);margin-left:10px}

/* SUMMARY */
.sum-hero{text-align:center;padding:2.5rem 0 2rem;border-bottom:1px solid var(--border);margin-bottom:2rem}
.sum-ring-wrap{display:inline-block;margin-bottom:1rem}
.sum-sub{font-size:13px;color:var(--ink-ghost)}
.sum-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:1.5rem}
.sm{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1rem;text-align:center}
.sm .smv{font-family:'Shippori Mincho',serif;font-size:24px;font-weight:600;color:var(--ink);margin-bottom:3px;font-variant-numeric:tabular-nums}
.sm .sml{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);letter-spacing:0.07em;text-transform:uppercase}
.pattern-list{list-style:none}
.pattern-list li{padding:10px 0;border-bottom:1px solid var(--border);font-size:14px;color:var(--ink-dim);display:flex;gap:10px;align-items:flex-start;line-height:1.5}
.pattern-list li:last-child{border-bottom:none}

/* MISC */
.hint{font-size:12px;color:var(--ink-ghost);margin-top:6px;line-height:1.5}
.skip-btn{background:none;border:none;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--ink-ghost);cursor:pointer;text-decoration:underline;-webkit-tap-highlight-color:transparent}
.skip-btn:hover{color:var(--ink-dim)}
h1{font-family:'Shippori Mincho',serif;font-size:26px;font-weight:600;line-height:1.3;margin-bottom:0.5rem;color:var(--ink)}
h2{font-family:'Shippori Mincho',serif;font-size:20px;font-weight:600;margin-bottom:0.75rem;color:var(--ink)}
.sub{font-size:14px;color:var(--ink-dim);line-height:1.6;margin-bottom:1.75rem}
@keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.fu{animation:fadeUp 0.3s ease forwards}
@media(max-width:520px){.shell{padding:0 1rem 4rem}.hero-title{font-size:28px}.score-card{flex-direction:column}.dims{grid-template-columns:1fr 1fr}.hero-stats{gap:1.5rem}}
</style>
</head>
<body>
<div class="shell">

<nav>
  <a href="#" class="nav-logo" onclick="goHome(event)">Manuel Becerra<span class="nav-dot">.</span></a>
  <div class="nav-right">
    <div class="streak-pill" id="streak-pill">&#128293; <span id="streak-num">0</span></div>
    <span class="nav-tag">Interview Coach</span>
  </div>
</nav>

<!-- SETUP -->
<div id="ph-setup" class="phase active">
  <div class="hero">
    <div class="hero-eyebrow"><span class="eyebrow-dot"></span>PM Interview Training Tool &middot; Active</div>
    <h1 class="hero-title">Train your<br>answers<span class="acc">.</span></h1>
    <p class="hero-desc">Speak your answers out loud. Get scored against your real weak patterns from 12 real interviews. Build automaticity before the next one.</p>
    <div class="hero-stats">
      <div class="hero-stat"><div class="hsv" id="st-sessions">&#x2014;</div><div class="hsl">Sessions</div></div>
      <div class="hero-stat"><div class="hsv" id="st-avg">&#x2014;</div><div class="hsl">Avg score</div></div>
      <div class="hero-stat"><div class="hsv" id="st-streak">&#x2014;</div><div class="hsl">Day streak</div></div>
    </div>
  </div>

  <div class="trend-card">
    <div class="trend-hdr">
      <span class="trend-lbl">Last 7 sessions</span>
      <span class="trend-best" id="trend-best"></span>
    </div>
    <div class="trend-bars" id="trend-bars"></div>
  </div>

  <div class="card">
    <span class="slabel">Anthropic API key</span>
    <div class="api-row">
      <input type="password" id="api-key" placeholder="sk-ant-api03-..." autocomplete="off" spellcheck="false">
      <button class="api-eye" onclick="toggleKey()">&#128065;</button>
    </div>
    <p class="hint">Stored locally in your browser only. Only sent to Anthropic.</p>
  </div>

  <div class="card">
    <span class="slabel">OpenAI transcription key</span>
    <div class="api-row">
      <input type="password" id="oa-key" placeholder="sk-..." autocomplete="off" spellcheck="false">
      <button class="api-eye" onclick="toggleOAKey()">&#128065;</button>
    </div>
    <p class="hint">Used only to transcribe voice answers with GPT-4o mini Transcribe.</p>
  </div>

  <div class="card">
    <span class="slabel">Job description (optional)</span>
    <textarea id="jd-input" rows="3" placeholder="Paste the role and responsibilities. Leave blank for standard PM questions."></textarea>
  </div>

  <span class="slabel">Session length</span>
  <div class="mode-grid">
    <div class="mode-card selected" data-mode="full" onclick="selectMode(this,'full')"><div class="mct">Full &mdash; 5 questions</div><div class="mcd">Full scoring &middot; session summary &middot; streak</div></div>
    <div class="mode-card" data-mode="quick" onclick="selectMode(this,'quick')"><div class="mct">Quick &mdash; 3 questions</div><div class="mcd">Faster loop &middot; same feedback depth</div></div>
  </div>

  <span class="slabel">Language</span>
  <div class="mode-grid">
    <div class="mode-card selected" data-lang="en" onclick="selectLang(this,'en')"><div class="mct">English</div><div class="mcd">EN questions &middot; EN scoring</div></div>
    <div class="mode-card" data-lang="de" onclick="selectLang(this,'de')"><div class="mct">Deutsch</div><div class="mcd">DE Fragen &middot; DE Feedback</div></div>
  </div>

  <span class="slabel">Answer mode</span>
  <div class="mode-grid">
    <div class="mode-card selected" data-input="voice" onclick="selectInput(this,'voice')"><div class="mct">Voice</div><div class="mcd">Speak out loud &middot; live transcript &middot; real pressure</div></div>
    <div class="mode-card" data-input="text" onclick="selectInput(this,'text')"><div class="mct">Text</div><div class="mcd">Type your answer &middot; no mic needed</div></div>
  </div>

  <div class="btn-row">
    <button class="btn btn-primary" onclick="startSession()">Start session &rarr;</button>
    <button class="btn-ghost" onclick="clearHistory()">Reset history</button>
  </div>
</div>

<!-- LOADING -->
<div id="ph-loading" class="phase">
  <h1 id="load-title">Building your questions</h1>
  <div class="dots"><span></span><span></span><span></span><span class="dots-msg" id="load-msg">Analysing the role...</span></div>
</div>

<!-- QUESTION -->
<div id="ph-question" class="phase">
  <div class="prog-track"><div class="prog-bar" id="prog"></div></div>
  <div class="q-meta" id="q-meta">Question 1 of 5</div>
  <div class="q-text fu" id="q-text"></div>
  <div class="cpar-row" id="cpar-row">
    <span class="cpar-pill">C &mdash; Context</span>
    <span class="cpar-pill">P &mdash; Problem</span>
    <span class="cpar-pill">A &mdash; Action (use "I")</span>
    <span class="cpar-pill">R &mdash; Result + metric</span>
  </div>
  <div id="voice-area" class="rec-box">
    <div class="timer-row">
      <div class="rec-status-row"><div class="rec-dot" id="rec-dot"></div><span id="rec-status-text">Ready &mdash; tap to record</span></div>
      <div class="timer-num" id="timer">0:00</div>
    </div>
    <div class="timer-track"><div class="timer-fill" id="timer-bar" style="width:100%"></div></div>
    <div class="vol-bars" id="vol-bars"><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div></div>
    <div class="txlive" id="txlive">Your words will appear here as you speak...</div>
  </div>
  <div id="text-area" class="rec-box" style="display:none">
    <div class="timer-row">
      <span class="slabel" style="margin:0">Type your answer</span>
      <div class="timer-num" id="timer-text">0:00</div>
    </div>
    <div class="timer-track"><div class="timer-fill" id="timer-bar-text" style="width:100%"></div></div>
    <textarea id="text-answer" rows="6" placeholder="CPAR: Context &rarr; Problem &rarr; Action (I did...) &rarr; Result (metric)..."></textarea>
  </div>
  <div class="btn-row">
    <button class="btn btn-primary" id="rec-btn" onclick="handleRecBtn()">Start recording</button>
    <button class="btn" id="submit-btn" onclick="submitAnswer()" style="display:none">Get feedback &rarr;</button>
    <button class="skip-btn" id="skip-btn" onclick="skipQuestion()">Skip &rarr;</button>
  </div>
  <p class="hint" id="q-hint" style="margin-top:12px">Min 30s &middot; target 60&ndash;90s &middot; stop at 2min</p>
</div>

<!-- SELF RATE -->
<div id="ph-selfrate" class="phase">
  <div class="prog-track"><div class="prog-bar" id="prog-sr"></div></div>
  <h2 id="sr-title">Rate yourself first</h2>
  <p class="sub" id="sr-sub">Before the AI scores &mdash; how did you do on each dimension?</p>
  <div class="rate-grid">
    <div class="rate-cell"><div class="rl">Structure</div><div class="stars" data-dim="structure"></div></div>
    <div class="rate-cell"><div class="rl">Ownership ("I")</div><div class="stars" data-dim="ownership"></div></div>
    <div class="rate-cell"><div class="rl">Metric cited</div><div class="stars" data-dim="metric"></div></div>
    <div class="rate-cell"><div class="rl">No hedging</div><div class="stars" data-dim="hedging"></div></div>
  </div>
  <div class="btn-row"><button class="btn btn-primary" id="sr-btn" onclick="showFeedback()">See AI feedback &rarr;</button></div>
</div>

<!-- FEEDBACK -->
<div id="ph-feedback" class="phase">
  <div class="prog-track"><div class="prog-bar" id="prog-fb"></div></div>
  <div class="score-card">
    <svg width="90" height="90" viewBox="0 0 90 90" style="flex-shrink:0">
      <circle class="rbg" cx="45" cy="45" r="36"/>
      <circle class="rfg" id="ring-fg" cx="45" cy="45" r="36" stroke="var(--accent)" stroke-dasharray="226.2" stroke-dashoffset="226.2"/>
      <text class="rnum" id="ring-num" x="45" y="42">&mdash;</text>
      <text class="rsub" x="45" y="56">/10</text>
    </svg>
    <div class="dims">
      <div class="dim"><div class="dl">Structure</div><div class="dv" id="dc-str">&mdash;</div></div>
      <div class="dim"><div class="dl">Ownership</div><div class="dv" id="dc-own">&mdash;</div></div>
      <div class="dim"><div class="dl">Metrics</div><div class="dv" id="dc-met">&mdash;</div></div>
      <div class="dim"><div class="dl">Overall</div><div class="dv" id="dc-ov">&mdash;</div></div>
    </div>
  </div>
  <div class="tag-row" id="tag-row"></div>
  <div class="card" style="margin-top:0.75rem">
    <span class="slabel" id="fb-label">Feedback</span>
    <div id="fb-stream" class="fb-body"><div class="dots"><span></span><span></span><span></span><span class="dots-msg" id="fb-dots-msg">Scoring your answer...</span></div></div>
  </div>
  <div class="card">
    <span class="slabel" id="ans-label">Your answer</span>
    <div id="tx-display" style="font-size:13px;color:var(--ink-ghost);line-height:1.7"></div>
  </div>
  <div class="btn-row" id="fb-btns" style="display:none">
    <button class="btn" id="retry-btn" onclick="retryQuestion()">Try again</button>
    <button class="btn btn-primary" id="next-btn" onclick="nextQuestion()">Next question &rarr;</button>
  </div>
</div>

<!-- SUMMARY -->
<div id="ph-summary" class="phase">
  <div class="sum-hero">
    <div class="sum-ring-wrap">
      <svg width="130" height="130" viewBox="0 0 130 130">
        <circle cx="65" cy="65" r="54" fill="none" stroke="var(--surface2)" stroke-width="10"/>
        <circle id="sum-ring" cx="65" cy="65" r="54" fill="none" stroke="var(--accent)" stroke-width="10"
          stroke-linecap="round" stroke-dasharray="339.3" stroke-dashoffset="339.3"
          transform="rotate(-90 65 65)"
          style="transition:stroke-dashoffset 1.2s cubic-bezier(0.4,0,0.2,1),stroke 0.5s"/>
        <text x="65" y="61" text-anchor="middle" dominant-baseline="central" id="sum-ring-num"
          style="font-family:'Shippori Mincho',serif;font-size:30px;font-weight:600;fill:var(--ink)">&mdash;</text>
        <text x="65" y="80" text-anchor="middle"
          style="font-family:'DM Mono',monospace;font-size:10px;fill:var(--ink-ghost);letter-spacing:0.08em">SCORE</text>
      </svg>
    </div>
    <div class="sum-sub" id="sum-meta">Session complete</div>
  </div>
  <div class="sum-metrics">
    <div class="sm"><div class="smv" id="sum-answered">0</div><div class="sml">answered</div></div>
    <div class="sm"><div class="smv" id="sum-avg">0</div><div class="sml">avg score</div></div>
    <div class="sm"><div class="smv" id="sum-time">0m</div><div class="sml">time</div></div>
  </div>
  <div class="card"><span class="slabel" id="pat-label">Session patterns</span><ul class="pattern-list" id="pat-list"></ul></div>
  <div class="card-accent"><span class="slabel" id="pri-label">Priority for tomorrow</span><div id="pri-text" style="font-size:14px;line-height:1.7;color:var(--ink-dim)"></div></div>
  <div class="btn-row">
    <button class="btn btn-primary" id="new-btn" onclick="restartSession()">New session &rarr;</button>
    <button class="btn" id="drill-btn" onclick="drillWeakest()">Drill weakest &rarr;</button>
  </div>
</div>

</div>
<script>
const PROFILE=`You are a high-candor AI interview coach for Manu Becerra Perez, Berlin-based PM. Coach in same language as the answer — German answer = German feedback, English answer = English feedback.

PROFILE: Target PM/PO roles Berlin, deadline July 2026. Upper-mid level.
Lengoo (2020-2024): PO/PM on HALOS AI translation. Flow initiative end-to-end. METRICS: CSAT +15%, adoption +20%, engagement +30%, website +40% visitors, task completion +20%.
Cognigy (2024-present): Product Support Engineer. 200+ tickets/month. METRIC: resolution -30%. Tools: Kibana, Grafana, Postman, GitLab.
Aneekaa Studio (2015-2024): Co-founder, 20+ clients (Adidas, Zalando, Blinkist). 100% on-time.
Certs: PSPO I, PSM I. IU Akademie PM Weiterbildung Mar-Jul 2026.

INTERVIEW HISTORY (12 scored, pattern = loses round 1-2, always communication not experience):
JustWatch 7.5 · GLS/NXT 6.5 · Berliner Verlag 6.5 · Green Medical 6 · bookingkit 6 · Tagesspiegel 6 · Jorge/LatAm 6 · 1KOMMA5 6 · GoToMarket 5.5 · Emporix 5.5 · Talon.One 5 · Octopus 5

CRITICAL WEAKNESSES (all confirmed across 12 interviews):
1. INTRO TOO LONG — runs 10-15 min every time. Must be under 90 seconds.
2. "WE" NOT "I" — every interview. "we built/wir haben". Must say "I decided/Ich habe entschieden".
3. NO METRICS — 11/12 interviews. Has numbers but never cites them: CSAT +15%, adoption +20%, resolution -30%.
4. NON-LINEAR — loses thread, never lands conclusion.
5. HEDGING: EN: I think/maybe/kind of/I guess/probably. DE: ich denke/vielleicht/irgendwie/eigentlich/halt/na ja.
6. NO TRADE-OFF — describes what was done, never what was cut.
7. TECHNICAL TANGENTS — drifts into infra/APIs/LLMs when nervous.
8. FIRST SENTENCE DELAY — starts with context not the answer.

FLOW STORY: Real-time AI translation at Lengoo. Problem: Google Translate = fast but wrong terminology; professional workflows = too slow. I designed hybrid: MT + Translation Memories + Glossaries. I owned discovery (20+ interviews), backlog, engineering coordination, MVP, post-launch. I cut editing features from MVP to ship faster. Result: ~50% faster, CSAT +15%. I defined latency <1s requirement.

SCORING: 7/10 = genuinely strong. 5/10 = average. Don't inflate. Never say "great answer" unless score ≥8. Quote verbatim for every negative. "we/wir" >2x → deduct Ownership. No metric → cap Metrics at 3/10. First sentence ≠ direct answer → deduct Structure. >200 words → flag. List every hedging word. If the answer reads like pasted prep notes or a briefing document, say so explicitly and call out that it needs to sound like a spoken answer, not a written script. If the answer repeats the same point more than once, call that out and say exactly what to remove. When content is strong but delivery is too long, separate "good material" from "bad delivery". For every major issue, include one precise fix and one concrete example of how to say it differently. Example format: "Cut the Aneekaa detail, move Cognigy into one sentence, open with 'I'm a Product Manager who led Flow end-to-end and improved adoption by 20%.'" End with ONE rewrite of how answer should open. Root causes: "ownership gap"/"metric blindness"/"structure collapse"/"hedge spiral"/"tangent drift"/"intro bloat"/"first-sentence delay"/"repetition"`;

const ANTHROPIC_SYSTEM=[
  {
    type:'text',
    text:PROFILE,
    // Cache the static coach prompt so repeated scoring calls are cheaper and faster.
    cache_control:{type:'ephemeral'}
  }
];

const POOL={
  en:["Tell me about yourself. Under 90 seconds.","Walk me through a product you owned end-to-end from discovery to launch.","How do you prioritize a backlog when sales, engineering, and CS all have competing requests?","Tell me about the hardest trade-off you personally made on a product.","How do you work with engineers? Give a specific example.","How do you use data to make product decisions? A real example.","Tell me about a time you managed a difficult stakeholder conflict.","What would have made one of your past products fail? How did you prevent it?","How do you deal with an ambiguous problem with very little information?","What is your biggest weakness as a PM? Be specific."],
  de:["Stellen Sie sich kurz vor. Maximal 90 Sekunden.","Beschreiben Sie ein Produkt, das Sie von der Entdeckung bis zum Launch vollständig verantwortet haben.","Wie priorisieren Sie ein Backlog, wenn Sales, Engineering und CS alle unterschiedliche Prioritäten haben?","Erzählen Sie von der schwierigsten Entscheidung, die Sie persönlich bei einem Produkt getroffen haben.","Wie arbeiten Sie mit Entwicklern zusammen? Ein konkretes Beispiel.","Wie nutzen Sie Daten für Produktentscheidungen? Ein reales Beispiel.","Erzählen Sie von einem Stakeholder-Konflikt, den Sie erfolgreich gelöst haben.","Was hätte eines Ihrer Produkte zum Scheitern gebracht? Wie haben Sie das verhindert?","Wie gehen Sie mit unklaren Problemen um, wenn Sie kaum Information haben?","Was ist Ihre größte Schwäche als PM? Konkret."]
};
const CPAR={en:['C — Context','P — Problem','A — Action (use "I")','R — Result + metric'],de:['K — Kontext','P — Problem','A — Aktion (sag "Ich")','E — Ergebnis + Zahl']};
const T={
  en:{load:'Building your questions',lmsg:'Analysing the role...',ready:'Ready — tap to record',rec:'Recording — speak now',stop:'Stop recording',start:'Start recording',rerec:'Re-record',done:'Done — review or submit',getfb:'Get feedback →',next:'Next question →',retry:'Try again',skip:'Skip →',hint:'Min 30s · target 60–90s · stop at 2min',srt:'Rate yourself first',srs:'Before the AI scores — how did you do?',seefb:'See AI feedback →',fbl:'Feedback',ansl:'Your answer',scoring:'Scoring your answer...',complete:'Session complete',patterns:'Session patterns',priority:'Priority for tomorrow',newsess:'New session →',drillw:'Drill weakest →',timer:'Start timer'},
  de:{load:'Fragen werden vorbereitet',lmsg:'Rolle wird analysiert...',ready:'Bereit — tippe zum Aufnehmen',rec:'Aufnahme läuft — sprich jetzt',stop:'Aufnahme stoppen',start:'Aufnahme starten',rerec:'Neu aufnehmen',done:'Fertig — prüfen oder absenden',getfb:'Feedback anzeigen →',next:'Nächste Frage →',retry:'Nochmal versuchen',skip:'Überspringen →',hint:'Min 30s · Ziel 60–90s · Stop bei 2min',srt:'Erst selbst bewerten',srs:'Bevor du das KI-Feedback siehst — wie schätzt du dich ein?',seefb:'KI-Feedback →',fbl:'Feedback',ansl:'Deine Antwort',scoring:'Antwort wird bewertet...',complete:'Session abgeschlossen',patterns:'Muster dieser Session',priority:'Priorität für morgen',newsess:'Neue Session →',drillw:'Schwächstes üben →',timer:'Timer starten'}
};

let S={mode:'full',input:'voice',lang:'en',key:'',oaKey:'',jd:'',questions:[],cq:0,total:5,answers:[],scores:[],selfR:[],sessStart:null,timerInt:null,timerSec:0,recog:null,recorder:null,audioStream:null,audioChunks:[],transcript:'',finalTranscript:'',recording:false,stopRequested:false,transcribing:false,actx:null,analyser:null,raf:null,wk:{own:0,met:0,hed:0,str:0,len:0},selfRat:{}};

const QUESTION_META={
  en:[
    {q:'Tell me about yourself. Under 90 seconds.',tags:['intro','structure','hedge']},
    {q:'Walk me through a product you owned end-to-end from discovery to launch.',tags:['ownership','metrics','delivery']},
    {q:'How do you prioritize a backlog when sales, engineering, and CS all have competing requests?',tags:['priority','tradeoff','stakeholder']},
    {q:'Tell me about the hardest trade-off you personally made on a product.',tags:['ownership','tradeoff','decision']},
    {q:'How do you work with engineers? Give a specific example.',tags:['stakeholder','ownership','collaboration']},
    {q:'How do you use data to make product decisions? A real example.',tags:['metrics','data','decision']},
    {q:'Tell me about a time you managed a difficult stakeholder conflict.',tags:['stakeholder','conflict','ownership']},
    {q:'What would have made one of your past products fail? How did you prevent it?',tags:['risk','tradeoff','metrics']},
    {q:'How do you deal with an ambiguous problem with very little information?',tags:['ambiguity','structure','decision']},
    {q:'What is your biggest weakness as a PM? Be specific.',tags:['weakness','hedge','structure']}
  ],
  de:[
    {q:'Stellen Sie sich kurz vor. Maximal 90 Sekunden.',tags:['intro','structure','hedge']},
    {q:'Beschreiben Sie ein Produkt, das Sie von der Entdeckung bis zum Launch vollständig verantwortet haben.',tags:['ownership','metrics','delivery']},
    {q:'Wie priorisieren Sie ein Backlog, wenn Sales, Engineering und CS alle unterschiedliche Prioritäten haben?',tags:['priority','tradeoff','stakeholder']},
    {q:'Erzählen Sie von der schwierigsten Entscheidung, die Sie persönlich bei einem Produkt getroffen haben.',tags:['ownership','tradeoff','decision']},
    {q:'Wie arbeiten Sie mit Entwicklern zusammen? Ein konkretes Beispiel.',tags:['stakeholder','ownership','collaboration']},
    {q:'Wie nutzen Sie Daten für Produktentscheidungen? Ein reales Beispiel.',tags:['metrics','data','decision']},
    {q:'Erzählen Sie von einem Stakeholder-Konflikt, den Sie erfolgreich gelöst haben.',tags:['stakeholder','conflict','ownership']},
    {q:'Was hätte eines Ihrer Produkte zum Scheitern gebracht? Wie haben Sie das verhindert?',tags:['risk','tradeoff','metrics']},
    {q:'Wie gehen Sie mit unklaren Problemen um, wenn Sie kaum Information haben?',tags:['ambiguity','structure','decision']},
    {q:'Was ist Ihre größte Schwäche als PM? Konkret.',tags:['weakness','hedge','structure']}
  ]
};

function jdTerms(jd){
  return (jd||'').toLowerCase()
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g,'')
    .replace(/[^a-z0-9\s+-]/g,' ')
    .split(/\s+/)
    .filter(Boolean);
}

function questionScore(meta, jd){
  const text=(meta.q+' '+(jd||'')).toLowerCase();
  const terms=jdTerms(jd);
  let score=0;
  const add=(cond,val)=>{if(cond)score+=val;};
  add(meta.tags.includes('intro'),10);
  add(meta.tags.includes('metrics'),8);
  add(meta.tags.includes('ownership'),8);
  add(meta.tags.includes('tradeoff'),7);
  add(meta.tags.includes('stakeholder'),6);
  add(meta.tags.includes('structure'),6);
  add(meta.tags.includes('hedge'),5);
  add(text.includes('metric')||text.includes('number')||text.includes('zahl')||text.includes('csat')||text.includes('adoption')||text.includes('resolution'),4);
  add(text.includes('engineer')||text.includes('engineering')||text.includes('entwickler'),4);
  add(text.includes('stakeholder')||text.includes('sales')||text.includes('cs'),3);
  add(text.includes('conflict')||text.includes('konflikt'),3);
  add(text.includes('ambiguous')||text.includes('unklar'),3);
  add(terms.some(t=>['data','metric','kpi','analytics','zahlen','zahl'].includes(t)),4);
  add(terms.some(t=>['ownership','decision','entscheid','tradeoff','trade-off'].includes(t)),4);
  add(terms.some(t=>['engineer','engineering','entwickler','tech'].includes(t)),3);
  add(terms.some(t=>['stakeholder','sales','cs','customer','kunden'].includes(t)),3);
  add(terms.some(t=>['conflict','konflikt','priority','priorit'].includes(t)),3);
  return score;
}

function pickQuestions(lang, mode, jd){
  const pool=QUESTION_META[lang]||QUESTION_META.en;
  const n=mode==='quick'?3:5;
  return [...pool]
    .map((meta,idx)=>({meta,idx,score:questionScore(meta,jd)}))
    .sort((a,b)=>b.score-a.score||a.idx-b.idx)
    .slice(0,n)
    .map(x=>x.meta.q);
}

function extractJson(text){
  const cleaned=(text||'').replace(/```json|```/g,'').trim();
  if(!cleaned)throw new Error('Empty response');
  try{return JSON.parse(cleaned);}catch{}
  const findBalanced=(openChar,closeChar)=>{
    const start=cleaned.indexOf(openChar);
    if(start<0)return null;
    let depth=0,inString=false,escape=false;
    for(let i=start;i<cleaned.length;i++){
      const ch=cleaned[i];
      if(escape){escape=false;continue;}
      if(ch==='\\'){escape=true;continue;}
      if(ch==='"'){inString=!inString;continue;}
      if(inString)continue;
      if(ch===openChar)depth++;
      if(ch===closeChar){
        depth--;
        if(depth===0)return cleaned.slice(start,i+1);
      }
    }
    return null;
  };
  const obj=findBalanced('{','}');
  if(obj)return JSON.parse(obj);
  const arr=findBalanced('[',']');
  if(arr)return JSON.parse(arr);
  throw new Error('No JSON found');
}

function toNum(v){
  if(typeof v==='number'&&Number.isFinite(v))return v;
  if(typeof v==='string'){
    const n=Number(v.trim());
    if(Number.isFinite(n))return n;
  }
  return null;
}

function clampScore(v, fallback){
  const n=toNum(v);
  const base=Number.isFinite(n)?Math.round(n):fallback;
  return Math.max(1,Math.min(10,base));
}

function countMatches(text, re){
  const m=(text||'').match(re);
  return m?m.length:0;
}

function buildNumberWordRegex(){
  const en=[
    'zero','one','two','three','four','five','six','seven','eight','nine',
    'ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen',
    'eighteen','nineteen','twenty','thirty','forty','fifty','sixty','seventy',
    'eighty','ninety','hundred','thousand','million','billion'
  ];
  const de=[
    'null','eins','ein','eine','einer','einem','einen','zwei','drei','vier','fuenf','fünf',
    'sechs','sieben','acht','neun','zehn','elf','zwölf','zwoelf','dreizehn','vierzehn',
    'fuenfzehn','fünfzehn','sechzehn','siebzehn','achtzehn','neunzehn','zwanzig',
    'dreissig','dreißig','vierzig','fuenfzig','fünfzig','sechzig','siebzig','achtzig',
    'neunzig','hundert','tausend','million','millionen'
  ];
  return new RegExp(`\\b(?:${[...en,...de].join('|')})\\b`,'i');
}

function extractMetricEvidence(text){
  const raw=String(text||'');
  const cleaned=raw
    .replace(/\bminus\s+/gi,'-')
    .replace(/\bplus\s+/gi,'+')
    .replace(/\bpercent\b/gi,'%')
    .replace(/\bprozent\b/gi,'%');
  const digitHits=cleaned.match(/\b[-+]?\d+(?:[.,]\d+)?\s*(?:%|x|times?)?\b/gi)||[];
  const wordHits=cleaned.match(buildNumberWordRegex())||[];
  const metricWords=cleaned.match(/\b(csat|kpi|kpis|metric|metrics|adoption|resolution|conversion|revenue|retention|latency|response time|throughput|users?|tickets?|growth|increase|decrease|improvement|saved|reduced|dropped)\b/gi)||[];
  const hits=[...digitHits,...wordHits,...metricWords];
  return [...new Set(hits.map(h=>h.trim()).filter(Boolean))];
}

function localScoreFallback(question, answer, timeSec){
  const q=(question||'').toLowerCase();
  const a=(answer||'').toLowerCase();
  const words=(answer||'').trim().split(/\s+/).filter(Boolean);
  const wc=words.length;
  const metricEvidence=extractMetricEvidence(a);
  const metricHit=metricEvidence.length>0;
  const weCount=countMatches(a,/\bwe\b/gi);
  const hedgeCount=countMatches(a,/\b(i think|maybe|kind of|sort of|probably|i guess|perhaps|actually|basically|somewhat|kinda|vielleicht|irgendwie|eigentlich|halt|na ja|ich denke)\b/gi);
  const hasDirectStart=/^(i|ich)\b/.test(a);
  const firstSentence=(answer||'').split(/[.!?]\s+/)[0] || '';
  const directQuestion=q.includes('yourself')||q.includes('tell me about yourself')||q.includes('stellen sie sich kurz vor');
  const structureScore=hasDirectStart||!directQuestion?7:5;
  const metricScore=metricHit?7:3;
  const ownershipScore=Math.max(1,10-Math.max(0,weCount-2)*2);
  const lengthPenalty=wc>200?2:wc>150?1:0;
  const hedgePenalty=Math.min(3,Math.floor(hedgeCount/2));
  const timePenalty=timeSec>120?1:0;
  const overall=Math.max(1,Math.min(10,Math.round((structureScore+metricScore+ownershipScore)/3)-lengthPenalty-hedgePenalty-timePenalty));
  const weaknesses=[];
  if(weCount>2)weaknesses.push('ownership gap');
  if(!metricHit)weaknesses.push('metric blindness');
  if(!hasDirectStart&&directQuestion)weaknesses.push('first-sentence delay');
  if(hedgeCount>0)weaknesses.push('hedge spiral');
  if(wc>180)weaknesses.push('intro bloat');
  if(q.includes('hardest')||q.includes('trade-off')||q.includes('tradeoff'))weaknesses.push('structure collapse');
  return {
    overall,
    structure: Math.max(1,Math.min(10,structureScore-lengthPenalty)),
    ownership: ownershipScore,
    metrics: metricScore,
    weaknesses_triggered:[...new Set(weaknesses)],
    what_worked:'',
    critical_fix:'',
    rewrite_sentence:'',
    cut_this:'',
    say_this:'',
    strongest_line:'',
    filler_words_found:(answer||'').match(/\b(um|uh|like|you know|vielleicht|eigentlich|halt|na ja)\b/gi)||[],
    metric_evidence:metricEvidence,
    we_count:weCount,
    metric_cited:metricHit,
    word_count:wc
  };
}

function normalizeScoreResponse(d, question, answer, timeSec){
  const fallback=localScoreFallback(question, answer, timeSec);
  const fields=['overall','structure','ownership','metrics'];
  const out={...fallback};
  fields.forEach(k=>{
    out[k]=clampScore(d?.[k], fallback[k]);
  });
  out.metric_cited=typeof d?.metric_cited==='boolean' ? (d.metric_cited || fallback.metric_cited) : fallback.metric_cited;
  out.metric_evidence=Array.isArray(d?.metric_evidence)&&d.metric_evidence.length?d.metric_evidence:fallback.metric_evidence;
  out.we_count=Number.isFinite(toNum(d?.we_count))?Math.max(0,Math.round(toNum(d.we_count))):fallback.we_count;
  out.word_count=Number.isFinite(toNum(d?.word_count))?Math.max(0,Math.round(toNum(d.word_count))):fallback.word_count;
  out.weaknesses_triggered=Array.isArray(d?.weaknesses_triggered)&&d.weaknesses_triggered.length?d.weaknesses_triggered:fallback.weaknesses_triggered;
  ['what_worked','critical_fix','rewrite_sentence','cut_this','say_this','strongest_line'].forEach(k=>{
    out[k]=typeof d?.[k]==='string'?d[k].trim():fallback[k];
  });
  out.filler_words_found=Array.isArray(d?.filler_words_found)&&d.filler_words_found.length?d.filler_words_found:fallback.filler_words_found;
  return out;
}

/* STORAGE */
function lh(){try{return JSON.parse(localStorage.getItem('mb_dh')||'[]')}catch{return[]}}
function sh(h){localStorage.setItem('mb_dh',JSON.stringify(h))}
function saveS(avg){const h=lh();h.push({d:new Date().toISOString().split('T')[0],s:avg});if(h.length>30)h.splice(0,h.length-30);sh(h)}
function getStreak(){
  const h=lh();if(!h.length)return 0;
  const today=new Date().toISOString().split('T')[0];
  const dates=[...new Set(h.map(x=>x.d))].sort().reverse();
  let streak=0,cur=new Date(today);
  for(let d of dates){const ds=new Date(d);const diff=Math.round((cur-ds)/(864e5));if(diff===0||diff===1){streak++;cur=ds;}else break;}
  return streak;
}
function updateStats(){
  const h=lh();const streak=getStreak();
  const avg=h.length?Math.round(h.reduce((a,b)=>a+b.s,0)/h.length):null;
  $('st-streak').textContent=streak||'—';
  $('st-avg').textContent=avg?avg+'/10':'—';
  $('st-sessions').textContent=h.length||'—';
  const sb=$('streak-pill');
  if(streak>1){sp=document.getElementById('streak-pill');if(sp)sp.style.display='flex';$('streak-num').textContent=streak;}else sb.style.display='none';
  renderTrend(h);
}
function renderTrend(h){
  const el=$('trend-bars');const last=h.slice(-7);
  if(!last.length){el.innerHTML='<div class="trend-empty">No sessions yet. Start your first drill.</div>';$('trend-best').textContent='';return;}
  const padded=Array(7-last.length).fill(null).concat(last);
  const best=Math.max(...last.map(x=>x.s));
  $('trend-best').textContent='best: '+best+'/10';
  el.innerHTML=padded.map(x=>{
    if(!x)return`<div class="tb-wrap"><div class="tb e" style="height:4px"></div><div class="td"></div></div>`;
    const ht=Math.round((x.s/10)*46)+4;
    const c=x.s>=7?'g':x.s>=5?'w':'b';
    const label=x.d.slice(5).replace('-','/');
    return`<div class="tb-wrap"><div class="tb ${c}" style="height:${ht}px"></div><div class="td">${label}</div></div>`;
  }).join('');
}
function clearHistory(){if(confirm('Reset all history?')){localStorage.removeItem('mb_dh');updateStats();}}

/* SETUP */
function $(id){return document.getElementById(id)}
function toggleKey(){const i=$('api-key');i.type=i.type==='password'?'text':'password';}
function toggleOAKey(){const i=$('oa-key');i.type=i.type==='password'?'text':'password';}
function selectMode(el,m){qsa('[data-mode]').forEach(c=>c.classList.remove('selected'));el.classList.add('selected');S.mode=m;S.total=m==='quick'?3:5;}
function selectLang(el,l){qsa('[data-lang]').forEach(c=>c.classList.remove('selected'));el.classList.add('selected');S.lang=l;}
function selectInput(el,m){qsa('[data-input]').forEach(c=>c.classList.remove('selected'));el.classList.add('selected');S.input=m;}
function qsa(s){return document.querySelectorAll(s)}

window.addEventListener('load',()=>{
  const k=localStorage.getItem('mb_dk');if(k)$('api-key').value=k;
  const ok=localStorage.getItem('mb_oa');if(ok)$('oa-key').value=ok;
  updateStats();buildStars();
});
function buildStars(){
  qsa('.stars').forEach(row=>{
    const dim=row.dataset.dim;
    for(let i=1;i<=5;i++){const s=document.createElement('span');s.className='star';s.textContent='★';s.dataset.val=i;s.onclick=()=>rateSelf(row,dim,i);row.appendChild(s);}
  });
}
function rateSelf(row,dim,val){row.querySelectorAll('.star').forEach((s,i)=>s.classList.toggle('on',i<val));S.selfRat[dim]=val;}

function resetSessionState(){
  stopTimer();
  stopRec();
  S.cq=0;
  S.total=S.mode==='quick'?3:5;
  S.answers=[];
  S.scores=[];
  S.selfR=[];
  S.questions=[];
  S.sessStart=null;
  S.transcript='';
  S.finalTranscript='';
  S.recording=false;
  S.stopRequested=false;
  S.timerSec=0;
  S.selfRat={};
  S.wk={own:0,met:0,hed:0,str:0,len:0};
  const ta=$('text-answer');
  if(ta)ta.value='';
}

function goHome(e){
  if(e)e.preventDefault();
  resetSessionState();
  showP('ph-setup');
}

/* SESSION */
async function startSession(){
  const key=$('api-key').value.trim();
  if(!key){alert('Add your Anthropic API key first.');return;}
  S.key=key;localStorage.setItem('mb_dk',key);
  const oaKey=$('oa-key').value.trim();
  S.oaKey=oaKey; if(oaKey)localStorage.setItem('mb_oa',oaKey);
  S.jd=$('jd-input').value.trim();
  S.cq=0;S.answers=[];S.scores=[];S.selfR=[];S.sessStart=Date.now();
  S.wk={own:0,met:0,hed:0,str:0,len:0};
  const t=T[S.lang];
  showP('ph-loading');$('load-title').textContent=t.load;
  const msgs=S.lang==='de'?['Rolle wird analysiert...','Fragen werden ausgewählt...','Auf dein Profil angepasst...']:['Analysing the role...','Picking the hardest questions...','Tailoring to your profile...'];
  let mi=0;const li=setInterval(()=>{mi=(mi+1)%msgs.length;$('load-msg').textContent=msgs[mi];},1400);
  try{
    S.questions=pickQuestions(S.lang,S.mode,S.jd);
  }catch(e){
    console.error('Question selection error:',e);
    S.questions=(QUESTION_META[S.lang]||QUESTION_META.en).slice(0,S.total).map(x=>x.q);
  }finally{
    clearInterval(li);
  }
  showQ();
}

function showQ(){
  stopTimer();stopRec();
  const t=T[S.lang];const q=S.questions[S.cq];const pct=(S.cq/S.total)*100;
  ['prog','prog-sr','prog-fb'].forEach(id=>{const el=$(id);if(el)el.style.width=pct+'%';});
  $('q-meta').textContent=S.lang==='de'?`Frage ${S.cq+1} von ${S.total}`:`Question ${S.cq+1} of ${S.total}`;
  $('q-text').textContent=q;
  qsa('.cpar-pill').forEach((p,i)=>{const lb=CPAR[S.lang];if(lb[i])p.textContent=lb[i];});
  $('txlive').textContent=S.lang==='de'?'Deine Worte erscheinen hier...':'Your words will appear here...';
  $('txlive').classList.remove('has-text');
  $('timer').textContent='0:00';$('timer').className='timer-display';
  $('timer-bar').style.width='100%';$('timer-bar').style.background='var(--sage)';
  $('rec-dot').classList.remove('on');
  $('rec-status-text').textContent=t.ready;
  $('rec-btn').textContent=t.start;$('rec-btn').style.display='inline-flex';
  $('submit-btn').style.display='none';$('submit-btn').textContent=t.getfb;
  $('skip-btn').textContent=t.skip;$('q-hint').textContent=t.hint;
  if($('text-answer'))$('text-answer').value='';
  S.transcript='';S.finalTranscript='';S.recording=false;S.stopRequested=false;S.timerSec=0;S.selfRat={};
  $('voice-area').style.display=S.input==='voice'?'block':'none';
  $('text-area').style.display=S.input==='text'?'block':'none';
  if(S.input==='text')$('rec-btn').textContent=t.timer;
  qsa('.star').forEach(s=>s.classList.remove('on'));
  showP('ph-question');
}

/* TIMER */
function startTimer(){
  S.timerSec=0;S.maxS=120;S.maxS=120;const sa=performance.now();
  S.timerInt=setInterval(()=>{
    const e=Math.floor((performance.now()-sa)/1000);S.timerSec=e;
    const mm=Math.floor(e/60),ss=String(e%60).padStart(2,'0');
    const d=$('timer'),b=$('timer-bar');
    if(d){d.textContent=`${mm}:${ss}`;d.className='timer-display'+(e>90?' danger':e>60?' warn':'');}
    if(b){b.style.width=Math.max(0,100-(e/S.maxS)*100)+'%';b.style.background=e>90?'var(--danger)':e>60?'var(--warn)':'var(--sage)';}
    if(e>=120)stopAndReady();
  },1000);
}
function startTextTimer(){
  $('rec-btn').textContent=T[S.lang].stop;S.timerSec=0;const sa=performance.now();
  S.timerInt=setInterval(()=>{
    const e=Math.floor((performance.now()-sa)/1000);S.timerSec=e;
    const mm=Math.floor(e/60),ss=String(e%60).padStart(2,'0');
    const d=$('timer-text'),b=$('timer-bar-text');
    if(d){d.textContent=`${mm}:${ss}`;d.className='timer-display'+(e>90?' danger':e>60?' warn':'');}
    if(b){b.style.width=Math.max(0,100-(e/120)*100)+'%';}
  },1000);
  $('submit-btn').style.display='inline-flex';
}
function stopTimer(){if(S.timerInt){clearInterval(S.timerInt);S.timerInt=null;}}

/* RECORDING */
function handleRecBtn(){
  if(S.input==='voice'){if(!S.recording&&!S.transcribing)startRec();else if(S.recording)stopAndReady();}
  else{if(!S.timerInt)startTextTimer();else stopTimer();}
}
async function startRec(){
  if(!navigator.mediaDevices||!window.MediaRecorder){alert('Voice recording is not supported here. Use text mode.');return;}
  if(!S.oaKey){alert('Add your OpenAI transcription key first.');return;}
  try{
    const stream=await navigator.mediaDevices.getUserMedia({
      audio:{
        echoCancellation:true,
        noiseSuppression:true,
        autoGainControl:true,
      }
    });
    S.stopRequested=false;
    S.transcribing=false;
    S.audioStream=stream;
    S.audioChunks=[];
    S.finalTranscript='';
    S.transcript='';
    const opts={};
    if(MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) opts.mimeType='audio/webm;codecs=opus';
    else if(MediaRecorder.isTypeSupported('audio/webm')) opts.mimeType='audio/webm';
    S.recorder=new MediaRecorder(stream,opts);
    S.recorder.ondataavailable=e=>{if(e.data&&e.data.size>0)S.audioChunks.push(e.data);};
    S.recorder.onstop=()=>{if(S.stopRequested)transcribeAudio();};
    S.recording=true;
    $('rec-dot').classList.add('on');
    $('rec-status-text').textContent=T[S.lang].rec;
    $('rec-btn').textContent=T[S.lang].stop;
    $('voice-area').classList.add('live');
    $('txlive').textContent=S.lang==='de'?'Sprich jetzt...':'Speak now...';
    $('txlive').classList.add('has-text');
    startTimer();
    startVol(stream);
    S.recorder.start(250);
  }catch(e){
    console.warn('MediaRecorder start failed:',e);
    alert('Could not start mic.');
  }
}
function stopAndReady(){
  S.stopRequested=true;
  stopTimer();
  stopVol();
  $('rec-btn').textContent=S.lang==='de'?'Transkribiere...':'Transcribing...';
  $('rec-status-text').textContent=S.lang==='de'?'Transkribiere...':'Transcribing...';
  if(S.recorder&&S.recorder.state!=='inactive'){
    try{S.recorder.stop();}catch(e){console.warn('Recorder stop failed:',e);transcribeAudio();}
  }else{
    transcribeAudio();
  }
}
function stopRec(){
  S.recording=false;
  if(S.recorder){try{if(S.recorder.state!=='inactive')S.recorder.stop();}catch(e){}S.recorder=null;}
  if(S.audioStream){try{S.audioStream.getTracks().forEach(t=>t.stop());}catch(e){}S.audioStream=null;}
  S.audioChunks=[];
  stopTimer();stopVol();
  $('rec-dot').classList.remove('on');
  $('voice-area').classList.remove('live');
}
function startVol(stream){
  if(!navigator.mediaDevices||!stream)return;
  if(S.actx){try{S.actx.close();}catch(e){}S.actx=null;}
  try{
    S.actx=new (window.AudioContext||window.webkitAudioContext)();
    S.analyser=S.actx.createAnalyser();S.analyser.fftSize=64;
    S.actx.createMediaStreamSource(stream).connect(S.analyser);
    const bars=qsa('.vb'),data=new Uint8Array(S.analyser.frequencyBinCount);
    function draw(){
      if(!S.recording){bars.forEach(b=>{b.style.height='3px';b.style.background='var(--surface2)';});return;}
      S.analyser.getByteFrequencyData(data);
      bars.forEach((b,i)=>{const v=data[i*3]||0;b.style.height=Math.max(3,Math.min(22,v/6))+'px';b.style.background=v>80?'var(--sage)':'var(--surface2)';});
      S.raf=requestAnimationFrame(draw);
    }
    draw();
  }catch(e){}
}
function stopVol(){
  if(S.raf){cancelAnimationFrame(S.raf);S.raf=null;}
  if(S.actx){try{S.actx.close();}catch(e){}S.actx=null;}
}

async function transcribeAudio(){
  if(S.transcribing)return;
  S.transcribing=true;
  const t=T[S.lang];
  $('rec-status-text').textContent=S.lang==='de'?'Audio wird gesendet...':'Sending audio...';
  const chunks=S.audioChunks.slice();
  const blob=new Blob(chunks,{type:chunks[0]?.type||'audio/webm'});
  if(!blob.size){
    S.transcribing=false;
    stopRec();
    $('rec-btn').textContent=t.rerec;
    $('rec-status-text').textContent=S.lang==='de'?'Kein Audio aufgenommen.':'No audio captured.';
    return;
  }
  const fd=new FormData();
  fd.append('action','transcribe');
  fd.append('openai_key',S.oaKey);
  fd.append('lang',S.lang);
  fd.append('audio',blob,'answer.webm');
  try{
    const res=await fetch(location.href,{method:'POST',body:fd});
    const data=await res.json();
    const errMsg=typeof data.error==='string'
      ? data.error
      : data.error?.message || data.error?.error?.message || data.message || '';
    if(!res.ok||data.error){
      throw new Error(errMsg || `Transcription failed (HTTP ${res.status})`);
    }
    const text=(data.text||'').trim();
    S.finalTranscript=text;
    S.transcript=text;
    const el=$('txlive');
    if(el){el.textContent=text||'...';el.classList.toggle('has-text',text.length>0);}
    $('submit-btn').style.display='inline-flex';
    $('rec-btn').textContent=t.rerec;
    $('rec-status-text').textContent=S.lang==='de'?'Bereit — prüfe das Transkript':'Ready — review transcript';
  }catch(e){
    console.error('Transcription failed:',e);
    $('rec-status-text').textContent=S.lang==='de'?'Transkription fehlgeschlagen':'Transcription failed';
    alert(e?.message || 'Transcription failed');
  }finally{
    stopRec();
    S.transcribing=false;
  }
}

/* SUBMIT */
async function submitAnswer(){
  const ans=S.input==='voice'?(S.finalTranscript||S.transcript).trim():$('text-answer').value.trim();
  if(ans.length<30){alert(S.lang==='de'?'Antwort zu kurz.':'Answer too short. Give at least 30 seconds.');return;}
  S.answers.push({q:S.questions[S.cq],a:ans,t:S.timerSec});
  const pct=((S.cq+1)/S.total)*100;
  $('prog-sr').style.width=pct+'%';
  const t=T[S.lang];
  $('sr-title').textContent=t.srt;$('sr-sub').textContent=t.srs;$('sr-btn').textContent=t.seefb;
  S.selfRat={};qsa('.star').forEach(s=>s.classList.remove('on'));
  showP('ph-selfrate');
}

function showFeedback(){
  S.selfR.push({...S.selfRat});
  const pct=((S.cq+1)/S.total)*100;
  $('prog-fb').style.width=pct+'%';
  ['dc-str','dc-own','dc-met','dc-ov'].forEach(id=>{const el=$(id);if(el){el.textContent='—';el.closest('.dim').className='dim';}});
  $('ring-fg').style.strokeDashoffset='226.2';$('ring-fg').style.stroke='var(--sage)';$('ring-num').textContent='—';
  $('tag-row').innerHTML='';
  const t=T[S.lang];
  $('fb-stream').innerHTML=`<div class="ldots"><span></span><span></span><span></span><span class="lmsg">${t.scoring}</span></div>`;
  $('fb-label').textContent=t.fbl;$('ans-label').textContent=t.ansl;
  $('fb-btns').style.display='none';
  const ans=S.answers[S.answers.length-1];
  $('tx-display').textContent=ans.a;
  showP('ph-feedback');
  scoreFeedback(ans.q,ans.a,ans.t);
}

function retryQuestion(){
  if(S.answers.length)S.answers.pop();
  if(S.scores.length)S.scores.pop();
  if(S.selfR.length)S.selfR.pop();
  S.selfRat={};
  S.transcript='';
  S.finalTranscript='';
  S.timerSec=0;
  stopTimer();
  stopRec();
  if($('text-answer'))$('text-answer').value='';
  showQ();
}

async function scoreFeedback(question,answer,timeSec){
  const wc=answer.split(/\s+/).length;const isDE=S.lang==='de';
  const prompt=isDE
    ?`Frage: "${question}"\n\nManus Antwort (${timeSec}s, ~${wc} Wörter):\n"${answer}"\n\nBewerte streng. Zitiere seine exakten Worte bei Kritik. Jede Zahl, Zahlwort oder Mengenangabe im Antworttext zählt als Metric-Hinweis.\n\nNur JSON. Alle Score-Felder müssen Zahlen von 1 bis 10 sein. Nie null, nie leer, nie Text.\n{"overall":1,"structure":1,"ownership":1,"metrics":1,"weaknesses_triggered":[],"what_worked":"","critical_fix":"","rewrite_sentence":"","cut_this":"","say_this":"","strongest_line":"","filler_words_found":[],"metric_evidence":[],"we_count":0,"metric_cited":false,"word_count":${wc}}`
    :`Question: "${question}"\n\nManu's answer (${timeSec}s, ~${wc} words):\n"${answer}"\n\nScore harshly. Quote exact words for every negative. Any explicit number, spelled-out number, or quantity word in the answer counts as metric evidence.\n\nReturn ONLY JSON. All score fields must be numbers from 1 to 10. Never null, never blank, never text.\n{"overall":1,"structure":1,"ownership":1,"metrics":1,"weaknesses_triggered":[],"what_worked":"","critical_fix":"","rewrite_sentence":"","cut_this":"","say_this":"","strongest_line":"","filler_words_found":[],"metric_evidence":[],"we_count":0,"metric_cited":false,"word_count":${wc}}`;
  try{
    const res=await claude([{role:'user',content:prompt}],320);
    const d=extractJson(res);
    const s=normalizeScoreResponse(d, question, answer, timeSec);
    animRing(s.overall);
    [{id:'dc-str',v:s.structure},{id:'dc-own',v:s.ownership},{id:'dc-met',v:s.metrics},{id:'dc-ov',v:s.overall}].forEach(({id,v})=>{
      const el=$(id);if(el){el.textContent=v+'/10';el.closest('.dim').className='dim '+(v>=7?'good':v>=5?'warn':'bad');}
    });
    if(s.weaknesses_triggered){
      s.weaknesses_triggered.forEach(w=>{
        if(w.includes('ownership'))S.wk.own++;
        if(w.includes('metric'))S.wk.met++;
        if(w.includes('hedge')||w.includes('spiral'))S.wk.hed++;
        if(w.includes('structure'))S.wk.str++;
      });
    }
    if(s.word_count>180)S.wk.len++;
    const wt=(s.weaknesses_triggered||[]).map(w=>`<span class="wtag">${w}</span>`).join('');
    const mt=s.metric_cited?`<span class="wtag pass">${isDE?'Zahl genannt ✓':'metric cited ✓'}</span>`:`<span class="wtag">${isDE?'keine Zahl':'no metric'}</span>`;
    const we=s.we_count>1?`<span class="wtag">${isDE?`"wir" ${s.we_count}x`:`"we" ${s.we_count}x`}</span>`:'';
    const me=s.metric_evidence?.length?`<span class="wtag pass">${s.metric_evidence.slice(0,3).join(', ')}</span>`:'';
    $('tag-row').innerHTML=wt+mt+we+me;
    let html='';
    if(s.what_worked)html+=`<strong>${isDE?'Was gut war':'What worked'}:</strong>\n${s.what_worked}\n\n`;
    if(s.critical_fix)html+=`<strong>${isDE?'Das wichtigste zu verbessern':'Fix this first'}:</strong>\n${s.critical_fix}\n\n`;
    if(s.rewrite_sentence)html+=`<strong>${isDE?'Bessere Eröffnung':'Better opening'}:</strong>\n"${s.rewrite_sentence}"\n\n`;
    if(s.cut_this)html+=`<strong>${isDE?'Streichen':'Cut this'}:</strong>\n${s.cut_this}\n\n`;
    if(s.say_this)html+=`<strong>${isDE?'Stattdessen sagen':'Say this instead'}:</strong>\n${s.say_this}\n\n`;
    if(s.strongest_line)html+=`<strong>${isDE?'Stärkste Zeile':'Strongest line'}:</strong>\n${s.strongest_line}\n\n`;
    if(s.filler_words_found?.length)html+=`<strong>${isDE?'Füllwörter':'Filler words'}:</strong> ${s.filler_words_found.join(', ')}\n\n`;
    if(s.we_count>1)html+=`<strong>${isDE?`"Wir" ${s.we_count}× gesagt.`:`Said "we" ${s.we_count} times.`}</strong> ${isDE?'Sag "Ich".':'Use "I".'}\n\n`;
    if(!s.metric_cited)html+=`<strong>${isDE?'Keine Zahl.':'No metric.'}</strong> ${isDE?'Cognigy: -30%. Lengoo: +15% CSAT, +20% Adoption.':'Cognigy: -30% resolution. Lengoo: +15% CSAT, +20% adoption.'}\n`;
    $('fb-stream').innerHTML=`<div class="feedback-body fu">${html.trim()}</div>`;
    S.scores.push({overall:s.overall,structure:s.structure,ownership:s.ownership,metrics:s.metrics,weaknesses:s.weaknesses_triggered||[],metric_cited:s.metric_cited,we_count:s.we_count||0});
    $('fb-btns').style.display='flex';
    $('retry-btn').textContent=S.lang==='de'?T.de.retry:T.en.retry;
    $('next-btn').textContent=S.lang==='de'?T.de.next:T.en.next;
  }catch(e){
    console.error('Score error:', e);
    $('fb-stream').innerHTML=`<div class="feedback-body">${S.lang==='de'?'Fehler: ':'Error: '}${e.message||'unknown'}</div>`;
    $('fb-btns').style.display='flex';
    $('retry-btn').textContent=S.lang==='de'?T.de.retry:T.en.retry;
    S.scores.push({overall:0,structure:0,ownership:0,metrics:0,weaknesses:[],metric_cited:false,we_count:0});
  }
}

function animRing(score){
  const circ=226.2,offset=circ-(circ*(score/10));
  const ring=$('ring-fg'),num=$('ring-num');
  ring.style.strokeDashoffset=offset;
  ring.style.stroke=score>=7?'var(--sage)':score>=5?'var(--warn)':'var(--danger)';
  let cur=0;const step=setInterval(()=>{cur+=0.4;num.textContent=Math.min(Math.round(cur),score)+'/10';if(cur>=score)clearInterval(step);},40);
}

function nextQuestion(){S.cq++;if(S.cq>=S.total)buildSummary();else showQ();}
function skipQuestion(){
  S.answers.push({q:S.questions[S.cq],a:'[skipped]',t:0});
  S.scores.push({overall:0,structure:0,ownership:0,metrics:0,weaknesses:['skipped'],metric_cited:false,we_count:0});
  S.selfR.push({});S.cq++;
  if(S.cq>=S.total)buildSummary();else showQ();
}

function buildSummary(){
  stopTimer();stopRec();
  const isDE=S.lang==='de';
  const answered=S.scores.filter(s=>s.overall>0).length;
  const avg=answered?Math.round(S.scores.filter(s=>s.overall>0).reduce((a,b)=>a+b.overall,0)/answered):0;
  const totalMin=Math.round((Date.now()-S.sessStart)/60000);
  if(avg>0)saveS(avg);
  updateStats();
  setTimeout(()=>{
    const ring=$('sum-ring');const circ=314.2;
    ring.style.strokeDashoffset=circ-(circ*(avg/10));
    ring.style.stroke=avg>=7?'var(--sage)':avg>=5?'var(--warn)':'var(--danger)';
    $('sum-ring-num').textContent=avg;
  },200);
  $('sum-meta').textContent=isDE?`${answered} Antwort${answered!==1?'en':''} bewertet`:`${answered} answer${answered!==1?'s':''} scored`;
  $('sum-answered').textContent=answered;$('sum-avg').textContent=avg+'/10';$('sum-time').textContent=totalMin+'m';
  const wk=S.wk;const patterns=[];
  if(isDE){
    if(wk.own>=2)patterns.push({i:'⚠',t:`"Wir" statt "Ich" in ${wk.own} Antworten. Dein konstantestes Muster.`});
    if(wk.met>=2)patterns.push({i:'⚠',t:`Keine Zahl in ${wk.met} Antworten. Cognigy: -30%. Lengoo: +15% CSAT.`});
    if(wk.hed>=2)patterns.push({i:'⚠',t:`Füllwörter in ${wk.hed} Antworten. Eliminiere "ich denke", "vielleicht".`});
    if(wk.str>=2)patterns.push({i:'⚠',t:`Struktur in ${wk.str} Antworten kollabiert. Erster Satz = direkte Antwort.`});
    if(wk.len>=2)patterns.push({i:'⚠',t:`Zu lang in ${wk.len} Fällen. Ziel: 60–90 Sek.`});
    if(!patterns.length)patterns.push({i:'✓',t:'Keine großen Muster. Weiter so täglich.'});
  }else{
    if(wk.own>=2)patterns.push({i:'⚠',t:`Said "we" not "I" in ${wk.own} answers. Most consistent pattern.`});
    if(wk.met>=2)patterns.push({i:'⚠',t:`No metric in ${wk.met} answers. Cognigy: -30%. Lengoo: +15% CSAT, +20% adoption.`});
    if(wk.hed>=2)patterns.push({i:'⚠',t:`Hedging in ${wk.hed} answers. Eliminate "I think", "maybe".`});
    if(wk.str>=2)patterns.push({i:'⚠',t:`Structure collapsed in ${wk.str} answers. First sentence = direct answer.`});
    if(wk.len>=2)patterns.push({i:'⚠',t:`Too long in ${wk.len} cases. Target 60–90s.`});
    if(!patterns.length)patterns.push({i:'✓',t:'No major patterns. Keep the daily reps going.'});
  }
  $('pat-label').textContent=T[S.lang].patterns;
  $('pat-list').innerHTML=patterns.map(p=>`<li><span class="pi">${p.i}</span><span>${p.t}</span></li>`).join('');
  const top=Object.entries(wk).sort((a,b)=>b[1]-a[1])[0];
  const pm=isDE?{own:'Morgen: Jede "wir" zählen. Ziel: null. Ersetze mit "Ich habe entschieden".',met:'Morgen: Zahl aufschreiben bevor du antwortest. Cognigy: -30%. Lengoo: +15% CSAT.',hed:'Morgen: Ersten Satz ohne Füllwörter. Direkt zur Aussage.',str:'Morgen: KPAE aufschreiben bevor du sprichst.',len:'Morgen: 90-Sek-Timer. Stop wenn er klingelt.'}:{own:"Tomorrow: count every 'we'. Target zero. Replace with 'I decided'.",met:"Tomorrow: write your metric before answering. Cognigy: -30%. Lengoo: +15% CSAT.",hed:"Tomorrow: first sentence with zero qualifying words.",str:"Tomorrow: write CPAR before speaking.",len:"Tomorrow: 90-second timer. Stop when it rings."};
  const fb=isDE?'Dieselben Fragen morgen wieder.':'Same questions tomorrow.';
  $('pri-label').textContent=T[S.lang].priority;
  $('pri-text').textContent=top&&top[1]>0?(pm[top[0]]||fb):fb;
  $('new-btn').textContent=T[S.lang].newsess;$('drill-btn').textContent=T[S.lang].drillw;
  showP('ph-summary');
}

function drillWeakest(){
  const top=Object.entries(S.wk).sort((a,b)=>b[1]-a[1])[0];
  const isDE=S.lang==='de';
  const dq=isDE?{own:'Beschreibe eine Entscheidung, die DU persönlich getroffen hast. Zahlen.',met:'Erkläre eine Produktverbesserung. Zahlen vorher und nachher.',hed:'Wie priorisierst du ein Backlog? Direkte Antwort in einem Satz.',str:'Stell dich in 4 Sätzen vor: wer du bist, was du gebaut hast, Ergebnis, warum du hier bist.',len:'Größter Erfolg als PM? Unter 60 Sekunden.'}:{own:"Tell me about a decision you personally made. Numbers.",met:"Walk me through a product improvement. Before and after numbers.",hed:"How do you prioritize a backlog? One direct sentence.",str:"Tell me about yourself in 4 sentences: who, what you built, result, why you're here.",len:"Your biggest PM achievement? Under 60 seconds."};
  if(top&&top[1]>0&&dq[top[0]]){
    S.questions=[dq[top[0]]];S.total=1;S.cq=0;S.answers=[];S.scores=[];S.selfR=[];
    S.sessStart=Date.now();S.wk={own:0,met:0,hed:0,str:0,len:0};showQ();
  }else restartSession();
}
function restartSession(){resetSessionState();updateStats();showP('ph-setup');}

/* CLAUDE */
async function claude(messages,maxTokens=400){
  const models=['claude-3-5-haiku-latest','claude-sonnet-4-20250514'];
  let lastErr=null;
  for(const model of models){
    const r=await fetch('https://api.anthropic.com/v1/messages',{method:'POST',headers:{'Content-Type':'application/json','x-api-key':S.key,'anthropic-version':'2023-06-01','anthropic-dangerous-direct-browser-access':'true'},body:JSON.stringify({model,max_tokens:maxTokens,system:ANTHROPIC_SYSTEM,messages})});
    const text=await r.text();
    if(r.ok){
      let json;
      try{
        json=JSON.parse(text);
      }catch(e){
        throw new Error(`Bad API response: ${text.slice(0,240)}`);
      }
      const out=json&&json.content&&json.content[0]&&json.content[0].text;
      if(typeof out!=='string')throw new Error(`Missing content in API response: ${text.slice(0,240)}`);
      if(json.usage)console.info('Anthropic usage',model,json.usage);
      return out;
    }
    lastErr=new Error(`API ${r.status}: ${text.slice(0,240)}`);
    const isFallbackable=
      r.status===404||
      text.includes('not_found_error')||
      [408,429,500,502,503,504,529].includes(r.status);
    if(!isFallbackable)break;
  }
  throw lastErr||new Error('API request failed');
}

function showP(id){qsa('.phase').forEach(p=>p.classList.remove('active'));$(id).classList.add('active');window.scrollTo({top:0,behavior:'smooth'});}
</script>
</body>
</html>

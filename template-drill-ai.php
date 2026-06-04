<?php
/* Template Name: Interview Drill */
if($_SERVER['REQUEST_METHOD']==='POST'&&(($_POST['action']??'')==='transcribe')){
  header('Content-Type: application/json; charset=utf-8');
  $apiKey=trim($_POST['openai_key']??'');
  $lang=trim($_POST['lang']??'');
  if($apiKey===''){http_response_code(400);echo json_encode(['error'=>'Missing OpenAI API key']);exit;}
  if(empty($_FILES['audio']['tmp_name'])||!is_uploaded_file($_FILES['audio']['tmp_name'])){http_response_code(400);echo json_encode(['error'=>'Missing audio upload']);exit;}
  $ch=curl_init('https://api.openai.com/v1/audio/transcriptions');
  $payload=['model'=>'gpt-4o-mini-transcribe','file'=>new CURLFile($_FILES['audio']['tmp_name'],$_FILES['audio']['type']?:'audio/webm',$_FILES['audio']['name']?:'answer.webm')];
  if($lang==='en'||$lang==='de')$payload['language']=$lang;
  curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$apiKey]]);
  $raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$err=curl_error($ch);curl_close($ch);
  if($raw===false){http_response_code(500);echo json_encode(['error'=>$err?:'OpenAI transcription failed']);exit;}
  http_response_code($status?:200);echo $raw;exit;
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
  --sage:#6fcf97;
  --gold:#c8a840;--gold-bg:rgba(200,168,64,0.1);
  --r:8px;--rl:12px;
}
html{font-size:16px;-webkit-text-size-adjust:100%}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100dvh;line-height:1.6}
.shell{max-width:720px;margin:0 auto;padding:0 1.5rem 5rem}

/* NAV */
nav{position:sticky;top:0;z-index:40;background:var(--bg);display:flex;align-items:center;justify-content:space-between;padding:1rem 0;border-bottom:1px solid var(--border);margin-bottom:0;flex-wrap:wrap;gap:8px}
.nav-right{flex-wrap:wrap}
.nav-logo{font-family:'Shippori Mincho',serif;font-size:16px;font-weight:600;letter-spacing:0.03em;color:var(--ink);text-decoration:none;cursor:pointer}
.nav-dot{color:var(--accent)}
.nav-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.api-pill{display:inline-flex;align-items:center;gap:5px;font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);letter-spacing:0.08em;text-transform:uppercase;padding:4px 10px;border-radius:20px;border:1px solid var(--border-med);background:none;cursor:pointer;transition:all 0.2s;white-space:nowrap}
.api-pill:hover{border-color:var(--border-strong)}
.api-pill.connected{border-color:rgba(111,207,151,0.35);color:var(--sage)}
.api-dot{width:5px;height:5px;border-radius:50%;background:var(--ink-ghost);transition:background 0.3s;flex-shrink:0}
.api-pill.connected .api-dot{background:var(--sage);box-shadow:0 0 5px rgba(111,207,151,0.5)}
.streak-pill{display:none;align-items:center;gap:4px;background:var(--gold-bg);border:1px solid rgba(200,168,64,0.2);border-radius:20px;padding:3px 10px;font-family:'DM Mono',monospace;font-size:10px;color:var(--gold)}
.settings-btn{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);letter-spacing:0.08em;text-transform:uppercase;padding:4px 10px;border-radius:20px;border:1px solid var(--border-med);background:none;cursor:pointer;transition:all 0.2s}
.settings-btn:hover{background:var(--surface);border-color:var(--border-strong);color:var(--ink)}

/* MODE TABS */
.mode-tabs{display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:2rem;overflow-x:auto;scrollbar-width:none}
.mode-tabs::-webkit-scrollbar{display:none}
.mode-tab{font-family:'DM Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-ghost);padding:0.75rem 1.25rem;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;transition:all 0.2s;white-space:nowrap;margin-bottom:-1px}
.mode-tab:hover{color:var(--ink-dim)}
.mode-tab.active{color:var(--accent);border-bottom-color:var(--accent)}

/* VIEWS */
.view{display:none}.view.active{display:block}

/* COMMON */
h1{font-family:'Shippori Mincho',serif;font-size:clamp(26px,5vw,42px);font-weight:600;line-height:1.1;margin-bottom:0.75rem;color:var(--ink)}
h2{font-family:'Shippori Mincho',serif;font-size:20px;font-weight:600;margin-bottom:0.5rem;color:var(--ink)}
h3{font-family:'Shippori Mincho',serif;font-size:16px;font-weight:600;color:var(--ink)}
.acc{color:var(--accent)}
.sub{font-size:14px;color:var(--ink-dim);line-height:1.7;margin-bottom:1.5rem}
.slabel{display:block;font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:8px}
.eyebrow{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
.edot{width:5px;height:5px;border-radius:50%;background:var(--accent);display:inline-block}
.hint{font-size:12px;color:var(--ink-ghost);margin-top:6px;line-height:1.5}
.divider{height:1px;background:var(--border);margin:1.5rem 0}

/* CARDS */
.card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1rem}
.card-accent{background:var(--accent-dim);border:1px solid rgba(232,255,0,0.15);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1rem}
.card-warn{background:var(--warn-bg);border:1px solid rgba(212,144,64,0.25);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1rem}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;padding:10px 20px;border-radius:var(--r);border:1px solid var(--border-med);background:transparent;color:var(--ink);cursor:pointer;transition:all 0.15s;-webkit-tap-highlight-color:transparent;touch-action:manipulation;min-height:44px}
.btn:hover{background:var(--surface2);border-color:var(--border-strong)}.btn:active{transform:scale(0.97)}
.btn-primary{background:var(--accent);color:#000;border-color:transparent;font-weight:600}.btn-primary:hover{background:var(--accent-hover)}
.btn-ghost{background:none;border:none;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--ink-ghost);cursor:pointer;text-decoration:underline;-webkit-tap-highlight-color:transparent;padding:0}
.btn-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:1.25rem;align-items:center}

/* INPUTS */
input[type=text],input[type=password],textarea{width:100%;font-family:'DM Sans',sans-serif;font-size:16px;color:var(--ink);background:var(--surface);border:1px solid var(--border-med);border-radius:var(--r);padding:10px 14px;outline:none;transition:border-color 0.15s;-webkit-appearance:none}
input::placeholder,textarea::placeholder{color:var(--ink-ghost)}
input:focus,textarea:focus{border-color:var(--accent)}
textarea{resize:vertical;min-height:90px;line-height:1.6}
.api-row{display:flex;gap:8px;margin-bottom:1rem}.api-row input{flex:1}
.eye{background:none;border:1px solid var(--border-med);border-radius:var(--r);font-size:14px;cursor:pointer;padding:0 10px;height:44px;color:var(--ink-ghost)}

/* MODE GRID */
.mode-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:1.5rem}
.mode-card{border:1px solid var(--border-med);border-radius:var(--rl);padding:1rem 1.125rem;cursor:pointer;background:var(--bg3);transition:border-color 0.15s,background 0.15s;-webkit-tap-highlight-color:transparent}
.mode-card:hover{border-color:var(--border-strong);background:var(--surface)}
.mode-card.selected{border:1.5px solid var(--accent);background:var(--accent-dim)}
.mode-card .mct{font-size:14px;font-weight:500;color:var(--ink);margin-bottom:3px}
.mode-card .mcd{font-size:12px;color:var(--ink-ghost);line-height:1.4}

/* HERO */
.hero{padding:2rem 0 1.75rem;border-bottom:1px solid var(--border);margin-bottom:2rem}
.hero-title{font-family:'Shippori Mincho',serif;font-size:clamp(28px,5vw,44px);font-weight:600;line-height:1.1;margin-bottom:1rem;color:var(--ink)}
.hero-stats{display:flex;gap:2rem;flex-wrap:wrap;margin-top:1.25rem}
.hero-stat .hsv{font-family:'Shippori Mincho',serif;font-size:26px;font-weight:600;color:var(--accent);line-height:1;margin-bottom:3px}
.hero-stat .hsl{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);letter-spacing:0.06em;text-transform:uppercase}

/* TREND */
.trend-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1.5rem}
.trend-hdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.trend-lbl{font-size:13px;color:var(--ink-dim);font-weight:500}
.trend-best{font-family:'DM Mono',monospace;font-size:11px;color:var(--ink-ghost)}
.trend-bars{display:flex;align-items:flex-end;gap:8px;height:52px}
.tb-wrap{display:flex;flex-direction:column;align-items:center;gap:4px;flex:1}
.tb{width:100%;border-radius:3px 3px 0 0;min-height:3px;background:var(--surface2);transition:height 0.5s cubic-bezier(0.4,0,0.2,1)}
.tb.g{background:var(--accent)}.tb.w{background:var(--warn)}.tb.b{background:var(--danger)}.tb.e{opacity:0.3}
.td{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);text-align:center}
.trend-empty{font-size:13px;color:var(--ink-ghost);text-align:center;padding:14px 0}

/* PROGRESS */
.prog-track{height:2px;background:var(--surface2);border-radius:1px;margin-bottom:2rem}
.prog-bar{height:100%;background:var(--accent);border-radius:1px;transition:width 0.4s ease}

/* QUESTION SCREEN */
.q-meta{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:12px}
.q-text{font-family:'Shippori Mincho',serif;font-size:clamp(18px,3vw,24px);font-weight:600;line-height:1.4;margin-bottom:1.25rem;color:var(--ink)}
.cpar-row{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1.25rem}
.cpar-pill{font-family:'DM Mono',monospace;font-size:10px;letter-spacing:0.04em;padding:3px 9px;border-radius:20px;background:var(--surface);color:var(--ink-ghost);border:1px solid var(--border)}
.cpar-pill:first-child{border-color:rgba(232,255,0,0.25);color:rgba(232,255,0,0.75)}

/* TIMER + REC */
.rec-box{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1.25rem 1.5rem;margin-bottom:1rem;transition:border-color 0.3s}
.rec-box.live{border-color:rgba(224,80,80,0.4)}
.timer-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.timer-display{font-family:'DM Mono',monospace;font-size:24px;font-weight:500;color:var(--ink);font-variant-numeric:tabular-nums}
.timer-display.warn{color:var(--warn)}.timer-display.danger{color:var(--danger)}
.timer-track{height:3px;background:var(--surface2);border-radius:2px;margin-bottom:1rem;overflow:hidden}
.timer-fill{height:100%;background:var(--sage);border-radius:2px;transition:width 1s linear,background 0.5s}
.rec-status-row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink-dim);margin-bottom:10px}
.rec-dot{width:7px;height:7px;border-radius:50%;background:var(--ink-ghost);flex-shrink:0}
.rec-dot.on{background:var(--danger);animation:blink 1s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0.3}}
.vol-bars{display:flex;align-items:flex-end;gap:3px;height:22px;margin-bottom:10px}
.vb{width:3px;border-radius:2px;background:var(--surface2);min-height:3px;transition:height 0.08s}
.txlive{font-size:14px;color:var(--ink-ghost);line-height:1.7;min-height:56px;font-style:italic}
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

/* TAGS / WEAKNESS TAGS */
.tag-row{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
.wtag{font-size:11px;padding:3px 9px;border-radius:20px;background:var(--danger-bg);color:var(--danger);border:1px solid rgba(224,80,80,0.2)}
.wtag.pass{background:var(--success-bg);color:var(--success);border-color:rgba(80,160,112,0.2)}

/* FEEDBACK BODY */
.feedback-body{font-size:14px;line-height:1.8;color:var(--ink-dim);white-space:pre-wrap}
.feedback-body strong{color:var(--ink);font-weight:500}

/* SELF RATE */
.rate-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:1.5rem}
.rate-cell{border:1px solid var(--border-med);border-radius:var(--r);padding:10px 12px;background:var(--bg3)}
.rate-cell .rl{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:6px}
.stars{display:flex;gap:4px}
.star{font-size:18px;cursor:pointer;color:var(--surface2);transition:color 0.1s;-webkit-tap-highlight-color:transparent}
.star.on{color:var(--accent)}

/* LOADING DOTS */
.dots{display:flex;gap:5px;align-items:center;padding:1.5rem 0}
.dots span{width:5px;height:5px;border-radius:50%;background:var(--ink-ghost);animation:bounce 1.2s infinite}
.dots span:nth-child(2){animation-delay:0.2s}.dots span:nth-child(3){animation-delay:0.4s}
@keyframes bounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-5px)}}
.dots-msg{font-size:14px;color:var(--ink-ghost);margin-left:8px}

/* SUMMARY */
.sum-hero{text-align:center;padding:2rem 0 1.75rem;border-bottom:1px solid var(--border);margin-bottom:1.75rem}
.sum-ring-wrap{display:inline-block;margin-bottom:1rem}
.sum-sub{font-size:13px;color:var(--ink-ghost)}
.sum-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:1.5rem}
.sm{background:var(--bg3);border:1px solid var(--border);border-radius:var(--rl);padding:1rem;text-align:center}
.sm .smv{font-family:'Shippori Mincho',serif;font-size:24px;font-weight:600;color:var(--ink);margin-bottom:3px;font-variant-numeric:tabular-nums}
.sm .sml{font-family:'DM Mono',monospace;font-size:9px;color:var(--ink-ghost);letter-spacing:0.07em;text-transform:uppercase}
.pattern-list{list-style:none}
.pattern-list li{padding:10px 0;border-bottom:1px solid var(--border);font-size:14px;color:var(--ink-dim);display:flex;gap:10px;align-items:flex-start;line-height:1.5}
.pattern-list li:last-child{border-bottom:none}
.pi{flex-shrink:0}

/* MODAL */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.8);z-index:50;align-items:flex-start;justify-content:center;padding-top:80px}
.modal-bg.open{display:flex}
.modal{background:var(--bg3);border:1px solid var(--border-med);border-radius:var(--rl);padding:1.75rem;width:calc(100% - 2rem);max-width:480px;max-height:85vh;overflow-y:auto}
.modal h2{font-family:'Shippori Mincho',serif;font-size:20px;font-weight:600;margin-bottom:0.5rem}
.modal .msub{font-size:13px;color:var(--ink-ghost);line-height:1.7;margin-bottom:1.25rem}

/* ── PACKMATIC STYLE COMPONENTS ── */
.pm-header{background:var(--bg3);border:1px solid var(--border-med);border-radius:var(--rl);padding:18px 20px;margin-bottom:20px;display:flex;gap:14px;align-items:flex-start}
.pm-logo{width:44px;height:44px;background:var(--surface);border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:'DM Mono',monospace;font-size:11px;font-weight:600;color:var(--ink-ghost);flex-shrink:0;letter-spacing:0.05em;border:1px solid var(--border)}
.pm-info h1{font-size:15px;font-weight:600;color:var(--ink);margin-bottom:3px;font-family:'DM Sans',sans-serif}
.pm-info p{font-size:12px;color:var(--ink-ghost);margin-bottom:10px}
.badges{display:flex;flex-wrap:wrap;gap:5px}
.badge{font-size:10px;padding:2px 9px;border-radius:20px;font-weight:500;font-family:'DM Mono',monospace;letter-spacing:0.03em}
.badge-y{background:#2a2500;color:#c8b400}
.badge-g{background:#0a2a1a;color:#4caf80}
.badge-r{background:#2a0f0f;color:#e06060}
.badge-b{background:#0a1020;color:#4a90d0}

.pm-alert{background:#1a1200;border:1px solid #3a2a00;border-radius:var(--r);padding:14px 16px;margin-bottom:20px}
.pm-alert-title{font-size:10px;font-weight:600;color:#c8a000;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:10px;font-family:'DM Mono',monospace}
.pm-alert-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.pm-alert-item{font-size:13px;color:#c8b888;display:flex;gap:8px;align-items:baseline}
.pm-alert-num{color:#c8a000;font-weight:600;font-size:12px;flex-shrink:0;font-family:'DM Mono',monospace}

.pm-section{font-size:10px;font-weight:600;color:var(--ink-ghost);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:12px;font-family:'DM Mono',monospace}
.pm-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:20px}
.pm-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--r);padding:13px}
.pm-card-tag{font-size:10px;font-weight:600;color:var(--ink-ghost);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:5px;font-family:'DM Mono',monospace}
.pm-card-title{font-size:13px;font-weight:600;color:var(--ink);margin-bottom:4px}
.pm-card-desc{font-size:12px;color:var(--ink-ghost);line-height:1.5}
.hl{color:#c8b400}

/* ACCORDION */
.q-block{background:var(--bg3);border:1px solid var(--border);border-radius:var(--r);margin-bottom:8px;overflow:hidden}
.q-head{padding:14px 16px;cursor:pointer;display:flex;justify-content:space-between;align-items:flex-start;gap:12px;transition:background 0.15s;-webkit-tap-highlight-color:transparent}
.q-head:hover{background:var(--surface)}
.q-num{font-size:10px;color:var(--ink-ghost);margin-bottom:3px;font-family:'DM Mono',monospace;letter-spacing:0.05em}
.q-label{font-size:14px;font-weight:500;color:var(--ink);line-height:1.4}
.q-arrow{font-size:16px;color:var(--ink-ghost);flex-shrink:0;transition:transform 0.2s;user-select:none;margin-top:2px}
.q-arrow.open{transform:rotate(180deg)}
.q-body{display:none;padding:0 16px 16px;border-top:1px solid var(--border)}
.q-body.open{display:block}
.q-tags{display:flex;flex-wrap:wrap;gap:6px;margin:12px 0 10px}
.tag-warn{font-size:11px;padding:2px 9px;border-radius:20px;background:#2a1500;color:#e08040;font-family:'DM Mono',monospace}
.tag-tip{font-size:11px;padding:2px 9px;border-radius:20px;background:#0a1a2a;color:#4a90d0;font-family:'DM Mono',monospace}
.ans-label{font-size:10px;font-weight:600;color:var(--ink-ghost);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;margin-top:12px;font-family:'DM Mono',monospace}
.ans-box{background:var(--bg2);border-radius:var(--r);padding:13px 15px;font-size:13.5px;line-height:1.8;color:var(--ink-dim);white-space:pre-wrap;border:1px solid var(--border)}
.insight{font-size:12px;color:var(--ink-ghost);border-left:2px solid var(--border-med);padding-left:10px;margin-top:12px;font-style:italic;line-height:1.5}
.practice-btn{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-family:'DM Mono',monospace;letter-spacing:0.04em;color:var(--accent);background:var(--accent-dim);border:1px solid rgba(232,255,0,0.2);border-radius:20px;padding:3px 10px;cursor:pointer;margin-top:12px;transition:background 0.15s;-webkit-tap-highlight-color:transparent}
.practice-btn:hover{background:rgba(232,255,0,0.18)}

/* METRICS TABLE */
.mt{width:100%;border-collapse:collapse;margin-bottom:20px}
.mt th{font-size:10px;font-weight:600;color:var(--ink-ghost);text-transform:uppercase;letter-spacing:0.05em;text-align:left;padding:8px 10px;border-bottom:1px solid var(--border-med);font-family:'DM Mono',monospace}
.mt td{font-size:13px;color:var(--ink-dim);padding:8px 10px;border-bottom:1px solid var(--border)}
.mt td:first-child{color:var(--ink-ghost)}
.mt td:nth-child(2){color:#c8b400;font-weight:600;font-family:'DM Mono',monospace}
.mt tr:last-child td{border-bottom:none}

/* DAILY PRACTICE SPECIFIC */
.day-card{background:var(--bg3);border:1px solid var(--border-strong);border-radius:var(--rl);padding:1.5rem;margin-bottom:1.5rem;position:relative;overflow:hidden}
.day-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--accent),transparent)}
.day-tag{font-family:'DM Mono',monospace;font-size:10px;color:var(--accent);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:8px}
.day-q{font-family:'Shippori Mincho',serif;font-size:clamp(17px,3vw,22px);font-weight:600;line-height:1.4;color:var(--ink);margin-bottom:1rem}
.pick-toggle{background:none;border:none;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--ink-ghost);cursor:pointer;text-decoration:underline;padding:0;margin-top:8px;display:block}
.pick-list{display:none;margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem}
.pick-list.open{display:block}
.pick-item{padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;color:var(--ink-dim);cursor:pointer;transition:color 0.15s;display:flex;justify-content:space-between;align-items:center}
.pick-item:last-child{border-bottom:none}
.pick-item:hover{color:var(--ink)}
.pick-day{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost)}

/* STUDY CARD (Daily Practice rehearsal step) */
.study-q{font-family:'Shippori Mincho',serif;font-size:clamp(19px,3.2vw,26px);font-weight:600;line-height:1.35;color:var(--ink);margin-bottom:1.25rem}
.study-keys{display:flex;flex-direction:column;gap:8px;margin-bottom:1.25rem}
.study-key{background:var(--accent-dim);border:1px solid rgba(232,255,0,0.18);border-radius:var(--r);padding:10px 14px;display:flex;gap:10px;align-items:flex-start}
.study-key-label{font-family:'DM Mono',monospace;font-size:9px;color:var(--accent);letter-spacing:0.06em;text-transform:uppercase;flex-shrink:0;width:74px;padding-top:2px}
.study-key-text{font-size:13.5px;color:var(--ink);line-height:1.5;font-weight:500}
.study-answer-label{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.08em;text-transform:uppercase;margin:1.25rem 0 8px;display:flex;align-items:center;gap:8px}
.study-answer-label::after{content:'';flex:1;height:1px;background:var(--border)}
.study-answer{background:var(--bg2);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;font-size:14px;line-height:1.85;color:var(--ink-dim);white-space:pre-wrap}
.study-tip{font-size:12px;color:var(--ink-ghost);border-left:2px solid var(--accent);padding-left:12px;margin-top:1rem;line-height:1.6;font-style:italic}
.rep-counter{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);letter-spacing:0.06em;text-transform:uppercase}
.compare-grid{display:grid;grid-template-columns:1fr;gap:10px;margin-top:0.75rem}
.compare-col-label{font-family:'DM Mono',monospace;font-size:9px;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:6px}
.compare-yours .compare-col-label{color:var(--ink-ghost)}
.compare-model .compare-col-label{color:var(--accent)}

/* CHECKLIST */
.checklist{list-style:none}
.checklist li{display:flex;align-items:flex-start;gap:10px;padding:9px 0;border-bottom:1px solid var(--border);font-size:13px;color:var(--ink-dim);line-height:1.5}
.checklist li:last-child{border-bottom:none}
.ci{font-size:14px;flex-shrink:0;width:20px;text-align:center}
.c-pass{color:var(--sage)}.c-warn{color:var(--warn)}.c-fail{color:var(--danger)}

/* PRACTICE OVERLAY */
.overlay{display:none;position:fixed;inset:0;background:var(--bg);z-index:60;overflow-y:auto;padding:1.5rem}
.overlay.open{display:block}
.overlay-inner{max-width:680px;margin:0 auto;padding-bottom:4rem}
.overlay-close{background:none;border:1px solid var(--border-med);border-radius:var(--r);font-size:13px;color:var(--ink-ghost);cursor:pointer;padding:6px 14px;font-family:'DM Sans',sans-serif;margin-bottom:1.5rem}

/* COMPANY PREP INPUT */
.prep-input-area{background:var(--bg3);border:1px solid var(--border-med);border-radius:var(--rl);padding:1.5rem;margin-bottom:1.5rem}
.saved-prep-item{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--r);margin-bottom:6px;gap:10px}
.saved-prep-item:hover{border-color:var(--border-med)}
.saved-prep-name{font-size:14px;color:var(--ink);font-weight:500;cursor:pointer;flex:1}
.saved-prep-meta{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost)}
.saved-prep-actions{display:flex;gap:6px}
.saved-prep-del{background:none;border:none;font-size:12px;color:var(--ink-ghost);cursor:pointer;padding:4px 8px;border-radius:var(--r);transition:background 0.15s}
.saved-prep-del:hover{background:var(--danger-bg);color:var(--danger)}
.prep-output{display:none}
.prep-output.active{display:block}

/* LOADING STATE */
.loading-state{display:none;text-align:center;padding:3rem 0}
.loading-state.active{display:block}

@keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.fu{animation:fadeUp 0.3s ease forwards}

@media(max-width:600px){
  /* shell */
  .shell{padding:0 1rem 5rem}

  /* nav */
  nav{padding:0.75rem 0;gap:8px}
  .nav-right{gap:6px}
  .api-pill{font-size:8px;padding:4px 8px}
  .settings-btn{font-size:8px;padding:4px 8px}
  .streak-pill{font-size:9px;padding:3px 8px}

  /* mode tabs */
  .mode-tabs{gap:0;margin-bottom:1.5rem}
  .mode-tab{font-size:9px;padding:0.65rem 0.875rem;letter-spacing:0.05em}

  /* hero */
  .hero-title{font-size:28px}
  .hero-stats{gap:1.5rem}
  .hero-stat .hsv{font-size:22px}

  /* mode grid — stack on mobile */
  .mode-grid{grid-template-columns:1fr}

  /* cards */
  .card{padding:1rem 1.125rem}
  .day-card{padding:1.25rem}

  /* question */
  .q-text{font-size:18px}
  .cpar-row{gap:4px}
  .cpar-pill{font-size:9px;padding:2px 7px}

  /* score */
  .score-card{flex-direction:column;align-items:flex-start;gap:1rem}
  .dims{grid-template-columns:1fr 1fr}

  /* self rate */
  .rate-grid{grid-template-columns:1fr 1fr}

  /* summary */
  .sum-metrics{grid-template-columns:repeat(3,1fr)}
  .sm .smv{font-size:20px}

  /* prep */
  .pm-grid{grid-template-columns:1fr}
  .pm-alert-grid{grid-template-columns:1fr}
  .pm-header{flex-direction:column;gap:10px}

  /* modal */
  .modal{width:calc(100% - 1rem);padding:1.25rem}

  /* overlay */
  .overlay{padding:1rem}

  /* buttons */
  .btn{font-size:13px;padding:10px 16px}
  .btn-row{gap:8px}
  .timer-display{font-size:20px}
}
.qr-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:60;align-items:center;justify-content:center;padding:1.5rem}
.qr-modal-bg.open{display:flex}
.qr-modal{background:var(--bg3);border:1px solid var(--border-med);border-radius:var(--rl);padding:1.75rem;width:100%;max-width:380px;text-align:center}
.qr-modal h2{font-family:'Shippori Mincho',serif;font-size:20px;font-weight:600;margin-bottom:0.5rem}
.qr-modal p{font-size:13px;color:var(--ink-ghost);line-height:1.6;margin-bottom:1.25rem}
.qr-box{display:inline-block;background:#fff;padding:12px;border-radius:var(--r);margin-bottom:1.25rem}
.qr-warn{font-size:11px;color:var(--warn);background:var(--warn-bg);border:1px solid rgba(212,144,64,0.2);border-radius:var(--r);padding:8px 12px;margin-bottom:1.25rem;line-height:1.5}
.qr-expire{font-family:'DM Mono',monospace;font-size:10px;color:var(--ink-ghost);margin-bottom:1rem}
</style>
</head>
<body>
<div class="shell">

<!-- NAV -->
<nav>
  <a href="#" class="nav-logo" onclick="goHome(event)">Manuel Becerra<span class="nav-dot">.</span></a>
  <div class="nav-right">
    <button class="api-pill" id="oai-pill" onclick="openModal('modal-keys')" title="OpenAI voice transcription">
      <span class="api-dot" id="oai-dot"></span>Voice
    </button>
    <button class="api-pill" id="claude-pill" onclick="openModal('modal-keys')" title="Anthropic AI scoring">
      <span class="api-dot" id="claude-dot"></span>AI Score
    </button>
    <div class="streak-pill" id="streak-pill">&#128293; <span id="streak-num">0</span></div>
    <button class="settings-btn" onclick="openModal('modal-keys')">Settings</button>
  </div>
</nav>

<!-- MODE TABS -->
<div class="mode-tabs" id="mode-tabs">
  <button class="mode-tab active" onclick="switchView('daily')" id="tab-daily">Daily Practice</button>
  <button class="mode-tab" onclick="switchView('drill')" id="tab-drill">AI Drill</button>
  <button class="mode-tab" onclick="switchView('prep')" id="tab-prep">Company Prep</button>
</div>

<!-- ════════════════════════════════════════════
     VIEW: DAILY PRACTICE (Tier 1 — zero API)
     ════════════════════════════════════════════ -->
<div id="view-daily" class="view active">

  <!-- HOME -->
  <div id="dp-home" class="phase active">
    <div class="hero">
      <div class="eyebrow"><span class="edot"></span>Daily Practice</div>
      <div class="hero-title">Train your<br>delivery<span class="acc">.</span></div>
      <p class="sub">Zero API. Opens every morning. Drill the eight patterns until they are automatic.</p>
      <div class="hero-stats">
        <div class="hero-stat"><div class="hsv" id="dp-sessions">—</div><div class="hsl">Sessions</div></div>
        <div class="hero-stat"><div class="hsv" id="dp-streak">—</div><div class="hsl">Day streak</div></div>
      </div>
    </div>

    <!-- WEAKNESSES REMINDER -->
    <div class="pm-alert" style="margin-bottom:1.5rem">
      <div class="pm-alert-title">&#9888; Your 8 confirmed failure patterns — read before every session</div>
      <div class="pm-alert-grid">
        <div class="pm-alert-item"><span class="pm-alert-num">1</span>Intro too long. Target 90 seconds.</div>
        <div class="pm-alert-item"><span class="pm-alert-num">2</span>"We" not "I". Say "I decided".</div>
        <div class="pm-alert-item"><span class="pm-alert-num">3</span>No metrics. Cite CSAT, adoption, resolution.</div>
        <div class="pm-alert-item"><span class="pm-alert-num">4</span>Non-linear. Use CPAR every time.</div>
        <div class="pm-alert-item"><span class="pm-alert-num">5</span>Hedging. Cut "I think", "maybe", "kind of".</div>
        <div class="pm-alert-item"><span class="pm-alert-num">6</span>No trade-off. Name what you cut.</div>
        <div class="pm-alert-item"><span class="pm-alert-num">7</span>Technical tangents. Stay on product impact.</div>
        <div class="pm-alert-item"><span class="pm-alert-num">8</span>First sentence delay. Lead with the answer.</div>
      </div>
    </div>

    <!-- QUESTION PICKER -->
    <div class="day-card">
      <div class="day-tag">Pick a question to drill</div>
      <div class="day-q" id="dp-day-q">Choose from the list below or start with a random one.</div>
      <div class="btn-row" style="margin-top:0">
        <button class="btn btn-primary" onclick="dpStartRandom()">Random question &rarr;</button>
      </div>
      <div class="pick-list open" id="dp-pick-list" style="display:block">
        <div id="dp-pick-items"></div>
      </div>
    </div>

    <!-- METRICS COLD READ -->
    <div class="slabel">Metrics — have these cold</div>
    <table class="mt" style="margin-bottom:1.5rem">
      <tr><th>Company</th><th>Metric</th><th>Context</th></tr>
      <tr><td>Lengoo</td><td>+20%</td><td>Adoption after Flow launch</td></tr>
      <tr><td>Lengoo</td><td>+15%</td><td>CSAT from discovery-driven changes</td></tr>
      <tr><td>Lengoo</td><td>50% faster</td><td>Translation time reduction</td></tr>
      <tr><td>Lengoo</td><td>+30%</td><td>Engagement post-launch</td></tr>
      <tr><td>Cognigy</td><td>-30%</td><td>Resolution time via Signal</td></tr>
      <tr><td>Cognigy</td><td>200+/mo</td><td>Tickets processed by Signal</td></tr>
      <tr><td>Aneekaa</td><td>100%</td><td>On-time delivery, 20+ clients</td></tr>
      <tr><td>Aneekaa</td><td>60%</td><td>Repeat business rate</td></tr>
    </table>
  </div>

  <!-- STUDY CARD (rehearsal: read question + full answer + key phrases, then practice) -->
  <div id="dp-study" class="phase" style="display:none">
    <button class="overlay-close" style="margin-bottom:1.25rem" onclick="dpBackToHome()">&#8592; Back to questions</button>
    <div class="q-meta" id="dp-study-tag">Study</div>
    <div class="study-q" id="dp-study-q"></div>
    <div class="study-keys" id="dp-study-keys"></div>
    <div class="study-answer-label">Full model answer &mdash; read it out loud first</div>
    <div class="study-answer" id="dp-study-answer"></div>
    <div class="study-tip" id="dp-study-tip"></div>
    <div class="btn-row">
      <button class="btn btn-primary" onclick="dpGoToPractice('voice')">Practice out loud &rarr;</button>
      <button class="btn" onclick="dpGoToPractice('text')">Practice by typing</button>
    </div>
    <p class="hint" style="margin-top:10px">Read the answer until it feels natural. Then practice from memory. The answer comes back after so you can compare.</p>
  </div>

  <!-- DRILL QUESTION SCREEN -->
  <div id="dp-question" class="phase" style="display:none">
    <button class="overlay-close" style="margin-bottom:1rem" onclick="dpBackToStudy()">&#8592; Back to study card</button>
    <div class="prog-track"><div class="prog-bar" id="dp-prog"></div></div>
    <div class="q-meta" id="dp-q-num">Question 1 of 7</div>
    <div class="q-text fu" id="dp-q-text"></div>
    <div class="cpar-row">
      <span class="cpar-pill">C — Context</span>
      <span class="cpar-pill">P — Problem</span>
      <span class="cpar-pill">A — Action (use "I")</span>
      <span class="cpar-pill">R — Result + metric</span>
    </div>

    <!-- HINTS: warn tags + tip, populated from DRILL_QS -->
    <div id="dp-q-hints" style="margin-bottom:1rem"></div>

    <!-- VOICE AREA -->
    <div id="dp-voice-area" class="rec-box">
      <div class="timer-row">
        <div class="rec-status-row"><div class="rec-dot" id="dp-rec-dot"></div><span id="dp-rec-status">Ready — tap to record</span></div>
        <div class="timer-display" id="dp-timer">0:00</div>
      </div>
      <div class="timer-track"><div class="timer-fill" id="dp-timer-bar" style="width:100%"></div></div>
      <div class="vol-bars"><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div></div>
      <div class="txlive" id="dp-txlive">Your words will appear here as you speak...</div>
    </div>

    <!-- TEXT AREA -->
    <div id="dp-text-area" class="rec-box" style="display:none">
      <div class="timer-row">
        <span class="slabel" style="margin:0">Type your answer</span>
        <div class="timer-display" id="dp-timer-text">0:00</div>
      </div>
      <div class="timer-track"><div class="timer-fill" id="dp-timer-bar-text" style="width:100%"></div></div>
      <textarea id="dp-text-answer" rows="6" placeholder="CPAR: Context — Problem — Action (I did...) — Result (metric)..."></textarea>
    </div>

    <div class="btn-row">
      <button class="btn btn-primary" id="dp-rec-btn" onclick="dpHandleRec()">Start recording</button>
      <button class="btn" id="dp-submit-btn" onclick="dpSubmit()" style="display:none">Check answer &rarr;</button>
      <button class="btn-ghost" onclick="dpSkip()">Skip &rarr;</button>
    </div>
    <p class="hint" style="margin-top:10px">Min 30s · target 60–90s · stop at 2min</p>
    <div style="margin-top:1rem;display:flex;gap:8px">
      <button class="mode-card" id="dp-mode-voice" style="padding:8px 12px;text-align:left" onclick="dpSetInput('voice')"><div class="mct" style="font-size:12px">Voice</div></button>
      <button class="mode-card" id="dp-mode-text" style="padding:8px 12px;text-align:left" onclick="dpSetInput('text')"><div class="mct" style="font-size:12px">Text</div></button>
    </div>
  </div>

  <!-- REVIEW SCREEN -->
  <div id="dp-review" class="phase" style="display:none">
    <div class="prog-track"><div class="prog-bar" id="dp-prog-rv"></div></div>
    <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:0.5rem">
      <h2 style="margin:0">Compare and check</h2>
      <span class="rep-counter" id="dp-rep-counter"></span>
    </div>
    <p class="sub" style="margin-bottom:1rem">Auto-detection ran on your answer. The model answer is back so you can compare line by line.</p>

    <div class="card">
      <span class="slabel">Auto-detection</span>
      <ul class="checklist" id="dp-checklist"></ul>
    </div>

    <div class="compare-grid">
      <div class="card compare-yours" style="margin-bottom:0">
        <div class="compare-col-label">Your answer</div>
        <div id="dp-ans-display" style="font-size:13px;color:var(--ink-dim);line-height:1.7;white-space:pre-wrap"></div>
      </div>
      <div class="card compare-model" style="margin-bottom:0">
        <div class="compare-col-label">Model answer</div>
        <div id="dp-model-text" style="font-size:13px;color:var(--ink-dim);line-height:1.7;white-space:pre-wrap"></div>
        <div class="insight" id="dp-model-insight" style="margin-top:10px"></div>
      </div>
    </div>

    <div id="dp-ai-score-area" style="display:none" class="card" style="margin-top:1rem">
      <span class="slabel">AI Score</span>
      <div id="dp-ai-score-content"><div class="dots"><span></span><span></span><span></span><span class="dots-msg">Scoring with Claude...</span></div></div>
    </div>

    <div class="btn-row">
      <button class="btn btn-primary" onclick="dpAgain()">Again &#8635;</button>
      <button class="btn" onclick="dpNewQuestion()">Another question &rarr;</button>
      <button class="btn-ghost" onclick="dpFinish()">Finish &amp; see summary</button>
    </div>
  </div>

  <!-- DAILY SUMMARY -->
  <div id="dp-summary" class="phase" style="display:none">
    <div style="text-align:center;padding:2rem 0 1.5rem;border-bottom:1px solid var(--border);margin-bottom:1.5rem">
      <div style="font-family:'Shippori Mincho',serif;font-size:48px;font-weight:600;color:var(--accent);margin-bottom:4px" id="dp-sum-count">0</div>
      <div style="font-size:13px;color:var(--ink-ghost)">questions drilled today</div>
    </div>
    <div class="card">
      <span class="slabel">Patterns detected</span>
      <ul class="checklist" id="dp-sum-patterns"></ul>
    </div>
    <div class="card-accent">
      <span class="slabel">Tomorrow's focus</span>
      <div id="dp-sum-focus" style="font-size:14px;line-height:1.7;color:var(--ink-dim)"></div>
    </div>
    <div class="btn-row">
      <button class="btn btn-primary" onclick="dpHome()">Back to questions</button>
      <button class="btn" onclick="dpStartRandom()">Drill another &rarr;</button>
    </div>
  </div>

</div><!-- /view-daily -->


<!-- ════════════════════════════════════════════
     VIEW: AI DRILL (Tier 2+3)
     ════════════════════════════════════════════ -->
<div id="view-drill" class="view">

  <!-- SETUP -->
  <div id="ph-setup" class="phase active">
    <div class="hero">
      <div class="eyebrow"><span class="edot"></span>AI Drill</div>
      <div class="hero-title">Score every<br>answer<span class="acc">.</span></div>
      <p class="sub">Voice transcription via OpenAI. Scoring via Claude. Both are optional — text mode always works.</p>
      <div class="hero-stats">
        <div class="hero-stat"><div class="hsv" id="st-sessions">—</div><div class="hsl">Sessions</div></div>
        <div class="hero-stat"><div class="hsv" id="st-avg">—</div><div class="hsl">Avg score</div></div>
        <div class="hero-stat"><div class="hsv" id="st-streak">—</div><div class="hsl">Day streak</div></div>
      </div>
    </div>

    <div class="trend-card">
      <div class="trend-hdr"><span class="trend-lbl">Last 7 sessions</span><span class="trend-best" id="trend-best"></span></div>
      <div class="trend-bars" id="trend-bars"></div>
    </div>

    <div class="card">
      <span class="slabel">Anthropic API key</span>
      <div class="api-row">
        <input type="password" id="api-key" placeholder="sk-ant-api03-..." autocomplete="off" spellcheck="false">
        <button class="eye" onclick="toggleKey()">&#128065;</button>
      </div>
      <p class="hint">Unlocks AI scoring. Stored locally only.</p>
    </div>

    <div class="card">
      <span class="slabel">OpenAI transcription key</span>
      <div class="api-row">
        <input type="password" id="oa-key" placeholder="sk-proj-..." autocomplete="off" spellcheck="false">
        <button class="eye" onclick="toggleOAKey()">&#128065;</button>
      </div>
      <p class="hint">Unlocks accurate voice transcription. Falls back to browser speech if not set.</p>
    </div>

    <div class="card">
      <span class="slabel">Job description (optional)</span>
      <textarea id="jd-input" rows="3" placeholder="Paste the role and responsibilities. Claude will pick the most relevant questions."></textarea>
    </div>

    <span class="slabel">Session length</span>
    <div class="mode-grid">
      <div class="mode-card selected" data-mode="full" onclick="selectMode(this,'full')"><div class="mct">Full — 5 questions</div><div class="mcd">Full scoring · session summary · streak</div></div>
      <div class="mode-card" data-mode="quick" onclick="selectMode(this,'quick')"><div class="mct">Quick — 3 questions</div><div class="mcd">Faster loop · same feedback depth</div></div>
    </div>

    <span class="slabel">Language</span>
    <div class="mode-grid">
      <div class="mode-card selected" data-lang="en" onclick="selectLang(this,'en')"><div class="mct">English</div><div class="mcd">EN questions · EN scoring</div></div>
      <div class="mode-card" data-lang="de" onclick="selectLang(this,'de')"><div class="mct">Deutsch</div><div class="mcd">DE Fragen · DE Feedback</div></div>
    </div>

    <span class="slabel">Answer mode</span>
    <div class="mode-grid">
      <div class="mode-card selected" data-input="voice" onclick="selectInput(this,'voice')"><div class="mct">Voice</div><div class="mcd">Speak out loud · live transcript · real pressure</div></div>
      <div class="mode-card" data-input="text" onclick="selectInput(this,'text')"><div class="mct">Text</div><div class="mcd">Type your answer · no mic needed</div></div>
    </div>

    <div class="btn-row">
      <button class="btn btn-primary" onclick="startSession()">Start session &rarr;</button>
      <button class="btn-ghost" onclick="clearHistory()">Reset history</button>
    </div>
  </div>

  <!-- LOADING -->
  <div id="ph-loading" class="phase" style="display:none">
    <h1 id="load-title">Building your questions</h1>
    <div class="dots"><span></span><span></span><span></span><span class="dots-msg" id="load-msg">Analysing the role...</span></div>
    <div class="btn-row"><button class="btn-ghost" onclick="abortSession()">Cancel</button></div>
  </div>

  <!-- QUESTION -->
  <div id="ph-question" class="phase" style="display:none">
    <button class="overlay-close" style="margin-bottom:1rem" onclick="abortSession()">&#8592; Cancel session</button>
    <div class="prog-track"><div class="prog-bar" id="prog"></div></div>
    <div class="q-meta" id="q-meta">Question 1 of 5</div>
    <div class="q-text fu" id="q-text"></div>
    <div class="cpar-row" id="cpar-row">
      <span class="cpar-pill">C — Context</span>
      <span class="cpar-pill">P — Problem</span>
      <span class="cpar-pill">A — Action (use "I")</span>
      <span class="cpar-pill">R — Result + metric</span>
    </div>
    <div id="voice-area" class="rec-box">
      <div class="timer-row">
        <div class="rec-status-row"><div class="rec-dot" id="rec-dot"></div><span id="rec-status-text">Ready — tap to record</span></div>
        <div class="timer-display" id="timer">0:00</div>
      </div>
      <div class="timer-track"><div class="timer-fill" id="timer-bar" style="width:100%"></div></div>
      <div class="vol-bars" id="vol-bars"><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div></div>
      <div class="txlive" id="txlive">Your words will appear here as you speak...</div>
    </div>
    <div id="text-area" class="rec-box" style="display:none">
      <div class="timer-row">
        <span class="slabel" style="margin:0">Type your answer</span>
        <div class="timer-display" id="timer-text">0:00</div>
      </div>
      <div class="timer-track"><div class="timer-fill" id="timer-bar-text" style="width:100%"></div></div>
      <textarea id="text-answer" rows="6" placeholder="CPAR: Context — Problem — Action (I did...) — Result (metric)..."></textarea>
    </div>
    <div class="btn-row">
      <button class="btn btn-primary" id="rec-btn" onclick="handleRecBtn()">Start recording</button>
      <button class="btn" id="submit-btn" onclick="submitAnswer()" style="display:none">Get feedback &rarr;</button>
      <button class="btn-ghost" id="skip-btn" onclick="skipQuestion()">Skip &rarr;</button>
    </div>
    <p class="hint" id="q-hint" style="margin-top:12px">Min 30s · target 60–90s · stop at 2min</p>
  </div>

  <!-- SELF RATE -->
  <div id="ph-selfrate" class="phase" style="display:none">
    <button class="overlay-close" style="margin-bottom:1rem" onclick="backToRecord()">&#8592; Back to re-record</button>
    <div class="prog-track"><div class="prog-bar" id="prog-sr"></div></div>
    <h2 id="sr-title">Rate yourself first</h2>
    <p class="sub" id="sr-sub">Before the AI scores — how did you do on each dimension?</p>
    <div class="rate-grid">
      <div class="rate-cell"><div class="rl">Structure</div><div class="stars" data-dim="structure"></div></div>
      <div class="rate-cell"><div class="rl">Ownership ("I")</div><div class="stars" data-dim="ownership"></div></div>
      <div class="rate-cell"><div class="rl">Metric cited</div><div class="stars" data-dim="metric"></div></div>
      <div class="rate-cell"><div class="rl">No hedging</div><div class="stars" data-dim="hedging"></div></div>
    </div>
    <div class="btn-row"><button class="btn btn-primary" id="sr-btn" onclick="showFeedback()">See AI feedback &rarr;</button></div>
  </div>

  <!-- FEEDBACK -->
  <div id="ph-feedback" class="phase" style="display:none">
    <div class="prog-track"><div class="prog-bar" id="prog-fb"></div></div>
    <div class="score-card">
      <svg width="90" height="90" viewBox="0 0 90 90" style="flex-shrink:0">
        <circle class="rbg" cx="45" cy="45" r="36"/>
        <circle class="rfg" id="ring-fg" cx="45" cy="45" r="36" stroke="var(--accent)" stroke-dasharray="226.2" stroke-dashoffset="226.2"/>
        <text class="rnum" id="ring-num" x="45" y="42">—</text>
        <text class="rsub" x="45" y="56">/10</text>
      </svg>
      <div class="dims">
        <div class="dim"><div class="dl">Structure</div><div class="dv" id="dc-str">—</div></div>
        <div class="dim"><div class="dl">Ownership</div><div class="dv" id="dc-own">—</div></div>
        <div class="dim"><div class="dl">Metrics</div><div class="dv" id="dc-met">—</div></div>
        <div class="dim"><div class="dl">Overall</div><div class="dv" id="dc-ov">—</div></div>
      </div>
    </div>
    <div class="tag-row" id="tag-row"></div>
    <div class="card" style="margin-top:0.75rem">
      <span class="slabel" id="fb-label">Feedback</span>
      <div id="fb-stream" class="feedback-body"><div class="dots"><span></span><span></span><span></span><span class="dots-msg">Scoring your answer...</span></div></div>
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
  <div id="ph-summary" class="phase" style="display:none">
    <div class="sum-hero">
      <div class="sum-ring-wrap">
        <svg width="130" height="130" viewBox="0 0 130 130">
          <circle cx="65" cy="65" r="54" fill="none" stroke="var(--surface2)" stroke-width="10"/>
          <circle id="sum-ring" cx="65" cy="65" r="54" fill="none" stroke="var(--accent)" stroke-width="10"
            stroke-linecap="round" stroke-dasharray="339.3" stroke-dashoffset="339.3"
            transform="rotate(-90 65 65)"
            style="transition:stroke-dashoffset 1.2s cubic-bezier(0.4,0,0.2,1),stroke 0.5s"/>
          <text x="65" y="61" text-anchor="middle" dominant-baseline="central" id="sum-ring-num"
            style="font-family:'Shippori Mincho',serif;font-size:30px;font-weight:600;fill:var(--ink)">—</text>
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

</div><!-- /view-drill -->


<!-- ════════════════════════════════════════════
     VIEW: COMPANY PREP (Tier 3)
     ════════════════════════════════════════════ -->
<div id="view-prep" class="view">

  <!-- SAVED PREPS LIST -->
  <div id="prep-saved-list" style="display:none;margin-bottom:1.5rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
      <span class="slabel" style="margin:0">Saved prep kits</span>
    </div>
    <div id="prep-saved-items"></div>
  </div>

  <!-- INPUT AREA -->
  <div id="prep-input" class="prep-input-area">
    <div class="eyebrow"><span class="edot"></span>Company Prep Generator</div>
    <h2 style="margin-bottom:0.5rem">Prep for any<br>company<span class="acc">.</span></h2>
    <p class="sub">Paste the JD. Claude generates a tailored prep kit in your voice with your real metrics.</p>

    <div class="card-warn" style="margin-bottom:1.25rem">
      <span class="slabel">Requires Anthropic key</span>
      <p style="font-size:13px;color:var(--ink-dim);line-height:1.6">Add your key in Settings above. It is stored locally only and never shared.</p>
    </div>

    <div style="margin-bottom:1rem">
      <span class="slabel">Company name</span>
      <input type="text" id="prep-company" placeholder="e.g. Personio, Zalando, N26">
    </div>
    <div style="margin-bottom:1rem">
      <span class="slabel">Role title</span>
      <input type="text" id="prep-role" placeholder="e.g. Senior Product Manager, Product Owner">
    </div>
    <div style="margin-bottom:1.25rem">
      <span class="slabel">Job description</span>
      <textarea id="prep-jd" rows="6" placeholder="Paste the full job description here. The more detail you give, the more tailored the prep kit."></textarea>
    </div>
    <div class="btn-row" style="margin-top:0">
      <button class="btn btn-primary" onclick="generatePrep()">Generate prep kit &rarr;</button>
    </div>
  </div>

  <!-- LOADING -->
  <div class="loading-state" id="prep-loading">
    <div class="dots"><span></span><span></span><span></span><span class="dots-msg">Building your prep kit...</span></div>
    <p class="hint" style="margin-top:0.5rem;text-align:center">Claude is tailoring questions to your profile and the JD</p>
  </div>

  <!-- OUTPUT -->
  <div class="prep-output" id="prep-output">
    <!-- Populated by JS -->
  </div>

</div><!-- /view-prep -->


<!-- SETTINGS MODAL -->
<div class="modal-bg" id="modal-keys" onclick="closeBg(event,'modal-keys')">
  <div class="modal">
    <h2>API Keys</h2>
    <p class="msub">Stored locally in your browser only. Never sent anywhere except the respective API.</p>

    <span class="slabel">Anthropic API key</span>
    <div class="api-row">
      <input type="password" id="modal-claude-key" placeholder="sk-ant-api03-..." autocomplete="off" spellcheck="false">
      <button class="eye" onclick="toggleEye('modal-claude-key')">&#128065;</button>
    </div>
    <p class="hint" style="margin-bottom:1rem">Unlocks: AI scoring in AI Drill + Company Prep generation</p>

    <span class="slabel">OpenAI API key</span>
    <div class="api-row">
      <input type="password" id="modal-oai-key" placeholder="sk-proj-..." autocomplete="off" spellcheck="false">
      <button class="eye" onclick="toggleEye('modal-oai-key')">&#128065;</button>
    </div>
    <p class="hint" style="margin-bottom:1.25rem">Unlocks: accurate voice transcription via GPT-4o mini</p>

    <div class="btn-row" style="margin-top:0">
      <button class="btn btn-primary" onclick="saveKeys()">Save keys</button>
      <button class="btn" id="qr-share-btn" onclick="copyDeviceLink()" style="display:none">Copy device link &#128279;</button>
      <button class="btn-ghost" onclick="closeModal('modal-keys')">Cancel</button>
    </div>
  </div>
</div>

<!-- DEVICE LINK MODAL -->
<div class="qr-modal-bg" id="modal-link" onclick="closeBg(event,'modal-link')">
  <div class="qr-modal">
    <h2>Copy to another device</h2>
    <p>Copy this link and open it on your phone or iPad. Keys load and save automatically, then the link cleans itself.</p>
    <div class="qr-warn">&#9888; This link contains your API keys. Send only to yourself via iMessage, Apple Notes, or AirDrop. Never share publicly.</div>
    <div style="display:flex;gap:8px;margin-bottom:1rem">
      <input type="text" id="device-link-input" readonly style="font-size:11px;padding:8px 10px;color:var(--ink-ghost);cursor:text">
      <button class="btn btn-primary" id="copy-btn" onclick="doCopyLink()" style="white-space:nowrap;flex-shrink:0">Copy &#10003;</button>
    </div>
    <p style="font-size:11px;color:var(--ink-ghost);line-height:1.6;margin-bottom:1.25rem">Best way: paste in <strong style="color:var(--ink)">Apple Notes</strong> (syncs to all your Apple devices automatically) or <strong style="color:var(--ink)">iMessage to yourself</strong>.</p>
    <button class="btn" onclick="closeModal('modal-link')">Done</button>
  </div>
</div>

<!-- PRACTICE OVERLAY (from Company Prep Q&A) -->
<div class="overlay" id="practice-overlay">
  <div class="overlay-inner">
    <button class="overlay-close" onclick="closeOverlay()">&#8592; Back to prep</button>
    <div class="prog-track"><div class="prog-bar" id="ov-prog" style="width:0%"></div></div>
    <div class="q-meta" id="ov-q-label"></div>
    <div class="q-text fu" id="ov-q-text"></div>
    <div class="cpar-row">
      <span class="cpar-pill">C — Context</span>
      <span class="cpar-pill">P — Problem</span>
      <span class="cpar-pill">A — Action (use "I")</span>
      <span class="cpar-pill">R — Result + metric</span>
    </div>
    <div class="rec-box" id="ov-voice-area">
      <div class="timer-row">
        <div class="rec-status-row"><div class="rec-dot" id="ov-rec-dot"></div><span id="ov-rec-status">Ready — tap to record</span></div>
        <div class="timer-display" id="ov-timer">0:00</div>
      </div>
      <div class="timer-track"><div class="timer-fill" id="ov-timer-bar" style="width:100%"></div></div>
      <div class="vol-bars"><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div><div class="vb"></div></div>
      <div class="txlive" id="ov-txlive">Your words will appear here...</div>
    </div>
    <div class="btn-row">
      <button class="btn btn-primary" id="ov-rec-btn" onclick="ovHandleRec()">Start recording</button>
      <button class="btn" id="ov-submit-btn" onclick="ovSubmit()" style="display:none">Get feedback &rarr;</button>
      <button class="btn-ghost" onclick="closeOverlay()">Cancel</button>
    </div>
    <div id="ov-feedback" style="display:none;margin-top:1.5rem"></div>
    <div id="ov-after" class="btn-row" style="display:none">
      <button class="btn btn-primary" onclick="ovTryAgain()">Try again &#8635;</button>
      <button class="btn" onclick="closeOverlay()">Back to prep</button>
    </div>
  </div>
</div>

</div><!-- /shell -->
<script>
/* ── PROFILE + SYSTEM PROMPT ── */
const PROFILE=`You are a high-candor AI interview coach for Manu Becerra Perez, Berlin-based PM. Coach in same language as the answer — German answer = German feedback, English answer = English feedback.

PROFILE: Target PM/PO roles Berlin, deadline July 2026.
Lengoo (2020-2024): PO/PM on HALOS AI translation. Flow initiative end-to-end. METRICS: CSAT +15%, adoption +20%, engagement +30%, website +40% visitors, task completion +20%, translation time -50%.
Cognigy (2024-present): Product Support Engineer. 200+ tickets/month. METRIC: resolution -30%. Self-initiated Signal (Claude API). Tools: Kibana, Grafana, Postman, GitLab.
Aneekaa Studio (2015-2024): Co-founder, 20+ clients (Adidas, Zalando, Blinkist). 100% on-time. 60% repeat business.
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

SCORING: 7/10 = genuinely strong. 5/10 = average. Don't inflate. Never say "great answer" unless score >=8. Quote verbatim for every negative. "we/wir" >2x = deduct Ownership. No metric = cap Metrics at 3/10. First sentence != direct answer = deduct Structure. >200 words = flag. List every hedging word. End with ONE rewrite of how the answer should open.
Root causes: "ownership gap"/"metric blindness"/"structure collapse"/"hedge spiral"/"tangent drift"/"intro bloat"/"first-sentence delay"/"repetition"`;

const ANTHROPIC_SYSTEM=[{type:'text',text:PROFILE,cache_control:{type:'ephemeral'}}];

/* ── DRILL QUESTIONS (from interview_drill_script.md) ── */
const DRILL_QS=[
  {
    id:'intro',
    q:'Tell me about yourself. Under 90 seconds.',
    day:'Monday',
    warn:['Intro too long — target 75 seconds','Missing metrics'],
    tip:'Lead with Lengoo and Flow. Two metrics minimum. End on the company-specific line. Silence after.',
    answer:'I am a Product Manager and Product Owner based in Berlin for around 14 years. My most relevant PM experience was at Lengoo, an AI translation platform, where I led Flow end-to-end: discovery, roadmap, prioritisation, engineering coordination, launch, and post-launch iteration. Adoption up 20 percent. CSAT up 15 percent.\n\nBefore that and in parallel I co-founded Aneekaa Studio, a creative agency where I led brand and digital projects for clients like Adidas, Zalando, and Blinkist. 100 percent on-time delivery.\n\nSince 2024 I have been at Cognigy as a Product Support Engineer. I process 200+ tickets a month and self-initiated Signal, an AI-powered feedback intelligence tool that reduced resolution time by 30 percent. It is live in my portfolio.\n\nI am completing a PM Weiterbildung at IU Akademie Berlin. I am looking for a PM role with full product ownership.',
    insight:'60 seconds max. Two metrics minimum. End on the company-specific line. Then silence. Do not trail off.'
  },
  {
    id:'flow',
    q:'Walk me through a product you owned end-to-end from discovery to launch.',
    day:'Tuesday',
    warn:["Saying 'we' instead of 'I'",'Going too long without a metric'],
    tip:'Flow at Lengoo. Workaround signal, 20+ interviews, MVP scope cut, metrics. Never say "we decided."',
    answer:'At Lengoo, enterprise customers needed fast translation but Google Translate ignored company terminology and professional workflows were too slow. Users were leaving the platform to translate in Google Translate and coming back to fix terminology manually. That workaround was the real signal.\n\nI led Flow end to end. I ran 20+ discovery interviews across linguists, PMs, and customer success managers. I identified the core need: speed plus terminology consistency in one step. I deliberately cut editing features from the MVP to ship faster. That was the key trade-off.\n\nFlow was built on HALOS, combining MT models, Translation Memories, and Glossaries. I owned the backlog, wrote specs, coordinated engineering through fortnightly sprints.\n\nAdoption up 20 percent. CSAT up 15 percent. Translation time down 50 percent.',
    insight:'Lead with the problem and the workaround signal. That observation is what makes this story sharp. Then action, then metric.'
  },
  {
    id:'signal',
    q:'Tell me about your latest achievement with numbers.',
    day:'Wednesday',
    warn:['No metric = incomplete answer','Must say "I self-initiated" not "we built"'],
    tip:'Signal at Cognigy. Problem, three options considered, decision, result. Four metrics minimum.',
    answer:'At Cognigy, support tickets were misrouted manually across four teams. Speed and context were getting lost.\n\nI decided to fix this without being asked. I considered three approaches: rules-based router, Zendesk macros, or an AI signal layer. I chose the AI layer because rules break with every new ticket category.\n\nI sat with each team lead to understand which signals mattered, designed the categorisation framework from their input, and built Signal using the Claude API.\n\n200+ tickets per month now processed automatically. Resolution time down 30 percent. Four teams adopted it. Three product prioritisation decisions came directly from the signals it surfaced.',
    insight:'The "I decided to fix this without being asked" line is the ownership signal. Do not skip it.'
  },
  {
    id:'discovery',
    q:'How do you run discovery? Walk me through your actual process.',
    day:'Saturday',
    warn:['Generic answer — must be specific','No process without a real example'],
    tip:'Flow at Lengoo. 20+ interviews, workaround observation. The behaviour was the data.',
    answer:'Discovery starts with a hypothesis I am trying to validate or invalidate, not confirm.\n\nFor Flow at Lengoo I ran 20+ structured interviews. I did not just ask what users needed. I watched what they actually did. The workaround was the real signal: users were leaving the platform entirely for Google Translate then fixing terminology manually. That observation defined the entire MVP scope. Speed and terminology consistency first. Full editing workflow cut.\n\nThat discovery decision saved roughly three months of build time. Adoption up 20 percent after launch validated the hypothesis.\n\nAt Cognigy I applied the same approach. 200+ monthly tickets classified and clustered. Friction surfaced, structured insights brought to discovery sessions. Resolution time down 30 percent.',
    insight:'The workaround observation is what separates this from a generic discovery answer. Lead with it.'
  },
  {
    id:'metrics',
    q:'How do you define success metrics for a feature before it ships?',
    day:'Sunday',
    warn:['Vague metrics = no answer','Must define targets before shipping, not after'],
    tip:'Three layers: adoption, quality, operational. Flow example. Rating system catching the regression.',
    answer:'I structure metrics in three layers before a single line of code is written.\n\nAdoption: unique users and weekly actives. Quality: a 1-to-5 star rating system, user-generated signal. Operational: latency targets. For Flow I defined under one second as a hard requirement.\n\nThe key rule: I define the target before we ship, not after. Otherwise you are doing reporting, not measuring.\n\nPost-launch on Flow, the quality layer caught a terminology regression in week two. I flagged it in sprint three before Customer Success even noticed. That is the system working.',
    insight:'The regression story is the proof that the system actually worked. Always include it.'
  },
  {
    id:'stakeholder',
    q:'Tell me about a time you handled a difficult stakeholder conflict.',
    day:'Thursday',
    warn:["Do not say 'we agreed'",'Must show you made the call, not waited for consensus'],
    tip:'Lengoo Sales vs Engineering. 70/30 split. You made the call with data.',
    answer:'At Lengoo, Sales wanted features for enterprise clients and Engineering needed tech debt time. Both came to me with competing urgent requests in the same sprint.\n\nI set up one focused session and presented both perspectives with data: revenue at risk on one side, estimated cost of deferred tech debt on the other. I decided on a 70/30 split. 70 percent feature work, 30 percent tech debt, with a clear re-evaluation at next planning.\n\nI made the call. I did not wait for the room to agree. The sprint shipped on time. The enterprise feature closed the deal.',
    insight:'"I made the call. I did not wait for the room to agree." That line is the ownership signal. Do not soften it.'
  },
  {
    id:'tradeoff',
    q:'Tell me about the hardest trade-off you personally made on a product.',
    day:'Friday',
    warn:['Must name what you cut and why','Must have a metric at the end'],
    tip:'Cutting editing from Flow MVP. Sales pushed. Discovery data won. Ship faster, validate first.',
    answer:'Building Flow. Several stakeholders wanted advanced editing capabilities in the MVP, similar to professional translation editors.\n\nDuring discovery I confirmed that users did not need a full editing workflow. They needed fast translation verification. Editing tools would have delayed the product by months. I decided to cut editing from the MVP and prioritise instant translation and terminology consistency.\n\nSales pushed. I presented the discovery insights and showed that the fastest path to validating the product was focusing on speed first. We could expand later.\n\nShipped the MVP significantly faster. Validated the core value. Translation time down 50 percent. Editing was added in a later iteration once the core was proven.',
    insight:'The trade-off is the proof of prioritisation. Name what you cut, name why, and name what you shipped instead.'
  },
  {
    id:'engineering',
    q:'Walk me through a time you disagreed with engineering on scope.',
    day:'Sunday',
    warn:["Do not say 'we decided together' — you made the call",'Must have a result'],
    tip:'Source selector panel in Flow. User data vs engineering velocity. You called it.',
    answer:'Engineering wanted to cut the source selector panel from the Flow MVP to save time. Design had already built it. Both sides had a real argument.\n\nI called a focused decision session. Brought user interview data showing source transparency was a core need. Then I asked engineering to scope a minimum viable version: function over full design. Then I made the call.\n\nShipped a simplified version in sprint two. Users rated it as one of the most useful features in post-launch feedback. Engineering respected the speed. Design accepted the scope cut.\n\nI made the decision. I did not wait for the room to agree.',
    insight:'The key line is "I made the decision." Not "we reached a compromise." Own the call.'
  },
  {
    id:'weakness',
    q:'What is your biggest weakness as a PM? Be specific.',
    day:'Sunday',
    warn:['Do not say "I work too hard"','Must be real and show self-awareness'],
    tip:'Over-explaining under pressure. Real, coachable, with a fix you are actively working on.',
    answer:'I tend to over-explain under pressure. When I am nervous or the question is broad, I give context that is helpful to me but too much for the interviewer.\n\nI have been actively working on this. I use the CPAR framework to structure answers before I speak. I practice with a timer. It is improving, but it is still something I have to consciously manage.\n\nThe upside is that I am thorough and rarely miss details. The task is learning when to stop.',
    insight:'This is the honest answer. It is also the one that demonstrates the exact self-awareness interviewers are probing for.'
  },
  {
    id:'titlereframe',
    q:'Your current title is Product Support Engineer, not PM. How do you explain that?',
    day:'Sunday',
    warn:['Do not be defensive','Do not over-explain — own it and pivot fast'],
    tip:'Title does not reflect the work. State the facts. End with the offer to walk through Signal.',
    answer:'The title does not reflect the work. At Cognigy I took on the role because I wanted to get close to enterprise B2B customers and understand how a complex AI platform actually fails in the field. That insight is worth more to me as a PM than another year of backlog grooming.\n\nThe actual work I have done there is product work. Identifying patterns, writing product briefs, shipping Signal, collaborating with engineering on root cause analysis. The title is a mismatch. The experience is not.\n\nI am happy to walk you through Signal if it would help.',
    insight:'"The title is a mismatch. The experience is not." That is the line. Deliver it clean and move on.'
  },
  {
    id:'aiproduct',
    q:'How do you evaluate whether an AI feature is working well?',
    day:'Sunday',
    warn:['Separate model metrics from product metrics','Define targets before shipping'],
    tip:'Two angles: PM lens (adoption, task completion, satisfaction) and builder lens (Signal, KÅr eval framework).',
    answer:'I separate model metrics from product metrics and track them independently.\n\nProduct metrics tell you whether users are achieving their goals: adoption, task completion rate, satisfaction scores. Model metrics tell you whether the AI is doing its job: output quality, confidence thresholds, edge case handling.\n\nAt Lengoo I defined a 1-to-5 star rating system before Flow launched. It caught a terminology regression in week two before Customer Success even flagged it. That is what pre-defined metrics do.\n\nI am currently applying the same thinking to KÅr, a longevity companion I am building as a portfolio piece. The evaluation framework covers six criteria per output: accurate, understandable, personalised, honest, actionable, safe. Calibrated through real user scenarios, not hardcoded thresholds.',
    insight:'This question separates PM candidates who talk about AI from ones who have actually built eval frameworks. Lead with the Lengoo example, close with KÅr.'
  },
  {
    id:'prioritise',
    q:'How do you prioritise a backlog when sales, engineering, and CS all have competing requests?',
    warn:['Do not describe a framework without a real example','Must end with a result'],
    tip:'Four dimensions plus real data. Lengoo example. Ship the top 3 without conflict.',
    answer:'I score items across four dimensions: Business impact, User value, Effort, and Strategic alignment.\n\nAt Lengoo I combined this with usage data and customer interview insights to identify real friction. That gave me a defensible priority order everyone could see the logic behind.\n\nResult: we shipped the top three backlog items in the next sprint without stakeholder conflict. No politics. Just data.\n\nAt Cognigy I apply the same approach. Ticket volume and customer impact data tell me which fixes go to the top. 200+ monthly tickets give me a continuous signal stream.',
    insight:'The "defensible priority order" framing is what makes this answer strong. It shows you can hold the line when someone pushes back.'
  },
  {
    id:'whypm',
    q:'Why product management? Why not stay in UX or engineering-adjacent roles?',
    warn:['Do not say "I am passionate about products"','Must show the progression was intentional'],
    tip:'Came to PM through doing the work. Designer asking why. Lengoo owning the answer. Cognigy without the title.',
    answer:'I came to product through doing the work first, not through a decision to become a PM.\n\nAs a UX designer at Aneekaa I was always asking why we were building this, not just how to design it. At Lengoo I started owning the answer to that question. Discovery, roadmap, engineering coordination, launch. I was doing the job before I had the title.\n\nAt Cognigy I am doing the same thing again, just without the formal title. Translating 200+ monthly tickets into product direction, writing user stories, self-initiating Signal.\n\nProduct is where I have the most impact. At the intersection of users, engineering, and business strategy. That is where I naturally think.',
    insight:'"I came to product through doing the work" is the honest frame. It shows continuity, not a career pivot.'
  },
  {
    id:'failure',
    q:'Tell me about a product failure. What happened and what did you learn?',
    warn:['Do not pick a fake failure','Must name what you would do differently'],
    tip:'The Lengoo formatting feature. Built on CS feedback. Users did not adopt. Lesson: trust data signals over a single loud voice.',
    answer:'At Lengoo we built an advanced text formatting feature based on strong CS feedback. Multiple customer success managers said enterprise clients wanted it. We prioritised it and shipped it.\n\nUsers did not adopt it. The root cause took us two sprints to find. Users were still struggling with basic translation quality. They never got to the point where formatting mattered. We had solved the wrong layer of the problem.\n\nI pulled the initiative and refocused the backlog on translation quality fundamentals.\n\nThe lesson I took from that: trust data signals over a single loud voice, even when that voice is internal. One team saying customers want something is not the same as usage data showing customers need something. I now separate advocacy from evidence before anything goes on the roadmap.',
    insight:'"Advocacy versus evidence" is the line. It shows you learned something precise, not just something vague about listening to users.'
  },
  {
    id:'ambiguity',
    q:'How do you deal with an ambiguous problem when you have very little information?',
    warn:['Do not say "I gather requirements"','Must show a concrete process'],
    tip:'Triangulate signals. Cheapest test first. Ambiguity is a reason for faster experiments, not waiting.',
    answer:'I triangulate signals first. When tickets, analytics, and CS feedback converge on the same friction point, that is the signal to act. When they diverge, that tells me the problem is not well understood yet.\n\nAt Cognigy I regularly face this. A ticket comes in that does not fit any known category. I reproduce the problem myself before bringing it to engineering. I classify assumptions separately from facts. Then I design the cheapest test that would prove or disprove the most important assumption.\n\nAmbiguity is not a reason to wait. It is a reason for faster, smaller experiments.\n\nFor Flow at Lengoo, the whole MVP was built on one ambiguous question: what do enterprise users actually need when Google Translate is too slow? Twenty interviews and workaround observation gave me the answer in two weeks. Speed and terminology consistency. Everything else was noise.',
    insight:'"Ambiguity is a reason for faster experiments, not waiting." That is the line that shows PM maturity.'
  },
  {
    id:'sprint',
    q:'What do you do when a sprint goal is at risk mid-sprint?',
    warn:['Do not say you escalate immediately','Must show you protect the team first'],
    tip:'Separate non-negotiables from negotiables before the sprint starts. Transparency mid-sprint. No surprises.',
    answer:'I separate non-negotiables from negotiables before the sprint starts. If velocity drops mid-sprint, I already know what can move without hitting the critical path.\n\nAt Lengoo I ran this explicitly. Every sprint had a must-have list and a nice-to-have list agreed upfront. If something slipped, I moved nice-to-haves first and communicated the trade-off immediately, not at the end of the sprint.\n\nThe principle is: no surprises at sprint review. If we are going to miss something, stakeholders know by Wednesday, not Friday.\n\nI also talk directly to engineering management mid-sprint, not through a status update. A five-minute conversation on day three is worth more than a formal blocker log on day eight.',
    insight:'"No surprises at sprint review" is the line. It shows you protect trust with stakeholders, not just the delivery plan.'
  },
  {
    id:'ceostakeholder',
    q:'Tell me about the most difficult stakeholder you have managed.',
    warn:['Do not name the person or make it personal','Must show a systemic fix, not a one-off'],
    tip:'CEO at Lengoo requesting short-notice demos. Demo-Shell solution. Systemic fix that removed recurring friction.',
    answer:'At Lengoo the CEO regularly requested short-notice demos for investor meetings, sometimes with 24 hours notice. This kept pulling engineers off sprint work to set up demo environments.\n\nI did not try to change the CEO behaviour. That was a losing battle. Instead I looked at the underlying need: he needed a stable, impressive demo available at any time.\n\nI proposed a Demo-Shell: a separate prototype branch maintained by one engineer, updated after each major release, never connected to live data. The CEO could demo from it anytime without anyone being pulled off sprint work.\n\nThe friction disappeared. The CEO got what he needed. Engineering got protected sprint time. The fix was structural, not relational.\n\nThe lesson: when a stakeholder creates recurring friction, look at the system, not the person.',
    insight:'"Look at the system, not the person" is the PM move. It shows you solve root causes, not symptoms.'
  },
  {
    id:'goals',
    q:'Where do you want to be in three years?',
    warn:['Do not say "in a leadership role" without substance','Must connect to real product craft'],
    tip:'Lead a product area. Deepen AI evaluation. KÅr is where that thinking is developing now.',
    answer:'Leading a product area end-to-end in a company that takes product seriously. Full ownership, not execution support for someone else\'s roadmap.\n\nI also want to deepen my AI product evaluation craft. The question of how you know an AI feature is actually working, not just technically running, is one most PM teams do not have a good answer to yet. I am building that thinking with KÅr right now.\n\nLonger term I want to be the kind of PM who designs feedback loops that make AI products better over time. The eval framework, the confidence thresholds, the human review layer. That is the craft I want to develop.\n\nThree years from now I want to be able to point to a product area I owned and show the metric line moving.',
    insight:'The KÅr reference shows the three-year goal is not aspirational. You are already building toward it.'
  },
  {
    id:'valueproposition',
    q:'Why should we hire you over other PM candidates?',
    warn:['Do not be vague','Three specific things, no more'],
    tip:'Three things most candidates do not have: build with AI, sit at product-engineering boundary, cover the full surface.',
    answer:'Three things, specifically.\n\nFirst, I build with AI, not just manage it. Signal is a live Claude API application processing real data. My n8n automations run every day. Most PM candidates talk about AI products. I ship them.\n\nSecond, I sit at the product-engineering boundary in a way most PMs do not. At Cognigy I reproduce bugs myself in Kibana before I bring them to engineering. I read Grafana dashboards, test API calls in Postman, read Swagger specs. I come to engineering with a hypothesis, not a request.\n\nThird, I cover the full product surface. Discovery, roadmap, user stories, Figma components, design systems, post-launch metrics. I do not hand off at the spec and come back at the launch.\n\nThat combination is rare on the Berlin PM market right now.',
    insight:'"That combination is rare on the Berlin PM market right now." Deliver that last line with confidence and stop.'
  },
  {
    id:'aiaday',
    q:'How do you use AI in your day-to-day work as a PM?',
    warn:['Do not list tools','Must show AI as infrastructure, not a novelty'],
    tip:'AI as infrastructure. n8n workflows. Daily job discovery. Cover letter generator. Cursor for root cause. Signal.',
    answer:'I treat AI as infrastructure, not a tool I open when I need help.\n\nI run a self-hosted n8n server with Claude and OpenAI APIs. A daily job discovery agent pulls from three job boards, scores each role against my profile with GPT-4o mini, and appends filtered results to a Google Sheet every morning at 6am. A separate workflow generates tailored cover letters automatically when I mark a role as apply.\n\nAt Cognigy I use Cursor with Claude for root cause analysis. Instead of reading through 50 log lines manually, I paste the relevant section and ask what is broken.\n\nSignal itself is an AI application I built. It is live, processing real data.\n\nThe difference between me and a PM who uses ChatGPT occasionally is that my AI infrastructure runs whether I interact with it or not. It is part of how I work, not a feature I turn on.',
    insight:'"It runs whether I interact with it or not." That line shows the level of integration. It is not a productivity trick. It is infrastructure.'
  },
  {
    id:'engineering',
    q:'How comfortable are you working with engineers? Give a real example.',
    warn:['Do not say "I speak their language" without proving it','Must have a concrete example'],
    tip:'Cognigy: Kibana, Grafana, Postman, Swagger. Come with a hypothesis not a request. Signal built directly.',
    answer:'Very comfortable. I close the gap between product and engineering by doing technical work myself before I bring something to the team.\n\nAt Cognigy I reproduce bugs before I escalate them. I open Kibana, find the relevant log lines, identify the failure pattern, and come to the engineering conversation with a hypothesis and a log excerpt. Not a description of what a customer reported.\n\nI read Grafana dashboards for performance signals. I test API calls in Postman. I read Swagger specs to understand what a new integration would require before I write the user story.\n\nI also built Signal directly using the Anthropic API. Not with a no-code wrapper. I designed the prompt architecture, the routing logic, and the fallback behaviour myself.\n\nMy job is not to make architecture decisions. It is to write acceptance criteria that do not leave engineers guessing, and to catch when a technical shortcut will create a product problem downstream.',
    insight:'"Come with a hypothesis, not a request." Engineers trust PMs who do the work before asking for help.'
  },
  {
    id:'figma',
    q:'How deep is your Figma experience?',
    warn:['Do not overstate','Must have a concrete deliverable'],
    tip:'Migrated Lengoo from Sketch. Built design system from scratch. Portfolio in Figma with Claude MCP.',
    answer:'I migrated Lengoo from Sketch to Figma and built the design system foundation from scratch: components, variants, auto-layout for responsive layouts, reusable patterns across the full product surface.\n\nAt Aneekaa I maintained parallel design systems for 20+ client projects across nine years. Every brand had its own component library.\n\nCurrently I design my portfolio in Figma. I prototype, connect to Claude via MCP for design system updates, and follow Figma Make and Lovable actively.\n\nI have never had a Figma blocker in delivery. I can move from a rough sketch to a spec-ready prototype without a design handoff in between.',
    insight:'The "never had a Figma blocker" line is the proof point. It shows depth without overclaiming.'
  },
  {
    id:'sideprojects',
    q:'What do you do outside of work? Any side projects?',
    warn:['Do not list hobbies','Frame as range, not distraction'],
    tip:'Tacks and ELOTL show range. AI agenda tool shows building is how you think. Frame as creative discipline.',
    answer:'I run two food ventures in Berlin. Tacks Berlin is a street taco concept. ELOTL is a Mexican-German fusion fine dining idea for Prenzlauer Berg. Both are real operating projects, not ideas.\n\nI also built a personal AI agenda tool. It takes voice input through Siri, pulls from my calendar and reminders, and structures my day into five to seven priorities. I built it because I was spending 20 minutes every morning deciding what to work on. That time is now zero.\n\nBuilding is how I think. When I have a friction point in my own life, I scope the solution, build the minimum version, and iterate. The food ventures run on that same discipline. The AI tool runs on that same discipline.\n\nRange is not a distraction. It is evidence of how I operate.',
    insight:'"Building is how I think." That is the line that ties it together. It reframes everything that follows as evidence of PM instincts.'
  },
  {
    id:'kor',
    q:'Tell me about a product you are building right now outside of work.',
    warn:['Do not describe it as a side project','Frame it as a portfolio piece with real methodology'],
    tip:'KÅr. Start with the insight from discovery. Confidence thresholds. Eval framework. It is your answer to the AI evaluation question.',
    answer:'I am building KÅr, a longevity companion for active people. The entry point is sports injury because that is when people pay the most attention to their body and when existing systems fail them most visibly.\n\nThe discovery insight came from a survey I ran. Sixteen responses in four days. The finding was not that people needed better exercise plans. It was that most people said they did not understand what their doctor or physio told them. People are getting appointments. The appointments are not solving the confusion. That is the wedge.\n\nI built KÅr specifically to answer one interview question: how do you evaluate whether an AI feature is working well? The eval framework covers six criteria per output: accurate, understandable, personalised, honest, actionable, safe. Three confidence tiers. Tier three is a hard escalation with no AI output at all, because a confident wrong answer in a medical context is worse than no answer.\n\nCurrent state: concept testing with four real users. The metric I am watching is paraphrase quality. If a user cannot restate what they read, comprehension failed and trust does not matter.\n\nThe whole case study is in my portfolio.',
    insight:'KÅr is not a side project. It is the proof that you can build an AI eval framework, not just describe one.'
  }
];

/* ── DAY ROTATION ── */
// getDay(): 0=Sun,1=Mon,2=Tue,3=Wed,4=Thu,5=Fri,6=Sat
const DAY_ROTATION={0:'intro',1:'intro',2:'flow',3:'signal',4:'stakeholder',5:'tradeoff',6:'discovery'};

/* ── QUESTION POOL FOR AI DRILL ── */
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

const CPAR={en:['C — Context','P — Problem','A — Action (use "I")','R — Result + metric'],de:['K — Kontext','P — Problem','A — Aktion (sag "Ich")','E — Ergebnis + Zahl']};
const T={
  en:{load:'Building your questions',ready:'Ready — tap to record',rec:'Recording — speak now',stop:'Stop recording',start:'Start recording',rerec:'Re-record',getfb:'Get feedback →',next:'Next question →',retry:'Try again',skip:'Skip →',hint:'Min 30s · target 60–90s · stop at 2min',srt:'Rate yourself first',srs:'Before the AI scores — how did you do?',seefb:'See AI feedback →',fbl:'Feedback',ansl:'Your answer',scoring:'Scoring your answer...',complete:'Session complete',patterns:'Session patterns',priority:'Priority for tomorrow',newsess:'New session →',drillw:'Drill weakest →',timer:'Start timer'},
  de:{load:'Fragen werden vorbereitet',ready:'Bereit — tippe zum Aufnehmen',rec:'Aufnahme läuft — sprich jetzt',stop:'Aufnahme stoppen',start:'Aufnahme starten',rerec:'Neu aufnehmen',getfb:'Feedback anzeigen →',next:'Nächste Frage →',retry:'Nochmal versuchen',skip:'Überspringen →',hint:'Min 30s · Ziel 60–90s · Stop bei 2min',srt:'Erst selbst bewerten',srs:'Bevor du das KI-Feedback siehst — wie schätzt du dich ein?',seefb:'KI-Feedback →',fbl:'Feedback',ansl:'Deine Antwort',scoring:'Antwort wird bewertet...',complete:'Session abgeschlossen',patterns:'Muster dieser Session',priority:'Priorität für morgen',newsess:'Neue Session →',drillw:'Schwächstes üben →',timer:'Timer starten'}
};

/* ── STATE ── */
let S={mode:'full',input:'voice',lang:'en',key:'',oaKey:'',jd:'',questions:[],cq:0,total:5,answers:[],scores:[],selfR:[],sessStart:null,timerInt:null,timerSec:0,recog:null,recorder:null,audioStream:null,audioChunks:[],transcript:'',finalTranscript:'',recording:false,stopRequested:false,transcribing:false,actx:null,analyser:null,raf:null,wk:{own:0,met:0,hed:0,str:0,len:0},selfRat:{}};

let DP={qIdx:0,questions:[],answers:[],wk:{we:0,metric:0,hedge:0,len:0},timerInt:null,timerSec:0,recording:false,recog:null,input:'voice'};
let OV={timerInt:null,timerSec:0,recording:false,recog:null,transcript:'',qText:''};

/* ── UTILS ── */
function $(id){return document.getElementById(id)}
function qsa(s){return document.querySelectorAll(s)}

/* ── KEYS ── */
window.addEventListener('load',()=>{
  const ck=localStorage.getItem('mb_dk');
  const ok=localStorage.getItem('mb_oa');
  if(ck){$('api-key').value=ck;$('modal-claude-key').value=ck;S.key=ck;}
  if(ok){$('oa-key').value=ok;$('modal-oai-key').value=ok;S.oaKey=ok;}
  updateApiPills();
  updateStats();
  buildStars();
  dpInit();
  renderDrillHome();
  renderSavedPreps();
});

function saveKeys(){
  const ck=$('modal-claude-key').value.trim();
  const ok=$('modal-oai-key').value.trim();
  if(ck){localStorage.setItem('mb_dk',ck);$('api-key').value=ck;S.key=ck;}
  if(ok){localStorage.setItem('mb_oa',ok);$('oa-key').value=ok;S.oaKey=ok;}
  closeModal('modal-keys');
  updateApiPills();
}

function updateApiPills(){
  const ck=localStorage.getItem('mb_dk');
  const ok=localStorage.getItem('mb_oa');
  const cp=$('claude-pill');const op=$('oai-pill');
  if(ck){cp.classList.add('connected');}else{cp.classList.remove('connected');}
  if(ok){op.classList.add('connected');}else{op.classList.remove('connected');}
  // Show QR share button only when at least one key is set
  const btn=$('qr-share-btn');
  if(btn)btn.style.display=(ck||ok)?'inline-flex':'none';
}

function toggleKey(){const i=$('api-key');i.type=i.type==='password'?'text':'password';}
function toggleOAKey(){const i=$('oa-key');i.type=i.type==='password'?'text':'password';}
function toggleEye(id){const i=$(id);i.type=i.type==='password'?'text':'password';}
function openModal(id){
  $(id).classList.add('open');
  const ck=localStorage.getItem('mb_dk');
  const ok=localStorage.getItem('mb_oa');
  if(ck&&$('modal-claude-key'))$('modal-claude-key').value=ck;
  if(ok&&$('modal-oai-key'))$('modal-oai-key').value=ok;
  // show share button if keys exist
  const btn=$('qr-share-btn');
  if(btn)btn.style.display=(ck||ok)?'inline-flex':'none';
}
function closeModal(id){$(id).classList.remove('open')}
function closeBg(e,id){if(e.target.id===id)closeModal(id)}

/* ── DEVICE LINK SHARE ── */
function copyDeviceLink(){
  const ck=localStorage.getItem('mb_dk')||'';
  const ok=localStorage.getItem('mb_oa')||'';
  if(!ck&&!ok){alert('No keys saved yet. Add keys first.');return;}
  const base='https://hi.manubecerra.com/interview-drill/';
  const hash='#k='+encodeURIComponent(btoa(JSON.stringify({ck,ok})));
  const url=base+hash;
  $('device-link-input').value=url;
  closeModal('modal-keys');
  $('modal-link').classList.add('open');
}

function doCopyLink(){
  const input=$('device-link-input');
  const url=input.value;
  if(!url)return;
  navigator.clipboard.writeText(url).then(()=>{
    const btn=$('copy-btn');
    btn.textContent='Copied ✓';
    btn.style.background='var(--sage)';
    setTimeout(()=>{btn.textContent='Copy ✓';btn.style.background='';},2500);
  }).catch(()=>{
    // fallback for older browsers
    input.select();document.execCommand('copy');
    $('copy-btn').textContent='Copied ✓';
  });
}

/* ── AUTO-LOAD KEYS FROM LINK HASH ── */
(function checkHashKeys(){
  try{
    const hash=location.hash;
    if(!hash.includes('k='))return;
    const raw=hash.split('k=')[1];
    if(!raw)return;
    const data=JSON.parse(atob(decodeURIComponent(raw)));
    if(data.ck)localStorage.setItem('mb_dk',data.ck);
    if(data.ok)localStorage.setItem('mb_oa',data.ok);
    // Clean the hash immediately so keys don't sit in the URL bar
    history.replaceState(null,'',location.pathname+location.search);
    if(data.ck||data.ok){
      setTimeout(()=>{
        updateApiPills();
        // subtle confirmation, no alert
        const pill=$('claude-pill');
        if(pill){pill.style.transition='all 0.3s';pill.style.borderColor='var(--sage)';}
      },400);
    }
  }catch(e){/* invalid or missing hash, ignore */}
})();

/* ── VIEW SWITCHING ── */
function switchView(v){
  qsa('.view').forEach(el=>el.classList.remove('active'));
  qsa('.mode-tab').forEach(el=>el.classList.remove('active'));
  $('view-'+v).classList.add('active');
  $('tab-'+v).classList.add('active');
  if(v==='prep')renderSavedPreps();
  window.scrollTo({top:0,behavior:'smooth'});
}

function goHome(e){if(e)e.preventDefault();switchView('daily');dpGoHome();}

/* ══════════════════════════════════════════
   DAILY PRACTICE (Tier 1)
══════════════════════════════════════════ */
function dpInit(){
  // Build question list in order
  DP.questions=DRILL_QS.slice();
  renderDrillHome();
}

function dpSetRotation(mode){
  DP.rotation=mode;
  localStorage.setItem('mb_dp_rotation',mode);
  ['random','weakness','sequential','day'].forEach(m=>{
    $('rot-'+m).classList.toggle('selected',m===mode);
  });
  renderDrillHome();
}

function dpPickQuestion(){
  const mode=DP.rotation||'random';
  const dayIdx=new Date().getDay();
  const DAY_ROT={0:'intro',1:'intro',2:'flow',3:'signal',4:'stakeholder',5:'tradeoff',6:'discovery'};
  const dayNames=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

  if(mode==='day'){
    const id=DAY_ROT[dayIdx];
    const q=DRILL_QS.find(q=>q.id===id)||DRILL_QS[0];
    $('dp-day-tag').textContent=dayNames[dayIdx]+' — Day rotation';
    return q;
  }
  if(mode==='sequential'){
    const idx=parseInt(localStorage.getItem('mb_dp_seq')||'0')%DRILL_QS.length;
    $('dp-day-tag').textContent=`Question ${idx+1} of ${DRILL_QS.length} — Sequential`;
    return DRILL_QS[idx];
  }
  if(mode==='weakness'){
    const scores=JSON.parse(localStorage.getItem('mb_dp_scores')||'{}');
    // find question with lowest average score, or random if no data
    let worst=null;let worstAvg=99;
    DRILL_QS.forEach(q=>{
      const sc=scores[q.id];
      if(sc&&sc.count>0){const avg=sc.total/sc.count;if(avg<worstAvg){worstAvg=avg;worst=q;}}
    });
    if(worst){$('dp-day-tag').textContent='Weakest pattern — drill it';return worst;}
    // fall through to random if no scores yet
    $('dp-day-tag').textContent='No history yet — random pick';
  }
  // random (default)
  const r=DRILL_QS[Math.floor(Math.random()*DRILL_QS.length)];
  $('dp-day-tag').textContent='Random pick';
  return r;
}

function renderDrillHome(){
  // Render question picker — all questions listed, click to drill
  const container=$('dp-pick-items');
  container.innerHTML='';
  DRILL_QS.forEach((q)=>{
    const div=document.createElement('div');
    div.className='pick-item';
    div.innerHTML=`<span>${q.q}</span>`;
    div.onclick=()=>dpStartOne(q);
    container.appendChild(div);
  });
  // Stats
  const h=lh();
  $('dp-sessions').textContent=h.length||'—';
  $('dp-streak').textContent=getStreak()||'—';
}

function dpStartRandom(){
  const q=DRILL_QS[Math.floor(Math.random()*DRILL_QS.length)];
  dpStartOne(q);
}

function dpStartOne(q){
  DP.todayQ=q;
  DP.answers=[];DP.wk={we:0,metric:0,hedge:0,len:0};
  DP.qIdx=0;
  DP.queue=[q];
  DP.reps=0;
  dpShowStudy();
}

/* STUDY CARD — read question + full answer + key phrases before practising */
function dpShowStudy(){
  const q=DP.queue[DP.qIdx];
  $('dp-study-tag').textContent=q.day?`${q.day} · Study`:'Study';
  $('dp-study-q').textContent=q.q;
  $('dp-study-answer').textContent=q.answer||'';
  $('dp-study-tip').textContent=q.insight||q.tip||'';
  // Extract the three key phrases: ownership line, metric line, trade-off line
  $('dp-study-keys').innerHTML=dpBuildKeys(q);
  dpShowPhase('dp-study');
}

function dpBuildKeys(q){
  const ans=q.answer||'';
  const sentences=ans.split(/(?<=[.!?])\s+/).map(s=>s.trim()).filter(Boolean);
  const keys=[];
  // Ownership: a sentence with "I decided / I led / I cut / I chose / I built"
  const own=sentences.find(s=>/\bI (decided|led|cut|chose|built|owned|ran|identified|defined|set)\b/i.test(s));
  // Metric: a sentence containing a number/percent
  const met=sentences.find(s=>/(\d+\s*(percent|%)|up \d+|down \d+|\+\d+|-\d+|\d+\+?\/mo|\d+ percent)/i.test(s));
  // Trade-off: a sentence naming what was cut
  const trade=sentences.find(s=>/(cut|deprioritis|dropped|trade.?off|instead of|without being asked|I did not wait)/i.test(s));
  if(own)keys.push({l:'Ownership',t:own});
  if(met&&met!==own)keys.push({l:'Metric',t:met});
  if(trade&&trade!==own&&trade!==met)keys.push({l:'Trade-off',t:trade});
  if(!keys.length)return '';
  return keys.map(k=>`<div class="study-key"><span class="study-key-label">${k.l}</span><span class="study-key-text">${k.t}</span></div>`).join('');
}

function dpGoToPractice(mode){
  DP.input=mode;
  dpShowQ();
}

function dpBackToStudy(){
  dpStopTimer();
  if(DP.recog){try{DP.recog.stop();}catch(e){}DP.recog=null;}
  DP.recording=false;
  dpShowStudy();
}

function dpBackToHome(){
  dpStopTimer();
  if(DP.recog){try{DP.recog.stop();}catch(e){}DP.recog=null;}
  DP.recording=false;
  dpShowPhase('dp-home');
}

function dpShowQ(){
  const q=DP.queue[DP.qIdx];
  $('dp-q-num').textContent=q.day?`${q.day} · Practice from memory`:'Practice from memory';
  $('dp-q-text').textContent=q.q;
  $('dp-prog').style.width='0%';

  // Render warn tags + tip as hints
  const hintsEl=$('dp-q-hints');
  if(hintsEl){
    const warnHtml=(q.warn||[]).map(w=>`<span class="tag-warn">${w}</span>`).join('');
    const tipHtml=q.tip?`<span class="tag-tip">${q.tip}</span>`:'';
    hintsEl.innerHTML=`<div class="q-tags" style="margin:0">${warnHtml}${tipHtml}</div>`;
  }

  $('dp-txlive').textContent='Your words will appear here as you speak...';
  $('dp-txlive').classList.remove('has-text');
  $('dp-timer').textContent='0:00';
  $('dp-timer').className='timer-display';
  $('dp-timer-bar').style.width='100%';
  $('dp-rec-dot').classList.remove('on');
  $('dp-rec-status').textContent='Ready — tap to record';
  $('dp-rec-btn').textContent='Start recording';
  $('dp-submit-btn').style.display='none';
  if($('dp-text-answer'))$('dp-text-answer').value='';
  DP.transcript='';DP.recording=false;DP.timerSec=0;
  dpSetInput(DP.input||'voice');
  dpShowPhase('dp-question');
}

function dpSetInput(mode){
  DP.input=mode;
  $('dp-voice-area').style.display=mode==='voice'?'block':'none';
  $('dp-text-area').style.display=mode==='text'?'block':'none';
  $('dp-mode-voice').classList.toggle('selected',mode==='voice');
  $('dp-mode-text').classList.toggle('selected',mode==='text');
  if(mode==='text')$('dp-rec-btn').textContent='Start timer';
}

function dpHandleRec(){
  if(DP.input==='voice'){
    if(!DP.recording)dpStartRec();
    else dpStopRec();
  }else{
    if(!DP.timerInt)dpStartTextTimer();
    else{clearInterval(DP.timerInt);DP.timerInt=null;$('dp-submit-btn').style.display='inline-flex';}
  }
}

function dpStartRec(){
  const oaKey=localStorage.getItem('mb_oa');
  if(oaKey){dpStartOARec(oaKey);return;}
  // Native fallback
  const SR=window.SpeechRecognition||window.webkitSpeechRecognition;
  if(!SR){alert('Voice not supported in this browser. Switch to Text mode.');return;}
  DP.recog=new SR();DP.recog.continuous=true;DP.recog.interimResults=true;DP.recog.lang='en-US';
  DP.finalTranscript='';
  DP.recog.onresult=e=>{
    let interim='';
    for(let i=e.resultIndex;i<e.results.length;i++){
      if(e.results[i].isFinal)DP.finalTranscript+=e.results[i][0].transcript+' ';
      else interim+=e.results[i][0].transcript;
    }
    const txt=(DP.finalTranscript+interim).trim();
    $('dp-txlive').textContent=txt||'Speak now...';
    $('dp-txlive').classList.toggle('has-text',txt.length>0);
  };
  DP.recog.onerror=e=>{console.warn('SR error:',e.error);};
  DP.recog.onend=()=>{
    if(DP.recording){
      DP.recording=false;dpStopTimer();
      $('dp-rec-dot').classList.remove('on');$('dp-voice-area').classList.remove('live');
      $('dp-rec-btn').textContent='Re-record';
      $('dp-rec-status').textContent='Ready — review transcript';
      DP.transcript=DP.finalTranscript.trim();
      if(DP.transcript)$('dp-submit-btn').style.display='inline-flex';
    }
  };
  DP.recog.start();
  DP.recording=true;
  $('dp-rec-dot').classList.add('on');
  $('dp-rec-status').textContent='Recording — speak now';
  $('dp-rec-btn').textContent='Stop';
  $('dp-voice-area').classList.add('live');
  dpStartTimer();
}

async function dpStartOARec(oaKey){
  if(!navigator.mediaDevices){alert('Mic not available.');return;}
  try{
    const stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true}});
    DP.audioStream=stream;DP.audioChunks=[];DP.finalTranscript='';
    const opts={};
    if(MediaRecorder.isTypeSupported('audio/webm;codecs=opus'))opts.mimeType='audio/webm;codecs=opus';
    DP.recorder=new MediaRecorder(stream,opts);
    DP.recorder.ondataavailable=e=>{if(e.data&&e.data.size>0)DP.audioChunks.push(e.data);};
    DP.recorder.onstop=()=>dpTranscribe(oaKey);
    DP.recorder.start(250);
    DP.recording=true;
    $('dp-rec-dot').classList.add('on');$('dp-rec-status').textContent='Recording — speak now';
    $('dp-rec-btn').textContent='Stop';$('dp-voice-area').classList.add('live');
    $('dp-txlive').textContent='Speak now...';$('dp-txlive').classList.add('has-text');
    dpStartTimer();
  }catch(e){alert('Could not start mic: '+e.message);}
}

function dpStopRec(){
  DP.recording=false;
  if(DP.recog){try{DP.recog.stop();}catch(e){}DP.recog=null;}
  if(DP.recorder&&DP.recorder.state!=='inactive'){
    $('dp-rec-btn').textContent='Transcribing...';
    $('dp-rec-status').textContent='Transcribing...';
    try{DP.recorder.stop();}catch(e){dpCleanupAudio();}
  }else{dpCleanupAudio();}
  dpStopTimer();
  $('dp-rec-dot').classList.remove('on');$('dp-voice-area').classList.remove('live');
}

async function dpTranscribe(oaKey){
  const blob=new Blob(DP.audioChunks,{type:DP.audioChunks[0]?.type||'audio/webm'});
  if(!blob.size){dpCleanupAudio();$('dp-rec-btn').textContent='Re-record';return;}
  $('dp-rec-status').textContent='Transcribing...';
  const fd=new FormData();
  fd.append('action','transcribe');fd.append('openai_key',oaKey);fd.append('lang','en');
  fd.append('audio',blob,'answer.webm');
  try{
    const res=await fetch(location.href,{method:'POST',body:fd});
    const data=await res.json();
    if(!res.ok||data.error)throw new Error(data.error||'Transcription failed');
    const text=(data.text||'').trim();
    DP.transcript=text;DP.finalTranscript=text;
    $('dp-txlive').textContent=text||'...';$('dp-txlive').classList.toggle('has-text',text.length>0);
    $('dp-submit-btn').style.display='inline-flex';
    $('dp-rec-btn').textContent='Re-record';$('dp-rec-status').textContent='Ready — review transcript';
  }catch(e){
    $('dp-rec-status').textContent='Transcription failed';alert(e.message);
  }finally{dpCleanupAudio();}
}

function dpCleanupAudio(){
  if(DP.audioStream){try{DP.audioStream.getTracks().forEach(t=>t.stop());}catch(e){}DP.audioStream=null;}
  DP.recorder=null;DP.audioChunks=[];
  $('dp-rec-btn').textContent='Re-record';
  $('dp-rec-status').textContent='Done';
}

function dpStartTimer(){
  DP.timerSec=0;const sa=performance.now();
  DP.timerInt=setInterval(()=>{
    const e=Math.floor((performance.now()-sa)/1000);DP.timerSec=e;
    const mm=Math.floor(e/60),ss=String(e%60).padStart(2,'0');
    const d=$('dp-timer'),b=$('dp-timer-bar');
    if(d){d.textContent=`${mm}:${ss}`;d.className='timer-display'+(e>90?' danger':e>60?' warn':'');}
    if(b){b.style.width=Math.max(0,100-(e/120)*100)+'%';b.style.background=e>90?'var(--danger)':e>60?'var(--warn)':'var(--sage)';}
    if(e>=120)dpStopRec();
  },1000);
}

function dpStartTextTimer(){
  $('dp-rec-btn').textContent='Stop timer';DP.timerSec=0;const sa=performance.now();
  DP.timerInt=setInterval(()=>{
    const e=Math.floor((performance.now()-sa)/1000);DP.timerSec=e;
    const mm=Math.floor(e/60),ss=String(e%60).padStart(2,'0');
    const d=$('dp-timer-text'),b=$('dp-timer-bar-text');
    if(d){d.textContent=`${mm}:${ss}`;d.className='timer-display'+(e>90?' danger':e>60?' warn':'');}
    if(b){b.style.width=Math.max(0,100-(e/120)*100)+'%';}
  },1000);
  $('dp-submit-btn').style.display='inline-flex';
}

function dpStopTimer(){if(DP.timerInt){clearInterval(DP.timerInt);DP.timerInt=null;}}

function dpSubmit(){
  const ans=DP.input==='voice'?(DP.transcript||DP.finalTranscript||'').trim():($('dp-text-answer').value||'').trim();
  if(ans.length<20){alert('Answer too short. Give at least a sentence.');return;}
  dpStopTimer();
  DP.answers.push({q:DP.queue[DP.qIdx].q,a:ans,t:DP.timerSec});
  dpRunChecklist(ans);
}

function dpRunChecklist(ans){
  const wc=ans.split(/\s+/).filter(Boolean).length;
  const we=(ans.match(/\bwe\b/gi)||[]).length;
  const hasMetric=/(\d+%|up \d+|down \d+|\+\d+|-\d+|percent|percent)/i.test(ans)||(ans.match(/\b(adoption|csat|resolution|engagement|conversion|retention)\b/gi)||[]).length>0;
  const hedges=(ans.match(/\b(i think|maybe|kind of|sort of|probably|i guess|perhaps|basically|somewhat|kinda)\b/gi)||[]).length;
  const firstSent=ans.split(/[.!?]/)[0]||'';
  const leadsWithContext=/^(at |in |during |when |after |before |so |well |um )/i.test(firstSent.trim());
  const hasTradeoff=/(cut|dropped|removed|decided against|chose not|prioritised|deprioritised|trade.?off|instead of)/i.test(ans);

  if(we>1)DP.wk.we++;
  if(!hasMetric)DP.wk.metric++;
  if(hedges>0)DP.wk.hedge++;
  if(wc>180)DP.wk.len++;

  const checks=[];
  if(we===0)checks.push({ok:true,text:'Used "I" consistently — no "we" detected'});
  else if(we===1)checks.push({ok:null,text:`Said "we" once — watch this`});
  else checks.push({ok:false,text:`Said "we" ${we} times — replace with "I decided"`});

  if(hasMetric)checks.push({ok:true,text:'Metric cited — good'});
  else checks.push({ok:false,text:'No metric detected — add CSAT, adoption, or resolution number'});

  if(hedges===0)checks.push({ok:true,text:'No hedging language — clean delivery'});
  else checks.push({ok:false,text:`Hedging detected (${hedges}x) — cut "I think", "maybe", "probably"`});

  if(!leadsWithContext)checks.push({ok:true,text:'First sentence leads with answer — correct'});
  else checks.push({ok:false,text:'First sentence is context — lead with the answer instead'});

  if(wc<=150)checks.push({ok:true,text:`${wc} words — within target (60-90s)`});
  else if(wc<=180)checks.push({ok:null,text:`${wc} words — slightly long, aim for under 150`});
  else checks.push({ok:false,text:`${wc} words — too long, cut context and repeat points`});

  if(hasTradeoff)checks.push({ok:true,text:'Trade-off mentioned — shows prioritisation'});
  else checks.push({ok:null,text:'No trade-off mentioned — consider naming what you cut'});

  $('dp-checklist').innerHTML=checks.map(c=>`
    <li>
      <span class="ci ${c.ok===true?'c-pass':c.ok===false?'c-fail':'c-warn'}">${c.ok===true?'✓':c.ok===false?'✗':'!'}</span>
      <span>${c.text}</span>
    </li>`).join('');

  $('dp-ans-display').textContent=ans;

  const q=DP.queue[DP.qIdx];
  $('dp-model-text').textContent=q.answer||'';
  $('dp-model-insight').textContent=q.insight||'';
  $('dp-rep-counter').textContent=DP.reps>0?`Rep ${DP.reps}`:'';

  $('dp-prog-rv').style.width='100%';

  // AI score (optional) if Claude key set
  const ck=localStorage.getItem('mb_dk');
  if(ck){
    $('dp-ai-score-area').style.display='block';
    dpGetAIScore(q.q,ans,DP.timerSec);
  }else{
    $('dp-ai-score-area').style.display='none';
  }

  dpShowPhase('dp-review');
}

async function dpGetAIScore(question,answer,timeSec){
  const ck=localStorage.getItem('mb_dk');
  if(!ck)return;
  const wc=answer.split(/\s+/).length;
  const prompt=`Question: "${question}"\n\nManu's answer (${timeSec}s, ~${wc} words):\n"${answer}"\n\nScore harshly. Quote exact words for every negative.\n\nReturn ONLY JSON:\n{"overall":1,"structure":1,"ownership":1,"metrics":1,"weaknesses_triggered":[],"what_worked":"","critical_fix":"","rewrite_sentence":"","filler_words_found":[],"we_count":0,"metric_cited":false,"word_count":${wc}}`;
  try{
    const res=await claude([{role:'user',content:prompt}],320,ck);
    const d=extractJson(res);
    let html='';
    if(d.what_worked)html+=`<strong>What worked:</strong>\n${d.what_worked}\n\n`;
    if(d.critical_fix)html+=`<strong>Fix this first:</strong>\n${d.critical_fix}\n\n`;
    if(d.rewrite_sentence)html+=`<strong>Better opening:</strong>\n"${d.rewrite_sentence}"\n\n`;
    if(d.we_count>1)html+=`<strong>Said "we" ${d.we_count} times.</strong> Use "I".\n\n`;
    if(!d.metric_cited)html+=`<strong>No metric.</strong> Cognigy: -30%. Lengoo: +15% CSAT, +20% adoption.\n`;
    const scoreColor=d.overall>=7?'var(--sage)':d.overall>=5?'var(--warn)':'var(--danger)';
    $('dp-ai-score-content').innerHTML=`<div style="display:flex;align-items:center;gap:12px;margin-bottom:12px"><div style="font-family:'Shippori Mincho',serif;font-size:32px;font-weight:600;color:${scoreColor}">${d.overall}<span style="font-size:16px;color:var(--ink-ghost)">/10</span></div><div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;flex:1"><div style="background:var(--surface);padding:6px 10px;border-radius:var(--r)"><div style="font-size:9px;color:var(--ink-ghost);font-family:'DM Mono',monospace;text-transform:uppercase">Structure</div><div style="font-weight:500">${d.structure}/10</div></div><div style="background:var(--surface);padding:6px 10px;border-radius:var(--r)"><div style="font-size:9px;color:var(--ink-ghost);font-family:'DM Mono',monospace;text-transform:uppercase">Ownership</div><div style="font-weight:500">${d.ownership}/10</div></div><div style="background:var(--surface);padding:6px 10px;border-radius:var(--r)"><div style="font-size:9px;color:var(--ink-ghost);font-family:'DM Mono',monospace;text-transform:uppercase">Metrics</div><div style="font-weight:500">${d.metrics}/10</div></div></div></div><div class="feedback-body fu">${html.trim()}</div>`;
  }catch(e){
    $('dp-ai-score-content').innerHTML=`<p style="font-size:13px;color:var(--ink-ghost)">Local scoring only (Claude unavailable). Check your API key.</p>`;
  }
}

function dpAgain(){
  // Repeat the SAME question. Counts as a rep.
  DP.reps=(DP.reps||0)+1;
  dpShowQ();
}

function dpNewQuestion(){
  // Different random question, fresh study card.
  const pool=DRILL_QS.filter(x=>x.id!==(DP.queue[DP.qIdx]&&DP.queue[DP.qIdx].id));
  const q=pool[Math.floor(Math.random()*pool.length)]||DRILL_QS[0];
  dpStartOne(q);
}

function dpFinish(){
  dpShowSummary();
}

function dpRetry(){dpShowQ();}

function dpSkip(){
  // Skip now lands on the review screen with the model answer visible,
  // so a skip still teaches instead of dumping straight to the summary.
  const q=DP.queue[DP.qIdx];
  DP.answers.push({q:q.q,a:'[skipped]',t:0});
  $('dp-checklist').innerHTML='<li><span class="ci c-warn">!</span><span>Skipped — no answer to check. Read the model answer below, then hit Again.</span></li>';
  $('dp-ans-display').textContent='(skipped)';
  $('dp-model-text').textContent=q.answer||'';
  $('dp-model-insight').textContent=q.insight||'';
  $('dp-rep-counter').textContent=DP.reps>0?`Rep ${DP.reps}`:'';
  $('dp-prog-rv').style.width='100%';
  $('dp-ai-score-area').style.display='none';
  dpShowPhase('dp-review');
}

function dpShowSummary(){
  $('dp-sum-count').textContent=DP.answers.filter(a=>a.a!=='[skipped]').length;
  const wk=DP.wk;const patterns=[];
  if(wk.we>=1)patterns.push({ok:false,text:`Said "we" instead of "I" in ${wk.we} answer${wk.we>1?'s':''}. Most consistent pattern.`});
  if(wk.metric>=1)patterns.push({ok:false,text:`No metric in ${wk.metric} answer${wk.metric>1?'s':''}. Cognigy: -30%. Lengoo: +15% CSAT, +20% adoption.`});
  if(wk.hedge>=1)patterns.push({ok:false,text:`Hedging in ${wk.hedge} answer${wk.hedge>1?'s':''}. Cut "I think", "maybe", "probably".`});
  if(wk.len>=1)patterns.push({ok:false,text:`Too long in ${wk.len} case${wk.len>1?'s':''}. Target 60–90s.`});
  if(!patterns.length)patterns.push({ok:true,text:'No major patterns. Clean session. Keep the reps going.'});
  $('dp-sum-patterns').innerHTML=patterns.map(p=>`<li><span class="ci ${p.ok?'c-pass':'c-fail'}">${p.ok?'✓':'⚠'}</span><span>${p.text}</span></li>`).join('');
  const top=Object.entries(wk).sort((a,b)=>b[1]-a[1])[0];
  const focusMap={we:"Tomorrow: count every 'we'. Target zero. Replace with 'I decided'.",metric:"Tomorrow: write your metric before answering. Cognigy: -30%. Lengoo: +15% CSAT.",hedge:"Tomorrow: first sentence with zero qualifying words. Direct statement.",len:"Tomorrow: 90-second timer. Stop when it rings."};
  $('dp-sum-focus').textContent=top&&top[1]>0?(focusMap[top[0]]||'Same questions tomorrow.'): 'Same questions tomorrow. Keep the reps going.';
  dpShowPhase('dp-summary');
  // Advance sequential counter
  if(DP.rotation==='sequential'){
    const cur=parseInt(localStorage.getItem('mb_dp_seq')||'0');
    localStorage.setItem('mb_dp_seq',String((cur+1)%DRILL_QS.length));
  }
  // Save per-question weakness scores for 'weakest first' mode
  if(DP.todayQ&&DP.wk){
    const scores=JSON.parse(localStorage.getItem('mb_dp_scores')||'{}');
    const id=DP.todayQ.id;
    const issueCount=DP.wk.we+DP.wk.metric+DP.wk.hedge+DP.wk.len;
    if(!scores[id])scores[id]={total:0,count:0};
    scores[id].total+=issueCount;scores[id].count++;
    localStorage.setItem('mb_dp_scores',JSON.stringify(scores));
  }
  // Save to history
  const answered=DP.answers.filter(a=>a.a!=='[skipped]').length;
  if(answered>0){const h=lh();h.push({d:new Date().toISOString().split('T')[0],s:answered*2});if(h.length>30)h.splice(0,h.length-30);sh(h);}
  renderDrillHome();
}

function dpHome(){dpShowPhase('dp-home');}
function dpGoHome(){dpStopTimer();if(DP.recog){try{DP.recog.stop();}catch(e){}}DP.recog=null;dpShowPhase('dp-home');}

function dpShowPhase(id){
  ['dp-home','dp-study','dp-question','dp-review','dp-summary'].forEach(p=>{
    const el=$(p);if(el)el.style.display=p===id?'block':'none';
  });
  window.scrollTo({top:0,behavior:'smooth'});
}

/* ══════════════════════════════════════════
   AI DRILL (Tier 2+3) — from template-drill-ai.php
══════════════════════════════════════════ */
function selectMode(el,m){qsa('[data-mode]').forEach(c=>c.classList.remove('selected'));el.classList.add('selected');S.mode=m;S.total=m==='quick'?3:5;}
function selectLang(el,l){qsa('[data-lang]').forEach(c=>c.classList.remove('selected'));el.classList.add('selected');S.lang=l;}
function selectInput(el,m){qsa('[data-input]').forEach(c=>c.classList.remove('selected'));el.classList.add('selected');S.input=m;}

function buildStars(){
  qsa('.stars').forEach(row=>{
    const dim=row.dataset.dim;
    for(let i=1;i<=5;i++){const s=document.createElement('span');s.className='star';s.textContent='★';s.dataset.val=i;s.onclick=()=>rateSelf(row,dim,i);row.appendChild(s);}
  });
}
function rateSelf(row,dim,val){row.querySelectorAll('.star').forEach((s,i)=>s.classList.toggle('on',i<val));S.selfRat[dim]=val;}

function resetSessionState(){
  stopTimer();stopRec();
  S.cq=0;S.total=S.mode==='quick'?3:5;S.answers=[];S.scores=[];S.selfR=[];S.questions=[];
  S.sessStart=null;S.transcript='';S.finalTranscript='';S.recording=false;S.stopRequested=false;
  S.timerSec=0;S.selfRat={};S.wk={own:0,met:0,hed:0,str:0,len:0};
  const ta=$('text-answer');if(ta)ta.value='';
}

function showDrillPhase(id){
  ['ph-setup','ph-loading','ph-question','ph-selfrate','ph-feedback','ph-summary'].forEach(p=>{
    const el=$(p);if(el)el.style.display=p===id?'block':'none';
  });
  window.scrollTo({top:0,behavior:'smooth'});
}

async function startSession(){
  const key=$('api-key').value.trim();
  S.key=key;if(key)localStorage.setItem('mb_dk',key);
  const oaKey=$('oa-key').value.trim();
  S.oaKey=oaKey;if(oaKey)localStorage.setItem('mb_oa',oaKey);
  S.jd=$('jd-input').value.trim();
  S.cq=0;S.answers=[];S.scores=[];S.selfR=[];S.sessStart=Date.now();
  S.wk={own:0,met:0,hed:0,str:0,len:0};
  showDrillPhase('ph-loading');
  $('load-title').textContent=T[S.lang].load;
  const msgs=['Analysing the role...','Picking the hardest questions...','Tailoring to your profile...'];
  let mi=0;const li=setInterval(()=>{mi=(mi+1)%msgs.length;$('load-msg').textContent=msgs[mi];},1400);
  try{S.questions=pickQuestions(S.lang,S.mode,S.jd);}
  catch(e){S.questions=(QUESTION_META[S.lang]||QUESTION_META.en).slice(0,S.total).map(x=>x.q);}
  finally{clearInterval(li);}
  showQ();
}

function jdTerms(jd){return(jd||'').toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g,'').replace(/[^a-z0-9\s+-]/g,' ').split(/\s+/).filter(Boolean);}

function questionScore(meta,jd){
  const text=(meta.q+' '+(jd||'')).toLowerCase();const terms=jdTerms(jd);let score=0;
  const add=(cond,val)=>{if(cond)score+=val;};
  add(meta.tags.includes('intro'),10);add(meta.tags.includes('metrics'),8);add(meta.tags.includes('ownership'),8);
  add(meta.tags.includes('tradeoff'),7);add(meta.tags.includes('stakeholder'),6);add(meta.tags.includes('structure'),6);
  add(meta.tags.includes('hedge'),5);
  add(text.includes('metric')||text.includes('csat')||text.includes('adoption')||text.includes('resolution'),4);
  add(text.includes('engineer')||text.includes('engineering'),4);add(text.includes('stakeholder')||text.includes('sales'),3);
  add(terms.some(t=>['data','metric','kpi','analytics'].includes(t)),4);
  add(terms.some(t=>['ownership','decision','tradeoff','trade-off'].includes(t)),4);
  add(terms.some(t=>['engineer','engineering','tech'].includes(t)),3);
  return score;
}

function pickQuestions(lang,mode,jd){
  const pool=QUESTION_META[lang]||QUESTION_META.en;const n=mode==='quick'?3:5;
  return [...pool].map((meta,idx)=>({meta,idx,score:questionScore(meta,jd)})).sort((a,b)=>b.score-a.score||a.idx-b.idx).slice(0,n).map(x=>x.meta.q);
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
  $('rec-dot').classList.remove('on');$('rec-status-text').textContent=t.ready;
  $('rec-btn').textContent=t.start;$('rec-btn').style.display='inline-flex';
  $('submit-btn').style.display='none';$('submit-btn').textContent=t.getfb;
  $('skip-btn').textContent=t.skip;$('q-hint').textContent=t.hint;
  if($('text-answer'))$('text-answer').value='';
  S.transcript='';S.finalTranscript='';S.recording=false;S.stopRequested=false;S.timerSec=0;S.selfRat={};
  $('voice-area').style.display=S.input==='voice'?'block':'none';
  $('text-area').style.display=S.input==='text'?'block':'none';
  if(S.input==='text')$('rec-btn').textContent=t.timer;
  qsa('.star').forEach(s=>s.classList.remove('on'));
  showDrillPhase('ph-question');
}

function startTimer(){
  S.timerSec=0;S.maxS=120;const sa=performance.now();
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

function handleRecBtn(){
  if(S.input==='voice'){if(!S.recording&&!S.transcribing)startRec();else if(S.recording)stopAndReady();}
  else{if(!S.timerInt)startTextTimer();else stopTimer();}
}
async function startRec(){
  if(!navigator.mediaDevices||!window.MediaRecorder){startNativeSR();return;}
  if(!S.oaKey){startNativeSR();return;}
  try{
    const stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true,autoGainControl:true}});
    S.stopRequested=false;S.transcribing=false;S.audioStream=stream;S.audioChunks=[];S.finalTranscript='';S.transcript='';
    const opts={};
    if(MediaRecorder.isTypeSupported('audio/webm;codecs=opus'))opts.mimeType='audio/webm;codecs=opus';
    else if(MediaRecorder.isTypeSupported('audio/webm'))opts.mimeType='audio/webm';
    S.recorder=new MediaRecorder(stream,opts);
    S.recorder.ondataavailable=e=>{if(e.data&&e.data.size>0)S.audioChunks.push(e.data);};
    S.recorder.onstop=()=>{if(S.stopRequested)transcribeAudio();};
    S.recording=true;
    $('rec-dot').classList.add('on');$('rec-status-text').textContent=T[S.lang].rec;
    $('rec-btn').textContent=T[S.lang].stop;$('voice-area').classList.add('live');
    $('txlive').textContent=S.lang==='de'?'Sprich jetzt...':'Speak now...';$('txlive').classList.add('has-text');
    startTimer();startVol(stream);S.recorder.start(250);
  }catch(e){console.warn('MediaRecorder failed:',e);startNativeSR();}
}
function startNativeSR(){
  const SR=window.SpeechRecognition||window.webkitSpeechRecognition;
  if(!SR){alert('Voice not supported. Use text mode.');return;}
  S.recog=new SR();S.recog.continuous=true;S.recog.interimResults=true;S.recog.lang=S.lang==='de'?'de-DE':'en-US';
  S.finalTranscript='';S.recording=true;
  $('rec-dot').classList.add('on');$('rec-status-text').textContent=T[S.lang].rec;
  $('rec-btn').textContent=T[S.lang].stop;$('voice-area').classList.add('live');
  $('txlive').textContent='Speak now...';$('txlive').classList.add('has-text');
  startTimer();
  S.recog.onresult=e=>{
    let interim='';
    for(let i=e.resultIndex;i<e.results.length;i++){
      if(e.results[i].isFinal)S.finalTranscript+=e.results[i][0].transcript+' ';
      else interim+=e.results[i][0].transcript;
    }
    const txt=(S.finalTranscript+interim).trim();
    $('txlive').textContent=txt||'...';$('txlive').classList.toggle('has-text',txt.length>0);
  };
  S.recog.onerror=e=>{console.warn('SR:',e.error);};
  S.recog.onend=()=>{
    if(S.recording){
      S.recording=false;stopTimer();
      $('rec-dot').classList.remove('on');$('voice-area').classList.remove('live');
      S.transcript=S.finalTranscript.trim();
      $('submit-btn').style.display='inline-flex';
      $('rec-btn').textContent=T[S.lang].rerec;$('rec-status-text').textContent='Ready — review transcript';
    }
  };
  S.recog.start();
}
function stopAndReady(){
  S.stopRequested=true;stopTimer();stopVol();
  $('rec-btn').textContent=S.lang==='de'?'Transkribiere...':'Transcribing...';
  $('rec-status-text').textContent=S.lang==='de'?'Transkribiere...':'Transcribing...';
  if(S.recog){try{S.recog.stop();}catch(e){}S.recog=null;return;}
  if(S.recorder&&S.recorder.state!=='inactive'){try{S.recorder.stop();}catch(e){transcribeAudio();}}
  else{transcribeAudio();}
}
function stopRec(){
  S.recording=false;
  if(S.recog){try{S.recog.stop();}catch(e){}S.recog=null;}
  if(S.recorder){try{if(S.recorder.state!=='inactive')S.recorder.stop();}catch(e){}S.recorder=null;}
  if(S.audioStream){try{S.audioStream.getTracks().forEach(t=>t.stop());}catch(e){}S.audioStream=null;}
  S.audioChunks=[];stopTimer();stopVol();$('rec-dot').classList.remove('on');$('voice-area').classList.remove('live');
}
function startVol(stream){
  if(!stream)return;if(S.actx){try{S.actx.close();}catch(e){}S.actx=null;}
  try{
    S.actx=new(window.AudioContext||window.webkitAudioContext)();S.analyser=S.actx.createAnalyser();S.analyser.fftSize=64;
    S.actx.createMediaStreamSource(stream).connect(S.analyser);
    const bars=qsa('#vol-bars .vb'),data=new Uint8Array(S.analyser.frequencyBinCount);
    function draw(){
      if(!S.recording){bars.forEach(b=>{b.style.height='3px';b.style.background='var(--surface2)';});return;}
      S.analyser.getByteFrequencyData(data);
      bars.forEach((b,i)=>{const v=data[i*3]||0;b.style.height=Math.max(3,Math.min(22,v/6))+'px';b.style.background=v>80?'var(--sage)':'var(--surface2)';});
      S.raf=requestAnimationFrame(draw);
    }
    draw();
  }catch(e){}
}
function stopVol(){if(S.raf){cancelAnimationFrame(S.raf);S.raf=null;}if(S.actx){try{S.actx.close();}catch(e){}S.actx=null;}}

async function transcribeAudio(){
  if(S.transcribing)return;S.transcribing=true;
  $('rec-status-text').textContent=S.lang==='de'?'Audio wird gesendet...':'Sending audio...';
  const chunks=S.audioChunks.slice();const blob=new Blob(chunks,{type:chunks[0]?.type||'audio/webm'});
  if(!blob.size){S.transcribing=false;stopRec();$('rec-btn').textContent=T[S.lang].rerec;return;}
  const fd=new FormData();fd.append('action','transcribe');fd.append('openai_key',S.oaKey);fd.append('lang',S.lang);fd.append('audio',blob,'answer.webm');
  try{
    const res=await fetch(location.href,{method:'POST',body:fd});
    const data=await res.json();
    if(!res.ok||data.error)throw new Error(data.error||`HTTP ${res.status}`);
    const text=(data.text||'').trim();S.finalTranscript=text;S.transcript=text;
    const el=$('txlive');if(el){el.textContent=text||'...';el.classList.toggle('has-text',text.length>0);}
    $('submit-btn').style.display='inline-flex';$('rec-btn').textContent=T[S.lang].rerec;
    $('rec-status-text').textContent=S.lang==='de'?'Bereit — prüfe das Transkript':'Ready — review transcript';
  }catch(e){
    console.error('Transcription failed:',e);$('rec-status-text').textContent='Transcription failed';alert(e?.message||'Transcription failed');
  }finally{stopRec();S.transcribing=false;}
}

async function submitAnswer(){
  const ans=S.input==='voice'?(S.finalTranscript||S.transcript).trim():$('text-answer').value.trim();
  if(ans.length<30){alert(S.lang==='de'?'Antwort zu kurz.':'Answer too short.');return;}
  S.answers.push({q:S.questions[S.cq],a:ans,t:S.timerSec});
  const pct=((S.cq+1)/S.total)*100;$('prog-sr').style.width=pct+'%';
  const t=T[S.lang];$('sr-title').textContent=t.srt;$('sr-sub').textContent=t.srs;$('sr-btn').textContent=t.seefb;
  S.selfRat={};qsa('.star').forEach(s=>s.classList.remove('on'));
  showDrillPhase('ph-selfrate');
}

function showFeedback(){
  S.selfR.push({...S.selfRat});const pct=((S.cq+1)/S.total)*100;$('prog-fb').style.width=pct+'%';
  ['dc-str','dc-own','dc-met','dc-ov'].forEach(id=>{const el=$(id);if(el){el.textContent='—';el.closest('.dim').className='dim';}});
  $('ring-fg').style.strokeDashoffset='226.2';$('ring-fg').style.stroke='var(--sage)';$('ring-num').textContent='—';
  $('tag-row').innerHTML='';$('fb-stream').innerHTML=`<div class="dots"><span></span><span></span><span></span><span class="dots-msg">Scoring your answer...</span></div>`;
  $('fb-label').textContent=T[S.lang].fbl;$('ans-label').textContent=T[S.lang].ansl;$('fb-btns').style.display='none';
  const ans=S.answers[S.answers.length-1];$('tx-display').textContent=ans.a;
  showDrillPhase('ph-feedback');scoreFeedback(ans.q,ans.a,ans.t);
}

function retryQuestion(){
  if(S.answers.length)S.answers.pop();if(S.scores.length)S.scores.pop();if(S.selfR.length)S.selfR.pop();
  S.selfRat={};S.transcript='';S.finalTranscript='';S.timerSec=0;stopTimer();stopRec();
  if($('text-answer'))$('text-answer').value='';showQ();
}

/* Back from self-rate to re-record the same answer (no data loss beyond the popped answer) */
function backToRecord(){
  if(S.answers.length)S.answers.pop();
  S.transcript='';S.finalTranscript='';S.timerSec=0;stopTimer();stopRec();
  if($('text-answer'))$('text-answer').value='';
  showQ();
}

/* Abort an in-progress AI Drill session and return to setup cleanly */
function abortSession(){
  stopTimer();stopRec();
  resetSessionState();updateStats();
  showDrillPhase('ph-setup');
}

async function scoreFeedback(question,answer,timeSec){
  const wc=answer.split(/\s+/).length;const isDE=S.lang==='de';
  const prompt=isDE
    ?`Frage: "${question}"\n\nManus Antwort (${timeSec}s, ~${wc} Wörter):\n"${answer}"\n\nBewerte streng. Nur JSON:\n{"overall":1,"structure":1,"ownership":1,"metrics":1,"weaknesses_triggered":[],"what_worked":"","critical_fix":"","rewrite_sentence":"","cut_this":"","say_this":"","strongest_line":"","filler_words_found":[],"metric_evidence":[],"we_count":0,"metric_cited":false,"word_count":${wc}}`
    :`Question: "${question}"\n\nManu's answer (${timeSec}s, ~${wc} words):\n"${answer}"\n\nScore harshly. Quote exact words for every negative. Return ONLY JSON:\n{"overall":1,"structure":1,"ownership":1,"metrics":1,"weaknesses_triggered":[],"what_worked":"","critical_fix":"","rewrite_sentence":"","cut_this":"","say_this":"","strongest_line":"","filler_words_found":[],"metric_evidence":[],"we_count":0,"metric_cited":false,"word_count":${wc}}`;
  try{
    const key=localStorage.getItem('mb_dk');
    if(!key)throw new Error('No API key');
    const res=await claude([{role:'user',content:prompt}],320,key);
    const d=extractJson(res);const s=normalizeScore(d,question,answer,timeSec);
    renderFeedback(s,isDE);
  }catch(e){
    console.warn('Claude failed, using local fallback:',e);
    const s=localScore(question,answer,timeSec);renderFeedback(s,isDE);
  }
}

function renderFeedback(s,isDE){
  animRing(s.overall);
  [{id:'dc-str',v:s.structure},{id:'dc-own',v:s.ownership},{id:'dc-met',v:s.metrics},{id:'dc-ov',v:s.overall}].forEach(({id,v})=>{
    const el=$(id);if(el){el.textContent=v+'/10';el.closest('.dim').className='dim '+(v>=7?'good':v>=5?'warn':'bad');}
  });
  if(s.weaknesses_triggered){s.weaknesses_triggered.forEach(w=>{if(w.includes('ownership'))S.wk.own++;if(w.includes('metric'))S.wk.met++;if(w.includes('hedge')||w.includes('spiral'))S.wk.hed++;if(w.includes('structure'))S.wk.str++;});}
  if(s.word_count>180)S.wk.len++;
  const wt=(s.weaknesses_triggered||[]).map(w=>`<span class="wtag">${w}</span>`).join('');
  const mt=s.metric_cited?`<span class="wtag pass">${isDE?'Zahl ✓':'metric ✓'}</span>`:`<span class="wtag">${isDE?'keine Zahl':'no metric'}</span>`;
  const we=s.we_count>1?`<span class="wtag">"we" ${s.we_count}x</span>`:'';
  $('tag-row').innerHTML=wt+mt+we;
  let html='';
  if(s.what_worked)html+=`<strong>${isDE?'Was gut war':'What worked'}:</strong>\n${s.what_worked}\n\n`;
  if(s.critical_fix)html+=`<strong>${isDE?'Wichtigstes zu verbessern':'Fix this first'}:</strong>\n${s.critical_fix}\n\n`;
  if(s.rewrite_sentence)html+=`<strong>${isDE?'Bessere Eröffnung':'Better opening'}:</strong>\n"${s.rewrite_sentence}"\n\n`;
  if(s.cut_this)html+=`<strong>${isDE?'Streichen':'Cut this'}:</strong>\n${s.cut_this}\n\n`;
  if(s.say_this)html+=`<strong>${isDE?'Stattdessen sagen':'Say this instead'}:</strong>\n${s.say_this}\n\n`;
  if(s.strongest_line)html+=`<strong>${isDE?'Stärkste Zeile':'Strongest line'}:</strong>\n${s.strongest_line}\n\n`;
  if(s.filler_words_found?.length)html+=`<strong>${isDE?'Füllwörter':'Filler words'}:</strong> ${s.filler_words_found.join(', ')}\n\n`;
  if(s.we_count>1)html+=`<strong>Said "we" ${s.we_count} times.</strong> Use "I".\n\n`;
  if(!s.metric_cited)html+=`<strong>No metric.</strong> Cognigy: -30% resolution. Lengoo: +15% CSAT, +20% adoption.\n`;
  $('fb-stream').innerHTML=`<div class="feedback-body fu">${html.trim()||'Scoring complete.'}</div>`;
  S.scores.push({overall:s.overall,structure:s.structure,ownership:s.ownership,metrics:s.metrics,weaknesses:s.weaknesses_triggered||[],metric_cited:s.metric_cited,we_count:s.we_count||0});
  $('fb-btns').style.display='flex';$('retry-btn').textContent=T[S.lang].retry;$('next-btn').textContent=T[S.lang].next;
}

function animRing(score){
  const circ=226.2,offset=circ-(circ*(score/10));
  const ring=$('ring-fg'),num=$('ring-num');
  ring.style.strokeDashoffset=offset;ring.style.stroke=score>=7?'var(--sage)':score>=5?'var(--warn)':'var(--danger)';
  let cur=0;const step=setInterval(()=>{cur+=0.4;num.textContent=Math.min(Math.round(cur),score)+'/10';if(cur>=score)clearInterval(step);},40);
}

function nextQuestion(){S.cq++;if(S.cq>=S.total)buildSummary();else showQ();}
function skipQuestion(){
  S.answers.push({q:S.questions[S.cq],a:'[skipped]',t:0});
  S.scores.push({overall:0,structure:0,ownership:0,metrics:0,weaknesses:['skipped'],metric_cited:false,we_count:0});
  S.selfR.push({});S.cq++;if(S.cq>=S.total)buildSummary();else showQ();
}

function buildSummary(){
  stopTimer();stopRec();const isDE=S.lang==='de';
  const answered=S.scores.filter(s=>s.overall>0).length;
  const avg=answered?Math.round(S.scores.filter(s=>s.overall>0).reduce((a,b)=>a+b.overall,0)/answered):0;
  const totalMin=Math.round((Date.now()-S.sessStart)/60000);
  if(avg>0)saveS(avg);updateStats();
  setTimeout(()=>{const ring=$('sum-ring');const circ=314.2;ring.style.strokeDashoffset=circ-(circ*(avg/10));ring.style.stroke=avg>=7?'var(--sage)':avg>=5?'var(--warn)':'var(--danger)';$('sum-ring-num').textContent=avg;},200);
  $('sum-meta').textContent=`${answered} answer${answered!==1?'s':''} scored`;
  $('sum-answered').textContent=answered;$('sum-avg').textContent=avg+'/10';$('sum-time').textContent=totalMin+'m';
  const wk=S.wk;const patterns=[];
  if(wk.own>=2)patterns.push({i:'⚠',t:`Said "we" not "I" in ${wk.own} answers. Most consistent pattern.`});
  if(wk.met>=2)patterns.push({i:'⚠',t:`No metric in ${wk.met} answers. Cognigy: -30%. Lengoo: +15% CSAT.`});
  if(wk.hed>=2)patterns.push({i:'⚠',t:`Hedging in ${wk.hed} answers. Cut "I think", "maybe".`});
  if(wk.str>=2)patterns.push({i:'⚠',t:`Structure collapsed in ${wk.str} answers. First sentence = direct answer.`});
  if(wk.len>=2)patterns.push({i:'⚠',t:`Too long in ${wk.len} cases. Target 60–90s.`});
  if(!patterns.length)patterns.push({i:'✓',t:'No major patterns. Keep the daily reps going.'});
  $('pat-label').textContent=T[S.lang].patterns;
  $('pat-list').innerHTML=patterns.map(p=>`<li><span class="pi">${p.i}</span><span>${p.t}</span></li>`).join('');
  const top=Object.entries(wk).sort((a,b)=>b[1]-a[1])[0];
  const pm={own:"Tomorrow: count every 'we'. Target zero. Replace with 'I decided'.",met:"Tomorrow: write your metric before answering. Cognigy: -30%. Lengoo: +15% CSAT.",hed:"Tomorrow: first sentence with zero qualifying words.",str:"Tomorrow: write CPAR before speaking.",len:"Tomorrow: 90-second timer. Stop when it rings."};
  $('pri-label').textContent=T[S.lang].priority;$('pri-text').textContent=top&&top[1]>0?(pm[top[0]]||'Same questions tomorrow.'):'Same questions tomorrow.';
  $('new-btn').textContent=T[S.lang].newsess;$('drill-btn').textContent=T[S.lang].drillw;
  showDrillPhase('ph-summary');
}

function drillWeakest(){
  const top=Object.entries(S.wk).sort((a,b)=>b[1]-a[1])[0];
  const dq={own:"Tell me about a decision you personally made. Numbers.",met:"Walk me through a product improvement. Before and after numbers.",hed:"How do you prioritize a backlog? One direct sentence.",str:"Tell me about yourself in 4 sentences: who, what you built, result, why you're here.",len:"Your biggest PM achievement? Under 60 seconds."};
  if(top&&top[1]>0&&dq[top[0]]){S.questions=[dq[top[0]]];S.total=1;S.cq=0;S.answers=[];S.scores=[];S.selfR=[];S.sessStart=Date.now();S.wk={own:0,met:0,hed:0,str:0,len:0};showQ();}
  else restartSession();
}
function restartSession(){resetSessionState();updateStats();showDrillPhase('ph-setup');}

/* ══════════════════════════════════════════
   COMPANY PREP (Tier 3)
══════════════════════════════════════════ */
async function generatePrep(){
  const key=localStorage.getItem('mb_dk');
  if(!key){openModal('modal-keys');return;}
  const company=$('prep-company').value.trim();
  const role=$('prep-role').value.trim();
  const jd=$('prep-jd').value.trim();
  if(!company||!jd){alert('Add company name and job description.');return;}

  $('prep-input').style.display='none';
  $('prep-loading').classList.add('active');
  $('prep-output').classList.remove('active');

  const prompt=`You are generating a company-specific interview prep kit for Manu Becerra Perez, a Berlin PM.

COMPANY: ${company}
ROLE: ${role||'Product Manager / Product Owner'}
JD: ${jd}

MANU'S PROFILE:
- Lengoo (2020-2024): Led Flow end-to-end on AI translation platform. CSAT +15%, adoption +20%, translation time -50%, engagement +30%.
- Cognigy (2024-present): Product Support Engineer. 200+ tickets/month. Self-initiated Signal (Claude API). Resolution time -30%.
- Aneekaa Studio (2015-2024): Co-founder. 20+ clients (Adidas, Zalando, Blinkist). 100% on-time.
- PSPO I, PSM I. IU Akademie PM Weiterbildung Mar-Jul 2026.

Generate a prep kit in this EXACT JSON format:
{
  "company": "${company}",
  "role": "${role||'Product Manager'}",
  "tagline": "one line describing the company product in plain English",
  "badges": [{"text":"badge label","type":"y|g|r|b"}],
  "product_cards": [
    {"tag":"What it is","title":"short title","desc":"2 sentences"},
    {"tag":"Core problem solved","title":"short title","desc":"2 sentences"},
    {"tag":"Why now","title":"short title","desc":"2 sentences"},
    {"tag":"Key terms to know","title":"term1 · term2 · term3","desc":"brief definitions"}
  ],
  "questions": [
    {
      "label":"Q1 — Opening",
      "q":"Tell me about yourself.",
      "warn":["specific warning 1","specific warning 2"],
      "tip":"specific coaching tip for this question at this company",
      "answer":"Manu's full model answer in first person, using his real metrics, tailored to this company. 4-6 sentences. Must include CSAT +15%, adoption +20%, or resolution -30%.",
      "insight":"one line coaching note"
    }
  ]
}

Generate 8 questions total covering: opening, motivation (why this company specifically), product experience, what you're looking for, logistics, title gap (Support Engineer to PM), technical fit, salary (70-80K range for Berlin).

Return ONLY valid JSON. No markdown, no explanation.`;

  try{
    const res=await claude([{role:'user',content:prompt}],2000,key);
    const data=extractJson(res);
    renderPrepOutput(data);
  }catch(e){
    console.error('Prep generation failed:',e);
    alert('Generation failed: '+e.message+'\n\nCheck your Anthropic API key in Settings.');
    $('prep-input').style.display='block';$('prep-loading').classList.remove('active');
  }
}

function renderPrepOutput(data){
  $('prep-loading').classList.remove('active');
  prepSave(data);
  const out=$('prep-output');

  // Build badges HTML
  const badgesHtml=(data.badges||[]).map(b=>`<span class="badge badge-${b.type||'b'}">${b.text}</span>`).join('');

  // Build product grid
  const gridHtml=(data.product_cards||[]).map(c=>`
    <div class="pm-card">
      <div class="pm-card-tag">${c.tag}</div>
      <div class="pm-card-title">${c.title}</div>
      <div class="pm-card-desc">${c.desc}</div>
    </div>`).join('');

  // Build Q&A accordion
  const qs=data.questions||[];
  const qHtml=qs.map((item,i)=>{
    const tagsHtml=(item.warn||[]).map(w=>`<span class="tag-warn">${w}</span>`).join('')+`<span class="tag-tip">${item.tip||''}</span>`;
    return `<div class="q-block" id="prep-q-${i}">
      <div class="q-head" onclick="prepToggle(${i})">
        <div style="flex:1">
          <div class="q-num">${item.label||'Q'+(i+1)}</div>
          <div class="q-label">"${item.q}"</div>
        </div>
        <span class="q-arrow" id="prep-arr-${i}">▾</span>
      </div>
      <div class="q-body" id="prep-body-${i}">
        <div class="q-tags">${tagsHtml}</div>
        <div class="ans-label">Your answer</div>
        <div class="ans-box">${(item.answer||'').replace(/</g,'&lt;')}</div>
        <div class="insight">${item.insight||''}</div>
        <button class="practice-btn" onclick="openOverlay(${i},${JSON.stringify(item.q).replace(/'/g,"\\'")},'${(item.answer||'').replace(/'/g,"\\'")}')">&#9654; Practice this</button>
      </div>
    </div>`;
  }).join('');

  out.innerHTML=`
    <div class="pm-header">
      <div class="pm-logo">${(data.company||'CO').substring(0,2).toUpperCase()}</div>
      <div class="pm-info">
        <h1>${data.role||'Product Manager'} — ${data.company||'Company'}</h1>
        <p>${data.tagline||''}</p>
        <div class="badges">${badgesHtml}</div>
      </div>
    </div>

    <div class="pm-alert">
      <div class="pm-alert-title">&#9888; Your 4 confirmed failure patterns — watch for these</div>
      <div class="pm-alert-grid">
        <div class="pm-alert-item"><span class="pm-alert-num">1</span>Say "I decided" not "we explored"</div>
        <div class="pm-alert-item"><span class="pm-alert-num">2</span>Lead with the headline, context second</div>
        <div class="pm-alert-item"><span class="pm-alert-num">3</span>End every story with a metric</div>
        <div class="pm-alert-item"><span class="pm-alert-num">4</span>Stop at 90 seconds, no loops</div>
      </div>
    </div>

    <div class="pm-section">The product in 60 seconds</div>
    <div class="pm-grid">${gridHtml}</div>

    <div class="pm-section">Questions to prepare for</div>
    ${qHtml}

    <div class="pm-section" style="margin-top:20px">Your metrics — have these cold</div>
    <table class="mt">
      <tr><th>Company</th><th>Metric</th><th>Context</th></tr>
      <tr><td>Lengoo</td><td>+20%</td><td>Adoption after Flow launch</td></tr>
      <tr><td>Lengoo</td><td>+15%</td><td>CSAT from discovery-driven changes</td></tr>
      <tr><td>Lengoo</td><td>50% faster</td><td>Translation time reduction</td></tr>
      <tr><td>Cognigy</td><td>-30%</td><td>Resolution time via Signal</td></tr>
      <tr><td>Cognigy</td><td>200+/mo</td><td>Tickets processed by Signal</td></tr>
      <tr><td>Aneekaa</td><td>100%</td><td>On-time delivery, 20+ clients</td></tr>
    </table>

    <div style="text-align:center;font-size:12px;color:var(--ink-ghost);padding:1.5rem 0;border-top:1px solid var(--border);margin-top:1rem">
      hi.manubecerra.com &nbsp;·&nbsp; contact@manubecerra.com &nbsp;·&nbsp; Berlin<br><br>
      Answer structure: Context (2 sentences) → Problem → <strong style="color:var(--ink)">I decided</strong> → Result + metric. Stop.
    </div>

    <div class="btn-row" style="margin-top:1.5rem">
      <button class="btn" onclick="prepReset()">&#8592; All prep kits</button>
    </div>`;

  out.classList.add('active');
  window.scrollTo({top:0,behavior:'smooth'});
}

function prepToggle(i){
  const body=$('prep-body-'+i),arr=$('prep-arr-'+i);
  const isOpen=body.classList.contains('open');
  document.querySelectorAll('.q-body').forEach(b=>b.classList.remove('open'));
  document.querySelectorAll('.q-arrow').forEach(a=>a.classList.remove('open'));
  if(!isOpen){body.classList.add('open');arr.classList.add('open');body.scrollIntoView({behavior:'smooth',block:'nearest'});}
}

function prepReset(){
  $('prep-output').classList.remove('active');$('prep-output').innerHTML='';
  $('prep-input').style.display='block';
  renderSavedPreps();
}

/* ── SAVED PREPS ── */
function prepSave(data){
  const preps=prepGetAll();
  const key=data.company.toLowerCase().replace(/\s+/g,'-');
  preps[key]={data,saved:new Date().toISOString()};
  localStorage.setItem('mb_preps',JSON.stringify(preps));
  renderSavedPreps();
}
function prepGetAll(){try{return JSON.parse(localStorage.getItem('mb_preps')||'{}')}catch{return{}}}
function prepDelete(key){const preps=prepGetAll();delete preps[key];localStorage.setItem('mb_preps',JSON.stringify(preps));renderSavedPreps();}

function renderSavedPreps(){
  const preps=prepGetAll();
  const keys=Object.keys(preps);
  const container=$('prep-saved-list');
  const items=$('prep-saved-items');
  if(!keys.length){container.style.display='none';return;}
  container.style.display='block';
  items.innerHTML=keys.map(k=>{
    const p=preps[k];
    const d=new Date(p.saved);
    const dateStr=d.toLocaleDateString('en-GB',{day:'numeric',month:'short'});
    return `<div class="saved-prep-item">
      <div>
        <div class="saved-prep-name" onclick="prepLoad('${k}')">${p.data.company} — ${p.data.role||'PM'}</div>
        <div class="saved-prep-meta">Saved ${dateStr}</div>
      </div>
      <div class="saved-prep-actions">
        <button class="btn" style="padding:5px 12px;font-size:12px" onclick="prepLoad('${k}')">Open &rarr;</button>
        <button class="saved-prep-del" onclick="prepDelete('${k}')">Delete</button>
      </div>
    </div>`;
  }).join('');
}

function prepLoad(key){
  const preps=prepGetAll();
  if(!preps[key])return;
  $('prep-input').style.display='none';
  $('prep-saved-list').style.display='none';
  renderPrepOutput(preps[key].data);
}

/* ══════════════════════════════════════════
   PRACTICE OVERLAY
══════════════════════════════════════════ */
function openOverlay(idx,question,modelAnswer){
  OV.qText=question;OV.modelAnswer=modelAnswer;OV.transcript='';OV.timerSec=0;OV.recording=false;
  $('ov-q-label').textContent='Practice';$('ov-q-text').textContent=question;
  $('ov-txlive').textContent='Your words will appear here...';$('ov-txlive').classList.remove('has-text');
  $('ov-timer').textContent='0:00';$('ov-timer').className='timer-display';
  $('ov-timer-bar').style.width='100%';$('ov-rec-dot').classList.remove('on');
  $('ov-rec-status').textContent='Ready — tap to record';
  $('ov-rec-btn').textContent='Start recording';$('ov-submit-btn').style.display='none';
  $('ov-feedback').style.display='none';$('ov-feedback').innerHTML='';
  $('ov-after').style.display='none';
  $('practice-overlay').classList.add('open');
  window.scrollTo({top:0,behavior:'smooth'});
}

function ovTryAgain(){
  OV.transcript='';OV.finalTranscript='';OV.timerSec=0;OV.recording=false;
  $('ov-txlive').textContent='Your words will appear here...';$('ov-txlive').classList.remove('has-text');
  $('ov-timer').textContent='0:00';$('ov-timer').className='timer-display';
  $('ov-timer-bar').style.width='100%';$('ov-rec-dot').classList.remove('on');
  $('ov-rec-status').textContent='Ready — tap to record';$('ov-rec-btn').textContent='Start recording';
  $('ov-submit-btn').style.display='none';
  $('ov-feedback').style.display='none';$('ov-feedback').innerHTML='';
  $('ov-after').style.display='none';
  window.scrollTo({top:0,behavior:'smooth'});
}

function closeOverlay(){
  ovStopRec();ovStopTimer();$('practice-overlay').classList.remove('open');
}

function ovHandleRec(){
  if(!OV.recording)ovStartRec();else ovStopRec();
}

function ovStartRec(){
  const SR=window.SpeechRecognition||window.webkitSpeechRecognition;
  if(!SR){alert('Voice not supported. Note your answer manually.');return;}
  OV.recog=new SR();OV.recog.continuous=true;OV.recog.interimResults=true;OV.recog.lang='en-US';
  OV.transcript='';OV.finalTranscript='';OV.recording=true;
  $('ov-rec-dot').classList.add('on');$('ov-rec-status').textContent='Recording — speak now';
  $('ov-rec-btn').textContent='Stop';$('ov-voice-area').classList.add('live');
  $('ov-txlive').textContent='Speak now...';$('ov-txlive').classList.add('has-text');
  ovStartTimer();
  OV.recog.onresult=e=>{
    let interim='';
    for(let i=e.resultIndex;i<e.results.length;i++){
      if(e.results[i].isFinal)OV.finalTranscript+=e.results[i][0].transcript+' ';
      else interim+=e.results[i][0].transcript;
    }
    const txt=(OV.finalTranscript+interim).trim();
    $('ov-txlive').textContent=txt||'...';$('ov-txlive').classList.toggle('has-text',txt.length>0);
  };
  OV.recog.onerror=e=>{console.warn('OV SR:',e.error);};
  OV.recog.onend=()=>{
    if(OV.recording){OV.recording=false;ovStopTimer();$('ov-rec-dot').classList.remove('on');$('ov-voice-area').classList.remove('live');OV.transcript=OV.finalTranscript.trim();if(OV.transcript)$('ov-submit-btn').style.display='inline-flex';$('ov-rec-btn').textContent='Re-record';$('ov-rec-status').textContent='Done — review or submit';}
  };
  OV.recog.start();
}

function ovStopRec(){
  OV.recording=false;
  if(OV.recog){try{OV.recog.stop();}catch(e){}OV.recog=null;}
  ovStopTimer();$('ov-rec-dot').classList.remove('on');$('ov-voice-area').classList.remove('live');
}

function ovStartTimer(){
  OV.timerSec=0;const sa=performance.now();
  OV.timerInt=setInterval(()=>{
    const e=Math.floor((performance.now()-sa)/1000);OV.timerSec=e;
    const mm=Math.floor(e/60),ss=String(e%60).padStart(2,'0');
    const d=$('ov-timer'),b=$('ov-timer-bar');
    if(d){d.textContent=`${mm}:${ss}`;d.className='timer-display'+(e>90?' danger':e>60?' warn':'');}
    if(b){b.style.width=Math.max(0,100-(e/120)*100)+'%';b.style.background=e>90?'var(--danger)':e>60?'var(--warn)':'var(--sage)';}
    if(e>=120)ovStopRec();
  },1000);
}
function ovStopTimer(){if(OV.timerInt){clearInterval(OV.timerInt);OV.timerInt=null;}}

async function ovSubmit(){
  const ans=OV.transcript.trim();if(ans.length<20){alert('Answer too short.');return;}
  $('ov-submit-btn').style.display='none';
  $('ov-feedback').style.display='block';
  $('ov-feedback').innerHTML='<div class="dots"><span></span><span></span><span></span><span class="dots-msg">Scoring with Claude...</span></div>';
  const key=localStorage.getItem('mb_dk');
  if(!key){
    $('ov-feedback').innerHTML=`<div class="card"><span class="slabel">Local check</span><div class="feedback-body">${buildLocalFeedbackHtml(OV.qText,ans,OV.timerSec)}</div></div>`;
    $('ov-after').style.display='flex';
    return;
  }
  try{
    const wc=ans.split(/\s+/).length;
    const prompt=`Question: "${OV.qText}"\n\nManu's answer (${OV.timerSec}s, ~${wc} words):\n"${ans}"\n\nScore harshly. Return ONLY JSON:\n{"overall":1,"structure":1,"ownership":1,"metrics":1,"weaknesses_triggered":[],"what_worked":"","critical_fix":"","rewrite_sentence":"","we_count":0,"metric_cited":false,"word_count":${wc}}`;
    const res=await claude([{role:'user',content:prompt}],300,key);
    const d=extractJson(res);
    const scoreColor=d.overall>=7?'var(--sage)':d.overall>=5?'var(--warn)':'var(--danger)';
    let html=`<div style="font-family:'Shippori Mincho',serif;font-size:28px;font-weight:600;color:${scoreColor};margin-bottom:12px">${d.overall}<span style="font-size:14px;color:var(--ink-ghost)">/10</span></div>`;
    if(d.what_worked)html+=`<strong>What worked:</strong>\n${d.what_worked}\n\n`;
    if(d.critical_fix)html+=`<strong>Fix this first:</strong>\n${d.critical_fix}\n\n`;
    if(d.rewrite_sentence)html+=`<strong>Better opening:</strong>\n"${d.rewrite_sentence}"\n`;
    $('ov-feedback').innerHTML=`<div class="card"><div class="feedback-body fu">${html.trim()}</div></div>`;
    $('ov-after').style.display='flex';
  }catch(e){
    $('ov-feedback').innerHTML=`<div class="card"><span class="slabel">Local check</span><div class="feedback-body">${buildLocalFeedbackHtml(OV.qText,ans,OV.timerSec)}</div></div>`;
    $('ov-after').style.display='flex';
  }
}

function buildLocalFeedbackHtml(q,ans,t){
  const s=localScore(q,ans,t);let html='';
  if(s.we_count>1)html+=`<strong>Said "we" ${s.we_count} times.</strong> Use "I decided".\n\n`;
  if(!s.metric_cited)html+=`<strong>No metric.</strong> Add: Cognigy -30%, Lengoo +15% CSAT.\n\n`;
  if(s.word_count>180)html+=`<strong>${s.word_count} words.</strong> Too long. Target 60-90s.\n\n`;
  if(!html)html='Clean local check — no major issues detected.';
  return html;
}

/* ══════════════════════════════════════════
   SCORING UTILS
══════════════════════════════════════════ */
function localScore(question,answer,timeSec){
  const q=(question||'').toLowerCase();const a=(answer||'').toLowerCase();
  const words=(answer||'').trim().split(/\s+/).filter(Boolean);const wc=words.length;
  const we=(a.match(/\bwe\b/gi)||[]).length;
  const metricEvidence=extractMetricEvidence(a);const metricHit=metricEvidence.length>0;
  const hedgeCount=(a.match(/\b(i think|maybe|kind of|sort of|probably|i guess|perhaps|basically|somewhat|kinda)\b/gi)||[]).length;
  const hasDirectStart=/^(i|ich)\b/.test(a.trim());
  const directQ=q.includes('yourself')||q.includes('tell me about');
  const structureScore=hasDirectStart||!directQ?7:5;
  const metricScore=metricHit?7:3;
  const ownershipScore=Math.max(1,10-Math.max(0,we-2)*2);
  const lengthPenalty=wc>200?2:wc>150?1:0;
  const hedgePenalty=Math.min(3,Math.floor(hedgeCount/2));
  const overall=Math.max(1,Math.min(10,Math.round((structureScore+metricScore+ownershipScore)/3)-lengthPenalty-hedgePenalty));
  const weaknesses=[];
  if(we>2)weaknesses.push('ownership gap');if(!metricHit)weaknesses.push('metric blindness');
  if(!hasDirectStart&&directQ)weaknesses.push('first-sentence delay');if(hedgeCount>0)weaknesses.push('hedge spiral');if(wc>180)weaknesses.push('intro bloat');
  const lead=(answer||'').trim().split(/[.!?]\s+/)[0].trim();
  return{
    overall,structure:Math.max(1,Math.min(10,structureScore-lengthPenalty)),ownership:ownershipScore,metrics:metricScore,
    weaknesses_triggered:weaknesses,what_worked:metricHit?`Metric cited: ${metricEvidence.slice(0,2).join(', ')}.`:'',
    critical_fix:!hasDirectStart&&directQ?'Open with the answer immediately.':(we>2?'Replace "we" with "I decided".':`Tighten to ${Math.round(wc*0.7)} words.`),
    rewrite_sentence:hasDirectStart&&lead?lead:`I'm a Product Manager in Berlin who led Flow end-to-end and improved adoption by 20%.`,
    cut_this:'',say_this:'',strongest_line:lead||'',filler_words_found:(answer||'').match(/\b(um|uh|like|you know)\b/gi)||[],
    metric_evidence:metricEvidence,we_count:we,metric_cited:metricHit,word_count:wc
  };
}

function extractMetricEvidence(text){
  const raw=String(text||'');
  const cleaned=raw.replace(/\bminus\s+/gi,'-').replace(/\bplus\s+/gi,'+').replace(/\bpercent\b/gi,'%');
  const digitHits=cleaned.match(/\b[-+]?\d+(?:[.,]\d+)?\s*(?:%|x)?\b/gi)||[];
  const metricWords=cleaned.match(/\b(csat|adoption|resolution|conversion|retention|engagement|tickets?|growth|increase|decrease|improvement|saved|reduced)\b/gi)||[];
  return[...new Set([...digitHits,...metricWords].map(h=>h.trim()).filter(Boolean))];
}

function normalizeScore(d,question,answer,timeSec){
  const fb=localScore(question,answer,timeSec);
  const out={...fb};
  ['overall','structure','ownership','metrics'].forEach(k=>{
    const n=toNum(d?.[k]);out[k]=Number.isFinite(n)?Math.max(1,Math.min(10,Math.round(n))):fb[k];
  });
  out.metric_cited=typeof d?.metric_cited==='boolean'?(d.metric_cited||fb.metric_cited):fb.metric_cited;
  out.we_count=Number.isFinite(toNum(d?.we_count))?Math.max(0,Math.round(toNum(d.we_count))):fb.we_count;
  out.word_count=Number.isFinite(toNum(d?.word_count))?Math.max(0,Math.round(toNum(d.word_count))):fb.word_count;
  out.weaknesses_triggered=Array.isArray(d?.weaknesses_triggered)&&d.weaknesses_triggered.length?d.weaknesses_triggered:fb.weaknesses_triggered;
  ['what_worked','critical_fix','rewrite_sentence','cut_this','say_this','strongest_line'].forEach(k=>{out[k]=typeof d?.[k]==='string'?d[k].trim():fb[k];});
  out.filler_words_found=Array.isArray(d?.filler_words_found)&&d.filler_words_found.length?d.filler_words_found:fb.filler_words_found;
  return out;
}

function toNum(v){if(typeof v==='number'&&Number.isFinite(v))return v;if(typeof v==='string'){const n=Number(v.trim());if(Number.isFinite(n))return n;}return null;}

function extractJson(text){
  const cleaned=(text||'').replace(/```json|```/g,'').trim();
  if(!cleaned)throw new Error('Empty response');
  try{return JSON.parse(cleaned);}catch{}
  const findBalanced=(o,c)=>{const start=cleaned.indexOf(o);if(start<0)return null;let depth=0,inStr=false,esc=false;for(let i=start;i<cleaned.length;i++){const ch=cleaned[i];if(esc){esc=false;continue;}if(ch==='\\'){esc=true;continue;}if(ch==='"'){inStr=!inStr;continue;}if(inStr)continue;if(ch===o)depth++;if(ch===c){depth--;if(depth===0)return cleaned.slice(start,i+1);}}return null;};
  const obj=findBalanced('{','}');if(obj)return JSON.parse(obj);
  const arr=findBalanced('[',']');if(arr)return JSON.parse(arr);
  throw new Error('No JSON found');
}

/* ══════════════════════════════════════════
   STORAGE + STATS
══════════════════════════════════════════ */
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
  $('st-streak').textContent=streak||'—';$('st-avg').textContent=avg?avg+'/10':'—';$('st-sessions').textContent=h.length||'—';
  const sp=$('streak-pill');
  if(streak>1){if(sp)sp.style.display='flex';$('streak-num').textContent=streak;}else if(sp)sp.style.display='none';
  renderTrend(h);
}
function renderTrend(h){
  const el=$('trend-bars');const last=h.slice(-7);
  if(!last.length){el.innerHTML='<div class="trend-empty">No sessions yet. Start your first drill.</div>';$('trend-best').textContent='';return;}
  const padded=Array(7-last.length).fill(null).concat(last);
  const best=Math.max(...last.map(x=>x.s));$('trend-best').textContent='best: '+best+'/10';
  el.innerHTML=padded.map(x=>{
    if(!x)return`<div class="tb-wrap"><div class="tb e" style="height:4px"></div><div class="td"></div></div>`;
    const ht=Math.round((x.s/10)*42)+4;const c=x.s>=7?'g':x.s>=5?'w':'b';
    const label=x.d.slice(5).replace('-','/');
    return`<div class="tb-wrap"><div class="tb ${c}" style="height:${ht}px"></div><div class="td">${label}</div></div>`;
  }).join('');
}
function clearHistory(){if(confirm('Reset all history?')){localStorage.removeItem('mb_dh');updateStats();}}

/* ══════════════════════════════════════════
   CLAUDE API
══════════════════════════════════════════ */
async function claude(messages,maxTokens=400,keyOverride){
  const key=keyOverride||localStorage.getItem('mb_dk');
  if(!key)throw new Error('No Anthropic API key set');
  const r=await fetch('https://api.anthropic.com/v1/messages',{
    method:'POST',
    headers:{'Content-Type':'application/json','x-api-key':key,'anthropic-version':'2023-06-01','anthropic-dangerous-direct-browser-access':'true'},
    body:JSON.stringify({model:'claude-haiku-4-5-20251001',max_tokens:maxTokens,system:ANTHROPIC_SYSTEM,messages})
  });
  const text=await r.text();
  if(r.ok){
    let json;try{json=JSON.parse(text);}catch(e){throw new Error(`Bad API response: ${text.slice(0,200)}`);}
    const out=json?.content?.[0]?.text;
    if(typeof out!=='string')throw new Error('Missing content in API response');
    if(json.usage)console.info('Anthropic usage',json.usage);
    return out;
  }
  throw new Error(`API ${r.status}: ${text.slice(0,200)}`);
}
</script>
<script src='https://widget.superchat.de/snippet.js?applicationKey=WCOzVqDdxywB4QPLr7ag28MkpX' referrerpolicy='no-referrer-when-downgrade'></script>
</body>
</html>

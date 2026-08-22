<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Browser tools for making Bravo Fleet seals, headers, banners, plaques, patches and mission posters — no Photoshop required.">
<link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2032%2032%22%3E%3Crect%20width%3D%2232%22%20height%3D%2232%22%20rx%3D%225%22%20fill%3D%22%230d1420%22%2F%3E%3Ccircle%20cx%3D%2216%22%20cy%3D%2216%22%20r%3D%229%22%20fill%3D%22none%22%20stroke%3D%22%23d3a92c%22%20stroke-width%3D%223%22%2F%3E%3Ccircle%20cx%3D%2216%22%20cy%3D%2216%22%20r%3D%223.5%22%20fill%3D%22%232864a8%22%2F%3E%3C%2Fsvg%3E">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Bravo Fleet Graphics Tools">
<meta property="og:title" content="Bravo Fleet Graphics Tools">
<meta property="og:description" content="Browser tools for making Bravo Fleet seals, headers, banners, plaques, patches and mission posters — no Photoshop required.">
<meta property="og:url" content="https://crimsontacit.github.io/BF-Seal-Builder/">
<meta name="twitter:card" content="summary">
<title>Bravo Fleet Graphics Tools</title>
<style>
  :root{
    --bg:#070b12;
    --panel:#0d1420;
    --panel2:#111a29;
    --line:#1f2a3a;
    --line-bright:#2c3b52;
    --ink:#c7d3e0;
    --ink-dim:#6d7d92;
    --bolt-gold:#d3a92c;
    --bf-blue:#2864a8;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%}
  body{
    background:
      radial-gradient(1200px 700px at 75% -10%, rgba(59,152,168,.07), transparent 60%),
      radial-gradient(900px 600px at -10% 110%, rgba(216,166,58,.05), transparent 60%),
      var(--bg);
    color:var(--ink);
    font:13px/1.5 ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:34px;padding:24px;
  }
  h1{
    font-weight:normal;font-size:20px;letter-spacing:.45em;text-transform:uppercase;
    color:var(--bolt-gold);text-align:center;padding-left:.45em;
  }
  .sub{
    color:var(--ink-dim);letter-spacing:.18em;font-size:11px;text-transform:uppercase;
    text-align:center;margin-top:8px;
  }
  .cards{display:flex;gap:22px;flex-wrap:wrap;justify-content:center;max-width:980px}
  a.card{
    display:flex;flex-direction:column;align-items:center;gap:16px;
    width:290px;padding:30px 26px 26px;text-decoration:none;color:var(--ink);
    background:var(--panel);border:1px solid var(--line-bright);border-radius:4px;
    transition:border-color .15s ease, transform .15s ease;
  }
  a.card:hover{border-color:var(--bolt-gold);transform:translateY(-3px)}
  a.card h2{
    font-weight:normal;font-size:14px;letter-spacing:.3em;text-transform:uppercase;
    color:var(--ink);padding-left:.3em;
  }
  a.card:hover h2{color:var(--bolt-gold)}
  a.card p{color:var(--ink-dim);font-size:12px;line-height:1.6;text-align:center}
  a.card .go{
    margin-top:4px;font-size:11px;letter-spacing:.12em;text-transform:uppercase;
    color:var(--bf-blue);
  }
  a.card:hover .go{color:var(--bolt-gold)}
  /* seal mark: concentric rings */
  .mark-seal{
    width:72px;height:72px;border-radius:50%;flex:none;
    border:6px solid var(--bf-blue);
    box-shadow:inset 0 0 0 3px var(--bg), inset 0 0 0 5px var(--bolt-gold),
      inset 0 0 0 14px var(--bf-blue), inset 0 0 0 17px var(--bg),
      inset 0 0 0 19px var(--bolt-gold), inset 0 0 0 40px #143356;
  }
  /* header mark: metallic wordmark bars */
  .mark-hdr{
    width:72px;height:72px;flex:none;display:flex;flex-direction:column;
    align-items:center;justify-content:center;gap:7px;
  }
  .mark-hdr i{display:block;border-radius:1px}
  .mark-hdr i:nth-child(1){
    width:44px;height:12px;
    background:linear-gradient(180deg,#fdf6bd,#c9992e 48%,#8a6410 52%,#e8c14a);
  }
  .mark-hdr i:nth-child(2){
    width:58px;height:4px;border-radius:2px;
    background:linear-gradient(180deg,#fdf6bd,#c9992e 48%,#8a6410 52%,#e8c14a);
  }
  .mark-hdr i:nth-child(3){
    width:58px;height:22px;
    background:linear-gradient(180deg,#ffffff,#a6b0bc 48%,#79828e 52%,#d4dae1);
  }
  /* banner mark: gold delta beside a framed panel */
  .mark-bnr{
    width:72px;height:72px;flex:none;display:flex;align-items:center;gap:5px;
  }
  .mark-bnr i{
    display:block;width:20px;height:52px;flex:none;
    background:linear-gradient(160deg,#fdf6bd,#c9992e 45%,#8a6410 55%,#e8c14a);
    clip-path:polygon(50% 0,100% 100%,50% 74%,0 100%);
  }
  .mark-bnr b{
    display:block;flex:1;height:38px;border-radius:6px;
    border:3px solid #c9992e;background:#0f1a30;
  }
  /* plaque mark: bordered slate plate */
  .mark-plq{
    width:72px;height:72px;flex:none;display:flex;align-items:center;justify-content:center;
    border-radius:7px;background:linear-gradient(150deg,#55636f,#333d47);
    box-shadow:0 2px 5px rgba(0,0,0,.5);
  }
  .mark-plq i{
    display:block;width:48px;height:44px;border-radius:3px;border:3px solid #c9a541;
  }
  /* patch mark: nested triangles, the border rings scaled off the incenter
     (2/3 down) the way the tool insets them — exaggerated so the thin rails
     still read at 72px */
  .mark-pat{width:72px;height:62px;position:relative;flex:none}
  .mark-pat i{
    position:absolute;inset:0;display:block;
    clip-path:polygon(50% 0,100% 100%,0 100%);
    transform-origin:50% 66.7%;
  }
  .mark-pat i:nth-child(1){background:#2864a8}
  .mark-pat i:nth-child(2){background:#d3a92c;transform:scale(.88)}
  .mark-pat i:nth-child(3){background:#2864a8;transform:scale(.62)}
  .mark-pat i:nth-child(4){background:#13223a;transform:scale(.55)}
  /* mission mark: a framed 3:4 sheet with its gold title block at the foot */
  .mark-mis{
    width:54px;height:72px;flex:none;border:2px solid #c9992e;
    background:
      radial-gradient(1.2px 1.2px at 12px 13px, #cfe0f4 50%, transparent 50%),
      radial-gradient(1.2px 1.2px at 35px 9px, #cfe0f4 50%, transparent 50%),
      radial-gradient(1.2px 1.2px at 22px 25px, #cfe0f4 50%, transparent 50%),
      radial-gradient(1.2px 1.2px at 41px 30px, #cfe0f4 50%, transparent 50%),
      linear-gradient(180deg,#16283f 0%,#0e1a2c 58%,#070b12 100%);
    display:flex;flex-direction:column;justify-content:flex-end;align-items:center;
    gap:4px;padding:0 6px 7px;
  }
  .mark-mis i{
    display:block;width:100%;height:9px;
    background:linear-gradient(180deg,#fdf6bd,#c9992e 48%,#8a6410 52%,#e8c14a);
  }
  .mark-mis i:last-child{width:62%;height:4px}
  footer{
    color:var(--ink-dim);font-size:10px;letter-spacing:.08em;line-height:1.8;
    text-transform:uppercase;text-align:center;
  }
  footer b{color:var(--ink);font-weight:normal}
  footer a{color:var(--bf-blue);text-decoration:none;border-bottom:1px solid transparent}
  footer a:hover{color:var(--ink);border-bottom-color:var(--bf-blue)}

  /* shown only with JavaScript off */
  .noscript{
    margin:0;padding:16px 20px;background:#111a29;
    border-bottom:1px solid #2c3b52;color:#c7d3e0;
    font-size:13px;line-height:1.6;text-align:center;
  }
</style>
</head>
<body>
<noscript><div class="noscript">The tool links below work without JavaScript, but the tools themselves need it switched on.</div></noscript>

<div>
  <h1>Bravo Fleet Graphics Tools</h1>
  <div class="sub">In-browser replacements for the Photoshop workflow</div>
</div>

<div class="cards">
  <a class="card" href="/seal">
    <div class="mark-seal"></div>
    <h2>Seal Builder</h2>
    <p>Round seals for vessels, stations, and organizations — curved ring text, task-force emblems, starfields, custom art.</p>
    <div class="go">Open →</div>
  </a>
  <a class="card" href="/header">
    <div class="mark-hdr"><i></i><i></i><i></i></div>
    <h2>Header Builder</h2>
    <p>Metallic wordmark headers for BFMS command pages — gold and brushed-steel type, divider rules, emblems, transparent PNG output.</p>
    <div class="go">Open →</div>
  </a>
  <a class="card" href="/banner">
    <div class="mark-bnr"><i></i><b></b></div>
    <h2>Banner Builder</h2>
    <p>Ship and station banners — the fixed gold frame with your art, name, registry and class, and a task-force delta in place of the BF one.</p>
    <div class="go">Open →</div>
  </a>
  <a class="card" href="/plaque">
    <div class="mark-plq"><i></i></div>
    <h2>Plaque Builder</h2>
    <p>Dedication plaques from the original 2399 artwork — twelve backings, seven trims, editable rosters, raised or engraved lettering.</p>
    <div class="go">Open →</div>
  </a>
  <a class="card" href="/patch">
    <div class="mark-pat"><i></i><i></i><i></i><i></i></div>
    <h2>Patch Builder</h2>
    <p>Triangular patches — the wide-bordered command patch with type along its edges, or a rounded development-project patch with the title inside.</p>
    <div class="go">Open →</div>
  </a>
  <a class="card" href="/mission">
    <div class="mark-mis"><i></i><i></i></div>
    <h2>Mission Poster Builder</h2>
    <p>Mission posters and cover art — your image or a built-in starfield under thirteen fonts, raised, foil or embossed lettering, outlines and glows, frames, and the seal palette.</p>
    <div class="go">Open →</div>
  </a>
</div>

<footer>
  © Bravo Fleet 1997–<span id="copyYear"></span><br>
  Seal, patch, plaque and poster design by <b>CrimsonTacit</b> · original Columbia header by <b>JustSlide</b><br>
  Bravo Fleet logo by <b>Kevin Steeper</b> · ship banner by <b>Emily Wolf</b> and <b>Kevin Steeper</b><br>
  <a href="https://wiki.bravofleet.com/index.php/Credits#Graphics" target="_blank" rel="noopener">Full graphics credits here</a>
</footer>

<script>
document.getElementById("copyYear").textContent = new Date().getFullYear();
</script>
</body>
</html>

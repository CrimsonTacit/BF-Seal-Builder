<?php require __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Build metallic wordmark headers for BFMS command pages, and export them as transparent PNGs.">
<link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2032%2032%22%3E%3Crect%20width%3D%2232%22%20height%3D%2232%22%20rx%3D%225%22%20fill%3D%22%230d1420%22%2F%3E%3Crect%20x%3D%225%22%20y%3D%2212%22%20width%3D%2222%22%20height%3D%224%22%20fill%3D%22%23d3a92c%22%2F%3E%3Crect%20x%3D%225%22%20y%3D%2219%22%20width%3D%2214%22%20height%3D%223%22%20fill%3D%22%232864a8%22%2F%3E%3C%2Fsvg%3E">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Bravo Fleet Graphics Tools">
<meta property="og:title" content="Header Builder — Bravo Fleet Graphics Tools">
<meta property="og:description" content="Build metallic wordmark headers for BFMS command pages, and export them as transparent PNGs.">
<meta property="og:url" content="https://crimsontacit.github.io/BF-Seal-Builder/header-tool.html">
<meta name="twitter:card" content="summary">
<title>Header Builder</title>
<link rel="stylesheet" href="shared/chrome.css?v=d62425d5">
<style>
  header .mark{
    width:26px;height:14px;border-radius:2px;flex:none;
    background:linear-gradient(180deg,#fdf6b8,#c9992e 48%,#8a6410 52%,#e8c14a);
    box-shadow:0 0 0 2px var(--bg), 0 0 0 3px var(--line-bright);
  }
  .presets{display:flex;gap:6px;flex-wrap:wrap}
  .preset{
    display:flex;align-items:center;gap:6px;
    background:var(--panel2);border:1px solid var(--line-bright);border-radius:2px;
    padding:5px 9px;cursor:pointer;font-size:11px;letter-spacing:.06em;color:var(--ink);
  }
  .preset:hover{border-color:var(--bolt-gold)}
  .preset .sw{display:flex;gap:2px}
  .preset .sw i{width:14px;height:9px;display:block;border-radius:1px}
  .matrow .hide{display:none}

  /* ---------- stage ---------- */
  #stage{
    flex:1;display:flex;align-items:center;justify-content:center;position:relative;
    min-width:0;background-color:#10151d;
  }
  #hdrwrap{
    position:relative;z-index:1;width:min(92%, 1200px);
  }
  #hdrwrap svg{width:100%;height:auto;display:block}
</style>
</head>
<body>
<noscript><div class="noscript"><b>Header Builder</b> needs JavaScript switched on — it draws and exports your artwork entirely in the browser, with nothing sent to a server.</div></noscript>

<header>
  <div class="mark"></div>
  <h1>Header Builder</h1>
  <div class="sub">Bravo Fleet · BFMS Command Headers</div>
  <div class="spacer"></div>
  <a class="navlink" href="/">⌂</a>
  <a class="navlink" href="/seal">Seal →</a>
  <a class="navlink" href="/banner">Banner →</a>
  <a class="navlink" href="/plaque">Plaque →</a>
  <a class="navlink" href="/patch">Patch →</a>
  <a class="navlink" href="/mission">Poster →</a>
</header>

<main>
  <div id="panel">

    <details class="acc" open>
      <summary>Style Presets</summary>
      <div class="acc-body">
        <div class="presets" id="stylePresets"></div>
        <div class="hint">Presets set the materials for every element; text, fonts and layout are yours.</div>
      </div>
    </details>

    <details class="acc" open id="sec_top_wrap"><summary>Top Line</summary><div class="acc-body" id="sec_top"></div></details>
    <details class="acc" open id="sec_main_wrap"><summary>Main Line</summary><div class="acc-body" id="sec_main"></div></details>
    <details class="acc" id="sec_sub_wrap"><summary>Sub Line</summary><div class="acc-body" id="sec_sub"></div></details>

    <details class="acc" open>
      <summary>Divider</summary>
      <div class="acc-body">
        <div class="row"><label>Show divider</label><input type="checkbox" data-k="ruleOn"></div>
        <div class="row"><label>Thickness</label><input type="range" min="2" max="26" step="1" data-k="ruleH"><span class="val" data-val="ruleH"></span></div>
        <div class="row matrow"><label>Material</label><select data-k="ruleMat"></select><input type="color" data-k="c_rule" data-showif="ruleMat:custom"></div>
        <div class="row"><label>Finish</label><select data-k="ruleFinish"></select></div>
      </div>
    </details>

    <details class="acc">
      <summary>Emblem</summary>
      <div class="acc-body">
        <div class="row"><label>Emblem</label><select data-k="emblem"></select></div>
        <div class="row"><label>Placement</label><select data-k="emblemPos">
          <option value="rule">On the divider</option>
          <option value="above">Centered above</option>
          <option value="flankTop">Flanking top line</option>
          <option value="flankMain">Flanking main line</option>
        </select></div>
        <div class="row"><label>Size</label><input type="range" min="40" max="360" step="2" data-k="emblemSize"><span class="val" data-val="emblemSize"></span></div>
        <div class="row"><label>Clearance</label><input type="range" min="0" max="160" step="2" data-k="emblemGap"><span class="val" data-val="emblemGap"></span></div>
        <div class="row"><label>Vertical nudge</label><input type="range" min="-80" max="80" step="1" data-k="emblemY"><span class="val" data-val="emblemY"></span></div>
        <div class="row matrow"><label>Material</label><select data-k="emblemMat"></select><input type="color" data-k="c_emblem" data-showif="emblemMat:custom"></div>
        <div class="row"><label>Finish</label><select data-k="emblemFinish"></select></div>
        <div class="hint">Task-force emblems can also keep their original colors — pick "Original colors" as the material.</div>
      </div>
    </details>

    <details class="acc">
      <summary>Layout</summary>
      <div class="acc-body">
        <div class="row"><label>Block width</label><input type="range" min="600" max="1480" step="10" data-k="blockW"><span class="val" data-val="blockW"></span></div>
        <div class="row"><label>Row spacing</label><input type="range" min="0" max="120" step="2" data-k="rowGap"><span class="val" data-val="rowGap"></span></div>
        <div class="row"><label>All caps</label><input type="checkbox" data-k="uppercase"></div>
        <div class="hint">"Fit to block width" on a line stretches its letter spacing so the line exactly fills the block — the classic BFMS header look.</div>
      </div>
    </details>

    <details class="acc">
      <summary>Finish</summary>
      <div class="acc-body">
        <div class="row"><label>Bevel depth</label><input type="range" min="0" max="8" step="0.5" data-k="bevel"><span class="val" data-val="bevel"></span></div>
        <div class="row"><label>Shine</label><input type="range" min="0" max="2" step="0.05" data-k="shine"><span class="val" data-val="shine"></span></div>
        <div class="row"><label>Brushed texture</label><input type="range" min="0" max="1" step="0.05" data-k="texAmt"><span class="val" data-val="texAmt"></span></div>
        <div class="row"><label>Drop shadow</label><input type="range" min="0" max="1" step="0.05" data-k="shadow"><span class="val" data-val="shadow"></span></div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Export</summary>
      <div class="acc-body">
        <div class="row">
          <label>PNG width</label>
          <select id="exportSize">
            <option value="750">750 px</option>
            <option value="1500" selected>1500 px</option>
            <option value="3000">3000 px</option>
          </select>
        </div>
        <div class="btnrow">
          <button class="btn primary" id="exportPng">Export PNG</button>
          <button class="btn" id="exportSvg">Export SVG</button>
        </div>
        <div class="hint">PNG exports with a transparent background, ready to overlay on a BFMS cover image.</div>
      </div>
    </details>

    <section class="plain">
      <button class="btn danger" id="resetBtn">Reset to defaults</button>
    </section>

    <div class="credit">
      Fonts: <b>Sealstile</b> (Librestile, OFL) · <b>Tenor Sans</b> · <b>Cinzel</b> · <b>Michroma</b> · <b>Orbitron</b><br>
      all SIL Open Font License · exports embed everything<br><br>
      © Bravo Fleet 1997–<span id="copyYear"></span><br>
      Seal, patch, plaque and poster design by <b>CrimsonTacit</b><br>
      Original Columbia header by <b>JustSlide</b><br>
      Bravo Fleet logo by <b>Kevin Steeper</b><br>
      Ship banner by <b>Emily Wolf</b> and <b>Kevin Steeper</b><br>
      <a href="https://wiki.bravofleet.com/index.php/Credits#Graphics" target="_blank" rel="noopener">Full graphics credits here</a>
    </div>
  </div>

  <div id="stage" class="dark">
    <div class="stagebg" id="stageBg"></div>
    <div id="hdrwrap"></div>
    <div class="stagehint">Preview background is not exported — PNG output is transparent</div>
  </div>
</main>

<script src="shared/state.js?v=fa880068"></script>
<script src="shared/text.js?v=127a04be"></script>
<script src="shared/export.js?v=07669e3b"></script>
<script src="shared/stage.js?v=b9df1f51"></script>
<script>
/*HDRFONTS_START*/const FONTS = [
  {id:"sealstile", label:"Sealstile (seal font)", family:"Sealstile", weight:400, url:"fonts/sealstile.woff2?v=8128806d"},
  {id:"tenor", label:"Tenor Sans", family:"Tenor Sans", weight:400, url:"fonts/webfonts/tenor.woff2?v=b124d5a7"},
  {id:"cinzel", label:"Cinzel", family:"Cinzel", weight:600, url:"fonts/webfonts/cinzel.woff2?v=37543dc7"},
  {id:"michroma", label:"Michroma", family:"Michroma", weight:400, url:"fonts/webfonts/michroma.woff2?v=b1209818"},
  {id:"orbitron", label:"Orbitron", family:"Orbitron", weight:600, url:"fonts/webfonts/orbitron.woff2?v=3906d33a"}
];/*HDRFONTS_END*/
/*CHARGES_START*/const CHARGES = {
  "tf17b": {name:"Task Force 17", w:362, h:362, url:"assets/emblems/tf17b.png?v=444bb0c6"},
  "tf21b": {name:"Task Force 21", w:362, h:362, url:"assets/emblems/tf21b.png?v=ce55b01a"},
  "tf47b": {name:"Task Force 47", w:362, h:362, url:"assets/emblems/tf47b.png?v=0f18192a"},
  "tf72b": {name:"Task Force 72", w:362, h:362, url:"assets/emblems/tf72b.png?v=74ee1716"},
  "tf86b": {name:"Task Force 86", w:362, h:362, url:"assets/emblems/tf86b.png?v=9431a1bc"},
  "tf93b": {name:"Task Force 93", w:362, h:362, url:"assets/emblems/tf93b.png?v=6758739f"}
};/*CHARGES_END*/

/* register all fonts for the page UI + live preview */
const FONT_FACES = FONTS.map(f =>
  `@font-face{font-family:'${f.family}';font-weight:${f.weight};src:url(${f.url}) format('woff2');}`
).join("\n");
const styleEl = document.createElement("style");
styleEl.textContent = FONT_FACES;
document.head.appendChild(styleEl);

/* ================= materials ================= */
/* stops: vertical gradient over the glyph height, with the hard mid break
   that reads as a polished-metal horizon. base: mid tone used for satin/flat.
   edge: dark outline color for glyph definition. */
const MATERIALS = {
  gold:     { name:"Gold",       base:"#c9992e", edge:"#63470b",
              stops:[[0,"#fdf6bd"],[.28,"#f3d356"],[.47,"#c9992e"],[.5,"#8a6410"],[.53,"#a87c17"],[.74,"#e8c14a"],[1,"#fdf0a2"]] },
  silver:   { name:"Silver",     base:"#a6b0bc", edge:"#4c545e",
              stops:[[0,"#ffffff"],[.28,"#dfe4ea"],[.47,"#a6b0bc"],[.5,"#79828e"],[.53,"#8d97a3"],[.74,"#d4dae1"],[1,"#f6f8fa"]] },
  platinum: { name:"Platinum",   base:"#c9cdc4", edge:"#63665c",
              stops:[[0,"#ffffff"],[.28,"#eceee7"],[.47,"#c1c5ba"],[.5,"#969b8c"],[.53,"#a8ad9f"],[.74,"#e2e4dd"],[1,"#fafaf7"]] },
  copper:   { name:"Copper",     base:"#b45f2a", edge:"#54270c",
              stops:[[0,"#ffd9c0"],[.28,"#e89a63"],[.47,"#b45f2a"],[.5,"#7c3a12"],[.53,"#984f1e"],[.74,"#dd9258"],[1,"#ffcfae"]] },
  bronze:   { name:"Bronze",     base:"#96702c", edge:"#40300c",
              stops:[[0,"#f2e0b6"],[.28,"#cfa356"],[.47,"#96702c"],[.5,"#644312"],[.53,"#7d5a1e"],[.74,"#c49b4e"],[1,"#ecd9a8"]] },
  steel:    { name:"Steel Blue", base:"#8299b3", edge:"#2e3f50",
              stops:[[0,"#eef4fb"],[.28,"#c3d2e2"],[.47,"#8299b3"],[.5,"#5a7188"],[.53,"#6d849b"],[.74,"#b7c9db"],[1,"#e8f0f8"]] },
  gunmetal: { name:"Gunmetal",   base:"#4a525d", edge:"#14171c",
              stops:[[0,"#b9c0c9"],[.28,"#79828e"],[.47,"#4a525d"],[.5,"#30363f"],[.53,"#3d444e"],[.74,"#6d7681"],[1,"#a7aeb8"]] },
  custom:   { name:"Custom color…" },
};
const FINISHES = { polished:"Polished", brushed:"Brushed", satin:"Satin (matte)", flat:"Flat" };

/* ---- color math for custom-color ramps ---- */
function hexRgb(h){h=h.replace("#","");return [parseInt(h.slice(0,2),16),parseInt(h.slice(2,4),16),parseInt(h.slice(4,6),16)];}
function rgbHex(r,g,b){return "#"+[r,g,b].map(v=>Math.max(0,Math.min(255,Math.round(v))).toString(16).padStart(2,"0")).join("");}
function mix(hex, target, t){ // t 0..1 toward target ("#fff" or "#000")
  const a=hexRgb(hex), b=hexRgb(target);
  return rgbHex(a[0]+(b[0]-a[0])*t, a[1]+(b[1]-a[1])*t, a[2]+(b[2]-a[2])*t);
}
const ltn=(h,t)=>mix(h,"#ffffff",t), dkn=(h,t)=>mix(h,"#000000",t);

function rampFor(base){
  return [[0,ltn(base,.72)],[.28,ltn(base,.32)],[.47,base],[.5,dkn(base,.42)],[.53,dkn(base,.25)],[.74,ltn(base,.18)],[1,ltn(base,.6)]];
}
function matFor(el){ // -> {stops, edge, base}
  const key = S[el+"Mat"];
  if(key === "custom"){
    const base = S["c_"+el];
    return { stops: rampFor(base), edge: dkn(base,.62), base };
  }
  /* "original" (TF art untinted) never draws its gradient, but buildSVG
     still emits one — hand it something harmless */
  const m = MATERIALS[key] || MATERIALS.silver;
  return { stops: m.stops, edge: m.edge, base: m.base };
}

/* ================= state ================= */
const DEFAULTS = {
  topOn: true,  topText: "BRAVO FLEET", topFont: "tenor", topSize: 96,  topTrack: 8,  topFit: false, topMat: "gold",   topFinish: "polished",
  mainText: "COLUMBIA",                 mainFont: "tenor", mainSize: 230, mainTrack: 0, mainFit: true, mainMat: "silver", mainFinish: "brushed",
  subOn: false, subText: "TASK FORCE 47", subFont: "tenor", subSize: 70, subTrack: 14, subFit: false, subMat: "silver", subFinish: "brushed",
  ruleOn: true, ruleH: 8, ruleMat: "gold", ruleFinish: "polished",
  c_top:"#d3a92c", c_main:"#a6b0bc", c_sub:"#a6b0bc", c_rule:"#d3a92c", c_emblem:"#d3a92c",
  blockW: 1420, rowGap: 30, uppercase: true,
  emblem: "none", emblemPos: "rule", emblemSize: 120, emblemGap: 40, emblemY: 0,
  emblemMat: "gold", emblemFinish: "polished",
  bevel: 3, shine: 1, texAmt: 0.45, shadow: 0.3,
  lastPreset: "Columbia Classic",
};
let S = Object.assign({}, DEFAULTS);

/* approximate cap-height ratios so layout rows hug the glyphs */
const CAP = { sealstile:.72, tenor:.73, cinzel:.70, michroma:.75, orbitron:.78 };

const STYLES = [
  { name:"Columbia Classic", sw:["gold","silver"],
    set:{topMat:"gold",topFinish:"polished",ruleMat:"gold",ruleFinish:"polished",mainMat:"silver",mainFinish:"brushed",subMat:"silver",subFinish:"brushed",emblemMat:"gold",emblemFinish:"polished"} },
  { name:"All Gold", sw:["gold","gold"],
    set:{topMat:"gold",ruleMat:"gold",mainMat:"gold",subMat:"gold",emblemMat:"gold",topFinish:"polished",ruleFinish:"polished",mainFinish:"polished",subFinish:"polished",emblemFinish:"polished"} },
  { name:"Sterling", sw:["silver","silver"],
    set:{topMat:"silver",ruleMat:"silver",mainMat:"silver",subMat:"silver",emblemMat:"silver",topFinish:"polished",ruleFinish:"polished",mainFinish:"brushed",subFinish:"brushed",emblemFinish:"polished"} },
  { name:"Copper & Brass", sw:["bronze","copper"],
    set:{topMat:"bronze",ruleMat:"bronze",mainMat:"copper",subMat:"copper",emblemMat:"bronze",topFinish:"polished",ruleFinish:"polished",mainFinish:"brushed",subFinish:"brushed",emblemFinish:"polished"} },
  { name:"Cold Steel", sw:["steel","gunmetal"],
    set:{topMat:"steel",ruleMat:"gunmetal",mainMat:"steel",subMat:"steel",emblemMat:"steel",topFinish:"polished",ruleFinish:"polished",mainFinish:"brushed",subFinish:"brushed",emblemFinish:"polished"} },
  { name:"Gunmetal", sw:["gunmetal","gunmetal"],
    set:{topMat:"gunmetal",ruleMat:"gunmetal",mainMat:"gunmetal",subMat:"gunmetal",emblemMat:"gunmetal",topFinish:"brushed",ruleFinish:"polished",mainFinish:"brushed",subFinish:"brushed",emblemFinish:"brushed"} },
  { name:"Gold on Silver", sw:["silver","gold"],
    set:{topMat:"silver",topFinish:"polished",ruleMat:"silver",ruleFinish:"polished",mainMat:"gold",mainFinish:"polished",subMat:"gold",subFinish:"brushed",emblemMat:"silver",emblemFinish:"polished"} },
];

/* ================= geometry / render ================= */
const W = 1500, PAD = 46;

/* vector emblem paths (shared with the Seal Builder's separators) */
const VECT = {
  bolt:    { d:"M 1.82,239.05 L 20.8,122.5 L 0,122.17 L 64.55,0 L 45.4,115.23 L 66.7,115.23 Z", w:66.7, h:239.05 },
  /* delta and sparkle are drawn centered on the origin, so ox/oy are the
     origin's offset from the shape's center — not half the size (the bolt is
     corner-origin and relies on the w/2,h/2 default) */
  delta:   { d:"M 0,-30 C 7,-16 14,2 21,24 C 13,11 6,8 0,8 C -6,8 -13,11 -21,24 C -14,2 -7,-16 0,-30 Z", w:42, h:54, ox:0, oy:-3 },
  sparkle: { d:"M 0,-30 Q 1.9,-5.4 30,0 Q 1.9,5.4 0,30 Q -1.9,5.4 -30,0 Q -1.9,-5.4 0,-30 Z", w:60, h:60, ox:0, oy:0 },
};

function esc(s){return s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");}

/* Measured on a hidden SVG node via shared/text.js, not a canvas 2D context:
   layout() uses this to shrink a line that overflows its block, and the two
   engines disagree (Safari runs Sealstile wide enough to shrink a line the
   SVG never would). `gaps` keeps this tool's original count of length-1
   tracking gaps, so the switch doesn't resize type calibrated on it. */
function measureLine(el, fs){
  const font = FONTS.find(f => f.id === S[el+"Font"]) || FONTS[0];
  return TextKit.measure(lineText(el), fs, {
    family: font.family, weight: font.weight,
    track: S[el+"Fit"] ? 0 : S[el+"Track"], gaps: true,
  });
}
function lineText(el){
  let t = S[el+"Text"] || "";
  if(S.uppercase) t = t.toUpperCase();
  return t;
}
function hasDescender(el){ return !S.uppercase && /[gjpqy]/.test(S[el+"Text"]||""); }

function emblemDims(){
  if(S.emblem === "none") return null;
  const h = S.emblemSize;
  let w;
  if(S.emblem.startsWith("tf:")){
    const c = CHARGES[S.emblem.slice(3)];
    if(!c) return null;
    w = h * c.w / c.h;
  }else{
    const v = VECT[S.emblem];
    w = h * v.w / v.h;
  }
  return { w, h };
}

function layout(){
  const rows = [];
  let y = PAD;
  const emb = emblemDims();
  if(emb && S.emblemPos === "above"){
    rows.push({el:"emblem", top:y, h:emb.h});
    y += emb.h + S.rowGap;
  }
  for(const el of ["top","rule","main","sub"]){
    if(el === "rule"){
      if(!S.ruleOn) continue;
      /* an emblem riding the divider needs headroom above and below it */
      const over = (emb && S.emblemPos === "rule") ? Math.max(0, (emb.h - S.ruleH)/2 - S.rowGap*.6) : 0;
      y += over;
      rows.push({el:"rule", top:y, h:S.ruleH});
      y += S.ruleH + S.rowGap + over;
      continue;
    }
    if(el === "top" && (!S.topOn || !S.topText.trim())) continue;
    if(el === "sub" && (!S.subOn || !S.subText.trim())) continue;
    if(el === "main" && !S.mainText.trim()) continue;
    let size = S[el+"Size"];
    if(S[el+"Fit"]){
      /* fit-to-width stretches spacing when narrow, but must shrink the type
         when the line is naturally wider than the block (negative spacing
         would overlap glyphs) */
      const w0 = measureLine(el, size);
      if(w0 > S.blockW) size = Math.max(12, size * S.blockW / w0);
    }
    const h = size * (CAP[S[el+"Font"]] || .74);
    rows.push({el, top:y, h, baseline:y+h, size});
    y += h + (hasDescender(el) ? size*.22 : 0) + S.rowGap;
  }
  const H = y - S.rowGap + PAD;
  return { rows, H };
}

/* ---- filters ---- */
function finishParams(finish){
  switch(finish){
    case "polished": return { sc:0.9,  se:22, tex:0 };
    case "brushed":  return { sc:0.45, se:12, tex:1 };
    case "satin":    return { sc:0.18, se:7,  tex:0 };
    default:         return null; /* flat: no filter */
  }
}
function filterFor(el, seed){
  const finish = S[el+"Finish"];
  const p = finishParams(finish);
  if(!p) {
    return S.shadow > 0
      ? `<filter id="fx_${el}" x="-20%" y="-50%" width="140%" height="200%" color-interpolation-filters="sRGB">
           <feDropShadow dx="0" dy="4" stdDeviation="5" flood-color="#000" flood-opacity="${(S.shadow*.7).toFixed(2)}"/>
         </filter>` : "";
  }
  const tex = p.tex * S.texAmt;
  const bev = Math.max(0.01, S.bevel);
  const sc = (p.sc * S.shine).toFixed(2);
  let f = `<filter id="fx_${el}" x="-20%" y="-50%" width="140%" height="200%" color-interpolation-filters="sRGB">`;
  if(tex > 0){
    /* vertical brushed streaks: high x frequency, low y frequency */
    const amp = (tex*.5).toFixed(2), off = (0.5 - tex*.25).toFixed(2);
    f += `<feTurbulence type="fractalNoise" baseFrequency="0.9 0.012" numOctaves="2" seed="${seed}" result="tn"/>
      <feColorMatrix in="tn" type="matrix" values="${amp} 0 0 0 ${off}  ${amp} 0 0 0 ${off}  ${amp} 0 0 0 ${off}  0 0 0 0 1" result="texg"/>
      <feBlend in="SourceGraphic" in2="texg" mode="overlay" result="txd"/>
      <feComposite in="txd" in2="SourceAlpha" operator="in" result="base"/>`;
  }else{
    f += `<feOffset in="SourceGraphic" dx="0" dy="0" result="base"/>`;
  }
  f += `<feGaussianBlur in="SourceAlpha" stdDeviation="${(bev*.7).toFixed(1)}" result="blur"/>
    <feSpecularLighting in="blur" surfaceScale="${(bev*1.2).toFixed(1)}" specularConstant="${sc}" specularExponent="${p.se}" lighting-color="#ffffff" result="spec">
      <feDistantLight azimuth="235" elevation="48"/>
    </feSpecularLighting>
    <feComposite in="spec" in2="SourceAlpha" operator="in" result="specM"/>
    <feComposite in="base" in2="specM" operator="arithmetic" k1="0" k2="1" k3="0.85" k4="0" result="lit"/>`;
  if(S.shadow > 0){
    f += `<feDropShadow in="lit" dx="0" dy="4" stdDeviation="5" flood-color="#000" flood-opacity="${(S.shadow*.7).toFixed(2)}"/>`;
  }else{
    f += `<feOffset in="lit" dx="0" dy="0"/>`;
  }
  return f + `</filter>`;
}

function gradientFor(el, y1, y2){
  const m = matFor(el);
  const finish = S[el+"Finish"];
  let stops;
  if(finish === "flat"){
    stops = [[0, m.base],[1, m.base]];
  }else if(finish === "satin"){
    stops = [[0, ltn(m.base,.28)],[1, dkn(m.base,.3)]];
  }else{
    stops = m.stops;
  }
  return `<linearGradient id="grad_${el}" x1="0" y1="${y1}" x2="0" y2="${y2}" gradientUnits="userSpaceOnUse">`
    + stops.map(s=>`<stop offset="${s[0]}" stop-color="${s[1]}"/>`).join("")
    + `</linearGradient>`;
}

function textSVG(el, row){
  const font = FONTS.find(f => f.id === S[el+"Font"]) || FONTS[0];
  const m = matFor(el);
  const fit = S[el+"Fit"];
  const fs = row.size;
  const strokeW = Math.max(1, fs*.013);
  const fitAttr = fit ? ` textLength="${S.blockW}" lengthAdjust="spacing"` : ` letter-spacing="${S[el+"Track"]}"`;
  const flat = S[el+"Finish"] === "flat";
  const fxAttr = (flat && S.shadow<=0) ? "" : ` filter="url(#fx_${el})"`;
  return `<g${fxAttr}><text x="${W/2}" y="${row.baseline}" text-anchor="middle"
    font-family="${font.family}" font-weight="${font.weight}" font-size="${fs}"${fitAttr}
    fill="url(#grad_${el})" stroke="${m.edge}" stroke-width="${strokeW}" paint-order="stroke" stroke-linejoin="round">${esc(lineText(el))}</text></g>`;
}

function emblemSVG(cx, cy, emb){
  const el = "emblem", m = matFor(el);
  const flat = S[el+"Finish"] === "flat";
  const fxAttr = (flat && S.shadow<=0) ? "" : ` filter="url(#fx_${el})"`;
  if(S.emblem.startsWith("tf:")){
    const c = CHARGES[S.emblem.slice(3)];
    const x = cx - emb.w/2, y = cy - emb.h/2;
    if(S.emblemMat === "original"){
      return `<g${fxAttr}><image href="${uri(c.url)}" x="${x}" y="${y}" width="${emb.w}" height="${emb.h}"/></g>`;
    }
    return `<mask id="m_emb" maskUnits="userSpaceOnUse" x="${x}" y="${y}" width="${emb.w}" height="${emb.h}" style="mask-type:alpha">
        <image href="${uri(c.url)}" x="${x}" y="${y}" width="${emb.w}" height="${emb.h}"/>
      </mask>
      <g${fxAttr}><rect x="${x}" y="${y}" width="${emb.w}" height="${emb.h}" fill="url(#grad_emblem)" mask="url(#m_emb)"/></g>`;
  }
  const v = VECT[S.emblem];
  const s = emb.h / v.h;
  const ox = v.ox !== undefined ? v.ox : v.w/2, oy = v.oy !== undefined ? v.oy : v.h/2;
  return `<g${fxAttr}><path d="${v.d}" fill="url(#grad_emblem)" stroke="${m.edge}" stroke-width="${(1.2/s).toFixed(2)}" stroke-linejoin="round" paint-order="stroke"
    transform="translate(${cx},${cy}) scale(${s.toFixed(4)}) translate(${-ox},${-oy})"/></g>`;
}

/* Resources for the build currently in flight: null for the live preview
   (art referenced by URL), or exportResources()'s { fontURI, map } for an
   export. It's module-level because emblemSVG() sits outside buildSVG but
   still has to resolve art the same way; every build sets it, so it can't
   get stuck on a stale value. */
let RES = null;
const uri = u => (RES && RES.map[u]) || u;

/* res is undefined for the live preview and, for exports, the
   { fontURI, map } from exportResources(). */
function buildSVG(res){
  RES = res || null;
  const { rows, H } = layout();
  const emb = emblemDims();
  const cx = W/2;
  const xL = cx - S.blockW/2, xR = cx + S.blockW/2;

  let defs = "", body = "", usedFonts = new Set(), seed = 3;
  let embDrawn = false;

  for(const row of rows){
    const el = row.el;
    if(el === "rule"){
      const ry = row.top, rh = row.h;
      defs += gradientFor("rule", ry, ry+rh) + filterFor("rule", seed++);
      const rx = rh/2;
      if(emb && S.emblemPos === "rule"){
        const gap = emb.w/2 + S.emblemGap;
        body += `<g filter="url(#fx_rule)">
          <rect x="${xL}" y="${ry}" width="${Math.max(0, cx-gap-xL)}" height="${rh}" rx="${rx}" fill="url(#grad_rule)"/>
          <rect x="${cx+gap}" y="${ry}" width="${Math.max(0, xR-cx-gap)}" height="${rh}" rx="${rx}" fill="url(#grad_rule)"/>
        </g>`;
        const ecy = ry + rh/2 + S.emblemY;
        defs += gradientFor("emblem", ecy-emb.h/2, ecy+emb.h/2) + filterFor("emblem", seed++);
        body += emblemSVG(cx, ecy, emb);
        embDrawn = true;
      }else{
        body += `<g filter="url(#fx_rule)"><rect x="${xL}" y="${ry}" width="${S.blockW}" height="${rh}" rx="${rx}" fill="url(#grad_rule)"/></g>`;
      }
      continue;
    }
    if(el === "emblem"){ /* placement: above */
      const ecy = row.top + row.h/2 + S.emblemY;
      defs += gradientFor("emblem", ecy-emb.h/2, ecy+emb.h/2) + filterFor("emblem", seed++);
      body += emblemSVG(cx, ecy, emb);
      embDrawn = true;
      continue;
    }
    /* text row */
    usedFonts.add(S[el+"Font"]);
    defs += gradientFor(el, row.top, row.baseline) + filterFor(el, seed++);
    body += textSVG(el, row);
    if(emb && !embDrawn && ((S.emblemPos === "flankTop" && el === "top") || (S.emblemPos === "flankMain" && el === "main"))){
      const ecy = row.top + row.h/2 + S.emblemY;
      defs += gradientFor("emblem", ecy-emb.h/2, ecy+emb.h/2) + filterFor("emblem", seed++);
      /* hug the actual text width, clamped inside the canvas */
      const lineW = S[el+"Fit"] ? S.blockW : Math.min(S.blockW, measureLine(el, row.size));
      const off = Math.min(W/2 - emb.w/2 - 8, lineW/2 + S.emblemGap + emb.w/2);
      body += emblemSVG(cx - off, ecy, emb).replace('id="m_emb"','id="m_embL"').replace('url(#m_emb)','url(#m_embL)');
      body += emblemSVG(cx + off, ecy, emb);
      embDrawn = true;
    }
  }

  let fontCss = "";
  if(RES){
    /* only the faces this header actually uses get inlined */
    fontCss = `<defs><style>` + FONTS.filter(f=>usedFonts.has(f.id)).map(f =>
      ExportKit.fontFaceRule(f.family, f.weight, "normal", RES.fontURI[f.id])
    ).join("\n") + `</style></defs>`;
  }

  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${Math.round(H)}" width="${W}" height="${Math.round(H)}">`
    + fontCss + `<defs>${defs}</defs>` + body + `</svg>`;
}

/* ================= UI ================= */
const $ = id => document.getElementById(id);

function matOptions(sel, allowOriginal){
  let o = "";
  for(const k of Object.keys(MATERIALS)) o += `<option value="${k}">${MATERIALS[k].name}</option>`;
  if(allowOriginal) o += `<option value="original">Original colors (TF art)</option>`;
  return o;
}
function finishOptions(){
  return Object.entries(FINISHES).map(([k,v])=>`<option value="${k}">${v}</option>`).join("");
}
function fontOptions(){
  return FONTS.map(f=>`<option value="${f.id}">${f.label}</option>`).join("");
}

function lineControls(el, hasToggle){
  return `
    ${hasToggle ? `<div class="row"><label>Show line</label><input type="checkbox" data-k="${el}On"></div>` : ""}
    <input type="text" data-k="${el}Text" spellcheck="false">
    <div class="row"><label>Font</label><select data-k="${el}Font">${fontOptions()}</select></div>
    <div class="row"><label>Size</label><input type="range" min="30" max="330" step="2" data-k="${el}Size"><span class="val" data-val="${el}Size"></span></div>
    <div class="row"><label>Tracking</label><input type="range" min="-10" max="80" step="1" data-k="${el}Track"><span class="val" data-val="${el}Track"></span></div>
    <div class="row"><label>Fit to block width</label><input type="checkbox" data-k="${el}Fit"></div>
    <div class="row matrow"><label>Material</label><select data-k="${el}Mat">${matOptions()}</select><input type="color" data-k="c_${el}" data-showif="${el}Mat:custom"></div>
    <div class="row"><label>Finish</label><select data-k="${el}Finish"></select></div>`;
}

function buildPanel(){
  $("sec_top").innerHTML = lineControls("top", true);
  $("sec_main").innerHTML = lineControls("main", false);
  $("sec_sub").innerHTML = lineControls("sub", true);
  /* fill selects that were left empty in static HTML */
  document.querySelectorAll('select[data-k$="Finish"]').forEach(s=>{ if(!s.options.length) s.innerHTML = finishOptions(); });
  document.querySelectorAll('select[data-k$="Mat"]').forEach(s=>{
    if(!s.options.length) s.innerHTML = matOptions(null, s.dataset.k === "emblemMat");
  });
  /* emblem select */
  let eo = `<option value="none">None</option><option value="bolt">BF Bolt</option><option value="delta">Starfleet Delta</option><option value="sparkle">Sparkle</option>`;
  for(const k of Object.keys(CHARGES)) eo += `<option value="tf:${k}">${CHARGES[k].name}</option>`;
  document.querySelector('select[data-k="emblem"]').innerHTML = eo;
  /* style presets */
  $("stylePresets").innerHTML = STYLES.map((st,i)=>{
    const sw = st.sw.map(k=>{
      const m = MATERIALS[k];
      return `<i style="background:linear-gradient(180deg,${m.stops[0][1]},${m.stops[2][1]} 48%,${m.stops[3][1]} 52%,${m.stops[5][1]})"></i>`;
    }).join("");
    return `<button class="preset" data-style="${i}"><span class="sw">${sw}</span>${st.name}</button>`;
  }).join("");
  document.querySelectorAll("[data-style]").forEach(b=>b.addEventListener("click",()=>{
    Object.assign(S, STYLES[+b.dataset.style].set);
    S.lastPreset = STYLES[+b.dataset.style].name;
    syncUI(); render(); persist();
  }));
}

function bindInputs(){
  document.querySelectorAll("[data-k]").forEach(inp=>{
    const k = inp.dataset.k;
    const ev = (inp.type === "text" || inp.type === "range" || inp.type === "color") ? "input" : "change";
    inp.addEventListener(ev, ()=>{
      if(inp.type === "checkbox") S[k] = inp.checked;
      else if(inp.type === "range") S[k] = parseFloat(inp.value);
      else S[k] = inp.value;
      syncVals(); syncShowIf(); render(); persist();
    });
  });
}

function syncVals(){
  document.querySelectorAll("[data-val]").forEach(sp=>{ sp.textContent = S[sp.dataset.val]; });
}
function syncShowIf(){
  document.querySelectorAll("[data-showif]").forEach(n=>{
    const [k,v] = n.dataset.showif.split(":");
    n.classList.toggle("hide", S[k] !== v);
  });
}
function syncUI(){
  document.querySelectorAll("[data-k]").forEach(inp=>{
    const k = inp.dataset.k;
    if(!(k in S)) return;
    if(inp.type === "checkbox") inp.checked = !!S[k];
    else inp.value = S[k];
  });
  syncVals(); syncShowIf();
}

function render(){
  $("hdrwrap").innerHTML = buildSVG();
}

/* ================= export ================= */
const slug = () => StateKit.slugify(S.mainText, "header");
/* Inline every asset an export might need. Which faces a header uses is only
   known inside layout()'s row loop, so all five are resolved here rather than
   duplicating that logic; they're memoized, small, and the page used to ship
   all five to every visitor regardless. buildSVG still emits only the used ones. */
async function exportResources(){
  const urls = [];
  if(S.emblem.startsWith("tf:") && CHARGES[S.emblem.slice(3)])
    urls.push(CHARGES[S.emblem.slice(3)].url);
  const [map, ...fonts] = await Promise.all([
    ExportKit.fetchDataURIs(urls),
    ...FONTS.map(f => ExportKit.fetchDataURI(f.url)),
  ]);
  const fontURI = {};
  FONTS.forEach((f,i) => { fontURI[f.id] = fonts[i]; });
  return { map, fontURI };
}

async function renderToCanvas(widthPx){
  const svg = buildSVG(await exportResources());
  const { H } = layout();
  return ExportKit.svgToCanvas(svg, widthPx, Math.round(widthPx * H / W));
}

ExportKit.wireExport($("exportPng"), async ()=>{
  const size = parseInt($("exportSize").value, 10);
  const cv = await renderToCanvas(size);
  await ExportKit.downloadCanvasPNG(cv, slug()+"-header-"+size+".png");
});
ExportKit.wireExport($("exportSvg"), async ()=>{
  ExportKit.downloadSVG(buildSVG(await exportResources()), slug()+"-header.svg");
}, {busy:"Building…"});

$("resetBtn").addEventListener("click", ()=>{
  S = Object.assign({}, DEFAULTS);
  syncUI(); render(); persist();
});

/* ================= persistence ================= */
const LS_KEY = "headerbuilder-v1";
const persist = StateKit.persister(LS_KEY, () => S, 300);
try{
  const saved = localStorage.getItem(LS_KEY);
  if(saved) S = Object.assign({}, DEFAULTS, JSON.parse(saved));
}catch(e){}

/* ================= stage background ================= */
Stage.init();

/* ================= boot ================= */
$("copyYear").textContent = new Date().getFullYear();
buildPanel();
bindInputs();
syncUI();
render();
Promise.all(FONTS.map(f=>document.fonts.load(`${f.weight} 16px "${f.family}"`))).then(()=>render());
</script>
</body>
</html>

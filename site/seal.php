<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Build round vessel, station and organisation seals in your browser, and export them as transparent PNGs.">
<link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2032%2032%22%3E%3Crect%20width%3D%2232%22%20height%3D%2232%22%20rx%3D%225%22%20fill%3D%22%230d1420%22%2F%3E%3Ccircle%20cx%3D%2216%22%20cy%3D%2216%22%20r%3D%2210%22%20fill%3D%22none%22%20stroke%3D%22%23d3a92c%22%20stroke-width%3D%222.5%22%2F%3E%3Ccircle%20cx%3D%2216%22%20cy%3D%2216%22%20r%3D%225.5%22%20fill%3D%22%232864a8%22%2F%3E%3C%2Fsvg%3E">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Bravo Fleet Graphics Tools">
<meta property="og:title" content="Seal Builder — Bravo Fleet Graphics Tools">
<meta property="og:description" content="Build round vessel, station and organisation seals in your browser, and export them as transparent PNGs.">
<meta property="og:url" content="https://crimsontacit.github.io/BF-Seal-Builder/seal-tool.html">
<meta name="twitter:card" content="summary">
<title>Seal Builder</title>
<link rel="stylesheet" href="shared/chrome.css?v=d62425d5">
<style>
  .microstyle{font-family:"Sealstile",ui-monospace,monospace}
  header .mark{
    width:26px;height:26px;border-radius:50%;
    border:2px solid var(--bolt-gold);
    box-shadow:inset 0 0 0 4px var(--bg), inset 0 0 0 6px var(--bf-blue);
    flex:none;
  }
  a.btn{text-decoration:none;display:inline-block}
  section{border-bottom:1px solid var(--line);padding:14px 16px 16px}
  .presets-group{
    font-size:10px;letter-spacing:.15em;color:var(--ink-dim);text-transform:uppercase;
    margin:12px 0 6px;
  }
  .presets-group:first-child{margin-top:0}
  .colorgrid{display:grid;grid-template-columns:1fr 1fr;gap:4px 14px}
  .colorgrid .row{margin-bottom:4px}
  .presets{display:flex;gap:6px;flex-wrap:wrap}
  .preset{
    display:flex;align-items:center;gap:6px;
    background:var(--panel2);border:1px solid var(--line-bright);border-radius:2px;
    padding:5px 9px;cursor:pointer;font-size:11px;letter-spacing:.06em;color:var(--ink);
  }
  .preset:hover{border-color:var(--bolt-gold)}
  .preset .sw{display:flex}
  .preset .sw i{width:9px;height:9px;display:block}
  .filebox{
    border:1px dashed var(--line-bright);border-radius:2px;padding:12px;text-align:center;
    color:var(--ink-dim);cursor:pointer;font-size:11px;letter-spacing:.06em;
  }
  .filebox:hover,.filebox.drag{border-color:var(--bf-blue);color:var(--bf-blue)}

  /* ---------- stage ---------- */
  #stage{
    flex:1;display:flex;align-items:center;justify-content:center;position:relative;
    min-width:0;
  }
  #sealwrap{
    position:relative;z-index:1;
    width:min(78vh, 78%);aspect-ratio:1;
    filter:drop-shadow(0 18px 50px rgba(0,0,0,.6));
  }
  #stage.light #sealwrap,#stage.grey #sealwrap,#stage.checker #sealwrap{
    filter:drop-shadow(0 14px 34px rgba(20,25,35,.22));
  }
  #sealwrap svg{width:100%;height:100%;display:block}
  #sealwrap.grabbable{cursor:grab}
  #sealwrap.grabbing{cursor:grabbing}


  /* ---------- swatch popover ---------- */
  #swatchpop{
    position:fixed;z-index:50;display:none;width:246px;
    background:var(--panel2);border:1px solid var(--line-bright);border-radius:3px;
    padding:10px;box-shadow:0 14px 34px rgba(0,0,0,.55);
  }
  #swatchpop h3{
    font-family:"Sealstile",ui-monospace,monospace;font-weight:normal;
    font-size:10px;letter-spacing:.22em;color:var(--ink-dim);text-transform:uppercase;
    margin:10px 0 6px;
  }
  #swatchpop h3:first-child{margin-top:0}
  .swgrid{display:grid;grid-template-columns:repeat(8,1fr);gap:4px}
  .swgrid button{
    aspect-ratio:1;border:1px solid var(--line-bright);border-radius:2px;
    cursor:pointer;padding:0;
  }
  .swgrid button:hover{border-color:var(--bolt-gold)}
  .swgrid button.cur{outline:2px solid var(--bolt-gold);outline-offset:1px}
  #swatchpop .custom{margin-top:10px;width:100%}
</style>
</head>
<body>
<noscript><div class="noscript"><b>Seal Builder</b> needs JavaScript switched on — it draws and exports your artwork entirely in the browser, with nothing sent to a server.</div></noscript>

<header>
  <div class="mark"></div>
  <h1>Seal Builder</h1>
  <div class="sub">Bravo Fleet · Vessel &amp; Station Seals</div>
  <div class="spacer"></div>
  <a class="btn" href="/">⌂</a>
  <a class="btn" href="/header">Header →</a>
  <a class="btn" href="/banner">Banner →</a>
  <a class="btn" href="/plaque">Plaque →</a>
  <a class="btn" href="/patch">Patch →</a>
  <a class="btn" href="/mission">Poster →</a>
  <select id="exportSize">
    <option value="512">512 px</option>
    <option value="1024">1024 px</option>
    <option value="2048" selected>2048 px</option>
    <option value="4096">4096 px</option>
  </select>
  <button class="btn primary" id="exportPng">Export PNG</button>
  <button class="btn" id="exportSvg">Export SVG</button>
</header>

<main>
  <div id="panel">

    <details class="acc" open>
      <summary>Presets</summary>
      <div class="acc-body">
        <div id="presets"></div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Ring Text</summary>
      <div class="acc-body">
      <input type="text" id="topText" spellcheck="false">
      <input type="text" id="bottomText" spellcheck="false">
      <div class="row"><label>TF text preset</label><select id="tfTextPreset" style="flex:1.4"></select></div>
      <div class="row"><label>Font size</label><input type="range" id="fontSize" min="28" max="80" step="1"><span class="val" id="fontSizeV"></span></div>
      <div class="row"><label>Letter spacing</label><input type="range" id="letterSpacing" min="0" max="30" step="0.5"><span class="val" id="letterSpacingV"></span></div>
      <div class="row"><label>Uppercase</label><input type="checkbox" id="uppercase"></div>
      <div class="row"><label>Separator</label><select id="sepStyle" style="flex:1.4"></select></div>
      <div class="row"><label>Sep. size</label><input type="range" id="deltaSize" min="0.5" max="1.8" step="0.05"><span class="val" id="deltaSizeV"></span></div>
      <div class="row"><label>Sep. count</label><input type="range" id="sepCount" min="1" max="7" step="1"><span class="val" id="sepCountV"></span></div>
      <div class="row"><label>Sep. spacing</label><input type="range" id="sepSpacing" min="4" max="30" step="0.5"><span class="val" id="sepSpacingV"></span></div>
      <div class="row"><label>Track shift</label><input type="range" id="sepShift" min="-60" max="60" step="1"><span class="val" id="sepShiftV"></span></div>
      <div class="row"><label>Mirror angles</label><input type="checkbox" id="sepMirror"></div>
      <div class="row"><label>Sep. angle</label><input type="range" id="sepRot" min="-180" max="180" step="5"><span class="val" id="sepRotV"></span></div>
      <div class="row"><label>Sep. angle L</label><input type="range" id="sepRotL" min="-180" max="180" step="5"><span class="val" id="sepRotLV"></span></div>
      <div class="hint">Use “•” as a text separator (⌥8 on macOS). Separator groups sit mirrored at 3 and 9 o’clock and follow the band’s curve: 0° points up, ±90° outward. Track shift slides both groups toward the top text — useful when the bottom text runs long. Uncheck Mirror to angle the left group independently. TF text preset swaps the bottom text for a Task Force operations line and shrinks the font to fit.</div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Colors</summary>
      <div class="acc-body">
      <div class="colorgrid">
        <div class="row"><label>Ring band</label><input type="color" id="c_band"></div>
        <div class="row"><label>Ring text</label><input type="color" id="c_text"></div>
        <div class="row"><label>Outer edge</label><input type="color" id="c_edge"></div>
        <div class="row"><label>Separators</label><input type="color" id="c_delta"></div>
        <div class="row"><label>Accent ring A</label><input type="color" id="c_ring1"></div>
        <div class="row"><label>Inner ring</label><input type="color" id="c_gap"></div>
        <div class="row"><label>Accent ring B</label><input type="color" id="c_ring2"></div>
        <div class="row"><label>Center fill</label><input type="color" id="c_center"></div>
      </div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Geometry</summary>
      <div class="acc-body">
      <div class="row"><label>Outer edge</label><input type="range" id="edgeW" min="0" max="40" step="1"><span class="val" id="edgeWV"></span></div>
      <div class="row"><label>Ring band</label><input type="range" id="bandW" min="60" max="180" step="1"><span class="val" id="bandWV"></span></div>
      <div class="row"><label>Accent ring A</label><input type="range" id="ring1W" min="0" max="20" step="0.5"><span class="val" id="ring1WV"></span></div>
      <div class="row"><label>Accent ring B</label><input type="range" id="ring2W" min="0" max="20" step="0.5"><span class="val" id="ring2WV"></span></div>
      <div class="row"><label>Accent inset</label><input type="range" id="inset" min="0" max="40" step="1"><span class="val" id="insetV"></span></div>
      <div class="row"><label>Inner ring</label><input type="range" id="gapW" min="0" max="90" step="1"><span class="val" id="gapWV"></span></div>
      <div class="row"><label>Lock outer+inner</label><input type="checkbox" id="lockRings"></div>
      <div class="row"><label>Lock accents</label><input type="checkbox" id="lockAccents"></div>
      <div class="btnrow"><button class="btn" id="resetGeom">Reset dimensions</button></div>
      <div class="hint">Accent rings float inside the band, inset from the thick outer and inner rings so they never touch them or the text. Locks keep each pair's widths in step: drag either slider and its partner follows.</div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Background Image</summary>
      <div class="acc-body">
      <div class="filebox" id="filebox">Drop an image here or click to choose</div>
      <input type="file" id="fileInput" accept="image/*" style="display:none">
      <div class="row" style="margin-top:10px"><label>Zoom</label><input type="range" id="imgZoom" min="0.4" max="4" step="0.01"><span class="val" id="imgZoomV"></span></div>
      <div class="row"><label>Rotation</label><input type="range" id="imgRot" min="-180" max="180" step="1"><span class="val" id="imgRotV"></span></div>
      <div class="btnrow">
        <button class="btn" id="imgCenter">Re-center</button>
        <button class="btn" id="imgRemove">Remove image</button>
      </div>
      <div class="hint">Drag the seal preview to reposition the image inside its mask. Scroll over the seal to zoom.</div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Center Charge</summary>
      <div class="acc-body">
      <div class="row"><label>Emblem</label><select id="charge" style="flex:1.4"></select></div>
      <div id="chargeUpload" style="display:none">
        <div class="filebox" id="chargeFilebox">Drop an image here or click to choose</div>
        <input type="file" id="chargeFileInput" accept="image/*" style="display:none">
        <div class="btnrow"><button class="btn" id="chargeImgRemove">Remove custom image</button></div>
        <div class="hint">PNG or SVG preferred — both preserve transparency, so the charge sits cleanly over the center fill, starfield, or artwork.</div>
      </div>
      <div class="row"><label>Size</label><input type="range" id="chargeSize" min="0.2" max="1.6" step="0.02"><span class="val" id="chargeSizeV"></span></div>
      <div class="row"><label>Vert. offset</label><input type="range" id="chargeY" min="-200" max="200" step="2"><span class="val" id="chargeYV"></span></div>
      <div class="row"><label>Recolor</label><input type="checkbox" id="chargeTint"></div>
      <div class="row"><label>Charge color</label><input type="color" id="c_charge"></div>
      <div class="hint">Task force emblems, the BF bolt, or your own uploaded image, layered over the center fill, starfield, or artwork. Enable recolor to tint — this fills the shape's alpha with a flat color, so it suits silhouette-style art better than a full-color photo or logo.</div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Starfield</summary>
      <div class="acc-body">
      <div class="row"><label>Show starfield</label><input type="checkbox" id="starOn"></div>
      <div class="row"><label>Big sparkles</label><input type="checkbox" id="starSparkles"></div>
      <div class="row"><label>Star color</label><input type="color" id="c_star"></div>
      <div class="row"><label>Density</label><input type="range" id="starDensity" min="20" max="220" step="5"><span class="val" id="starDensityV"></span></div>
      <div class="btnrow"><button class="btn" id="starReroll">Reroll pattern</button></div>
      <div class="hint">A procedural star scatter with four-point sparkles, for seals without artwork — or behind a transparent emblem.</div>
      </div>
    </details>

    <section>
      <div class="btnrow">
        <button class="btn" id="resetBtn">Reset to defaults</button>
      </div>
    </section>

    <div class="credit">
      © Bravo Fleet 1997–<span id="copyYear"></span><br>
      Seal, patch, plaque and poster design by <b>CrimsonTacit</b><br>
      Original Columbia header by <b>JustSlide</b><br>
      Bravo Fleet logo by <b>Kevin Steeper</b><br>
      Ship banner by <b>Emily Wolf</b> and <b>Kevin Steeper</b><br>
      <a href="https://wiki.bravofleet.com/index.php/Credits#Graphics" target="_blank" rel="noopener">Full graphics credits here</a>
    </div>

  </div>

  <div id="stage">
    <div class="stagebg" id="stageBg"></div>
    <div id="sealwrap"></div>
    <div class="stagehint">Drag to position background image · Scroll to zoom</div>
  </div>
</main>

<script src="shared/state.js?v=fa880068"></script>
<script src="shared/export.js?v=07669e3b"></script>
<script src="shared/stage.js?v=b9df1f51"></script>
<script>
"use strict";

/* ================= font =================
   "Sealstile" = Librestile Ext Bold by ocelothe2k1, SIL OFL 1.1
   (fonts/OFL-Librestile.txt), renamed per its Reserved Font Name clause,
   with a bullet glyph added. Rebuild: tools/embed_assets.py --font

   Loaded by URL for the page and the live preview. Export can't use a URL —
   the SVG goes into an <img> to be rasterised, and an <img>-hosted SVG may
   not fetch anything — so exportResources() inlines it as a data URI there. */
const FONT_URL = "fonts/sealstile.woff2?v=8128806d";
const FONT_CSS = `@font-face{font-family:'Sealstile';src:url(${FONT_URL}) format('woff2');}`;

/* register for the page UI + live preview */
const styleEl = document.createElement("style");
styleEl.textContent = FONT_CSS;
document.head.appendChild(styleEl);

/* ================= state ================= */
const DEFAULTS = {
  topText: "USS PEGASUS • NCC-91775",
  bottomText: "UNITED FEDERATION OF PLANETS",
  fontSize: 46, letterSpacing: 5, uppercase: true,
  sepStyle: "delta", deltaSize: 1.25, sepRot: 0, sepRotL: 0, sepMirror: true,
  sepCount: 1, sepSpacing: 14, sepShift: 4,
  c_band: "#2864a8", c_text: "#ffffff", c_edge: "#1b4574", c_delta: "#d3a92c",
  c_ring1: "#d3a92c", c_gap: "#1b4574", c_ring2: "#d3a92c", c_center: "#143356",
  edgeW: 20, bandW: 116, ring1W: 5, ring2W: 5, inset: 12, gapW: 20,
  lockRings: true, lockAccents: true,
  imgZoom: 1, imgRot: 0, imgX: 0, imgY: 0,
  charge: "none", chargeSize: 0.8, chargeY: 0, chargeTint: false, c_charge: "#ffffff",
  starOn: false, c_star: "#d3a92c", starDensity: 90, starSeed: 7, starSparkles: true,
  lastPreset: "Bravo Blue",
};
let S = Object.assign({}, DEFAULTS);

/* images kept out of persisted state (can be large) */
let IMG = null; // {dataUrl, w, h}
let CUSTOM_CHARGE = null; // {dataUrl, w, h} — user-uploaded center charge

const PRESETS = [
  /* Official Bravo Fleet colors — bravofleet.com/graphics */
  { name:"Bravo Blue", group:"Official Bravo Fleet", colors:{c_band:"#2864a8",c_text:"#ffffff",c_edge:"#1b4574",c_delta:"#d3a92c",c_ring1:"#d3a92c",c_gap:"#1b4574",c_ring2:"#d3a92c",c_center:"#143356",c_star:"#d3a92c",c_charge:"#ffffff"} },
  { name:"Bolt Gold", group:"Official Bravo Fleet", colors:{c_band:"#d3a92c",c_text:"#14161c",c_edge:"#96781e",c_delta:"#14161c",c_ring1:"#14161c",c_gap:"#96781e",c_ring2:"#14161c",c_center:"#14161c",c_star:"#d3a92c",c_charge:"#d3a92c"} },
  { name:"TF17 Gray", group:"Official Bravo Fleet", colors:{c_band:"#434e5f",c_text:"#ffffff",c_edge:"#2a323e",c_delta:"#ffffff",c_ring1:"#ffffff",c_gap:"#2a323e",c_ring2:"#ffffff",c_center:"#1e242d",c_star:"#e8ecef",c_charge:"#434e5f"} },
  { name:"TF21 Purple", group:"Official Bravo Fleet", colors:{c_band:"#651060",c_text:"#ffffff",c_edge:"#40093d",c_delta:"#ffffff",c_ring1:"#ffffff",c_gap:"#40093d",c_ring2:"#ffffff",c_center:"#2e062b",c_star:"#f0e6ef",c_charge:"#651060"} },
  { name:"TF47 Orange", group:"Official Bravo Fleet", colors:{c_band:"#c64f1c",c_text:"#ffffff",c_edge:"#8a3512",c_delta:"#ffffff",c_ring1:"#ffffff",c_gap:"#8a3512",c_ring2:"#ffffff",c_center:"#5e240c",c_star:"#f5ece6",c_charge:"#c64f1c"} },
  { name:"TF72 Navy", group:"Official Bravo Fleet", colors:{c_band:"#20347f",c_text:"#ffffff",c_edge:"#142153",c_delta:"#ffffff",c_ring1:"#ffffff",c_gap:"#142153",c_ring2:"#ffffff",c_center:"#0e1839",c_star:"#e9ecf5",c_charge:"#20347f"} },
  { name:"TF86 Red", group:"Official Bravo Fleet", colors:{c_band:"#7c0309",c_text:"#ffffff",c_edge:"#4e0206",c_delta:"#ffffff",c_ring1:"#ffffff",c_gap:"#4e0206",c_ring2:"#ffffff",c_center:"#370204",c_star:"#f3e7e7",c_charge:"#7c0309"} },
  { name:"TF93 Green", group:"Official Bravo Fleet", colors:{c_band:"#1a4a3c",c_text:"#ffffff",c_edge:"#103028",c_delta:"#ffffff",c_ring1:"#ffffff",c_gap:"#103028",c_ring2:"#ffffff",c_center:"#0b221c",c_star:"#e7f0ed",c_charge:"#1a4a3c"} },
  /* Starfleet division colors — for inspiration, not official Bravo Fleet palettes */
  { name:"Command Red", group:"Department Inspired", colors:{c_band:"#a71313",c_text:"#ffffff",c_edge:"#6b0c0c",c_delta:"#ffffff",c_ring1:"#ffffff",c_gap:"#6b0c0c",c_ring2:"#ffffff",c_center:"#4b0909",c_star:"#f5e3e3",c_charge:"#a71313"} },
  { name:"Sciences Blue", group:"Department Inspired", colors:{c_band:"#2b53a7",c_text:"#ffffff",c_edge:"#1c356b",c_delta:"#ffffff",c_ring1:"#ffffff",c_gap:"#1c356b",c_ring2:"#ffffff",c_center:"#13254b",c_star:"#e9eef7",c_charge:"#2b53a7"} },
  { name:"Operations Gold", group:"Department Inspired", colors:{c_band:"#d6a444",c_text:"#14161c",c_edge:"#967330",c_delta:"#14161c",c_ring1:"#14161c",c_gap:"#967330",c_ring2:"#14161c",c_center:"#14161c",c_star:"#d6a444",c_charge:"#d6a444"} },
  { name:"Medical White", group:"Department Inspired", colors:{c_band:"#f4f6f8",c_text:"#1c2b3a",c_edge:"#b7c2cc",c_delta:"#1c2b3a",c_ring1:"#1c2b3a",c_gap:"#b7c2cc",c_ring2:"#1c2b3a",c_center:"#2f4a52",c_star:"#eef2f5",c_charge:"#f4f6f8"} },
  /* Hand-picked fictional themes, not tied to any real fleet's graphics */
  { name:"Sea Greens", group:"Other", colors:{c_band:"#175a6c",c_text:"#d8dee4",c_edge:"#0c3b48",c_delta:"#d8dee4",c_ring1:"#c9d1d9",c_gap:"#0c3b48",c_ring2:"#c9d1d9",c_center:"#0e2a33",c_star:"#d8dee4"} },
  { name:"Pegasus", group:"Other", colors:{c_band:"#9e2b25",c_text:"#ede6da",c_edge:"#141f4b",c_delta:"#ede6da",c_ring1:"#ede6da",c_gap:"#141f4b",c_ring2:"#ede6da",c_center:"#2a3e8c",c_star:"#ffffff"} },
  { name:"Federation", group:"Other", colors:{c_band:"#14245c",c_text:"#f4f6f8",c_edge:"#c9a227",c_delta:"#f4f6f8",c_ring1:"#c9a227",c_gap:"#c9a227",c_ring2:"#c9a227",c_center:"#14245c",c_star:"#e9c46a"} },
  { name:"Healer", group:"Other", colors:{c_band:"#2e6b7e",c_text:"#e8ecef",c_edge:"#dfe5e9",c_delta:"#e8ecef",c_ring1:"#dfe5e9",c_gap:"#dfe5e9",c_ring2:"#dfe5e9",c_center:"#4a5a6e",c_star:"#e8ecef"} },
];

/* Fourth Fleet task force operating lines — quick-fill for the bottom ring
   text, in place of "UNITED FEDERATION OF PLANETS" */
const TF_TEXT_PRESETS = [
  { tf:17, text:"FOURTH FLEET DEEP SPACE OPERATIONS" },
  { tf:21, text:"FOURTH FLEET FRONTIER OPERATIONS" },
  { tf:47, text:"FOURTH FLEET PATHFINDING OPERATIONS" },
  { tf:72, text:"FOURTH FLEET DIPLOMATIC OPERATIONS" },
  { tf:86, text:"FOURTH FLEET BORDER OPERATIONS" },
  { tf:93, text:"FOURTH FLEET HUMANITARIAN OPERATIONS" },
];

/* task-force emblem art, loaded by URL (files written by tools/embed_assets.py).
   Inlined as data URIs only at export time — see exportResources(). */
/*CHARGES_START*/const CHARGES = {
  "tf17b": {name:"Task Force 17", w:362, h:362, url:"assets/emblems/tf17b.png?v=444bb0c6"},
  "tf21b": {name:"Task Force 21", w:362, h:362, url:"assets/emblems/tf21b.png?v=ce55b01a"},
  "tf47b": {name:"Task Force 47", w:362, h:362, url:"assets/emblems/tf47b.png?v=0f18192a"},
  "tf72b": {name:"Task Force 72", w:362, h:362, url:"assets/emblems/tf72b.png?v=74ee1716"},
  "tf86b": {name:"Task Force 86", w:362, h:362, url:"assets/emblems/tf86b.png?v=9431a1bc"},
  "tf93b": {name:"Task Force 93", w:362, h:362, url:"assets/emblems/tf93b.png?v=6758739f"}
};/*CHARGES_END*/

/* ================= geometry / render ================= */
const VB = 1000, CX = 500, CY = 500, R_OUT = 496;


function starfieldSVG(centerR){
  if(!S.starOn) return "";
  const rnd = StateKit.mulberry32(S.starSeed);
  /* four-point sparkles are placed first (kept apart from each other) so the
     small-star scatter can dodge them; attempt caps keep the loops bounded */
  const sparkles = [];
  if(S.starSparkles){
    const nspark = 3 + Math.floor(rnd()*3);
    for(let i=0;i<nspark;i++){
      for(let t=0;t<60;t++){
        const a = rnd()*Math.PI*2, rr = Math.sqrt(rnd())*centerR*0.6;
        const x = Math.cos(a)*rr, y = Math.sin(a)*rr;
        const s = centerR*(0.12+rnd()*0.22);
        if(sparkles.every(o => Math.hypot(o.x-x, o.y-y) > (o.s+s)*0.85)){ sparkles.push({x,y,s}); break; }
      }
    }
  }
  let out = `<g fill="${S.c_star}">`;
  const n = S.starDensity;
  for(let i=0;i<n;i++){
    for(let t=0;t<40;t++){
      // rejection-sample inside circle, biased into a loose diagonal band
      const a = rnd()*Math.PI*2, rr = Math.sqrt(rnd())*centerR*0.92;
      const x = Math.cos(a)*rr, y = Math.sin(a)*rr;
      const band = (x+y)*0.5;
      if(Math.abs(band) > centerR*0.55 && rnd()<0.6) continue;
      const sz = 2 + rnd()*rnd()*9;
      if(sparkles.some(o => Math.hypot(o.x-x, o.y-y) < o.s + sz + 3)) continue;
      out += `<circle cx="${(CX+x).toFixed(1)}" cy="${(CY+y).toFixed(1)}" r="${sz.toFixed(1)}"/>`;
      break;
    }
  }
  for(const o of sparkles){
    const x = CX+o.x, y = CY+o.y, s = o.s, w = s*0.18;
    out += `<path d="M ${x} ${y-s} Q ${x+w*0.35} ${y-w} ${x+s} ${y} Q ${x+w*0.35} ${y+w} ${x} ${y+s} Q ${x-w*0.35} ${y+w} ${x-s} ${y} Q ${x-w*0.35} ${y-w} ${x} ${y-s} Z"/>`;
  }
  out += "</g>";
  return out;
}

/* separator glyphs pointing up; delta is drawn centered on origin, the bolt
   (from bf-bolt-white.svg) carries its own viewBox center in cx/cy */
const SEPARATORS = {
  delta:   { d: "M 0,-30 C 7,-16 14,2 21,24 C 13,11 6,8 0,8 C -6,8 -13,11 -21,24 C -14,2 -7,-16 0,-30 Z", scale: 1, cx: 0, cy: 0 },
  bolt:    { d: "M 1.82,239.05 L 20.8,122.5 L 0,122.17 L 64.55,0 L 45.4,115.23 L 66.7,115.23 Z", scale: 0.29, cx: 33.35, cy: 119.52 },
  sparkle: { d: "M 0,-30 Q 1.9,-5.4 30,0 Q 1.9,5.4 0,30 Q -1.9,5.4 -30,0 Q -1.9,-5.4 0,-30 Z", scale: 1, cx: 0, cy: 0 },
  circle:  { d: "M 0,-21 A 21 21 0 1 1 0,21 A 21 21 0 1 1 0,-21 Z", scale: 1, cx: 0, cy: 0 },
};

/* res is undefined for the live preview (assets referenced by URL) and, for
   exports, the { fontCSS, map } from exportResources() — map turning each
   asset URL into the data URI the rasterised SVG needs. */
function buildSVG(res){
  const uri = u => (res && res.map[u]) || u;
  const r_band_out = R_OUT - S.edgeW;                   // inner edge of thick outer ring
  const r_band_in  = r_band_out - S.bandW;              // outer edge of thick inner ring
  const centerR    = Math.max(40, r_band_in - S.gapW);
  const bandMid    = (r_band_out + r_band_in) / 2;
  /* accent rings float inside the band, inset from the thick rings */
  const rAccentA   = r_band_out - S.inset - S.ring1W/2;
  const rAccentB   = r_band_in + S.inset + S.ring2W/2;
  const fs = S.fontSize;
  const topR = bandMid - fs*0.36;      // baseline radius, glyphs grow outward
  const botR = bandMid + fs*0.36;      // baseline radius, glyphs grow inward

  const topText = S.uppercase ? S.topText.toUpperCase() : S.topText;
  const botText = S.uppercase ? S.bottomText.toUpperCase() : S.bottomText;

  const esc = s => s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");

  /* image transform */
  let imageSVG = "";
  if(IMG){
    const D = centerR*2;
    const base = D/Math.min(IMG.w, IMG.h);
    const sc = base*S.imgZoom;
    const w = IMG.w*sc, h = IMG.h*sc;
    const x = CX - w/2 + S.imgX, y = CY - h/2 + S.imgY;
    imageSVG = `<g transform="rotate(${S.imgRot} ${CX} ${CY})">` +
      `<image href="${IMG.dataUrl}" x="${x.toFixed(1)}" y="${y.toFixed(1)}" width="${w.toFixed(1)}" height="${h.toFixed(1)}" preserveAspectRatio="none"/></g>`;
  }

  /* separator groups: sepCount glyphs per side spaced along the band's track,
     mirrored about the vertical axis; sepShift slides both groups up the track.
     A glyph at track angle φ is rotated by φ so the group leans with the curve;
     sepRot is applied in that local frame (0°=up, ±90°=outward, both sides). */
  let deltas = "";
  const sepVec = SEPARATORS[S.sepStyle];
  const sepTF = S.sepStyle.startsWith("tf:") ? CHARGES[S.sepStyle.slice(3)] : null;
  if(S.sepStyle !== "none" && (sepVec || sepTF)){
    /* TF-emblem separators are PNGs: sized to match the delta's ~54-unit
       height and tinted to the separator color via #sepTintF */
    let mkGlyph;
    if(sepVec){
      const k = S.deltaSize * sepVec.scale;
      mkGlyph = () => `<path d="${sepVec.d}" transform="scale(${k}) translate(${-sepVec.cx} ${-sepVec.cy})"/>`;
    } else {
      const base = 54 * S.deltaSize / Math.max(sepTF.w, sepTF.h);
      const w = sepTF.w * base, h = sepTF.h * base;
      mkGlyph = () => `<image href="${uri(sepTF.url)}" x="${(-w/2).toFixed(1)}" y="${(-h/2).toFixed(1)}" width="${w.toFixed(1)}" height="${h.toFixed(1)}" filter="url(#sepTintF)"/>`;
    }
    const mk = (phi, rot) => {
      const a = phi * Math.PI/180;
      const x = CX + bandMid*Math.cos(a), y = CY + bandMid*Math.sin(a);
      return `<g transform="translate(${x.toFixed(2)} ${y.toFixed(2)}) rotate(${rot.toFixed(2)})">${mkGlyph()}</g>`;
    };
    const rotL = S.sepMirror ? S.sepRot : S.sepRotL;
    let out = "";
    for(let i=0;i<S.sepCount;i++){
      const d = (i - (S.sepCount-1)/2) * S.sepSpacing;
      const phiR = -S.sepShift + d;          // right group, centered on 3 o'clock
      const phiL = 180 + S.sepShift - d;     // mirrored partner on the left
      out += mk(phiR, phiR + S.sepRot) + mk(phiL, (phiL - 180) - rotL);
    }
    deltas = `<g fill="${S.c_delta}">` + out + `</g>`;
  }

  /* center charge: BF bolt (vector) or an embedded task-force emblem (PNG) */
  let chargeSVG = "";
  if(S.charge === "bolt"){
    const g = SEPARATORS.bolt;
    const k = (centerR * 2 * S.chargeSize) / 239.05;
    const fill = S.chargeTint ? S.c_charge : "#ffffff";
    chargeSVG = `<g fill="${fill}"><path d="${g.d}" transform="translate(${CX} ${CY + S.chargeY}) scale(${k}) translate(${-g.cx} ${-g.cy})"/></g>`;
  } else if(S.charge !== "none" && CHARGES[S.charge]){
    const c = CHARGES[S.charge];
    const base = (centerR * 2 * S.chargeSize) / Math.max(c.w, c.h);
    const w = c.w * base, h = c.h * base;
    const filt = S.chargeTint ? ` filter="url(#chargeTintF)"` : "";
    chargeSVG = `<image href="${uri(c.url)}" x="${(CX - w/2).toFixed(1)}" y="${(CY + S.chargeY - h/2).toFixed(1)}" width="${w.toFixed(1)}" height="${h.toFixed(1)}"${filt}/>`;
  } else if(S.charge === "custom" && CUSTOM_CHARGE){
    const c = CUSTOM_CHARGE;
    const base = (centerR * 2 * S.chargeSize) / Math.max(c.w, c.h);
    const w = c.w * base, h = c.h * base;
    const filt = S.chargeTint ? ` filter="url(#chargeTintF)"` : "";
    chargeSVG = `<image href="${c.dataUrl}" x="${(CX - w/2).toFixed(1)}" y="${(CY + S.chargeY - h/2).toFixed(1)}" width="${w.toFixed(1)}" height="${h.toFixed(1)}"${filt}/>`;
  }

  return `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 ${VB} ${VB}">
<defs>
${res ? `<style>${res.fontCSS}</style>` : ""}
<clipPath id="centerClip"><circle cx="${CX}" cy="${CY}" r="${centerR}"/></clipPath>
<filter id="chargeTintF"><feFlood flood-color="${S.c_charge}"/><feComposite in2="SourceAlpha" operator="in"/></filter>
<filter id="sepTintF"><feFlood flood-color="${S.c_delta}"/><feComposite in2="SourceAlpha" operator="in"/></filter>
<path id="arcTop" d="M ${CX-topR} ${CY} A ${topR} ${topR} 0 0 1 ${CX+topR} ${CY}"/>
<path id="arcBot" d="M ${CX-botR} ${CY} A ${botR} ${botR} 0 0 0 ${CX+botR} ${CY}"/>
</defs>
<circle cx="${CX}" cy="${CY}" r="${R_OUT}" fill="${S.c_edge}"/>
<circle cx="${CX}" cy="${CY}" r="${r_band_out}" fill="${S.c_band}"/>
<circle cx="${CX}" cy="${CY}" r="${r_band_in}" fill="${S.c_gap}"/>
<circle cx="${CX}" cy="${CY}" r="${centerR}" fill="${S.c_center}"/>
${S.ring1W > 0 ? `<circle cx="${CX}" cy="${CY}" r="${rAccentA}" fill="none" stroke="${S.c_ring1}" stroke-width="${S.ring1W}"/>` : ""}
${S.ring2W > 0 ? `<circle cx="${CX}" cy="${CY}" r="${rAccentB}" fill="none" stroke="${S.c_ring2}" stroke-width="${S.ring2W}"/>` : ""}
<g clip-path="url(#centerClip)">${starfieldSVG(centerR)}${imageSVG}${chargeSVG}</g>
<g fill="${S.c_text}" font-family="Sealstile, Michroma, sans-serif" font-size="${fs}" letter-spacing="${S.letterSpacing}">
<text text-anchor="middle"><textPath href="#arcTop" startOffset="50%">${esc(topText)}</textPath></text>
<text text-anchor="middle"><textPath href="#arcBot" startOffset="50%">${esc(botText)}</textPath></text>
</g>
${deltas}
</svg>`;
}

/* ================= live preview ================= */
const wrap = document.getElementById("sealwrap");
function render(){
  wrap.innerHTML = buildSVG();
  wrap.classList.toggle("grabbable", !!IMG);
  persist();
}

/* ================= controls wiring ================= */
const $ = id => document.getElementById(id);
$("copyYear").textContent = new Date().getFullYear();

const textInputs = ["topText","bottomText"];
const checkInputs = ["uppercase","starOn","starSparkles","chargeTint","sepMirror","lockRings","lockAccents"];
const selectInputs = ["sepStyle","charge"];
const rangeInputs = [
  ["fontSize",1],["letterSpacing",1],["deltaSize",2],
  ["sepCount",0],["sepSpacing",1],["sepShift",0],["sepRot",0],["sepRotL",0],
  ["edgeW",0],["bandW",0],["ring1W",1],["ring2W",1],["inset",0],["gapW",0],
  ["imgZoom",2],["imgRot",0],["chargeSize",2],["chargeY",0],["starDensity",0],
];
const colorInputs = ["c_band","c_text","c_edge","c_delta","c_ring1","c_gap","c_ring2","c_center","c_star","c_charge"];

function syncUI(){
  textInputs.forEach(id => $(id).value = S[id]);
  checkInputs.forEach(id => $(id).checked = S[id]);
  selectInputs.forEach(id => $(id).value = S[id]);
  rangeInputs.forEach(([id,dec]) => { $(id).value = S[id]; const v=$(id+"V"); if(v) v.textContent = Number(S[id]).toFixed(dec); });
  colorInputs.forEach(id => $(id).value = S[id]);
  $("sepRotL").disabled = S.sepMirror;
  $("chargeUpload").style.display = S.charge === "custom" ? "" : "none";
}

textInputs.forEach(id => $(id).addEventListener("input", e => { S[id]=e.target.value; render(); }));
checkInputs.forEach(id => $(id).addEventListener("change", e => { S[id]=e.target.checked; render(); }));
selectInputs.forEach(id => $(id).addEventListener("change", e => { S[id]=e.target.value; render(); }));
$("charge").addEventListener("change", () => { $("chargeUpload").style.display = S.charge === "custom" ? "" : "none"; });

/* unlinking the mirror seeds the left angle from the shared one so nothing jumps */
$("sepMirror").addEventListener("change", e => {
  if(!e.target.checked) S.sepRotL = S.sepRot;
  syncUI(); render();
});

/* charge + separator dropdown options */
{
  const sel = $("charge");
  const opts = [["none","None"],["bolt","BF Bolt"],["custom","Custom image…"]].concat(Object.keys(CHARGES).map(k => [k, CHARGES[k].name]));
  opts.forEach(([v,l]) => { const o = document.createElement("option"); o.value=v; o.textContent=l; sel.appendChild(o); });

  const ssel = $("sepStyle");
  const sopts = [["delta","Starfleet delta"],["bolt","BF bolt"],["sparkle","Sparkle"],["circle","Circle"]]
    .concat(Object.keys(CHARGES).map(k => ["tf:"+k, CHARGES[k].name]))
    .concat([["none","None"]]);
  sopts.forEach(([v,l]) => { const o = document.createElement("option"); o.value=v; o.textContent=l; ssel.appendChild(o); });
}
/* width locks: dragging one slider of a locked pair drags its partner,
   clamped to the partner's own range */
const LOCK_PAIRS = {
  edgeW:  {partner:"gapW",   flag:"lockRings"},
  gapW:   {partner:"edgeW",  flag:"lockRings"},
  ring1W: {partner:"ring2W", flag:"lockAccents"},
  ring2W: {partner:"ring1W", flag:"lockAccents"},
};
rangeInputs.forEach(([id,dec]) => $(id).addEventListener("input", e => {
  S[id]=parseFloat(e.target.value);
  const v=$(id+"V"); if(v) v.textContent = S[id].toFixed(dec);
  const lp = LOCK_PAIRS[id];
  if(lp && S[lp.flag]){
    const el = $(lp.partner);
    const val = Math.min(parseFloat(el.max), Math.max(parseFloat(el.min), S[id]));
    S[lp.partner] = val; el.value = val;
    const pv = $(lp.partner+"V"); if(pv) pv.textContent = val.toFixed(dec);
  }
  render();
}));
colorInputs.forEach(id => $(id).addEventListener("input", e => { S[id]=e.target.value; render(); }));

/* ---------- swatch popover ----------
   Clicking a color well opens a swatch picker (active preset's colors + the
   full palette across every preset) instead of the native color picker;
   "Custom color…" falls through to the native one. */
const pop = document.createElement("div");
pop.id = "swatchpop";
document.body.appendChild(pop);
let popFor = null, nativeBypass = false;

function paletteAll(){
  const out = [];
  PRESETS.forEach(p => Object.values(p.colors).forEach(c => { if(!out.includes(c)) out.push(c); }));
  return out;
}
function swatchSection(title, colors){
  const cur = (S[popFor]||"").toLowerCase();
  return `<h3>${title}</h3><div class="swgrid">` +
    colors.map(c => `<button data-c="${c}" class="${c.toLowerCase()===cur?"cur":""}" style="background:${c}" title="${c}"></button>`).join("") +
    `</div>`;
}
function openPop(id, anchor){
  popFor = id;
  const preset = PRESETS.find(p => p.name === S.lastPreset);
  pop.innerHTML =
    (preset ? swatchSection("Preset · "+preset.name, [...new Set(Object.values(preset.colors))]) : "") +
    swatchSection("Palette", paletteAll()) +
    `<button class="btn custom" id="swCustom">Custom color…</button>`;
  const r = anchor.getBoundingClientRect();
  pop.style.display = "block";
  pop.style.left = Math.max(8, Math.min(r.left, innerWidth - pop.offsetWidth - 12)) + "px";
  const below = r.bottom + 6;
  pop.style.top = (below + pop.offsetHeight > innerHeight - 8
    ? Math.max(8, r.top - pop.offsetHeight - 6) : below) + "px";
}
function closePop(){ pop.style.display = "none"; popFor = null; }

colorInputs.forEach(id => $(id).addEventListener("click", e => {
  if(nativeBypass) return;
  e.preventDefault();
  openPop(id, e.target);
}));
pop.addEventListener("click", e => {
  const b = e.target.closest("button"); if(!b || !popFor) return;
  if(b.id === "swCustom"){
    const inp = $(popFor); closePop();
    nativeBypass = true;
    try{ inp.showPicker ? inp.showPicker() : inp.click(); } finally { nativeBypass = false; }
    return;
  }
  S[popFor] = b.dataset.c; $(popFor).value = b.dataset.c;
  pop.querySelectorAll(".cur").forEach(x => x.classList.remove("cur"));
  pop.querySelectorAll(`[data-c="${b.dataset.c}"]`).forEach(x => x.classList.add("cur"));
  render();
});
document.addEventListener("pointerdown", e => {
  if(pop.style.display === "block" && !pop.contains(e.target)) closePop();
});
document.addEventListener("keydown", e => { if(e.key === "Escape") closePop(); });

/* presets, grouped into labeled sections by p.group (array order = display order) */
const presetBox = $("presets");
let presetGroupList = null, presetGroupSeen = null;
PRESETS.forEach(p => {
  if(p.group !== presetGroupSeen){
    presetGroupSeen = p.group;
    const label = document.createElement("div");
    label.className = "presets-group";
    label.textContent = presetGroupSeen;
    presetBox.appendChild(label);
    presetGroupList = document.createElement("div");
    presetGroupList.className = "presets";
    presetBox.appendChild(presetGroupList);
  }
  const b = document.createElement("button");
  b.className = "preset";
  const sw = ["c_edge","c_band","c_text","c_center"].map(k=>`<i style="background:${p.colors[k]}"></i>`).join("");
  b.innerHTML = `<span class="sw">${sw}</span>${p.name}`;
  b.addEventListener("click", ()=>{
    Object.assign(S, p.colors);
    S.edgeW = 20; S.gapW = 20; // presets frame the band with matching outer/inner ring widths
    S.lastPreset = p.name; syncUI(); render();
  });
  presetGroupList.appendChild(b);
});

const GEOMETRY_KEYS = ["edgeW","bandW","ring1W","ring2W","inset","gapW"];
$("resetGeom").addEventListener("click", ()=>{
  GEOMETRY_KEYS.forEach(k => S[k] = DEFAULTS[k]);
  syncUI(); render();
});

$("resetBtn").addEventListener("click", ()=>{
  S = Object.assign({}, DEFAULTS);
  syncUI(); render();
});

/* ---------- TF text presets ----------
   Quick-fill for the bottom ring text; shrinks font size (and letter
   spacing, in step) until the rendered text clears the band's curve
   without crowding the separator groups near 3/9 o'clock. */
{
  const sel = $("tfTextPreset");
  const ph = document.createElement("option");
  ph.value = ""; ph.textContent = "Apply TF text…";
  sel.appendChild(ph);
  TF_TEXT_PRESETS.forEach(p => {
    const o = document.createElement("option");
    o.value = String(p.tf); o.textContent = "TF" + p.tf + " — " + p.text;
    sel.appendChild(o);
  });
  sel.addEventListener("change", () => {
    const p = TF_TEXT_PRESETS.find(x => String(x.tf) === sel.value);
    sel.value = "";
    if(!p) return;
    S.bottomText = p.text;
    S.fontSize = DEFAULTS.fontSize;
    S.letterSpacing = DEFAULTS.letterSpacing;
    fitBottomText();
    syncUI(); render();
  });
}

function fitBottomText(){
  const MIN_FS = 28; // slider floor
  for(let i=0;i<25;i++){
    render();
    const svg = wrap.querySelector("svg");
    const arc = svg.getElementById("arcBot");
    const tp = svg.querySelectorAll("g > text > textPath")[1];
    const pathLen = arc.getTotalLength();
    const textLen = tp.getComputedTextLength();
    const limit = pathLen * 0.86; // leaves clearance for the separator groups near the ends
    if(textLen <= limit || S.fontSize <= MIN_FS) break;
    const scale = Math.max(0.9, limit / textLen);
    S.fontSize = Math.max(MIN_FS, Math.round(S.fontSize * scale));
    S.letterSpacing = Math.max(0, Math.round(S.letterSpacing * scale * 2) / 2);
  }
}

/* ================= image handling ================= */
const filebox = $("filebox"), fileInput = $("fileInput");
filebox.addEventListener("click", ()=>fileInput.click());
fileInput.addEventListener("change", e => { if(e.target.files[0]) loadImageFile(e.target.files[0]); });
["dragover","dragenter"].forEach(ev => {
  filebox.addEventListener(ev, e=>{e.preventDefault();filebox.classList.add("drag");});
  wrap.addEventListener(ev, e=>e.preventDefault());
});
["dragleave","drop"].forEach(ev => filebox.addEventListener(ev, e=>{e.preventDefault();filebox.classList.remove("drag");}));
filebox.addEventListener("drop", e => { if(e.dataTransfer.files[0]) loadImageFile(e.dataTransfer.files[0]); });
wrap.addEventListener("drop", e => { e.preventDefault(); if(e.dataTransfer.files[0]) loadImageFile(e.dataTransfer.files[0]); });

function loadImageFile(file){
  const rd = new FileReader();
  rd.onload = () => {
    const im = new Image();
    im.onload = () => {
      IMG = { dataUrl: rd.result, w: im.naturalWidth, h: im.naturalHeight };
      S.imgZoom = 1; S.imgX = 0; S.imgY = 0; S.imgRot = 0;
      filebox.textContent = file.name + " · " + im.naturalWidth + "×" + im.naturalHeight;
      syncUI(); render();
    };
    im.src = rd.result;
  };
  rd.readAsDataURL(file);
}
$("imgRemove").addEventListener("click", ()=>{
  IMG = null; filebox.textContent = "Drop an image here or click to choose"; render();
});
$("imgCenter").addEventListener("click", ()=>{ S.imgX=0; S.imgY=0; render(); });

/* ---------- custom center charge upload ---------- */
const chargeFilebox = $("chargeFilebox"), chargeFileInput = $("chargeFileInput");
chargeFilebox.addEventListener("click", ()=>chargeFileInput.click());
chargeFileInput.addEventListener("change", e => { if(e.target.files[0]) loadChargeImageFile(e.target.files[0]); });
["dragover","dragenter"].forEach(ev => chargeFilebox.addEventListener(ev, e=>{e.preventDefault();chargeFilebox.classList.add("drag");}));
["dragleave","drop"].forEach(ev => chargeFilebox.addEventListener(ev, e=>{e.preventDefault();chargeFilebox.classList.remove("drag");}));
chargeFilebox.addEventListener("drop", e => { if(e.dataTransfer.files[0]) loadChargeImageFile(e.dataTransfer.files[0]); });

function loadChargeImageFile(file){
  const rd = new FileReader();
  rd.onload = () => {
    const im = new Image();
    im.onload = () => {
      CUSTOM_CHARGE = { dataUrl: rd.result, w: im.naturalWidth, h: im.naturalHeight };
      chargeFilebox.textContent = file.name + " · " + im.naturalWidth + "×" + im.naturalHeight;
      render();
    };
    im.src = rd.result;
  };
  rd.readAsDataURL(file);
}
$("chargeImgRemove").addEventListener("click", ()=>{
  CUSTOM_CHARGE = null; chargeFilebox.textContent = "Drop an image here or click to choose"; render();
});

/* drag-to-pan + scroll-zoom over the preview */
let dragging = null;
wrap.addEventListener("pointerdown", e => {
  if(!IMG) return;
  dragging = { x:e.clientX, y:e.clientY, ix:S.imgX, iy:S.imgY };
  wrap.classList.add("grabbing");
  wrap.setPointerCapture(e.pointerId);
});
wrap.addEventListener("pointermove", e => {
  if(!dragging) return;
  const scale = VB / wrap.getBoundingClientRect().width;
  S.imgX = dragging.ix + (e.clientX - dragging.x)*scale;
  S.imgY = dragging.iy + (e.clientY - dragging.y)*scale;
  render();
});
["pointerup","pointercancel"].forEach(ev => wrap.addEventListener(ev, ()=>{
  dragging = null; wrap.classList.remove("grabbing");
}));
wrap.addEventListener("wheel", e => {
  if(!IMG) return;
  e.preventDefault();
  S.imgZoom = Math.min(4, Math.max(0.4, S.imgZoom * (e.deltaY < 0 ? 1.05 : 0.95)));
  syncUI(); render();
}, {passive:false});

/* starfield reroll */
$("starReroll").addEventListener("click", ()=>{ S.starSeed = Math.floor(Math.random()*1e9); render(); });

/* ================= export ================= */
const slug = () => StateKit.slugify(S.topText, "seal");
/* Pull every asset the current design actually uses into data URIs. An SVG
   rasterised through an <img> can't fetch, so this runs before any export. */
async function exportResources(){
  const urls = [];
  if(S.sepStyle.startsWith("tf:") && CHARGES[S.sepStyle.slice(3)])
    urls.push(CHARGES[S.sepStyle.slice(3)].url);
  if(CHARGES[S.charge]) urls.push(CHARGES[S.charge].url);
  const [map, font] = await Promise.all([
    ExportKit.fetchDataURIs(urls),
    ExportKit.fetchDataURI(FONT_URL),
  ]);
  return { map, fontCSS: ExportKit.fontFaceRule("Sealstile", 400, "normal", font) };
}

async function renderToCanvas(size){
  return ExportKit.svgToCanvas(buildSVG(await exportResources()), size, size);
}

ExportKit.wireExport($("exportPng"), async ()=>{
  const size = parseInt($("exportSize").value, 10);
  const cv = await renderToCanvas(size);
  await ExportKit.downloadCanvasPNG(cv, slug()+"-"+size+".png");
});
ExportKit.wireExport($("exportSvg"), async ()=>{
  ExportKit.downloadSVG(buildSVG(await exportResources()), slug()+".svg");
}, {busy:"Building…"});

/* ================= persistence ================= */
const LS_KEY = "sealbuilder-v5";
const persist = StateKit.persister(LS_KEY, () => S, 300);
try{
  const saved = localStorage.getItem(LS_KEY);
  if(saved){
    S = Object.assign({}, DEFAULTS, JSON.parse(saved));
  }else{
    /* v4 and earlier were designed against Microstyle; Sealstile runs ~13%
       wider at equal size, so scale text metrics on the one-time migration */
    const old = localStorage.getItem("sealbuilder-v4") || localStorage.getItem("sealbuilder-v3") || localStorage.getItem("sealbuilder-v2");
    if(old){
      S = Object.assign({}, DEFAULTS, JSON.parse(old));
      S.fontSize = Math.round(S.fontSize * 46/52);
      S.letterSpacing = Math.round(S.letterSpacing * 5/8 * 2) / 2;
    }
  }
}catch(e){}

/* ================= stage background preview ================= */
Stage.init();

/* ================= boot ================= */
document.fonts.load('16px "Sealstile"').then(()=>render());
syncUI();
render();
</script>
</body>
</html>

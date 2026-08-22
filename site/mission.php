<?php require __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Build mission posters and cover art from your own artwork, and export them at print resolution.">
<link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2032%2032%22%3E%3Crect%20width%3D%2232%22%20height%3D%2232%22%20rx%3D%225%22%20fill%3D%22%230d1420%22%2F%3E%3Crect%20x%3D%227%22%20y%3D%224%22%20width%3D%2218%22%20height%3D%2224%22%20rx%3D%222%22%20fill%3D%22%232864a8%22%2F%3E%3Crect%20x%3D%229%22%20y%3D%227%22%20width%3D%2214%22%20height%3D%223%22%20fill%3D%22%23d3a92c%22%2F%3E%3Crect%20x%3D%229%22%20y%3D%2222%22%20width%3D%2214%22%20height%3D%223%22%20fill%3D%22%23d3a92c%22%2F%3E%3C%2Fsvg%3E">
<title>Mission Poster Builder</title>
<link rel="stylesheet" href="shared/chrome.css?v=d62425d5">
<style>
  :root{--panel-w:352px}
  @font-face{font-family:"Sealstile";font-weight:400;src:url(fonts/sealstile.woff2) format("woff2")}
  @font-face{font-family:"Cinzel";font-weight:600;src:url(fonts/webfonts/cinzel.woff2) format("woff2")}
  @font-face{font-family:"Cinzel Decorative";font-weight:700;src:url(fonts/webfonts/cinzeldecor-700.woff2) format("woff2")}
  @font-face{font-family:"Playfair Display";font-weight:700;src:url(fonts/webfonts/playfair-700.woff2) format("woff2")}
  @font-face{font-family:"Cormorant Garamond";font-weight:600;src:url(fonts/webfonts/cormorant-600.woff2) format("woff2")}
  @font-face{font-family:"EB Garamond";font-weight:600;src:url(fonts/webfonts/ebgaramond-600.woff2) format("woff2")}
  @font-face{font-family:"EB Garamond";font-weight:500;font-style:italic;src:url(fonts/webfonts/ebgaramond-it500.woff2) format("woff2")}
  @font-face{font-family:"Tenor Sans";font-weight:400;src:url(fonts/webfonts/tenor.woff2) format("woff2")}
  @font-face{font-family:"Bebas Neue";font-weight:400;src:url(fonts/webfonts/bebas.woff2) format("woff2")}
  @font-face{font-family:"Oswald";font-weight:500;src:url(fonts/webfonts/oswald-500.woff2) format("woff2")}
  @font-face{font-family:"Anton";font-weight:400;src:url(fonts/webfonts/anton.woff2) format("woff2")}
  @font-face{font-family:"Michroma";font-weight:400;src:url(fonts/webfonts/michroma.woff2) format("woff2")}
  @font-face{font-family:"Orbitron";font-weight:600;src:url(fonts/webfonts/orbitron.woff2) format("woff2")}
  header .mark{
    width:17px;height:23px;flex:none;border:2px solid var(--bolt-gold);
    background:linear-gradient(180deg,#16233c,#0d1626);
    box-shadow:inset 0 -7px 0 -4px var(--bf-blue);
  }
  .btn.mini{padding:5px 9px;font-size:10px}
  /* a text row whose slot is switched off dims its own summary */
  details.acc.rowoff > summary{color:#3c4a5e}
  textarea{
    width:100%;background:#0a111c;border:1px solid var(--line-bright);color:var(--ink);
    font:inherit;padding:7px 9px;letter-spacing:.05em;border-radius:2px;margin-bottom:8px;
    resize:vertical;line-height:1.5;
  }
  input[type=text]:focus,textarea:focus{outline:none;border-color:var(--bf-blue)}
  .sublabel{color:var(--ink-dim);font-size:10px;letter-spacing:.14em;text-transform:uppercase;margin:12px 0 6px}
  .sublabel:first-child{margin-top:0}
  .filebox{
    border:1px dashed var(--line-bright);border-radius:2px;padding:14px 10px;
    text-align:center;color:var(--ink-dim);font-size:11px;cursor:pointer;margin-bottom:8px;
    line-height:1.5;word-break:break-all;
  }
  .filebox:hover,.filebox.drag{border-color:var(--bolt-gold);color:var(--bolt-gold)}
  .seg{display:flex;border:1px solid var(--line-bright);border-radius:2px;overflow:hidden;margin-bottom:8px}
  .seg button{
    flex:1;background:var(--panel2);border:none;color:var(--ink-dim);cursor:pointer;
    font:inherit;font-size:11px;letter-spacing:.1em;text-transform:uppercase;padding:8px 6px;
  }
  .seg button + button{border-left:1px solid var(--line-bright)}
  .seg button:hover{color:var(--ink)}
  .seg button.on{background:var(--bolt-gold);color:#14100a}
  .swgroup{margin-bottom:10px}
  .swgroup:last-child{margin-bottom:0}
  .swgroup-label{color:var(--ink-dim);font-size:10px;letter-spacing:.14em;text-transform:uppercase;margin-bottom:6px}
  .swatches{display:flex;gap:5px;flex-wrap:wrap}
  .sw{
    width:34px;height:26px;border-radius:2px;border:1px solid var(--line-bright);
    cursor:pointer;padding:0;overflow:hidden;display:block;
  }
  .sw:hover{border-color:var(--bolt-gold)}
  .sw.on{border-color:var(--bolt-gold);box-shadow:0 0 0 1px var(--bolt-gold)}
  .off{opacity:.4}

  #stage{
    flex:1;display:flex;align-items:center;justify-content:center;position:relative;
    min-width:0;background-color:#10151d;
  }
  /* The wrap fills the stage and centres the poster itself, rather than being
     sized by it: a flex item whose width comes from a percentage height on a
     replaced child is exactly the case Safari resolves to the SVG's intrinsic
     1800px, which overflowed the stage and left the poster flush to the panel.
     A 100%x100% box can't be mis-sized, and max-height/max-width on the SVG
     scale it inside that box in every engine. */
  #posterwrap{
    position:relative;z-index:1;width:100%;height:100%;
    padding:22px 22px 40px;
    display:flex;align-items:center;justify-content:center;
    filter:drop-shadow(0 10px 26px rgba(0,0,0,.55));
  }
  #posterwrap svg{
    width:auto;height:auto;max-width:100%;max-height:min(100%, 1160px);display:block;
  }
</style>
</head>
<body>
<noscript><div class="noscript"><b>Mission Poster Builder</b> needs JavaScript switched on — it draws and exports your artwork entirely in the browser, with nothing sent to a server.</div></noscript>

<header>
  <div class="mark"></div>
  <h1>Mission Poster Builder</h1>
  <div class="sub">Bravo Fleet · Mission Posters &amp; Cover Art</div>
  <div class="spacer"></div>
  <a class="navlink" href="/">⌂</a>
  <a class="navlink" href="/seal">Seal →</a>
  <a class="navlink" href="/header">Header →</a>
  <a class="navlink" href="/banner">Banner →</a>
  <a class="navlink" href="/plaque">Plaque →</a>
  <a class="navlink" href="/patch">Patch →</a>
</header>

<main>
  <div id="panel">

    <details class="acc" open>
      <summary>Format</summary>
      <div class="acc-body">
        <div class="seg" data-seg="ratio">
          <button data-v="poster">3:4</button>
          <button data-v="sheet">2:3</button>
          <button data-v="wide">4:5</button>
          <button data-v="square">1:1</button>
        </div>
        <div class="row"><label>Side margin</label><input type="range" min="0" max="360" step="2" data-k="margin"><span class="val" data-val="margin"></span></div>
        <div class="hint"><b>3:4</b> is the source artwork's shape (18×24in at 300dpi). <b>2:3</b> covers the classic one-sheet and a 6×9 novel; <b>4:5</b> is a 16×20 print; <b>1:1</b> is for a square thumbnail or an avatar crop. Every text line shrinks to fit inside the side margin.</div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Styles</summary>
      <div class="acc-body">
        <div id="presetGroups"></div>
        <div class="hint">A style sets the type's fill, relief and effects across every line, plus the frame. Your text, fonts and layout are left alone.</div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Colour Scheme</summary>
      <div class="acc-body">
        <div class="seg" data-seg="schemeMode" id="schemeModeSeg">
          <button data-v="std">Standard</button>
          <button data-v="swap">Swapped</button>
          <button data-v="inv">Inverted</button>
        </div>
        <div id="schemeGroups"></div>
        <div class="hint">The Seal Builder's full palette. A scheme only moves colours — the type's ink and fade, the outline and glow, the frame, the backdrop, the stars and the scrims — so pick a <b>style</b> for the look and a scheme to colour it, in either order. Each scheme reads three ways: <b>Standard</b> puts the light accent in the type over the theme colour's outline, <b>Swapped</b> trades those two round so the type itself carries the theme colour, and <b>Inverted</b> turns the whole poster over — pale field, dark type, white scrims. The swatches preview whichever reading is picked.</div>
      </div>
    </details>

    <div id="textRows"></div>

    <details class="acc">
      <summary>Text Effects</summary>
      <div class="acc-body">
        <div class="hint" style="margin:0 0 10px">These are shared by every line; each line chooses which of them it wears in its own section.</div>

        <div class="sublabel">Relief</div>
        <div class="row"><label>Depth</label><input type="range" min="1" max="24" step="0.5" data-k="bevel"><span class="val" data-val="bevel"></span></div>
        <div class="row"><label>Shine</label><input type="range" min="0" max="1.6" step="0.05" data-k="shine"><span class="val" data-val="shine"></span></div>

        <div class="sublabel">Outline</div>
        <div class="row"><label>Colour</label><input type="color" data-k="strokeCol"></div>

        <div class="sublabel">Glow</div>
        <div class="row"><label>Colour</label><input type="color" data-k="glowCol"></div>
        <div class="row"><label>Spread</label><input type="range" min="0" max="40" step="1" data-k="glowSize"><span class="val" data-val="glowSize"></span></div>
        <div class="row"><label>Softness</label><input type="range" min="1" max="60" step="1" data-k="glowBlur"><span class="val" data-val="glowBlur"></span></div>
        <div class="row"><label>Strength</label><input type="range" min="0" max="1" step="0.02" data-k="glowOp"><span class="val" data-val="glowOp"></span></div>

        <div class="sublabel">Shadow</div>
        <div class="row"><label>Strength</label><input type="range" min="0" max="1" step="0.02" data-k="shadowOp"><span class="val" data-val="shadowOp"></span></div>
        <div class="row"><label>Drop</label><input type="range" min="0" max="40" step="1" data-k="shadowDy"><span class="val" data-val="shadowDy"></span></div>
        <div class="row"><label>Softness</label><input type="range" min="0" max="40" step="1" data-k="shadowBlur"><span class="val" data-val="shadowBlur"></span></div>
      </div>
    </details>

    <details class="acc">
      <summary>Frame</summary>
      <div class="acc-body">
        <div class="row"><label>Style</label><select data-k="frame" id="frameSel"></select></div>
        <div class="row"><label>Inset</label><input type="range" min="0" max="240" step="2" data-k="frInset"><span class="val" data-val="frInset"></span></div>
        <div class="row"><label>Width</label><input type="range" min="1" max="160" step="1" data-k="frW"><span class="val" data-val="frW"></span></div>
        <div class="row"><label>Gap</label><input type="range" min="0" max="120" step="1" data-k="frGap"><span class="val" data-val="frGap"></span></div>
        <div class="row"><label>Corner radius</label><input type="range" min="0" max="200" step="2" data-k="frRadius"><span class="val" data-val="frRadius"></span></div>
        <div class="row"><label>Corner run</label><input type="range" min="40" max="700" step="5" data-k="frCorner"><span class="val" data-val="frCorner"></span></div>
        <div class="sublabel">Colour</div>
        <div class="seg" data-seg="frFill">
          <button data-v="solid">Solid</button>
          <button data-v="metal">Metal</button>
        </div>
        <div class="row"><label>Solid colour</label><input type="color" data-k="frCol"></div>
        <div class="row"><label>Material</label><select data-k="frMat" data-mats="1"></select></div>
        <div class="row"><label>Custom metal</label><input type="color" data-k="frMatCustom"></div>
        <div class="row"><input type="checkbox" data-k="frRelief"><label>Bevel the frame</label></div>
        <div class="hint"><b>Gap</b> separates the two rules of the double and deco frames. <b>Corner run</b> is how far the brackets reach along each edge. Pick <b>Custom</b> under Material to drive the ramp from the colour below it.</div>
      </div>
    </details>

    <details class="acc" open>
      <summary>Artwork</summary>
      <div class="acc-body">
        <div class="filebox" id="filebox">Drop an image here or click to choose</div>
        <input type="file" id="fileInput" accept="image/*" style="display:none">
        <div class="row"><label>Zoom</label><input type="range" min="0.3" max="4" step="0.01" data-k="imgZoom"><span class="val" data-val="imgZoom"></span></div>
        <div class="row"><label>Horizontal</label><input type="range" min="-1200" max="1200" step="4" data-k="imgX"><span class="val" data-val="imgX"></span></div>
        <div class="row"><label>Vertical</label><input type="range" min="-1600" max="1600" step="4" data-k="imgY"><span class="val" data-val="imgY"></span></div>
        <div class="btnrow">
          <button class="btn" id="imgCenter">Center</button>
          <button class="btn danger" id="imgRemove">Remove image</button>
        </div>
        <div class="sublabel">Starfield</div>
        <div class="row"><input type="checkbox" data-k="stars"><label>Use a starfield when there is no image</label></div>
        <div class="row"><label>Background</label><input type="color" data-k="c_back"><label style="flex:0 0 auto;color:var(--ink-dim)">Stars</label><input type="color" data-k="starFore"></div>
        <div class="row"><label>Density</label><input type="range" min="0" max="2400" step="20" data-k="starDensity"><span class="val" data-val="starDensity"></span></div>
        <div class="row"><input type="checkbox" data-k="starSparkles"><label>Large sparkles</label></div>
        <div class="btnrow"><button class="btn mini" id="starShuffle">Shuffle stars</button></div>
        <div class="hint">A given seed always draws the same sky, so <b>Shuffle</b> is how you get a different one. Density counts stars per 3:4 poster and scales with the taller shapes. The background also shows behind an uploaded image that doesn't cover the page.</div>

        <div class="sublabel">Grade</div>
        <div class="row"><label>Brightness</label><input type="range" min="0.3" max="1.8" step="0.02" data-k="bright"><span class="val" data-val="bright"></span></div>
        <div class="row"><label>Contrast</label><input type="range" min="0.4" max="2.2" step="0.02" data-k="contrast"><span class="val" data-val="contrast"></span></div>
        <div class="row"><label>Saturation</label><input type="range" min="0" max="2.5" step="0.02" data-k="sat"><span class="val" data-val="sat"></span></div>

        <div class="sublabel">Duotone</div>
        <div class="row"><input type="checkbox" data-k="duoOn"><label>Map to two colours</label></div>
        <div class="row"><label>Shadows</label><input type="color" data-k="duoA"><label style="flex:0 0 auto;color:var(--ink-dim)">Lights</label><input type="color" data-k="duoB"></div>
        <div class="row"><label>Strength</label><input type="range" min="0" max="1" step="0.02" data-k="duoAmt"><span class="val" data-val="duoAmt"></span></div>

        <div class="sublabel">Vignette &amp; scrims</div>
        <div class="row"><label>Vignette</label><input type="range" min="0" max="1" step="0.02" data-k="vig"><span class="val" data-val="vig"></span></div>
        <div class="row"><label>Top scrim</label><input type="range" min="0" max="1" step="0.02" data-k="scrimT"><span class="val" data-val="scrimT"></span></div>
        <div class="row"><label>Top reach</label><input type="range" min="0.05" max="0.9" step="0.01" data-k="scrimTH"><span class="val" data-val="scrimTH"></span></div>
        <div class="row"><label>Bottom scrim</label><input type="range" min="0" max="1" step="0.02" data-k="scrimB"><span class="val" data-val="scrimB"></span></div>
        <div class="row"><label>Bottom reach</label><input type="range" min="0.05" max="0.9" step="0.01" data-k="scrimBH"><span class="val" data-val="scrimBH"></span></div>
        <div class="row"><label>Scrim colour</label><input type="color" data-k="overlayCol"></div>
        <div class="hint">Scrims veil the art behind the type — the top one sits under the campaign line, the bottom one under the title block. Black holds white type; switch the colour to white for a pale poster with dark type (an <b>inverted</b> scheme does that for you).</div>

        <div class="sublabel">Grain</div>
        <div class="row"><label>Amount</label><input type="range" min="0" max="1" step="0.02" data-k="grain"><span class="val" data-val="grain"></span></div>
        <div class="row"><label>Size</label><input type="range" min="1" max="8" step="0.1" data-k="grainSize"><span class="val" data-val="grainSize"></span></div>
        <div class="hint">Film grain over the art, the scrims and the backdrop, but never the type. Size is in poster units, so the grain scales with the export instead of getting finer at print size.</div>

        <div class="sublabel">Emblem</div>
        <div class="row"><label>Overlay</label><select data-k="emblem" id="emblemSel"></select></div>
        <div class="row"><label>Size</label><input type="range" min="0.03" max="0.6" step="0.005" data-k="emScale"><span class="val" data-val="emScale"></span></div>
        <div class="row"><label>Horizontal</label><input type="range" min="-900" max="900" step="4" data-k="emX"><span class="val" data-val="emX"></span></div>
        <div class="row"><label>Vertical</label><input type="range" min="-1200" max="1200" step="4" data-k="emY"><span class="val" data-val="emY"></span></div>
        <div class="row"><label>Opacity</label><input type="range" min="0.05" max="1" step="0.05" data-k="emOp"><span class="val" data-val="emOp"></span></div>
        <div class="filebox" id="emBox" style="display:none">Drop an emblem here or click to choose</div>
        <input type="file" id="emInput" accept="image/*" style="display:none">
        <div class="hint">Task-force marks come in both families — the delta on its own, or the wide version with the numeral. Uploads (artwork and emblem alike) stay in this tab; they aren't saved with the design.</div>
      </div>
    </details>

    <details class="acc">
      <summary>Export</summary>
      <div class="acc-body">
        <div class="row">
          <label>PNG width</label>
          <select id="exportSize">
            <option value="900">900 px (½×)</option>
            <option value="1800" selected>1800 px (native)</option>
            <option value="3600">3600 px (2×)</option>
            <option value="5400">5400 px (print, 300dpi)</option>
          </select>
        </div>
        <div class="btnrow">
          <button class="btn primary" id="exportPng">Export PNG</button>
          <button class="btn" id="exportSvg">Export SVG</button>
        </div>
        <div class="hint">At 3:4 the print size is 5400×7200 — the same pixels as the original PSDs.</div>
      </div>
    </details>

    <section class="plain">
      <button class="btn danger" id="resetBtn">Reset to defaults</button>
    </section>

    <div class="credit">
      Fonts: <b>Sealstile</b> (Librestile) and twelve OFL faces<br>
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
    <div id="posterwrap"></div>
    <div class="stagehint">Preview background is not exported — the poster fills the whole frame</div>
  </div>
</main>

<script src="shared/state.js?v=fa880068"></script>
<script src="shared/metal.js?v=8aa4ac74"></script>
<script src="shared/export.js?v=07669e3b"></script>
<script src="shared/stage.js?v=b9df1f51"></script>
<script>
"use strict";
const $ = id => document.getElementById(id);
const esc = Metal.esc;
const f2 = n => Math.round(n*100)/100;
const clamp = (v,a,b) => Math.max(a, Math.min(b, v));

/* ================= canvas =================
   Width is fixed so a type size means the same thing whichever shape you pick;
   only the height changes. 3:4 is the source artwork's own shape — all three
   reference PSDs are 5400x7200 (18x24in at 300dpi), so native 1800x2400 is
   exactly a third of them and the print export lands back on their pixels. */
const W = 1800;
const RATIOS = {
  poster: { label:"3:4 Poster",   h:2400 },
  sheet:  { label:"2:3 One-Sheet", h:2700 },
  wide:   { label:"4:5 Portrait",  h:2250 },
  square: { label:"1:1 Square",    h:1800 },
};
const HH = () => RATIOS[S.ratio] ? RATIOS[S.ratio].h : 2400;

/* ================= fonts =================
   Sealstile — this project's own patched Librestile — is the default on every
   row; it is also the closest thing we have to the Microstyle Bold Extended the
   PSDs set their titles in. The rest are OFL faces chosen for poster and cover
   work: Cinzel/Cinzel Decorative for the Trajan-style epic title, Playfair and
   the Garamonds for a novel jacket, Bebas/Oswald/Anton for the condensed
   one-sheet and billing block, Michroma/Orbitron for something more technical.
   Files come from tools/fetch_shared_fonts.py. */
const FONTS = [
  { k:"sealstile", label:"Sealstile",          fam:"Sealstile",          w:400, st:"normal", url:"fonts/sealstile.woff2?v=8128806d" },
  { k:"cinzel",    label:"Cinzel",             fam:"Cinzel",             w:600, st:"normal", url:"fonts/webfonts/cinzel.woff2?v=37543dc7" },
  { k:"cinzeldec", label:"Cinzel Decorative",  fam:"Cinzel Decorative",  w:700, st:"normal", url:"fonts/webfonts/cinzeldecor-700.woff2?v=9f36288a" },
  { k:"playfair",  label:"Playfair Display",   fam:"Playfair Display",   w:700, st:"normal", url:"fonts/webfonts/playfair-700.woff2?v=02af2688" },
  { k:"cormorant", label:"Cormorant Garamond", fam:"Cormorant Garamond", w:600, st:"normal", url:"fonts/webfonts/cormorant-600.woff2?v=53205cf8" },
  { k:"garamond",  label:"EB Garamond",        fam:"EB Garamond",        w:600, st:"normal", url:"fonts/webfonts/ebgaramond-600.woff2?v=b4aa08b7" },
  { k:"garamondit",label:"EB Garamond Italic", fam:"EB Garamond",        w:500, st:"italic", url:"fonts/webfonts/ebgaramond-it500.woff2?v=668ea527" },
  { k:"tenor",     label:"Tenor Sans",         fam:"Tenor Sans",         w:400, st:"normal", url:"fonts/webfonts/tenor.woff2?v=b124d5a7" },
  { k:"bebas",     label:"Bebas Neue",         fam:"Bebas Neue",         w:400, st:"normal", url:"fonts/webfonts/bebas.woff2?v=441b026d" },
  { k:"oswald",    label:"Oswald",             fam:"Oswald",             w:500, st:"normal", url:"fonts/webfonts/oswald-500.woff2?v=5e619e5c" },
  { k:"anton",     label:"Anton",              fam:"Anton",              w:400, st:"normal", url:"fonts/webfonts/anton.woff2?v=23aab0b2" },
  { k:"michroma",  label:"Michroma",           fam:"Michroma",           w:400, st:"normal", url:"fonts/webfonts/michroma.woff2?v=b1209818" },
  { k:"orbitron",  label:"Orbitron",           fam:"Orbitron",           w:600, st:"normal", url:"fonts/webfonts/orbitron.woff2?v=3906d33a" },
];
const fontOf = k => FONTS.find(f => f.k === k) || FONTS[0];

/* ================= text rows =================
   Six named slots rather than a free list: a poster's lines have fixed jobs, so
   each one can carry sensible defaults and its own effect choices. Nothing is
   mutually exclusive — every slot is independent and can be switched off.

   A slot is anchored to the top or the bottom edge, never to an absolute y, so
   changing the canvas shape doesn't scatter the layout. Bottom-anchored blocks
   grow upward from their last baseline, the way the plaque's quote does, so a
   two-line title pushes into the art instead of off the bottom edge. */
const ROWS = [
  { k:"camp",   label:"Campaign",      anchor:"top",    lines:1, max:70,  open:true },
  { k:"pre",    label:"Above Title",   anchor:"bottom", lines:1, max:80,  open:false },
  { k:"title",  label:"Title",         anchor:"bottom", lines:4, max:160, open:true },
  { k:"sub",    label:"Subtitle",      anchor:"bottom", lines:2, max:120, open:false,
    note:"Its default spot is above the title, because the reference design runs the title and the story credit right down to the bottom edge." },
  { k:"credit", label:"Story Credit",  anchor:"bottom", lines:1, max:70,  open:true },
  { k:"bill",   label:"Billing Block", anchor:"bottom", lines:5, max:400, open:false,
    note:"A billing block wants the same bottom strip as the story credit — switch that one off, or raise it, before turning this on." },
];
const RELIEFS = [
  ["none",  "Flat"],
  ["raised","Raised"],
  ["emboss","Embossed"],
  ["inlay", "Inlaid"],
];

/* ================= frames ================= */
const FRAMES = {
  none:      "None",
  rule:      "Single rule",
  double:    "Double rule",
  band:      "Solid band",
  metal:     "Metal band",
  deco:      "Deco rule + corners",
  brackets:  "Corner brackets",
  letterbox: "Letterbox bars",
};

/* ================= emblems =================
   Both TF logo families are offered: the `tf##a.png` delta-only marks (what the
   banner tool uses, since its slot is delta-shaped) and the wide `tf##.png`
   variants that carry the big numeral — a poster has room for the numeral, so
   there is no reason to hide them here the way the banner has to. */
const TFS = [17, 21, 47, 72, 86, 93];
const EMBLEMS = { none: { label:"None", url:null, group:"" } };
EMBLEMS.bf  = { label:"Bravo Fleet delta", url:"assets/logos/bf-delta.png?v=322c9308", group:"Bravo Fleet" };
EMBLEMS.bfl = { label:"Bravo Fleet logo",  url:"assets/logos/bf-logo.png?v=16b17ae2",  group:"Bravo Fleet" };
TFS.forEach(n => { EMBLEMS["tf"+n]     = { label:"Task Force "+n, url:`assets/logos/tf${n}a.png`, group:"Task force delta" }; });
TFS.forEach(n => { EMBLEMS["tf"+n+"n"] = { label:"Task Force "+n, url:`assets/logos/tf${n}.png`,  group:"Task force with numeral" }; });
EMBLEMS.custom = { label:"Custom image…", url:null, group:"", custom:true };

const EM_SIZE = {};
Object.values(EMBLEMS).forEach(E => {
  if(!E.url) return;
  const im = new Image();
  im.onload = () => { EM_SIZE[E.url] = {w:im.naturalWidth, h:im.naturalHeight}; render(); };
  im.src = E.url;
});
/* uploaded emblem art, kept out of S/localStorage like IMG and the seal tool's
   CUSTOM_CHARGE — it doesn't survive a reload */
let CUSTOM_EM = null;   /* {dataUrl,w,h} */
/* the art the emblem slot should draw right now, whichever kind it is */
function emblemArt(){
  const E = EMBLEMS[S.emblem];
  if(!E) return null;
  if(E.custom) return CUSTOM_EM ? { href:CUSTOM_EM.dataUrl, w:CUSTOM_EM.w, h:CUSTOM_EM.h, url:null } : null;
  if(!E.url) return null;
  const nat = EM_SIZE[E.url] || { w:300, h:468 };
  return { href:E.url, w:nat.w, h:nat.h, url:E.url };
}

/* ================= state =================
   Row defaults come off the reference PSDs, divided by three for this canvas.
   All three set every line in Microstyle Bold Extended at HorizontalScale 0.9
   over a photo, in white, under a 20px cyan Stroke and a 50% Satin; Sealstile
   at squeeze 0.9 with the raised relief below is this tool's read of that. */
const FXDEF = {
  bevel:5, shine:.85,
  strokeCol:"#00a2ff",
  glowCol:"#0060ff", glowSize:8, glowBlur:18, glowOp:.55,
  shadowOp:.45, shadowDy:5, shadowBlur:8,
};
const ROWDEF = {
  camp:   { on:true,  txt:"ARCTURUS", font:"sealstile", size:300, track:0,     sq:.9,  lead:.9,  align:"center", y:353, x:0,
            fill:"solid", col:"#ffffff", col2:"#8a6410", mat:"gold", matCustom:"#c9992e", relief:"raised", strokeOn:true,  strokeW:7, glowOn:false, shadowOn:true },
  pre:    { on:false, txt:"THE FOURTH FLEET PRESENTS", font:"sealstile", size:52, track:.16, sq:.9, lead:1, align:"center", y:620, x:0,
            fill:"solid", col:"#ffffff", col2:"#8a6410", mat:"gold", matCustom:"#c9992e", relief:"none",   strokeOn:false, strokeW:3, glowOn:false, shadowOn:true },
  title:  { on:true,  txt:"ALL HANDS,\nBURY THE DEAD", font:"sealstile", size:200, track:0, sq:.9, lead:.82, align:"center", y:162, x:0,
            fill:"solid", col:"#ffffff", col2:"#8a6410", mat:"gold", matCustom:"#c9992e", relief:"raised", strokeOn:true,  strokeW:7, glowOn:false, shadowOn:true },
  sub:    { on:false, txt:"A Mission of the USS Arcturus", font:"garamondit", size:72, track:.02, sq:1, lead:1.1, align:"center", y:480, x:0,
            fill:"solid", col:"#e8eef6", col2:"#8a6410", mat:"silver", matCustom:"#c9992e", relief:"none", strokeOn:false, strokeW:3, glowOn:false, shadowOn:true },
  credit: { on:true,  txt:"A Bravo Fleet Story", font:"sealstile", size:100, track:-.03, sq:.9, lead:1, align:"center", y:52, x:0,
            fill:"solid", col:"#ffffff", col2:"#8a6410", mat:"gold", matCustom:"#c9992e", relief:"raised", strokeOn:true,  strokeW:4, glowOn:false, shadowOn:true },
  bill:   { on:false, txt:"BRAVO FLEET PRESENTS  A STORY OF TASK FORCE 47\nWRITTEN ON THE HOLODECK · STARDATE 78412.6", font:"oswald",
            size:30, track:.08, sq:1, lead:1.35, align:"center", y:46, x:0,
            fill:"solid", col:"#cfd8e2", col2:"#8a6410", mat:"silver", matCustom:"#c9992e", relief:"none", strokeOn:false, strokeW:2, glowOn:false, shadowOn:false },
};

const DEFAULTS = (() => {
  const D = Object.assign({
    ratio:"poster", margin:70, preset:"Bravo Fleet Story",
    /* frame */
    frame:"none", frInset:36, frW:8, frGap:14, frRadius:0, frCorner:260,
    frFill:"solid", frCol:"#d3a92c", frMat:"gold", frMatCustom:"#c9992e", frRelief:true,
    /* artwork */
    c_back:"#080d16", scheme:"", schemeMode:"std",
    stars:true, starSeed:1701, starDensity:800, starSparkles:true, starFore:"#cfe0f4",
    imgZoom:1, imgX:0, imgY:0,
    bright:1, contrast:1, sat:1,
    duoOn:false, duoA:"#08101f", duoB:"#cfe0f4", duoAmt:.85,
    vig:.35, scrimT:.5, scrimTH:.3, scrimB:.75, scrimBH:.42, overlayCol:"#000000",
    grain:0, grainSize:2.4,
    emblem:"none", emScale:.14, emX:0, emY:-560, emOp:1,
  }, FXDEF);
  ROWS.forEach(R => Object.entries(ROWDEF[R.k]).forEach(([p,v]) => { D[R.k+"_"+p] = v; }));
  return D;
})();

let S = JSON.parse(JSON.stringify(DEFAULTS));
let IMG = null;   /* {dataUrl,w,h} — never persisted, like the other tools */

/* ================= styles =================
   `all` is applied to every row (the poster reads as one lockup), `title` and
   `camp` may override it, `fx` sets the shared effect params, `frame` the
   frame. Text, fonts, sizes and positions are never touched. */
const PRESETS = [
  { name:"Bravo Fleet Story", group:"Reference", sw:["#ffffff","#00a2ff"],
    all:{ fill:"solid", col:"#ffffff", relief:"raised", strokeOn:true, glowOn:false, shadowOn:true },
    fx:{ strokeCol:"#00a2ff", bevel:5, shine:.85, shadowOp:.45, shadowDy:5, shadowBlur:8 },
    frame:{ frame:"none" } },

  { name:"Gold Foil", group:"Novel Cover", sw:["#f3d356","#8a6410"],
    all:{ fill:"metal", mat:"gold", matCustom:"#c9992e", relief:"raised", strokeOn:false, glowOn:false, shadowOn:true },
    fx:{ bevel:7, shine:1.05, shadowOp:.55, shadowDy:6, shadowBlur:9 },
    frame:{ frame:"metal", frMat:"gold", frInset:34, frW:14, frRadius:0, frRelief:true } },

  { name:"Silver Foil", group:"Novel Cover", sw:["#dfe4ea","#79828e"],
    all:{ fill:"metal", mat:"silver", matCustom:"#c9992e", relief:"raised", strokeOn:false, glowOn:false, shadowOn:true },
    fx:{ bevel:7, shine:1.05, shadowOp:.5, shadowDy:6, shadowBlur:9 },
    frame:{ frame:"metal", frMat:"silver", frInset:34, frW:14, frRadius:0, frRelief:true } },

  { name:"Embossed Slate", group:"Novel Cover", sw:["#8f9aa6","#2b3542"],
    all:{ fill:"solid", col:"#9dabba", relief:"emboss", strokeOn:false, glowOn:false, shadowOn:false },
    fx:{ bevel:8, shine:.5 },
    frame:{ frame:"rule", frCol:"#6d7d92", frInset:44, frW:3, frRadius:0, frRelief:false, frFill:"solid" } },

  { name:"One-Sheet", group:"Movie Poster", sw:["#ffffff","#111111"],
    all:{ fill:"solid", col:"#ffffff", relief:"none", strokeOn:false, glowOn:false, shadowOn:true },
    fx:{ shadowOp:.6, shadowDy:3, shadowBlur:12 },
    frame:{ frame:"letterbox", frCol:"#07080b", frInset:0, frW:74, frRadius:0, frRelief:false, frFill:"solid" } },

  { name:"Deco Epic", group:"Movie Poster", sw:["#e6c975","#1d2c46"],
    all:{ fill:"metal", mat:"bronze", relief:"raised", strokeOn:false, glowOn:false, shadowOn:true },
    fx:{ bevel:5, shine:.95, shadowOp:.5, shadowDy:5, shadowBlur:10 },
    frame:{ frame:"deco", frCol:"#c9a544", frInset:40, frW:5, frGap:12, frRadius:0, frRelief:false, frFill:"solid" } },

  { name:"Cold Open", group:"Movie Poster", sw:["#dceaff","#1c4f8a"],
    all:{ fill:"solid", col:"#e6f0ff", relief:"none", strokeOn:false, glowOn:true, shadowOn:true },
    fx:{ glowCol:"#2b7fd4", glowSize:6, glowBlur:26, glowOp:.7, shadowOp:.5, shadowDy:4, shadowBlur:10 },
    frame:{ frame:"brackets", frCol:"#8fc4f2", frInset:44, frW:5, frCorner:300, frRadius:0, frRelief:false, frFill:"solid" } },

  { name:"Red Alert", group:"Movie Poster", sw:["#ffffff","#7c0309"],
    all:{ fill:"solid", col:"#ffffff", relief:"raised", strokeOn:true, glowOn:true, shadowOn:true },
    fx:{ strokeCol:"#7c0309", glowCol:"#c2160f", glowSize:4, glowBlur:30, glowOp:.6, bevel:4, shine:.8, shadowOp:.5, shadowDy:4, shadowBlur:8 },
    frame:{ frame:"double", frCol:"#7c0309", frInset:36, frW:6, frGap:10, frRadius:0, frRelief:false, frFill:"solid" } },

  { name:"Etched Bronze", group:"Novel Cover", sw:["#c98f3d","#3a2a18"],
    all:{ fill:"metal", mat:"bronze", matCustom:"#a9702c", relief:"inlay", strokeOn:false, glowOn:false, shadowOn:false },
    fx:{ bevel:6, shine:.5, shadowOp:.4, shadowDy:3, shadowBlur:6 },
    frame:{ frame:"deco", frCol:"#a9702c", frInset:44, frW:4, frGap:10, frRadius:0, frRelief:false, frFill:"solid" } },

  { name:"Paperback", group:"Novel Cover", sw:["#f5ead4","#c9a544"],
    all:{ fill:"solid", col:"#f5ead4", relief:"none", strokeOn:false, glowOn:false, shadowOn:true },
    fx:{ shadowOp:.5, shadowDy:3, shadowBlur:10 },
    frame:{ frame:"rule", frCol:"#c9a544", frInset:52, frW:2, frRadius:0, frRelief:false, frFill:"solid" } },

  { name:"Chrome Titles", group:"Movie Poster", sw:["#e8eef6","#2a3340"],
    all:{ fill:"metal", mat:"platinum", matCustom:"#9fb2c6", relief:"raised", strokeOn:true, glowOn:false, shadowOn:true },
    fx:{ strokeCol:"#141a22", bevel:9, shine:1.15, shadowOp:.6, shadowDy:7, shadowBlur:12 },
    frame:{ frame:"none" } },

  { name:"Neon Noir", group:"Movie Poster", sw:["#ffd9f6","#22d3ee"],
    all:{ fill:"solid", col:"#ffd9f6", relief:"none", strokeOn:true, glowOn:true, shadowOn:false },
    fx:{ strokeCol:"#ff2fc4", glowCol:"#22d3ee", glowSize:5, glowBlur:34, glowOp:.85, shadowOp:.4, shadowDy:0, shadowBlur:14 },
    frame:{ frame:"letterbox", frCol:"#05060a", frInset:0, frW:60, frRadius:0, frRelief:false, frFill:"solid" } },

  /* the solid band and the wide-inset rules are the two frame kinds no other
     style reaches for, and both belong to flat printed art rather than to a
     jacket or a one-sheet */
  { name:"Gallery Print", group:"Print", sw:["#ece7dc","#23262e"],
    all:{ fill:"solid", col:"#ece7dc", relief:"none", strokeOn:false, glowOn:false, shadowOn:false },
    fx:{ shadowOp:.35, shadowDy:2, shadowBlur:8 },
    /* the band is a mat, so it wants to read as a border against dark art too —
       pure black would simply vanish into a night sky */
    frame:{ frame:"band", frCol:"#23262e", frInset:0, frW:64, frRadius:0, frRelief:false, frFill:"solid" } },

  { name:"Blueprint", group:"Print", sw:["#dbeaff","#0b2f5c"],
    all:{ fill:"solid", col:"#dbeaff", relief:"none", strokeOn:true, glowOn:false, shadowOn:false },
    fx:{ strokeCol:"#0b2f5c", shadowOp:.35, shadowDy:2, shadowBlur:6 },
    frame:{ frame:"double", frCol:"#8fb8e8", frInset:50, frW:2, frGap:8, frRadius:0, frRelief:false, frFill:"solid" } },
];

/* ================= colour schemes =================
   The seal tool's whole palette, carried over verbatim: the eight official
   Bravo Fleet colours from bravofleet.com/graphics, the four unofficial
   department-inspired ones, and the four hand-picked fictional themes. Each
   entry keeps the seal preset's own key colours so the two tools stay in step
   when that palette changes — `band` is the theme colour, `accent` its ring
   colour, `deep` the darkest shade, `star` the starfield colour.

   A scheme is a separate axis from a style: a style sets fill mode, relief,
   which effects a line wears and the frame's *shape*; a scheme only moves
   colours. Applying one over the other keeps the look and recolours it — which
   is why a scheme also flips a metal fill to the custom ramp, so gold foil
   becomes foil in the scheme's own colour instead of silently ignoring it. */
const SCHEMES = [
  { name:"Bravo Blue",      group:"Official Bravo Fleet", band:"#2864a8", accent:"#d3a92c", deep:"#143356", star:"#d3a92c" },
  { name:"Bolt Gold",       group:"Official Bravo Fleet", band:"#d3a92c", accent:"#14161c", deep:"#14161c", star:"#d3a92c" },
  { name:"TF17 Gray",       group:"Official Bravo Fleet", band:"#434e5f", accent:"#ffffff", deep:"#1e242d", star:"#e8ecef" },
  { name:"TF21 Purple",     group:"Official Bravo Fleet", band:"#651060", accent:"#ffffff", deep:"#2e062b", star:"#f0e6ef" },
  { name:"TF47 Orange",     group:"Official Bravo Fleet", band:"#c64f1c", accent:"#ffffff", deep:"#5e240c", star:"#f5ece6" },
  { name:"TF72 Navy",       group:"Official Bravo Fleet", band:"#20347f", accent:"#ffffff", deep:"#0e1839", star:"#e9ecf5" },
  { name:"TF86 Red",        group:"Official Bravo Fleet", band:"#7c0309", accent:"#ffffff", deep:"#370204", star:"#f3e7e7" },
  { name:"TF93 Green",      group:"Official Bravo Fleet", band:"#1a4a3c", accent:"#ffffff", deep:"#0b221c", star:"#e7f0ed" },

  { name:"Command Red",     group:"Department Inspired",  band:"#a71313", accent:"#ffffff", deep:"#4b0909", star:"#f5e3e3" },
  { name:"Sciences Blue",   group:"Department Inspired",  band:"#2b53a7", accent:"#ffffff", deep:"#13254b", star:"#e9eef7" },
  { name:"Operations Gold", group:"Department Inspired",  band:"#d6a444", accent:"#14161c", deep:"#14161c", star:"#d6a444" },
  { name:"Medical White",   group:"Department Inspired",  band:"#f4f6f8", accent:"#1c2b3a", deep:"#2f4a52", star:"#eef2f5" },

  { name:"Sea Greens",      group:"Other",                band:"#175a6c", accent:"#c9d1d9", deep:"#0e2a33", star:"#d8dee4" },
  { name:"Pegasus",         group:"Other",                band:"#9e2b25", accent:"#ede6da", deep:"#2a3e8c", star:"#ffffff" },
  { name:"Federation",      group:"Other",                band:"#14245c", accent:"#c9a227", deep:"#14245c", star:"#e9c46a" },
  { name:"Healer",          group:"Other",                band:"#2e6b7e", accent:"#dfe5e9", deep:"#4a5a6e", star:"#e8ecef" },
];

/* Each scheme reads three ways and `S.schemeMode` picks which — one table,
   three times the options, and no second set of hand-picked colours to keep in
   step with the seal tool's palette.

     std   the reference poster: the light `accent` in the type, the theme
           colour `band` in the outline and glow, over the scheme's darkest
           shade, black scrims.
     swap  the same poster with primary and secondary traded round — the type
           carries the theme colour and the accent moves out to the outline.
           The field stays dark, so this is a recolour of the same design
           rather than a different one.
     inv   the whole poster turned over: a pale field lightened out of `band`,
           dark type, and white scrims — a black scrim on a pale poster fights
           the type it exists to support, which is what `S.overlayCol` is for.

   Each reading carries a guard, because the palette is not uniform. `std`:
   three schemes set `accent` to the same near-black as `deep` (Bolt Gold and
   Operations Gold share one hex outright), which put the ink and the backdrop
   on top of each other, so a non-separating accent becomes a pale wash of the
   theme colour. `swap`: the theme colour is often close to its own darkest
   shade (Federation's `band` and `deep` are the same navy), so the ink is
   lifted toward white until it clears the backdrop. `inv`: `band` is only
   lightened into a field when there is room to lighten it (Medical White's is
   already near-white), and the ink falls back to `deep` when the field didn't
   land far enough from it (Bolt Gold). */
const lum = h => { const [r,g,b] = Metal.hexRgb(h); return (.2126*r + .7152*g + .0722*b)/255; };
/* WCAG relative luminance and contrast ratio — the plain weighted average above
   is fine for "are these two far apart", but the swap guard is deciding how far
   to lift an ink until it reads, which wants the gamma-corrected one. */
function relLum(h){
  const c = Metal.hexRgb(h).map(v => { v /= 255; return v <= .03928 ? v/12.92 : Math.pow((v+.055)/1.055, 2.4); });
  return .2126*c[0] + .7152*c[1] + .0722*c[2];
}
function contrast(a, b){
  const x = relLum(a)+.05, y = relLum(b)+.05;
  return Math.max(x,y)/Math.min(x,y);
}
function lift(ink, back, want){
  for(let t = 0; t < .65; t += .05){
    const v = Metal.ltn(ink, t);
    if(contrast(v, back) >= want) return v;
  }
  return Metal.ltn(ink, .65);
}
function schemeColors(c, mode){
  if(mode === "inv"){
    const bl = lum(c.band);
    const field = bl > .74 ? c.band : Metal.ltn(c.band, .84);
    const ink = (lum(field) - bl > .34) ? c.band : c.deep;
    return {
      ink, fade:c.deep, line:c.deep, glow:c.deep,
      back:field, star:Metal.dkn(c.band, .35), frame:ink, ramp:c.band, overlay:"#ffffff",
    };
  }
  if(mode === "swap"){
    const ink = lift(c.band, c.deep, 3.2);
    return {
      ink, fade:c.accent, line:c.accent, glow:c.accent,
      back:c.deep, star:c.star, frame:ink, ramp:c.band, overlay:"#000000",
    };
  }
  const ink = (lum(c.accent) - lum(c.deep) > .3) ? c.accent : Metal.ltn(c.band, .82);
  return {
    ink, fade:c.band, line:c.band, glow:c.band,
    back:c.deep, star:c.star, frame:ink, ramp:c.band, overlay:"#000000",
  };
}

/* ================= text metrics =================
   A live SVG <text> node, not a canvas 2D context — the poster is laid out in
   SVG and the two engines disagree enough to trip a fit (that is what shrank
   the plaque's registry line in Safari). Cached per font at a reference size. */
const MEAS_REF = 100;
const measSVG = document.createElementNS("http://www.w3.org/2000/svg", "svg");
measSVG.setAttribute("aria-hidden", "true");
measSVG.setAttribute("style", "position:absolute;left:-9999px;top:0;width:10px;height:10px;overflow:hidden");
const measNode = document.createElementNS("http://www.w3.org/2000/svg", "text");
measSVG.appendChild(measNode);
document.body.appendChild(measSVG);
const measCache = new Map();

/* A webfont declared in CSS is not fetched until something actually uses it, so
   the first measurement after picking a new font can land on *fallback* metrics
   — and caching that poisons the fit for the rest of the session (boot's
   document.fonts.ready has long since fired by then). So: measure freely once a
   face is ready, and for one that isn't, kick off its load, skip the cache, and
   re-render when it arrives. That is also why every font used to report the same
   ink extent — they were all being measured as the fallback. */
const boxCache = new Map();
const faceOK = new Set(), facePending = new Set();
function faceReady(F){
  if(faceOK.has(F.k)) return true;
  if(!document.fonts) return true;
  const spec = `${F.st === "italic" ? "italic " : ""}${F.w} ${MEAS_REF}px "${F.fam}"`;
  if(document.fonts.check(spec)){ faceOK.add(F.k); return true; }
  if(!facePending.has(F.k)){
    facePending.add(F.k);
    document.fonts.load(spec).then(() => {
      facePending.delete(F.k); faceOK.add(F.k);
      measCache.clear(); boxCache.clear(); render();
    }).catch(() => { facePending.delete(F.k); });
  }
  return false;
}
function applyFace(F){
  measNode.setAttribute("font-family", F.fam);
  measNode.setAttribute("font-weight", F.w);
  measNode.setAttribute("font-style", F.st);
  measNode.setAttribute("font-size", MEAS_REF);
}
function measureRaw(text, fk){
  const F = fontOf(fk), ok = faceReady(F), key = fk + " " + text;
  if(ok){
    const c = measCache.get(key);
    if(c !== undefined) return c;
  }
  applyFace(F);
  measNode.textContent = text;
  const w = measNode.getComputedTextLength();
  if(ok) measCache.set(key, w);
  return w;
}
/* letter-spacing lands after the last glyph too, so the fit counts it the way
   the browser advances it. Width is pre-squeeze. */
const measure = (t, fk, fs, ls) => measureRaw(t, fk)*(fs/MEAS_REF) + t.length*(ls||0);

/* How far a font's ink actually reaches above and below the baseline, per em.
   The metal ramp has to span the real ink or its ends clamp to a flat stop, and
   a fixed cap-height guess can't do that across thirteen faces — Sealstile's
   caps sit at ~0.72 em but its glyph box reaches ~0.99, and Playfair's
   ascenders go further still. Measured off the same hidden node as the widths,
   so it is the engine's own answer rather than a table to keep in sync. */
function inkExtent(fk){
  const F = fontOf(fk), ok = faceReady(F);
  if(ok){
    const c = boxCache.get(fk);
    if(c) return c;
  }
  applyFace(F);
  measNode.textContent = "HXAdfhklbgjpqy";   /* caps, ascenders, descenders */
  const b = measNode.getBBox();
  const m = { up: Math.max(0.5, -b.y/MEAS_REF), down: Math.max(0.05, (b.y + b.height)/MEAS_REF) };
  if(ok) boxCache.set(fk, m);
  return m;
}

/* ================= row layout ================= */
const rowGet = (k, p) => S[k+"_"+p];

/* Every visual line of a row, with its baseline, fitted size and tracking.
   Returns {lines:[{str,fs,ls,base}], x, anchor, top, bot} — top/bot bound the
   block so a gradient fill can span exactly the ink. */
function rowLayout(k){
  const raw = String(rowGet(k,"txt") == null ? "" : rowGet(k,"txt"));
  const strs = raw.split("\n");
  if(!strs.some(s => s.trim())) return null;
  const H = HH(), m = S.margin, sq = Math.max(.2, rowGet(k,"sq"));
  const maxW = Math.max(60, W - 2*m);
  const align = rowGet(k,"align");
  const x = (align === "left" ? m : align === "right" ? W - m : W/2) + rowGet(k,"x");
  const anchor = align === "left" ? "start" : align === "right" ? "end" : "middle";

  /* One scale for the whole row, set by its longest line — a title broken over
     two lines has to stay one size, so the fit is the smallest shrink any line
     needs rather than a per-line shrink. Leading rides the same scale, which
     keeps a block that had to shrink in proportion. */
  const size0 = rowGet(k,"size"), track = rowGet(k,"track"), fk = rowGet(k,"font");
  let scale = 1;
  for(const str of strs){
    if(!str.trim()) continue;
    const w = measure(str, fk, size0, size0*track)*sq;
    if(w > maxW) scale = Math.min(scale, maxW/w);
  }
  const fs = size0*scale, ls = size0*track*scale;
  const lines = strs.map(str => ({ str, fs, ls, base:0 }));

  const gap = fs*rowGet(k,"lead");
  const n = lines.length;
  const yEdge = rowGet(k,"y");
  const lastBase = R(k).anchor === "top" ? yEdge + gap*(n-1) : H - yEdge;
  lines.forEach((L,i) => { L.base = lastBase - gap*(n-1-i); });

  const ink = inkExtent(fk);
  return { lines, x, anchor, sq,
           top: lines[0].base - fs*ink.up,
           bot: lines[n-1].base + fs*ink.down };
}
const R = k => ROWS.find(r => r.k === k);

/* One <text> element: the squeeze rides a transform, so letter-spacing and the
   anchor correction are applied in the unsqueezed frame first. */
function textEl(L, x, base, anchor, sq, fk, fill, stroke, strokeW){
  const F = fontOf(fk);
  const dx = anchor === "end" ? L.ls : anchor === "start" ? 0 : L.ls/2;
  const tf = sq === 1 ? `translate(${f2(x)} ${f2(base)})`
                      : `translate(${f2(x)} ${f2(base)}) scale(${f2(sq)} 1)`;
  /* paint-order draws the stroke first and the fill over it, so only the outer
     half shows — doubling the width makes the slider read as an outside stroke,
     the way the PSD's Stroke effect is set. */
  const sk = strokeW > 0
    ? ` stroke="${stroke}" stroke-width="${f2(strokeW*2)}" stroke-linejoin="round" style="paint-order:stroke fill"`
    : "";
  return `<text transform="${tf}" x="${f2(dx)}" y="0" text-anchor="${anchor}"`
    + ` font-family="${esc(F.fam)}" font-weight="${F.w}" font-style="${F.st}"`
    + ` font-size="${f2(L.fs)}" letter-spacing="${f2(L.ls)}" fill="${fill}"${sk}`
    + ` xml:space="preserve">${esc(L.str)}</text>`;
}

/* ================= filters =================
   Relief and glow/shadow are two nested groups rather than one giant filter:
   the shared Metal relief filters can't have a glow spliced into them, but an
   outer filtered <g> sees the inner one's rendered result, so the glow and the
   shadow are cast by the finished lockup — stroke and all. */
function reliefFilter(id, mode){
  if(mode === "raised") return Metal.bevelFilter(id, { bevel:S.bevel, shine:S.shine, se:22, shadow:0 });
  if(mode === "emboss") return Metal.engraveFilter(id, { depth:Math.max(1,S.bevel*.9), blur:Math.max(1,S.bevel*.7), dark:.75, shine:S.shine*.6, se:14 });
  if(mode === "inlay")  return Metal.inlayFilter(id, { depth:Math.max(1,S.bevel*.4), blur:Math.max(1,S.bevel*.32), dark:.55, bevel:Math.max(1,S.bevel*.4), shine:S.shine*.25, se:22 });
  return "";
}
/* Glow under shadow under the artwork. feDropShadow renders its input too, so
   the shadow is built by hand to keep it a separate layer. */
function auraFilter(id, glow, shadow){
  const g = glow && S.glowOp > 0, s = shadow && S.shadowOp > 0;
  if(!g && !s) return "";
  let f = `<filter id="${id}" x="-25%" y="-60%" width="150%" height="220%" color-interpolation-filters="sRGB">`;
  const layers = [];
  if(s){
    f += `<feOffset in="SourceAlpha" dx="0" dy="${f2(S.shadowDy)}" result="so"/>`
      +  `<feGaussianBlur in="so" stdDeviation="${f2(Math.max(.01,S.shadowBlur))}" result="sb"/>`
      +  `<feFlood flood-color="#000000" flood-opacity="${f2(S.shadowOp)}" result="sc"/>`
      +  `<feComposite in="sc" in2="sb" operator="in" result="shadowL"/>`;
    layers.push("shadowL");
  }
  if(g){
    f += `<feMorphology in="SourceAlpha" operator="dilate" radius="${f2(Math.max(.01,S.glowSize))}" result="gd"/>`
      +  `<feGaussianBlur in="gd" stdDeviation="${f2(Math.max(.01,S.glowBlur))}" result="gb"/>`
      +  `<feFlood flood-color="${S.glowCol}" flood-opacity="${f2(S.glowOp)}" result="gc"/>`
      +  `<feComposite in="gc" in2="gb" operator="in" result="glowL"/>`;
    layers.push("glowL");
  }
  layers.push("SourceGraphic");
  return f + `<feMerge>${layers.map(l=>`<feMergeNode in="${l}"/>`).join("")}</feMerge></filter>`;
}

/* ================= frame ================= */
const rrect = (x,y,w,h,r) => {
  r = clamp(r, 0, Math.min(w,h)/2);
  return r <= 0 ? `M ${f2(x)} ${f2(y)} H ${f2(x+w)} V ${f2(y+h)} H ${f2(x)} Z`
    : `M ${f2(x+r)} ${f2(y)} H ${f2(x+w-r)} A ${f2(r)} ${f2(r)} 0 0 1 ${f2(x+w)} ${f2(y+r)}`
      + ` V ${f2(y+h-r)} A ${f2(r)} ${f2(r)} 0 0 1 ${f2(x+w-r)} ${f2(y+h)}`
      + ` H ${f2(x+r)} A ${f2(r)} ${f2(r)} 0 0 1 ${f2(x)} ${f2(y+h-r)}`
      + ` V ${f2(y+r)} A ${f2(r)} ${f2(r)} 0 0 1 ${f2(x+r)} ${f2(y)} Z`;
};
/* A band is a filled ring, not a stroke: two rounded rects under evenodd, so a
   corner radius and a wide band don't fight SVG's centred stroke alignment. */
const ringPath = (inset, w, r, H) =>
  rrect(inset, inset, W-2*inset, H-2*inset, r) + " " +
  rrect(inset+w, inset+w, W-2*(inset+w), H-2*(inset+w), Math.max(0, r-w));

function frameSVG(H){
  const kind = S.frame;
  if(kind === "none" || kind === undefined) return { defs:"", body:"" };
  const i = S.frInset, w = Math.max(.5, S.frW), g = S.frGap, r = S.frRadius;
  const metal = kind === "metal" || S.frFill === "metal";
  const matKey = S.frMat === "custom" ? S.frMatCustom : S.frMat;
  const M = Metal.matInfo(matKey);
  let defs = "";
  let paint = S.frCol;
  if(metal){
    defs += Metal.gradientDef("frGrad", M.stops, i, H-i);
    paint = "url(#frGrad)";
  }
  const useRelief = S.frRelief;
  if(useRelief) defs += Metal.bevelFilter("frFx", { bevel:Math.max(1.5, Math.min(w*.5, 10)), shine:metal ? .9 : .55, se:24, shadow:.28, shadowDy:3, shadowBlur:5 });
  const fx = useRelief ? ` filter="url(#frFx)"` : "";

  let body = "";
  if(kind === "rule"){
    body = `<path d="${ringPath(i, w, r, H)}" fill="${paint}" fill-rule="evenodd"/>`;
  }else if(kind === "double"){
    body = `<path d="${ringPath(i, w, r, H)}" fill="${paint}" fill-rule="evenodd"/>`
         + `<path d="${ringPath(i+w+g, Math.max(.5,w*.6), Math.max(0,r-w-g), H)}" fill="${paint}" fill-rule="evenodd"/>`;
  }else if(kind === "band" || kind === "metal"){
    body = `<path d="${ringPath(i, Math.max(4,w), r, H)}" fill="${paint}" fill-rule="evenodd"/>`;
  }else if(kind === "deco"){
    const c = Math.max(w*3, 26);
    body = `<path d="${ringPath(i, w, r, H)}" fill="${paint}" fill-rule="evenodd"/>`
         + `<path d="${ringPath(i+w+g, Math.max(.5,w*.5), Math.max(0,r-w-g), H)}" fill="${paint}" fill-rule="evenodd"/>`;
    /* a filled lozenge over each corner of the outer rule, the way a deco
       border pins its rules together */
    [[i,i],[W-i,i],[i,H-i],[W-i,H-i]].forEach(([px,py]) => {
      body += `<path d="M ${f2(px)} ${f2(py-c/2)} L ${f2(px+c/2)} ${f2(py)} L ${f2(px)} ${f2(py+c/2)} L ${f2(px-c/2)} ${f2(py)} Z" fill="${paint}"/>`;
    });
  }else if(kind === "brackets"){
    const run = Math.min(S.frCorner, Math.min(W, H)/2 - i - 4);
    const arm = (px, py, sx, sy) =>
      `<path d="M ${f2(px)} ${f2(py+sy*run)} L ${f2(px)} ${f2(py)} L ${f2(px+sx*run)} ${f2(py)}`
      + ` L ${f2(px+sx*run)} ${f2(py+sy*w)} L ${f2(px+sx*w)} ${f2(py+sy*w)} L ${f2(px+sx*w)} ${f2(py+sy*run)} Z" fill="${paint}"/>`;
    body = arm(i,i,1,1) + arm(W-i,i,-1,1) + arm(i,H-i,1,-1) + arm(W-i,H-i,-1,-1);
  }else if(kind === "letterbox"){
    const bw = Math.max(4, w);
    body = `<rect x="0" y="${f2(i)}" width="${W}" height="${f2(bw)}" fill="${paint}"/>`
         + `<rect x="0" y="${f2(H-i-bw)}" width="${W}" height="${f2(bw)}" fill="${paint}"/>`;
  }
  return { defs, body: fx ? `<g${fx}>${body}</g>` : body };
}

/* ================= artwork =================
   Seeded (`S.starSeed`) so a given seed is stable, and built the same way the
   seal tool builds its field: the big four-point sparkles are placed first with
   mutual separation, then the small stars are rejection-sampled away from every
   sparkle's arms. Attempt caps keep both loops bounded, so an extreme density
   may place fewer stars than asked rather than hanging.

   Density counts stars per 3:4 poster, scaled by height — otherwise the same
   number would read as a sparser sky on the taller 2:3 canvas. */
function starfield(H){
  const rnd = StateKit.mulberry32(S.starSeed);

  const sparkles = [];
  if(S.starSparkles){
    const nspark = 4 + Math.floor(rnd()*4);
    for(let i=0;i<nspark;i++){
      for(let t=0;t<60;t++){
        const x = (.08+rnd()*.84)*W, y = (.06+rnd()*.88)*H, sz = 30 + rnd()*58;
        if(sparkles.every(o => Math.hypot(o.x-x, o.y-y) > (o.s+sz)*0.9)){ sparkles.push({x, y, s:sz}); break; }
      }
    }
  }

  let out = `<g fill="${S.starFore}">`;
  const n = Math.round(S.starDensity * (H/2400));
  for(let i=0;i<n;i++){
    for(let t=0;t<24;t++){
      const sx = rnd()*W, sy = rnd()*H, r = .9 + rnd()*rnd()*4.2;
      if(sparkles.some(o => Math.hypot(o.x-sx, o.y-sy) < o.s + r + 4)) continue;
      out += `<circle cx="${f2(sx)}" cy="${f2(sy)}" r="${f2(r)}" opacity="${(.25+rnd()*.75).toFixed(2)}"/>`;
      break;
    }
  }
  for(const o of sparkles){
    const ww = o.s*.15;
    out += `<path d="M ${f2(o.x)} ${f2(o.y-o.s)} Q ${f2(o.x+ww*.35)} ${f2(o.y-ww)} ${f2(o.x+o.s)} ${f2(o.y)}`
        +  ` Q ${f2(o.x+ww*.35)} ${f2(o.y+ww)} ${f2(o.x)} ${f2(o.y+o.s)}`
        +  ` Q ${f2(o.x-ww*.35)} ${f2(o.y+ww)} ${f2(o.x-o.s)} ${f2(o.y)}`
        +  ` Q ${f2(o.x-ww*.35)} ${f2(o.y-ww)} ${f2(o.x)} ${f2(o.y-o.s)} Z" opacity=".9"/>`;
  }
  return out + `</g>`;
}

/* Grade then duotone, as one filter over the art group. The duotone is a
   luminance pass through a two-entry colour table — the same feComponentTransfer
   trick the plaque uses for its colourways — mixed back over the graded art by
   its own alpha, so "strength" is a real blend and not a second exposure. */
function artFilter(){
  const parts = [];
  let cur = "SourceGraphic";
  if(Math.abs(S.sat-1) > .005){
    parts.push(`<feColorMatrix in="${cur}" type="saturate" values="${f2(S.sat)}" result="aSat"/>`);
    cur = "aSat";
  }
  if(Math.abs(S.bright-1) > .005 || Math.abs(S.contrast-1) > .005){
    const sl = S.contrast*S.bright, ic = ((1-S.contrast)/2)*S.bright;
    parts.push(`<feComponentTransfer in="${cur}" result="aLev">`
      + ["R","G","B"].map(c => `<feFunc${c} type="linear" slope="${f2(sl)}" intercept="${f2(ic)}"/>`).join("")
      + `</feComponentTransfer>`);
    cur = "aLev";
  }
  if(S.duoOn && S.duoAmt > 0){
    const A = Metal.hexRgb(S.duoA), B = Metal.hexRgb(S.duoB);
    parts.push(`<feColorMatrix in="${cur}" type="matrix" result="aLum" values="`
      + `0.2126 0.7152 0.0722 0 0  0.2126 0.7152 0.0722 0 0  0.2126 0.7152 0.0722 0 0  0 0 0 1 0"/>`);
    parts.push(`<feComponentTransfer in="aLum" result="aDuo">`
      + `<feFuncR type="table" tableValues="${f2(A[0]/255)} ${f2(B[0]/255)}"/>`
      + `<feFuncG type="table" tableValues="${f2(A[1]/255)} ${f2(B[1]/255)}"/>`
      + `<feFuncB type="table" tableValues="${f2(A[2]/255)} ${f2(B[2]/255)}"/>`
      + `<feFuncA type="linear" slope="${f2(S.duoAmt)}"/></feComponentTransfer>`);
    parts.push(`<feMerge result="aOut"><feMergeNode in="${cur}"/><feMergeNode in="aDuo"/></feMerge>`);
  }
  if(!parts.length) return "";
  return `<filter id="artFx" x="0%" y="0%" width="100%" height="100%" color-interpolation-filters="sRGB">${parts.join("")}</filter>`;
}

/* Film grain, blended over the whole backdrop rather than mixed into artFilter,
   so it also lies over the scrims and the flat background the way grain lies
   over a whole print. fractalNoise is centred on mid grey and overlay leaves mid
   grey alone, so "amount" is just how far the noise is stretched away from it;
   the result is composited back into the group's own alpha so the noise can't
   spill past the page. baseFrequency is per user unit, so a grain size set here
   holds at every export size instead of going finer as the poster grows. */
function grainFilter(){
  if(S.grain <= 0) return "";
  const bf = f2(1/Math.max(.5, S.grainSize)), sl = f2(S.grain*3.2), ic = f2((1-S.grain*3.2)/2);
  const chan = ["R","G","B"].map(c => `<feFunc${c} type="linear" slope="${sl}" intercept="${ic}"/>`).join("");
  return `<filter id="grainFx" x="0%" y="0%" width="100%" height="100%" color-interpolation-filters="sRGB">
<feTurbulence type="fractalNoise" baseFrequency="${bf}" numOctaves="3" seed="12" stitchTiles="stitch" result="gn"/>
<feColorMatrix in="gn" type="matrix" result="gmono"
  values="0.33 0.33 0.33 0 0  0.33 0.33 0.33 0 0  0.33 0.33 0.33 0 0  0 0 0 0 1"/>
<feComponentTransfer in="gmono" result="gadj">${chan}</feComponentTransfer>
<feBlend in="gadj" in2="SourceGraphic" mode="overlay" result="gmix"/>
<feComposite in="gmix" in2="SourceGraphic" operator="in"/>
</filter>`;
}

/* ================= render ================= */
function buildSVG(res){
  res = res || {};
  const H = HH();

  /* --- art: photo or starfield, graded, then vignette and scrims --- */
  let art = "";
  if(IMG || res.imgHref){
    const href = res.imgHref || IMG.dataUrl;
    const k = Math.max(W/IMG.w, H/IMG.h) * S.imgZoom;
    const iw = IMG.w*k, ih = IMG.h*k;
    art = `<image href="${href}" x="${f2((W-iw)/2 + S.imgX)}" y="${f2((H-ih)/2 + S.imgY)}"`
        + ` width="${f2(iw)}" height="${f2(ih)}" preserveAspectRatio="none"/>`;
  }else if(S.stars){
    art = starfield(H);
  }
  const aFx = artFilter();
  const artG = art ? `<g${aFx ? ` filter="url(#artFx)"` : ""}>${art}</g>` : "";

  let overlays = "";
  if(S.vig > 0) overlays += `<rect x="0" y="0" width="${W}" height="${H}" fill="url(#vigG)"/>`;
  if(S.scrimT > 0) overlays += `<rect x="0" y="0" width="${W}" height="${f2(H*S.scrimTH)}" fill="url(#scrimTG)"/>`;
  if(S.scrimB > 0) overlays += `<rect x="0" y="${f2(H*(1-S.scrimBH))}" width="${W}" height="${f2(H*S.scrimBH)}" fill="url(#scrimBG)"/>`;

  let emblem = "";
  const EA = emblemArt();
  if(EA){
    const href = res.emHref || EA.href;
    const eh = H*S.emScale, ew = eh*EA.w/EA.h;
    emblem = `<image href="${href}" x="${f2(W/2 + S.emX - ew/2)}" y="${f2(H/2 + S.emY - eh/2)}"`
           + ` width="${f2(ew)}" height="${f2(eh)}" opacity="${f2(S.emOp)}"/>`;
  }

  /* --- type --- */
  let defs = "", type = "";
  for(const Rw of ROWS){
    const k = Rw.k;
    if(!rowGet(k,"on")) continue;
    const L = rowLayout(k);
    if(!L) continue;

    const mode = rowGet(k,"fill");
    let stops = null;
    if(mode === "metal"){
      const mk = rowGet(k,"mat") === "custom" ? rowGet(k,"matCustom") : rowGet(k,"mat");
      stops = Metal.matInfo(mk).stops;
    }else if(mode === "grad"){
      stops = [[0, rowGet(k,"col")],[1, rowGet(k,"col2")]];
    }
    /* an engraved cut has no colour of its own — it takes a darkened version of
       whatever the row was wearing, the way the plaque's engraved parts do */
    const relief = rowGet(k,"relief");
    let solid = rowGet(k,"col");
    if(relief === "emboss" && mode === "solid") solid = Metal.dkn(rowGet(k,"col"), .42);

    const rid = "r_"+k, aid = "a_"+k;
    const rf = reliefFilter(rid, relief);
    const af = auraFilter(aid, rowGet(k,"glowOn"), rowGet(k,"shadowOn"));
    defs += rf + af;

    const sw = rowGet(k,"strokeOn") ? rowGet(k,"strokeW") : 0;
    let inner = L.lines.filter(l => l.str.trim()).map((l, i) => {
      let fill = solid;
      if(stops){
        /* A userSpaceOnUse gradient is resolved in the coordinate system of the
           element that references it — i.e. *after* that element's transform.
           Each line carries translate(x, base), so feeding the ramp absolute
           canvas y put it a couple hundred units below the glyphs and clamped
           every pixel to stop[0]: gold, silver and platinum all came out the
           same pale near-white, which read as "the material picker does
           nothing". scale(sq,1) leaves y untouched, so the line's local y is
           just absolute y minus its baseline — and because every line of the
           row converts the same absolute top/bot, the ramp stays one
           continuous run across a multi-line block instead of restarting. */
        const gid = `g_${k}_${i}`;
        defs += Metal.gradientDef(gid, stops, L.top - l.base, L.bot - l.base);
        fill = `url(#${gid})`;
      }
      return textEl(l, L.x, l.base, L.anchor, L.sq, rowGet(k,"font"), fill, S.strokeCol, sw);
    }).join("");
    if(rf) inner = `<g filter="url(#${rid})">${inner}</g>`;
    if(af) inner = `<g filter="url(#${aid})">${inner}</g>`;
    type += inner;
  }

  /* backdrop, art and scrims together are what the grain lies over */
  const gFx = grainFilter();
  const backG = `<g${gFx ? ` filter="url(#grainFx)"` : ""}>`
    + `<rect x="0" y="0" width="${W}" height="${H}" fill="${S.c_back}"/>${artG}${overlays}</g>`;

  const FR = frameSVG(H);
  defs += FR.defs;

  return `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
  viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
<defs>
${res.fontCSS ? `<style>${res.fontCSS}</style>` : ""}
<clipPath id="pageClip"><rect x="0" y="0" width="${W}" height="${H}"/></clipPath>
<radialGradient id="vigG" cx="0.5" cy="0.46" r="0.78">
  <stop offset="0.35" stop-color="${S.overlayCol}" stop-opacity="0"/>
  <stop offset="1" stop-color="${S.overlayCol}" stop-opacity="${f2(S.vig)}"/>
</radialGradient>
<linearGradient id="scrimTG" x1="0" y1="0" x2="0" y2="1">
  <stop offset="0" stop-color="${S.overlayCol}" stop-opacity="${f2(S.scrimT)}"/>
  <stop offset="1" stop-color="${S.overlayCol}" stop-opacity="0"/>
</linearGradient>
<linearGradient id="scrimBG" x1="0" y1="0" x2="0" y2="1">
  <stop offset="0" stop-color="${S.overlayCol}" stop-opacity="0"/>
  <stop offset="1" stop-color="${S.overlayCol}" stop-opacity="${f2(S.scrimB)}"/>
</linearGradient>
${aFx}
${gFx}
${defs}
</defs>
<g clip-path="url(#pageClip)">
${backG}
${emblem}
${FR.body}
${type}
</g>
</svg>`;
}

function render(){ $("posterwrap").innerHTML = buildSVG(); }

/* ================= export ================= */
function usedFonts(){
  const keys = new Set();
  ROWS.forEach(R => { if(rowGet(R.k,"on") && String(rowGet(R.k,"txt")||"").trim()) keys.add(rowGet(R.k,"font")); });
  return FONTS.filter(f => keys.has(f.k));
}
async function exportResources(){
  const fonts = usedFonts();
  const EA = emblemArt();
  const [faces, emHref] = await Promise.all([
    Promise.all(fonts.map(f => ExportKit.fetchDataURI(f.url))),
    /* a built-in emblem is a URL that has to be inlined; an uploaded one is
       already a data URI */
    EA && EA.url ? ExportKit.fetchDataURI(EA.url) : Promise.resolve(EA ? EA.href : null),
  ]);
  return {
    fontCSS: fonts.map((f,i) => ExportKit.fontFaceRule(f.fam, f.w, f.st, faces[i])).join(""),
    emHref: emHref || undefined,
    imgHref: IMG ? IMG.dataUrl : undefined,
  };
}
function slug(){
  const src = String(rowGet("title","txt") || rowGet("camp","txt") || "mission").split("\n").join(" ");
  return StateKit.slugify(src, "mission");
}
async function renderToCanvas(widthPx){
  const svg = buildSVG(await exportResources());
  return ExportKit.svgToCanvas(svg, widthPx, Math.round(widthPx*HH()/W));
}
ExportKit.wireExport($("exportPng"), async ()=>{
  const size = parseInt($("exportSize").value, 10);
  const cv = await renderToCanvas(size);
  await ExportKit.downloadCanvasPNG(cv, slug()+"-"+size+".png");
});
ExportKit.wireExport($("exportSvg"), async ()=>{
  ExportKit.downloadSVG(buildSVG(await exportResources()), slug()+".svg");
}, {busy:"Building…"});

/* ================= image upload ================= */
const filebox = $("filebox"), fileInput = $("fileInput");
filebox.addEventListener("click", ()=>fileInput.click());
fileInput.addEventListener("change", e => { if(e.target.files[0]) loadImageFile(e.target.files[0]); });
["dragover","dragenter"].forEach(ev => {
  filebox.addEventListener(ev, e=>{e.preventDefault();filebox.classList.add("drag");});
  document.body.addEventListener(ev, e=>e.preventDefault());
});
["dragleave","drop"].forEach(ev => filebox.addEventListener(ev, e=>{e.preventDefault();filebox.classList.remove("drag");}));
filebox.addEventListener("drop", e => { if(e.dataTransfer.files[0]) loadImageFile(e.dataTransfer.files[0]); });
document.body.addEventListener("drop", e => { e.preventDefault(); if(e.dataTransfer.files[0]) loadImageFile(e.dataTransfer.files[0]); });
function loadImageFile(file){
  const rd = new FileReader();
  rd.onload = () => {
    const im = new Image();
    im.onload = () => {
      IMG = { dataUrl: rd.result, w: im.naturalWidth, h: im.naturalHeight };
      S.imgZoom = 1; S.imgX = 0; S.imgY = 0;
      filebox.textContent = file.name + " · " + im.naturalWidth + "×" + im.naturalHeight;
      syncUI(); render(); persist();
    };
    im.src = rd.result;
  };
  rd.readAsDataURL(file);
}
$("imgRemove").addEventListener("click", ()=>{
  IMG = null; filebox.textContent = "Drop an image here or click to choose"; render();
});
$("imgCenter").addEventListener("click", ()=>{ S.imgX=0; S.imgY=0; syncUI(); render(); persist(); });
$("starShuffle").addEventListener("click", ()=>{
  S.starSeed = Math.floor(Math.random()*1e9);
  render(); persist();
});

/* ================= custom emblem upload ================= */
const emBox = $("emBox"), emInput = $("emInput");
emBox.addEventListener("click", ()=>emInput.click());
emInput.addEventListener("change", e => { if(e.target.files[0]) loadEmblemFile(e.target.files[0]); });
["dragover","dragenter"].forEach(ev => emBox.addEventListener(ev, e=>{e.preventDefault();e.stopPropagation();emBox.classList.add("drag");}));
["dragleave","drop"].forEach(ev => emBox.addEventListener(ev, e=>{e.preventDefault();emBox.classList.remove("drag");}));
/* stopPropagation so an emblem dropped here doesn't also land in the body
   handler that loads the background artwork */
emBox.addEventListener("drop", e => {
  e.preventDefault(); e.stopPropagation();
  if(e.dataTransfer.files[0]) loadEmblemFile(e.dataTransfer.files[0]);
});
function loadEmblemFile(file){
  const rd = new FileReader();
  rd.onload = () => {
    const im = new Image();
    im.onload = () => {
      CUSTOM_EM = { dataUrl: rd.result, w: im.naturalWidth, h: im.naturalHeight };
      S.emblem = "custom";
      emBox.textContent = file.name + " · " + im.naturalWidth + "×" + im.naturalHeight;
      syncUI(); render(); persist();
    };
    im.src = rd.result;
  };
  rd.readAsDataURL(file);
}

/* ================= panel: text rows =================
   Generated from ROWS so a slot is one spec entry, not sixteen hand-written
   control rows six times over. */
const fontOpts = FONTS.map(f => `<option value="${f.k}">${esc(f.label)}</option>`).join("");
const matOpts = Object.entries(Metal.MATERIALS).map(([k,m]) => `<option value="${k}">${esc(m.name)}</option>`).join("")
              + `<option value="custom">Custom…</option>`;
function buildTextRows(){
  $("textRows").innerHTML = ROWS.map(R => {
    const k = R.k, K = p => `${k}_${p}`;
    return `
    <details class="acc" id="sec_${k}"${R.open ? " open" : ""}>
      <summary>${esc(R.label)}</summary>
      <div class="acc-body">
        <div class="row"><input type="checkbox" data-k="${K("on")}"><label>Show this line</label></div>
        <textarea data-k="${K("txt")}" rows="${R.lines}" maxlength="${R.max}" spellcheck="false"></textarea>
        <div class="row"><label>Font</label><select data-k="${K("font")}">${fontOpts}</select></div>
        <div class="row"><label>Size</label><input type="range" min="14" max="460" step="1" data-k="${K("size")}"><span class="val" data-val="${K("size")}"></span></div>
        <div class="row"><label>Tracking</label><input type="range" min="-0.08" max="0.4" step="0.005" data-k="${K("track")}"><span class="val" data-val="${K("track")}"></span></div>
        <div class="row"><label>Squeeze</label><input type="range" min="0.4" max="1.4" step="0.01" data-k="${K("sq")}"><span class="val" data-val="${K("sq")}"></span></div>
        <div class="row"><label>Leading</label><input type="range" min="0.6" max="2.2" step="0.01" data-k="${K("lead")}"><span class="val" data-val="${K("lead")}"></span></div>
        <div class="seg" data-seg="${K("align")}">
          <button data-v="left">Left</button><button data-v="center">Centre</button><button data-v="right">Right</button>
        </div>
        <div class="row"><label>From ${R.anchor === "top" ? "top" : "bottom"}</label><input type="range" min="0" max="2200" step="2" data-k="${K("y")}"><span class="val" data-val="${K("y")}"></span></div>
        <div class="row"><label>Nudge across</label><input type="range" min="-700" max="700" step="2" data-k="${K("x")}"><span class="val" data-val="${K("x")}"></span></div>

        <div class="sublabel">Fill</div>
        <div class="seg" data-seg="${K("fill")}">
          <button data-v="solid">Solid</button><button data-v="metal">Metal</button><button data-v="grad">Fade</button>
        </div>
        <div class="row"><label>Colour</label><input type="color" data-k="${K("col")}"><label style="flex:0 0 auto;color:var(--ink-dim)">to</label><input type="color" data-k="${K("col2")}"></div>
        <div class="row"><label>Material</label><select data-k="${K("mat")}">${matOpts}</select><input type="color" data-k="${K("matCustom")}"></div>

        <div class="sublabel">Effects</div>
        <div class="row"><label>Relief</label><select data-k="${K("relief")}">${RELIEFS.map(([v,l])=>`<option value="${v}">${l}</option>`).join("")}</select></div>
        <div class="row"><input type="checkbox" data-k="${K("strokeOn")}"><label>Outline</label><input type="range" min="0.5" max="40" step="0.5" data-k="${K("strokeW")}"><span class="val" data-val="${K("strokeW")}"></span></div>
        <div class="row"><input type="checkbox" data-k="${K("glowOn")}"><label>Glow</label></div>
        <div class="row"><input type="checkbox" data-k="${K("shadowOn")}"><label>Drop shadow</label></div>
        <div class="hint">Break the text over several lines with Return; the whole block takes one size, set by its longest line. <b>Squeeze</b> narrows the glyphs the way the original artwork sets its type at 90 %; <b>Material</b> and the swatch beside it apply to the Metal fill (the swatch drives the ramp when the material is <b>Custom</b>); the second colour is the Fade fill's far end. Effect strengths live in <b>Text Effects</b>.${R.note ? " " + esc(R.note) : ""}</div>
      </div>
    </details>`;
  }).join("");
}

/* ================= panel: styles ================= */
function buildPresets(){
  const groups = [];
  PRESETS.forEach(p => {
    let g = groups.find(x => x.name === p.group);
    if(!g) groups.push(g = {name:p.group, items:[]});
    g.items.push(p);
  });
  $("presetGroups").innerHTML = groups.map(g => `
    <div class="swgroup">
      <div class="swgroup-label">${esc(g.name)}</div>
      <div class="swatches">${g.items.map(p =>
        `<button class="sw" data-preset="${esc(p.name)}" title="${esc(p.name)}"
          style="background:linear-gradient(135deg,${p.sw[0]} 0 52%,${p.sw[1]} 52% 100%)"></button>`).join("")}</div>
    </div>`).join("");
  $("presetGroups").addEventListener("click", e => {
    const b = e.target.closest("[data-preset]"); if(!b) return;
    applyPreset(b.dataset.preset);
  });
}
function applyPreset(name){
  const p = PRESETS.find(x => x.name === name); if(!p) return;
  S.preset = name; S.scheme = "";
  if(p.fx) Object.assign(S, p.fx);
  ROWS.forEach(R => {
    const src = Object.assign({}, p.all, p[R.k] || {});
    Object.entries(src).forEach(([prop,v]) => { S[R.k+"_"+prop] = v; });
  });
  if(p.frame){
    Object.assign(S, { frame:"none", frFill:"solid" }, p.frame);
    if(p.frame.frame === "metal") S.frFill = "metal";
  }
  syncUI(); render(); persist();
}

/* painting is split out from binding: the swatches are repainted whenever the
   inverted toggle moves, and re-running buildSchemes would stack a second click
   handler on the container each time */
function paintSchemes(){
  const groups = [];
  SCHEMES.forEach(c => {
    let g = groups.find(x => x.name === c.group);
    if(!g) groups.push(g = {name:c.group, items:[]});
    g.items.push(c);
  });
  $("schemeGroups").innerHTML = groups.map(g => `
    <div class="swgroup">
      <div class="swgroup-label">${esc(g.name)}</div>
      <div class="swatches">${g.items.map(c => {
        const P = schemeColors(c, S.schemeMode);
        /* inverted's story is the pale field, the other two are ink against
           outline — so the swatch shows whichever pair the reading is about */
        const pair = S.schemeMode === "inv" ? [P.back, P.ink] : [P.ink, P.line];
        return `<button class="sw" data-scheme="${esc(c.name)}" title="${esc(c.name)}"
          style="background:linear-gradient(135deg,${pair[0]} 0 46%,${pair[1]} 46% 100%)"></button>`;
      }).join("")}</div>
    </div>`).join("");
}
function buildSchemes(){
  paintSchemes();
  $("schemeGroups").addEventListener("click", e => {
    const b = e.target.closest("[data-scheme]"); if(!b) return;
    applyScheme(b.dataset.scheme);
  });
  /* bindInputs also holds this segment, but it is wired later in boot and so
     fires second — set the mode here rather than reading a stale S */
  $("schemeModeSeg").addEventListener("click", e => {
    const b = e.target.closest("[data-v]"); if(!b) return;
    S.schemeMode = b.dataset.v;
    paintSchemes();
    if(S.scheme) applyScheme(S.scheme);
  });
}
function applyScheme(name){
  const c = SCHEMES.find(x => x.name === name); if(!c) return;
  const P = schemeColors(c, S.schemeMode);
  S.scheme = name;
  S.strokeCol = P.line;
  S.glowCol = P.glow;
  S.c_back = P.back;
  S.starFore = P.star;
  S.overlayCol = P.overlay;
  S.frCol = P.frame;
  S.frMatCustom = P.frame;
  if(S.frFill === "metal") S.frMat = "custom";
  ROWS.forEach(R => {
    S[R.k+"_col"] = P.ink;
    S[R.k+"_col2"] = P.fade;
    /* a metal row follows the scheme by driving Metal's custom ramp off the
       theme colour, rather than staying gold and looking like the click did
       nothing. The ramp gets `band`, not the ink: the ink is often plain white
       (or, inverted, near-black), and white foil would read as "the scheme was
       ignored". */
    S[R.k+"_matCustom"] = P.ramp;
    if(S[R.k+"_fill"] === "metal") S[R.k+"_mat"] = "custom";
  });
  syncUI(); render(); persist();
}

/* ================= wiring ================= */
function bindInputs(){
  document.querySelectorAll("[data-k]").forEach(inp=>{
    if(inp.dataset.bound) return;
    inp.dataset.bound = "1";
    const k = inp.dataset.k;
    const ev = (inp.tagName === "SELECT") ? "change" : "input";
    inp.addEventListener(ev, ()=>{
      if(inp.type === "range") S[k] = parseFloat(inp.value);
      else if(inp.type === "checkbox") S[k] = inp.checked;
      else S[k] = inp.value;
      syncVals(); syncEnable(); render(); persist();
    });
  });
  document.querySelectorAll("[data-seg]").forEach(seg=>{
    if(seg.dataset.bound) return;
    seg.dataset.bound = "1";
    seg.addEventListener("click", e=>{
      const b = e.target.closest("[data-v]"); if(!b) return;
      S[seg.dataset.seg] = b.dataset.v;
      syncUI(); render(); persist();
    });
  });
}
function syncVals(){
  document.querySelectorAll("[data-val]").forEach(sp=>{
    const v = S[sp.dataset.val];
    sp.textContent = (typeof v === "number" && !Number.isInteger(v)) ? v.toFixed(2) : v;
  });
}
/* dim what has no effect right now, so the panel says what it is doing */
function syncEnable(){
  const E = EMBLEMS[S.emblem];
  $("emBox").style.display = (E && E.custom) ? "" : "none";
  const starOn = !!S.stars;
  $("starShuffle").disabled = !starOn;
  ROWS.forEach(R => {
    const sec = $("sec_"+R.k);
    if(sec) sec.classList.toggle("rowoff", !rowGet(R.k,"on"));
  });
  const framed = S.frame !== "none";
  const fbody = $("frameSel").closest(".acc-body");
  fbody.querySelectorAll(".row, .sublabel, .seg").forEach(el=>{
    if(el.querySelector("#frameSel")) return;
    el.classList.toggle("off", !framed);
  });
}
function syncUI(){
  document.querySelectorAll("[data-k]").forEach(inp=>{
    const k = inp.dataset.k;
    if(!(k in S)) return;
    if(inp.type === "checkbox") inp.checked = !!S[k];
    else inp.value = S[k];
  });
  document.querySelectorAll("[data-seg]").forEach(seg=>{
    const v = S[seg.dataset.seg];
    seg.querySelectorAll("[data-v]").forEach(b => b.classList.toggle("on", b.dataset.v === v));
  });
  document.querySelectorAll("[data-preset]").forEach(b =>
    b.classList.toggle("on", b.dataset.preset === S.preset));
  document.querySelectorAll("[data-scheme]").forEach(b =>
    b.classList.toggle("on", b.dataset.scheme === S.scheme));
  syncVals(); syncEnable();
}

$("frameSel").innerHTML = Object.entries(FRAMES).map(([k,l])=>`<option value="${k}">${esc(l)}</option>`).join("");
document.querySelectorAll("[data-mats]").forEach(sel => { sel.innerHTML = matOpts; });
$("emblemSel").innerHTML = (() => {
  let html = "", open = "";
  for(const [k,E] of Object.entries(EMBLEMS)){
    if(E.group !== open){
      if(open) html += "</optgroup>";
      open = E.group;
      if(open) html += `<optgroup label="${esc(open)}">`;
    }
    html += `<option value="${k}">${esc(E.label)}</option>`;
  }
  return html + (open ? "</optgroup>" : "");
})();

Stage.init();
$("resetBtn").addEventListener("click", ()=>{
  S = JSON.parse(JSON.stringify(DEFAULTS));
  syncUI(); render(); persist();
});

/* ================= persistence ================= */
const LS_KEY = "missionbuilder-v2";
const LS_OLD = ["missionbuilder-v1"];
const persist = StateKit.persister(LS_KEY, () => S, 150);
(function boot(){
  buildTextRows(); buildPresets(); buildSchemes();
  try{
    let raw = localStorage.getItem(LS_KEY), old = false;
    for(const k of LS_OLD){ if(raw) break; raw = localStorage.getItem(k); old = !!raw; }
    if(raw){
      const j = JSON.parse(raw);
      /* v1 carried a single inverted flag; the three readings replaced it */
      if(old) j.schemeMode = j.schemeInv ? "inv" : "std";
      delete j.schemeInv;
      S = Object.assign(JSON.parse(JSON.stringify(DEFAULTS)), j);
    }
  }catch(e){}
  if(!RATIOS[S.ratio]) S.ratio = DEFAULTS.ratio;
  $("copyYear").textContent = new Date().getFullYear();
  bindInputs(); syncUI(); render();
  /* anything measured before the webfonts land was measured in fallback metrics */
  if(document.fonts && document.fonts.ready) document.fonts.ready.then(()=>{ measCache.clear(); boxCache.clear(); render(); });
})();
</script>
</body>
</html>

/* Shared text measurement. Plain script, no modules: hangs off window.TextKit.

   Measure in SVG, not on a canvas 2D context. Every tool in this suite lays
   its type out in SVG, and the two engines disagree — Safari's canvas runs
   Sealstile wide enough to trip the plaque's registry-line fit and shrink a
   line that the SVG it actually draws does not. So this measures with a
   hidden live <text> node and asks it for getComputedTextLength().

   Widths are cached per (family, weight, style, text) at a reference size and
   scaled, so a slider drag costs one measurement per new string rather than
   one per frame. The cache is cleared on document.fonts.ready, because
   anything measured before the webfonts land is fallback metrics.

   Tracking: CSS letter-spacing advances after the *last* glyph too, so the
   browser-accurate count is text.length. Some callers historically counted
   length-1 instead; pass {gaps:true} to keep that, rather than silently
   changing a layout that was calibrated against it. */
"use strict";
const TextKit = (() => {

  const REF = 100;

  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute("aria-hidden", "true");
  svg.setAttribute("style", "position:absolute;left:-9999px;top:0;width:10px;height:10px;overflow:hidden");
  const node = document.createElementNS("http://www.w3.org/2000/svg", "text");
  svg.appendChild(node);
  const attach = () => document.body.appendChild(svg);
  if(document.body) attach(); else document.addEventListener("DOMContentLoaded", attach);

  const cache = new Map();

  /* Width of text at the REF font size, cached. */
  function raw(text, family, weight, style){
    family = family || "Sealstile"; weight = weight || 400; style = style || "normal";
    const key = family + "|" + weight + "|" + style + "|" + text;
    let w = cache.get(key);
    if(w === undefined){
      node.setAttribute("font-family", family);
      node.setAttribute("font-weight", weight);
      node.setAttribute("font-style", style);
      node.setAttribute("font-size", REF);
      node.textContent = text;
      w = node.getComputedTextLength();
      cache.set(key, w);
    }
    return w;
  }

  /* Width at font size fs, including tracking.
     opts: { family, weight, style, track, gaps } */
  function measure(text, fs, opts){
    const o = opts || {};
    const track = o.track || 0;
    const n = o.gaps ? Math.max(0, text.length - 1) : text.length;
    return raw(text, o.family, o.weight, o.style) * (fs / REF) + n * track;
  }

  function clear(){ cache.clear(); }

  if(document.fonts && document.fonts.ready) document.fonts.ready.then(clear);

  return { REF, raw, measure, clear, cache };
})();

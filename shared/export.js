/* Asset + export helpers shared by every tool that loads assets by URL.
   Plain script, no modules: everything hangs off window.ExportKit.

   The live preview is inline SVG in the DOM, so it can reference fonts and
   images by relative URL. Exports go through an SVG string loaded into an
   <img>, which CANNOT fetch external resources — so every font/image must be
   inlined as a data URI first (that's what fetchDataURI is for). */
"use strict";
const ExportKit = (() => {

  const duriCache = new Map();
  /* Relative/absolute URL -> "data:...;base64,..." promise, memoized.

     Only *successful* fetches stay cached. Caching a rejected promise would
     mean one flaky request permanently broke every later export in the tab —
     the retry would keep handing back the same rejection until a reload. */
  function fetchDataURI(url){
    if(!duriCache.has(url)){
      const p = fetch(url)
        .then(r => { if(!r.ok) throw new Error(`fetch ${url}: ${r.status}`); return r.blob(); })
        .then(blob => new Promise((res, rej) => {
          const fr = new FileReader();
          fr.onload = () => res(fr.result);
          fr.onerror = () => rej(new Error(`read ${url}`));
          fr.readAsDataURL(blob);
        }))
        .catch(err => { duriCache.delete(url); throw err; });
      duriCache.set(url, p);
    }
    return duriCache.get(url);
  }

  /* Resolve several URLs at once into a { url: dataURI } map. */
  function fetchDataURIs(urls){
    const list = [...new Set(urls)].filter(Boolean);
    return Promise.all(list.map(u => fetchDataURI(u).then(d => [u, d])))
      .then(pairs => Object.fromEntries(pairs));
  }

  function fontFaceRule(family, weight, style, src){
    return `@font-face{font-family:"${family}";font-weight:${weight};font-style:${style};src:url(${src}) format("woff2");}`;
  }

  /* SVG string -> canvas of exactly wPx x hPx. */
  function svgToCanvas(svg, wPx, hPx){
    return new Promise((resolve, reject) => {
      const blob = new Blob([svg], {type:"image/svg+xml"});
      const url = URL.createObjectURL(blob);
      const im = new Image();
      im.onload = () => {
        /* small delay lets embedded fonts finish activating inside the SVG image */
        setTimeout(() => {
          const cv = document.createElement("canvas");
          cv.width = wPx; cv.height = hPx;
          cv.getContext("2d").drawImage(im, 0, 0, wPx, hPx);
          URL.revokeObjectURL(url);
          resolve(cv);
        }, 80);
      };
      im.onerror = e => { URL.revokeObjectURL(url); reject(e); };
      im.src = url;
    });
  }

  function download(url, name){
    const a = document.createElement("a");
    a.href = url; a.download = name;
    document.body.appendChild(a); a.click(); a.remove();
  }

  function downloadSVG(svg, name){
    const url = URL.createObjectURL(new Blob([svg], {type:"image/svg+xml"}));
    download(url, name);
    setTimeout(() => URL.revokeObjectURL(url), 5000);
  }

  function downloadCanvasPNG(cv, name){
    return new Promise(res => cv.toBlob(blob => {
      download(URL.createObjectURL(blob), name);
      res();
    }, "image/png"));
  }

  /* Wire up an export button.

     Every tool had this same block copy-pasted, and each copy had two holes:
     the SVG button had no try/catch at all (a failed asset fetch surfaced as
     an unhandled rejection and a button that just did nothing), and the
     failure label cleared after 400ms — quicker than anyone can read. Failure
     now holds long enough to notice and parks the reason on the tooltip. */
  function wireExport(btn, run, opts){
    if(typeof btn === "string") btn = document.getElementById(btn);
    if(!btn) return;
    const busyLabel = (opts && opts.busy) || "Rendering…";
    let running = false;
    btn.addEventListener("click", async () => {
      if(running) return;              /* double-click shouldn't start two renders */
      running = true;
      const label = btn.textContent;
      btn.textContent = busyLabel; btn.disabled = true; btn.removeAttribute("title");
      let hold = 400;
      try{
        await run();
      }catch(err){
        console.error(err);
        btn.textContent = "Export failed";
        btn.title = String((err && err.message) || err);
        hold = 2600;
      }
      setTimeout(() => {
        btn.textContent = label; btn.disabled = false; running = false;
      }, hold);
    });
  }

  return { fetchDataURI, fetchDataURIs, fontFaceRule, svgToCanvas,
           download, downloadSVG, downloadCanvasPNG, wireExport };
})();

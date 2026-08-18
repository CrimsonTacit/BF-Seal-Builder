/* Asset + export helpers shared by banner-tool.html and plaque-tool.html.
   Plain script, no modules: everything hangs off window.ExportKit.

   The live preview is inline SVG in the DOM, so it can reference fonts and
   images by relative URL. Exports go through an SVG string loaded into an
   <img>, which CANNOT fetch external resources — so every font/image must be
   inlined as a data URI first (that's what fetchDataURI is for). */
"use strict";
const ExportKit = (() => {

  const duriCache = new Map();
  /* Relative/absolute URL -> "data:...;base64,..." promise, memoized. */
  function fetchDataURI(url){
    if(!duriCache.has(url)){
      duriCache.set(url, fetch(url)
        .then(r => { if(!r.ok) throw new Error(`fetch ${url}: ${r.status}`); return r.blob(); })
        .then(blob => new Promise((res, rej) => {
          const fr = new FileReader();
          fr.onload = () => res(fr.result);
          fr.onerror = rej;
          fr.readAsDataURL(blob);
        })));
    }
    return duriCache.get(url);
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

  return { fetchDataURI, fontFaceRule, svgToCanvas, download, downloadSVG, downloadCanvasPNG };
})();
